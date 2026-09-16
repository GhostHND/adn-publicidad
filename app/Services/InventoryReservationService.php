<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryReservationService
{
    public function syncForWorkOrder(
        WorkOrder $workOrder
    ): void {
        if (
            !in_array(
                $workOrder->status,
                [
                    'draft',
                    'pending',
                ],
                true
            )
        ) {
            return;
        }

        DB::transaction(
            function () use ($workOrder) {
                $materialIds =
                    WorkOrderMaterial::query()
                        ->where(
                            'work_order_id',
                            $workOrder->id
                        )
                        ->pluck('id');

                /*
                |--------------------------------------------------------------------------
                | Liberar reservas que correspondían a materiales eliminados
                |--------------------------------------------------------------------------
                */

                $staleReservations =
                    InventoryReservation::query()
                        ->where(
                            'work_order_id',
                            $workOrder->id
                        )
                        ->whereNotIn(
                            'work_order_material_id',
                            $materialIds
                        )
                        ->where(
                            'status',
                            '!=',
                            'consumed'
                        )
                        ->get();

                foreach (
                    $staleReservations
                    as $reservation
                ) {
                    $reservation->update([
                        'quantity_reserved' =>
                            0,

                        'status' =>
                            'released',

                        'released_at' =>
                            now(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Recalcular cada material de la OT
                |--------------------------------------------------------------------------
                */

                $materials =
                    WorkOrderMaterial::query()
                        ->where(
                            'work_order_id',
                            $workOrder->id
                        )
                        ->orderBy('id')
                        ->get();

                foreach (
                    $materials
                    as $material
                ) {
                    if (
                        (float)
                        $material
                            ->quantity_consumed
                        > 0
                    ) {
                        continue;
                    }

                    $this
                        ->syncMaterialReservation(
                            $material
                        );
                }
            }
        );
    }

    /**
     * Reintenta completar todas las reservas pendientes que utilicen
     * un determinado material.
     *
     * Se utilizará automáticamente cuando aumente el inventario.
     */
    public function refreshPendingForInventoryItem(
        InventoryItem $inventoryItem
    ): void {
        DB::transaction(
            function () use (
                $inventoryItem
            ) {
                /*
                |--------------------------------------------------------------------------
                | Procesar primero las órdenes más antiguas
                |--------------------------------------------------------------------------
                */

                $workOrders =
                    WorkOrder::query()
                        ->whereIn(
                            'status',
                            [
                                'draft',
                                'pending',
                            ]
                        )
                        ->whereHas(
                            'materials',
                            function (
                                $query
                            ) use (
                                $inventoryItem
                            ) {
                                $query
                                    ->where(
                                        'inventory_item_id',
                                        $inventoryItem->id
                                    )
                                    ->where(
                                        'quantity_consumed',
                                        '<=',
                                        0
                                    );
                            }
                        )
                        ->orderBy(
                            'order_date'
                        )
                        ->orderBy('id')
                        ->get();

                foreach (
                    $workOrders
                    as $workOrder
                ) {
                    $this
                        ->syncForWorkOrder(
                            $workOrder
                        );
                }
            }
        );
    }

    /**
     * Actualiza solamente la reserva correspondiente a una línea
     * de material de una OT.
     */
    private function syncMaterialReservation(
        WorkOrderMaterial $material
    ): InventoryReservation {
        $inventoryItem =
            InventoryItem::query()
                ->whereKey(
                    $material
                        ->inventory_item_id
                )
                ->lockForUpdate()
                ->firstOrFail();

        $existingReservation =
            InventoryReservation::query()
                ->where(
                    'work_order_material_id',
                    $material->id
                )
                ->lockForUpdate()
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Material ya comprometido por otras órdenes
        |--------------------------------------------------------------------------
        */

        $otherReservationsQuery =
            InventoryReservation::query()
                ->where(
                    'inventory_item_id',
                    $inventoryItem->id
                )
                ->whereIn(
                    'status',
                    [
                        'reserved',
                        'partial',
                    ]
                );

        if ($existingReservation) {
            $otherReservationsQuery
                ->where(
                    'id',
                    '!=',
                    $existingReservation->id
                );
        }

        $reservedByOthers =
            (float) (
                $otherReservationsQuery
                    ->selectRaw(
                        '
                        COALESCE(
                            SUM(
                                GREATEST(
                                    quantity_reserved
                                    -
                                    quantity_consumed,
                                    0
                                )
                            ),
                            0
                        ) AS total_reserved
                        '
                    )
                    ->value(
                        'total_reserved'
                    )
                ?? 0
            );

        $physicalStock =
            max(
                round(
                    (float)
                    $inventoryItem
                        ->current_stock,
                    3
                ),
                0
            );

        /*
        |--------------------------------------------------------------------------
        | Existencia que esta OT puede reservar
        |--------------------------------------------------------------------------
        */

        $availableForThisOrder =
            max(
                round(
                    $physicalStock
                    -
                    $reservedByOthers,
                    3
                ),
                0
            );

        $required =
            max(
                round(
                    (float)
                    $material
                        ->quantity_planned,
                    3
                ),
                0
            );

        $reserved =
            round(
                min(
                    $required,
                    $availableForThisOrder
                ),
                3
            );

        /*
        |--------------------------------------------------------------------------
        | Determinar estado
        |--------------------------------------------------------------------------
        */

        if ($required <= 0) {
            $status =
                'released';
        } elseif (
            $reserved >=
            $required
        ) {
            $status =
                'reserved';
        } elseif (
            $reserved > 0
        ) {
            $status =
                'partial';
        } else {
            $status =
                'unavailable';
        }

        return InventoryReservation::updateOrCreate(
            [
                'work_order_material_id' =>
                    $material->id,
            ],
            [
                'work_order_id' =>
                    $material
                        ->work_order_id,

                'inventory_item_id' =>
                    $inventoryItem
                        ->id,

                'quantity_required' =>
                    $required,

                'quantity_reserved' =>
                    $reserved,

                'quantity_consumed' =>
                    0,

                'status' =>
                    $status,

                'reserved_at' =>
                    $reserved > 0
                        ? (
                            $existingReservation
                                ?->reserved_at
                            ?? now()
                        )
                        : null,

                'consumed_at' =>
                    null,

                'released_at' =>
                    $status ===
                    'released'
                        ? now()
                        : null,
            ]
        );
    }

    public function refreshAndAssertReady(
        WorkOrder $workOrder
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Antes de producir hacemos un último intento de reserva
        |--------------------------------------------------------------------------
        */

        $this
            ->syncForWorkOrder(
                $workOrder
            );

        $materials =
            WorkOrderMaterial::query()
                ->with([
                    'inventoryItem',
                    'reservation',
                ])
                ->where(
                    'work_order_id',
                    $workOrder->id
                )
                ->get();

        $missing = [];

        foreach (
            $materials
            as $material
        ) {
            $required =
                max(
                    (float)
                    $material
                        ->quantity_planned,
                    0
                );

            if ($required <= 0) {
                continue;
            }

            $reserved =
                (float) (
                    $material
                        ->reservation
                        ?->quantity_reserved
                    ?? 0
                );

            $missingQuantity =
                max(
                    round(
                        $required
                        -
                        $reserved,
                        3
                    ),
                    0
                );

            if (
                $missingQuantity > 0
            ) {
                $name =
                    $material
                        ->inventoryItem
                        ?->name
                    ?? 'Material';

                $unit =
                    $material
                        ->inventoryItem
                        ?->measurement_unit
                    ?? '';

                $missing[] =
                    $name .
                    ': faltan ' .
                    number_format(
                        $missingQuantity,
                        3
                    ) .
                    (
                        $unit
                            ? ' ' . $unit
                            : ''
                    );
            }
        }

        if (!empty($missing)) {
            throw ValidationException::withMessages([
                'inventory' =>
                    'No puedes iniciar producción porque faltan materiales: ' .
                    implode(
                        ' | ',
                        $missing
                    ),
            ]);
        }
    }

    public function consumeForWorkOrder(
        WorkOrder $workOrder,
        ?int $userId = null
    ): void {
        DB::transaction(
            function () use (
                $workOrder,
                $userId
            ) {
                $this
                    ->refreshAndAssertReady(
                        $workOrder
                    );

                $materials =
                    WorkOrderMaterial::query()
                        ->where(
                            'work_order_id',
                            $workOrder->id
                        )
                        ->orderBy('id')
                        ->get();

                foreach (
                    $materials
                    as $material
                ) {
                    if (
                        (float)
                        $material
                            ->quantity_consumed
                        > 0
                    ) {
                        continue;
                    }

                    $quantity =
                        max(
                            round(
                                (float)
                                $material
                                    ->quantity_planned,
                                3
                            ),
                            0
                        );

                    if ($quantity <= 0) {
                        continue;
                    }

                    $inventoryItem =
                        InventoryItem::query()
                            ->whereKey(
                                $material
                                    ->inventory_item_id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    $reservation =
                        InventoryReservation::query()
                            ->where(
                                'work_order_material_id',
                                $material->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    if (
                        (float)
                        $reservation
                            ->quantity_reserved
                        <
                        $quantity
                    ) {
                        throw ValidationException::withMessages([
                            'inventory' =>
                                'La reserva de "' .
                                $inventoryItem
                                    ->name .
                                '" ya no cubre la cantidad requerida.',
                        ]);
                    }

                    $previousStock =
                        round(
                            (float)
                            $inventoryItem
                                ->current_stock,
                            3
                        );

                    if (
                        $previousStock
                        <
                        $quantity
                    ) {
                        throw ValidationException::withMessages([
                            'inventory' =>
                                'El inventario físico de "' .
                                $inventoryItem
                                    ->name .
                                '" ya no es suficiente.',
                        ]);
                    }

                    $resultingStock =
                        round(
                            $previousStock
                            -
                            $quantity,
                            3
                        );

                    InventoryMovement::create([
                        'inventory_item_id' =>
                            $inventoryItem->id,

                        'created_by' =>
                            $userId,

                        'movement_type' =>
                            'exit',

                        'quantity' =>
                            $quantity,

                        'previous_stock' =>
                            $previousStock,

                        'resulting_stock' =>
                            $resultingStock,

                        'unit_cost' =>
                            $inventoryItem
                                ->unit_cost,

                        'reference' =>
                            $workOrder
                                ->work_order_number,

                        'notes' =>
                            'Consumo automático de material reservado para ' .
                            $workOrder
                                ->work_order_number .
                            '.',

                        'moved_at' =>
                            now(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Esto dispara InventoryItemObserver, pero como el stock baja
                    | no intentará recalcular reservas pendientes.
                    |--------------------------------------------------------------------------
                    */

                    $inventoryItem->update([
                        'current_stock' =>
                            $resultingStock,
                    ]);

                    $material->update([
                        'quantity_consumed' =>
                            $quantity,

                        'unit_cost_snapshot' =>
                            $inventoryItem
                                ->unit_cost,

                        'consumed_at' =>
                            now(),
                    ]);

                    $reservation->update([
                        'quantity_consumed' =>
                            $quantity,

                        'status' =>
                            'consumed',

                        'consumed_at' =>
                            now(),
                    ]);
                }
            }
        );
    }

    public function releaseForWorkOrder(
        WorkOrder $workOrder
    ): void {
        InventoryReservation::query()
            ->where(
                'work_order_id',
                $workOrder->id
            )
            ->whereIn(
                'status',
                [
                    'reserved',
                    'partial',
                    'unavailable',
                ]
            )
            ->update([
                'quantity_reserved' =>
                    0,

                'status' =>
                    'released',

                'released_at' =>
                    now(),
            ]);
    }
}