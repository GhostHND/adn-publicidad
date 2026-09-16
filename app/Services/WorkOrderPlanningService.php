<?php

namespace App\Services;

use App\Models\CatalogMaterialRecipe;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;

class WorkOrderPlanningService
{
    public function __construct(
        private InventoryReservationService $reservationService
    ) {
    }

    public function syncAutomaticMaterials(
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

        $workOrder->loadMissing([
            'items.catalogItem.materialRecipes.inventoryItem',
        ]);

        $calculatedMaterials = [];

        foreach (
            $workOrder->items
            as $workOrderItem
        ) {
            $catalogItem =
                $workOrderItem
                    ->catalogItem;

            if (!$catalogItem) {
                continue;
            }

            foreach (
                $catalogItem
                    ->materialRecipes
                    ->where(
                        'active',
                        true
                    )
                as $recipe
            ) {
                $quantity =
                    $this
                        ->calculateMaterialQuantity(
                            $workOrderItem,
                            $recipe
                        );

                if ($quantity <= 0) {
                    continue;
                }

                $inventoryItemId =
                    $recipe
                        ->inventory_item_id;

                if (
                    !isset(
                        $calculatedMaterials[
                            $inventoryItemId
                        ]
                    )
                ) {
                    $calculatedMaterials[
                        $inventoryItemId
                    ] = [
                        'quantity' => 0,

                        'unit_cost' =>
                            (float)
                            $recipe
                                ->inventoryItem
                                ->unit_cost,

                        'details' => [],
                    ];
                }

                $calculatedMaterials[
                    $inventoryItemId
                ]['quantity'] +=
                    $quantity;

                $calculatedMaterials[
                    $inventoryItemId
                ]['details'][] =
                    $workOrderItem
                        ->item_name .
                    ': ' .
                    number_format(
                        $quantity,
                        3
                    ) .
                    ' ' .
                    $recipe
                        ->inventoryItem
                        ->measurement_unit;
            }
        }

        $automaticMaterials =
            WorkOrderMaterial::query()
                ->where(
                    'work_order_id',
                    $workOrder->id
                )
                ->where(
                    'source',
                    'catalog_recipe'
                )
                ->where(
                    'quantity_consumed',
                    0
                );

        if (
            count(
                $calculatedMaterials
            ) > 0
        ) {
            $automaticMaterials
                ->whereNotIn(
                    'inventory_item_id',
                    array_keys(
                        $calculatedMaterials
                    )
                )
                ->delete();
        } else {
            $automaticMaterials
                ->delete();
        }

        foreach (
            $calculatedMaterials
            as $inventoryItemId =>
                $materialData
        ) {
            $existing =
                WorkOrderMaterial::query()
                    ->where(
                        'work_order_id',
                        $workOrder->id
                    )
                    ->where(
                        'inventory_item_id',
                        $inventoryItemId
                    )
                    ->first();

            if (
                $existing
                &&
                (float)
                $existing
                    ->quantity_consumed
                > 0
            ) {
                continue;
            }

            WorkOrderMaterial::updateOrCreate(
                [
                    'work_order_id' =>
                        $workOrder->id,

                    'inventory_item_id' =>
                        $inventoryItemId,
                ],
                [
                    'source' =>
                        'catalog_recipe',

                    'quantity_planned' =>
                        round(
                            $materialData[
                                'quantity'
                            ],
                            3
                        ),

                    'quantity_consumed' =>
                        0,

                    'unit_cost_snapshot' =>
                        $materialData[
                            'unit_cost'
                        ],

                    'consumed_at' =>
                        null,

                    'notes' =>
                        'Calculado automáticamente desde el catálogo.' .
                        PHP_EOL .
                        implode(
                            PHP_EOL,
                            $materialData[
                                'details'
                            ]
                        ),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Una vez calculados los materiales, reservarlos automáticamente
        |--------------------------------------------------------------------------
        */

        $this
            ->reservationService
            ->syncForWorkOrder(
                $workOrder
            );
    }

    private function calculateMaterialQuantity(
        object $workOrderItem,
        CatalogMaterialRecipe $recipe
    ): float {
        $quantitySold =
            max(
                (float)
                $workOrderItem
                    ->quantity,
                0
            );

        $width =
            max(
                (float)
                (
                    $workOrderItem
                        ->width
                    ?? 0
                ),
                0
            );

        $height =
            max(
                (float)
                (
                    $workOrderItem
                        ->height
                    ?? 0
                ),
                0
            );

        $rate =
            max(
                (float)
                $recipe
                    ->quantity_rate,
                0
            );

        $baseQuantity =
            match (
                $recipe
                    ->calculation_method
            ) {
                'AREA' =>
                    $width
                    * $height
                    * $quantitySold
                    * $rate,

                'LINEAR' =>
                    $width
                    * $quantitySold
                    * $rate,

                'FIXED' =>
                    $rate,

                default =>
                    $quantitySold
                    * $rate,
            };

        $waste =
            max(
                (float)
                $recipe
                    ->waste_percentage,
                0
            );

        if ($waste > 0) {
            $baseQuantity *=
                1 +
                (
                    $waste / 100
                );
        }

        return round(
            $baseQuantity,
            3
        );
    }
}