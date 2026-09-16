<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class BusinessAutomationService
{
    public function __construct(
        private TaskAutomationService $taskAutomationService,
        private WorkOrderPlanningService $workOrderPlanningService
    ) {
    }

    public function processApprovedQuotation(
        Quotation $quotation
    ): Sale {
        return DB::transaction(
            function () use ($quotation) {
                $quotation->loadMissing([
                    'client',
                    'items',
                ]);

                $sale =
                    $this
                        ->createOrFindSale(
                            $quotation
                        );

                $this->syncSaleItems(
                    $sale,
                    $quotation
                );

                $workOrder =
                    $this
                        ->createOrFindWorkOrder(
                            $sale,
                            $quotation
                        );

                $this->syncWorkOrderItems(
                    $workOrder,
                    $sale
                );

                /*
                |--------------------------------------------------------------------------
                | Materiales automáticos según recetas del catálogo
                |--------------------------------------------------------------------------
                */

                $this
                    ->workOrderPlanningService
                    ->syncAutomaticMaterials(
                        $workOrder
                    );

                /*
                |--------------------------------------------------------------------------
                | Tareas automáticas según flujo del catálogo
                |--------------------------------------------------------------------------
                */

                $this
                    ->taskAutomationService
                    ->generateForWorkOrder(
                        $workOrder
                    );

                return $sale;
            }
        );
    }

    private function createOrFindSale(
        Quotation $quotation
    ): Sale {
        $existingSale =
            Sale::query()
                ->where(
                    'quotation_id',
                    $quotation->id
                )
                ->first();

        if ($existingSale) {
            return $existingSale;
        }

        return Sale::create([
            'sale_number' =>
                $this
                    ->nextSaleNumber(),

            'client_id' =>
                $quotation
                    ->client_id,

            'quotation_id' =>
                $quotation->id,

            'created_by' =>
                $quotation
                    ->created_by
                ?? auth()->id(),

            'sale_date' =>
                now()
                    ->toDateString(),

            'status' =>
                'pending',

            'subtotal' =>
                $quotation
                    ->subtotal,

            'discount' =>
                $quotation
                    ->discount,

            'total' =>
                $quotation
                    ->total,

            'estimated_cost' =>
                $quotation
                    ->estimated_cost,

            'notes' =>
                $quotation
                    ->notes,
        ]);
    }

    private function syncSaleItems(
        Sale $sale,
        Quotation $quotation
    ): void {
        foreach (
            $quotation->items
            as $index =>
                $quotationItem
        ) {
            $quantity =
                max(
                    (float)
                    $quotationItem
                        ->quantity,
                    0.000001
                );

            $unitPrice =
                $this
                    ->resolveQuotationUnitPrice(
                        $quotationItem
                    );

            $unitCost =
                $this
                    ->resolveQuotationUnitCost(
                        $quotationItem
                    );

            $sale
                ->items()
                ->updateOrCreate(
                    [
                        'sort_order' =>
                            $index + 1,
                    ],
                    [
                        'catalog_item_id' =>
                            $quotationItem
                                ->catalog_item_id,

                        'item_code' =>
                            $quotationItem
                                ->item_code,

                        'item_name' =>
                            $quotationItem
                                ->item_name,

                        'description' =>
                            $quotationItem
                                ->description,

                        'pricing_method' =>
                            $quotationItem
                                ->pricing_method,

                        'measurement_unit' =>
                            $quotationItem
                                ->measurement_unit,

                        'quantity' =>
                            $quantity,

                        'width' =>
                            $quotationItem
                                ->width,

                        'height' =>
                            $quotationItem
                                ->height,

                        'unit_price' =>
                            $unitPrice,

                        'unit_cost' =>
                            $unitCost,

                        'sale_rate' =>
                            $quotationItem
                                ->sale_rate,

                        'cost_rate' =>
                            $quotationItem
                                ->cost_rate,

                        'subtotal' =>
                            round(
                                (float)
                                $quotationItem
                                    ->subtotal,
                                2
                            ),

                        'estimated_cost' =>
                            round(
                                (float)
                                $quotationItem
                                    ->estimated_cost,
                                2
                            ),
                    ]
                );
        }

        $sale->load('items');
    }

    private function createOrFindWorkOrder(
        Sale $sale,
        Quotation $quotation
    ): WorkOrder {
        $existingOrder =
            WorkOrder::query()
                ->where(
                    'sale_id',
                    $sale->id
                )
                ->first();

        if ($existingOrder) {
            return $existingOrder;
        }

        $responsibleEmployeeId =
            $this
                ->resolveDefaultOperator();

        return WorkOrder::create([
            'work_order_number' =>
                $this
                    ->nextWorkOrderNumber(),

            'client_id' =>
                $sale->client_id,

            'sale_id' =>
                $sale->id,

            'responsible_employee_id' =>
                $responsibleEmployeeId,

            'created_by' =>
                $sale->created_by
                ?? $quotation
                    ->created_by
                ?? auth()->id(),

            'title' =>
                $this
                    ->buildWorkOrderTitle(
                        $quotation
                    ),

            'status' =>
                'pending',

            'priority' =>
                'normal',

            'order_date' =>
                now()
                    ->toDateString(),

            'due_date' =>
                null,

            'description' =>
                $this
                    ->buildWorkOrderDescription(
                        $quotation
                    ),

            'internal_notes' =>
                'Orden creada automáticamente al aprobar la cotización ' .
                $quotation
                    ->quotation_number .
                '.',
        ]);
    }

    private function syncWorkOrderItems(
        WorkOrder $workOrder,
        Sale $sale
    ): void {
        $sale->loadMissing(
            'items'
        );

        foreach (
            $sale->items
            as $index =>
                $saleItem
        ) {
            $quantity =
                max(
                    (float)
                    $saleItem
                        ->quantity,
                    0.000001
                );

            $unitPrice =
                $saleItem
                    ->unit_price
                !== null
                    ? round(
                        (float)
                        $saleItem
                            ->unit_price,
                        2
                    )
                    : round(
                        (float)
                        $saleItem
                            ->subtotal
                        / $quantity,
                        2
                    );

            $subtotal =
                $saleItem
                    ->subtotal
                !== null
                    ? round(
                        (float)
                        $saleItem
                            ->subtotal,
                        2
                    )
                    : round(
                        $unitPrice
                        * $quantity,
                        2
                    );

            $workOrder
                ->items()
                ->updateOrCreate(
                    [
                        'sort_order' =>
                            $index + 1,
                    ],
                    [
                        'catalog_item_id' =>
                            $saleItem
                                ->catalog_item_id,

                        'item_code' =>
                            $saleItem
                                ->item_code,

                        'item_name' =>
                            $saleItem
                                ->item_name,

                        'description' =>
                            $saleItem
                                ->description,

                        'pricing_method' =>
                            $saleItem
                                ->pricing_method,

                        'measurement_unit' =>
                            $saleItem
                                ->measurement_unit,

                        'width' =>
                            $saleItem
                                ->width,

                        'height' =>
                            $saleItem
                                ->height,

                        'quantity' =>
                            $quantity,

                        'unit_price' =>
                            $unitPrice,

                        'subtotal' =>
                            $subtotal,
                    ]
                );
        }

        $workOrder->load(
            'items'
        );
    }

    private function resolveQuotationUnitPrice(
        object $quotationItem
    ): float {
        if (
            $quotationItem
                ->unit_price
            !== null
        ) {
            return round(
                (float)
                $quotationItem
                    ->unit_price,
                2
            );
        }

        $quantity =
            max(
                (float)
                $quotationItem
                    ->quantity,
                0.000001
            );

        if (
            $quotationItem
                ->subtotal
            !== null
        ) {
            return round(
                (float)
                $quotationItem
                    ->subtotal
                / $quantity,
                2
            );
        }

        if (
            $quotationItem
                ->pricing_method
            === 'AREA'
        ) {
            return round(
                (float)
                (
                    $quotationItem
                        ->width
                    ?? 0
                )
                *
                (float)
                (
                    $quotationItem
                        ->height
                    ?? 0
                )
                *
                (float)
                (
                    $quotationItem
                        ->sale_rate
                    ?? 0
                ),
                2
            );
        }

        if (
            $quotationItem
                ->pricing_method
            === 'LINEAR'
        ) {
            return round(
                (float)
                (
                    $quotationItem
                        ->width
                    ?? 0
                )
                *
                (float)
                (
                    $quotationItem
                        ->sale_rate
                    ?? 0
                ),
                2
            );
        }

        return 0;
    }

    private function resolveQuotationUnitCost(
        object $quotationItem
    ): float {
        if (
            $quotationItem
                ->unit_cost
            !== null
        ) {
            return round(
                (float)
                $quotationItem
                    ->unit_cost,
                2
            );
        }

        $quantity =
            max(
                (float)
                $quotationItem
                    ->quantity,
                0.000001
            );

        if (
            $quotationItem
                ->estimated_cost
            !== null
        ) {
            return round(
                (float)
                $quotationItem
                    ->estimated_cost
                / $quantity,
                2
            );
        }

        if (
            $quotationItem
                ->pricing_method
            === 'AREA'
        ) {
            return round(
                (float)
                (
                    $quotationItem
                        ->width
                    ?? 0
                )
                *
                (float)
                (
                    $quotationItem
                        ->height
                    ?? 0
                )
                *
                (float)
                (
                    $quotationItem
                        ->cost_rate
                    ?? 0
                ),
                2
            );
        }

        if (
            $quotationItem
                ->pricing_method
            === 'LINEAR'
        ) {
            return round(
                (float)
                (
                    $quotationItem
                        ->width
                    ?? 0
                )
                *
                (float)
                (
                    $quotationItem
                        ->cost_rate
                    ?? 0
                ),
                2
            );
        }

        return 0;
    }

    private function resolveDefaultOperator(): ?int
    {
        $defaultOperator =
            Employee::query()
                ->active()
                ->defaultOperator()
                ->orderBy('id')
                ->first();

        if ($defaultOperator) {
            return $defaultOperator
                ->id;
        }

        $activeEmployees =
            Employee::query()
                ->active()
                ->orderBy('id')
                ->get();

        if (
            $activeEmployees
                ->count()
            === 1
        ) {
            $employee =
                $activeEmployees
                    ->first();

            $employee->update([
                'is_default_operator' =>
                    true,
            ]);

            return $employee
                ->id;
        }

        return null;
    }

    private function buildWorkOrderTitle(
        Quotation $quotation
    ): string {
        $names =
            $quotation
                ->items
                ->pluck(
                    'item_name'
                )
                ->filter()
                ->unique()
                ->take(2)
                ->implode(' + ');

        if (!$names) {
            return 'Producción ' .
                $quotation
                    ->quotation_number;
        }

        return $names;
    }

    private function buildWorkOrderDescription(
        Quotation $quotation
    ): string {
        $lines = [];

        foreach (
            $quotation->items
            as $item
        ) {
            $description =
                $item->item_name;

            $description .=
                ' × ' .
                number_format(
                    (float)
                    $item->quantity,
                    2
                );

            if (
                $item->width !== null
                &&
                $item->height !== null
            ) {
                $description .=
                    ' · ' .
                    $item->width .
                    ' × ' .
                    $item->height;

                if (
                    $item
                        ->measurement_unit
                ) {
                    $description .=
                        ' ' .
                        $item
                            ->measurement_unit;
                }
            }

            if (
                $item
                    ->description
            ) {
                $description .=
                    ' · ' .
                    $item
                        ->description;
            }

            $lines[] =
                $description;
        }

        return implode(
            PHP_EOL,
            $lines
        );
    }

    private function nextSaleNumber(): string
    {
        $next =
            (
                Sale::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'VEN-' .
            now()
                ->format('Y') .
            '-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    private function nextWorkOrderNumber(): string
    {
        $next =
            (
                WorkOrder::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'OT-' .
            now()
                ->format('Y') .
            '-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}