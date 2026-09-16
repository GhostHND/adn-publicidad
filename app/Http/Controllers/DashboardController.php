<?php

namespace App\Http\Controllers;

use App\Models\InventoryReservation;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\Task;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | ÓRDENES PENDIENTES
        |--------------------------------------------------------------------------
        */

        $pendingOrders = WorkOrder::query()
            ->with([
                'client',
                'sale',
                'responsibleEmployee',
                'materials.inventoryItem',
                'materials.reservation',
            ])
            ->whereIn(
                'status',
                [
                    'draft',
                    'pending',
                ]
            )
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'normal' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('order_date')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CLASIFICAR SEGÚN DISPONIBILIDAD DE MATERIALES
        |--------------------------------------------------------------------------
        */

        $readyOrders = collect();

        $blockedOrders = collect();

        foreach (
            $pendingOrders
            as $order
        ) {
            $materialState =
                $this->materialState(
                    $order
                );

            if (
                $materialState['ready']
            ) {
                $readyOrders->push(
                    $order
                );
            } else {
                $blockedOrders->push(
                    $order
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ÓRDENES ACTIVAS
        |--------------------------------------------------------------------------
        */

        $activeOrders = WorkOrder::query()
            ->with([
                'client',
                'sale',
                'responsibleEmployee',
            ])
            ->whereIn(
                'status',
                [
                    'in_progress',
                    'paused',
                    'review',
                ]
            )
            ->orderByRaw("
                CASE status
                    WHEN 'in_progress' THEN 1
                    WHEN 'review' THEN 2
                    WHEN 'paused' THEN 3
                    ELSE 4
                END
            ")
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'normal' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | LISTOS PARA ENTREGA
        |--------------------------------------------------------------------------
        */

        $deliveryOrders = WorkOrder::query()
            ->with([
                'client',
                'sale.payments',
                'responsibleEmployee',
            ])
            ->where(
                'status',
                'completed'
            )
            ->orderBy('completed_at')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TAREAS QUE REQUIEREN ATENCIÓN
        |--------------------------------------------------------------------------
        */

        $tasks = Task::query()
            ->with([
                'workOrder.client',
                'responsibleEmployee',
            ])
            ->whereIn(
                'status',
                [
                    'in_progress',
                    'pending',
                    'issue',
                    'paused',
                    'review',
                ]
            )
            ->orderByRaw("
                CASE status
                    WHEN 'issue' THEN 1
                    WHEN 'in_progress' THEN 2
                    WHEN 'review' THEN 3
                    WHEN 'paused' THEN 4
                    WHEN 'pending' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CUENTAS POR COBRAR
        |--------------------------------------------------------------------------
        */

        $openSales = Sale::query()
            ->with([
                'client',
            ])
            ->withSum(
                'payments',
                'amount'
            )
            ->whereNotIn(
                'status',
                [
                    'paid',
                    'cancelled',
                ]
            )
            ->orderBy('sale_date')
            ->orderBy('id')
            ->get()
            ->filter(
                fn (
                    Sale $sale
                ) =>
                    $this
                        ->saleBalance(
                            $sale
                        )
                    > 0
            )
            ->values();

        $receivableTotal =
            $openSales
                ->sum(
                    fn (
                        Sale $sale
                    ) =>
                        $this
                            ->saleBalance(
                                $sale
                            )
                );

        /*
        |--------------------------------------------------------------------------
        | COTIZACIONES ENVIADAS SIN RESPUESTA
        |--------------------------------------------------------------------------
        */

        $waitingQuotations =
            Quotation::query()
                ->with('client')
                ->where(
                    'status',
                    'sent'
                )
                ->orderBy(
                    'valid_until'
                )
                ->orderBy('id')
                ->limit(10)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | NECESIDADES DE COMPRA
        |--------------------------------------------------------------------------
        */

        $purchaseNeeds =
            $this
                ->purchaseNeeds();

        /*
        |--------------------------------------------------------------------------
        | VENTAS Y COBROS DE HOY
        |--------------------------------------------------------------------------
        */

        $todaySales = Sale::query()
            ->whereDate(
                'sale_date',
                today()
            )
            ->where(
                'status',
                '!=',
                'cancelled'
            )
            ->sum('total');

        $todayPayments =
            SalePayment::query()
                ->whereDate(
                    'payment_date',
                    today()
                )
                ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | PAGOS RECIENTES
        |--------------------------------------------------------------------------
        */

        $recentPayments =
            SalePayment::query()
                ->with([
                    'sale.client',
                    'financialAccount',
                ])
                ->latest(
                    'payment_date'
                )
                ->latest('id')
                ->limit(8)
                ->get();

        /*
        |--------------------------------------------------------------------------
        | MÉTRICAS GENERALES
        |--------------------------------------------------------------------------
        */

        $summary = [
            'ready_orders' =>
                $readyOrders
                    ->count(),

            'blocked_orders' =>
                $blockedOrders
                    ->count(),

            'active_orders' =>
                $activeOrders
                    ->count(),

            'delivery_orders' =>
                $deliveryOrders
                    ->count(),

            'waiting_quotations' =>
                $waitingQuotations
                    ->count(),

            'receivable_sales' =>
                $openSales
                    ->count(),

            'receivable_total' =>
                round(
                    (float)
                    $receivableTotal,
                    2
                ),

            'purchase_items' =>
                $purchaseNeeds
                    ->count(),

            'today_sales' =>
                round(
                    (float)
                    $todaySales,
                    2
                ),

            'today_payments' =>
                round(
                    (float)
                    $todayPayments,
                    2
                ),
        ];

        return Inertia::render(
            'Dashboard',
            [
                'summary' =>
                    $summary,

                'readyOrders' =>
                    $readyOrders
                        ->take(8)
                        ->map(
                            fn (
                                WorkOrder $order
                            ) =>
                                $this
                                    ->orderData(
                                        $order
                                    )
                        )
                        ->values(),

                'blockedOrders' =>
                    $blockedOrders
                        ->take(8)
                        ->map(
                            fn (
                                WorkOrder $order
                            ) =>
                                $this
                                    ->blockedOrderData(
                                        $order
                                    )
                        )
                        ->values(),

                'activeOrders' =>
                    $activeOrders
                        ->take(8)
                        ->map(
                            fn (
                                WorkOrder $order
                            ) =>
                                $this
                                    ->orderData(
                                        $order
                                    )
                        )
                        ->values(),

                'deliveryOrders' =>
                    $deliveryOrders
                        ->take(8)
                        ->map(
                            fn (
                                WorkOrder $order
                            ) =>
                                $this
                                    ->deliveryOrderData(
                                        $order
                                    )
                        )
                        ->values(),

                'tasks' =>
                    $tasks
                        ->map(
                            fn (
                                Task $task
                            ) =>
                                $this
                                    ->taskData(
                                        $task
                                    )
                        )
                        ->values(),

                'receivables' =>
                    $openSales
                        ->take(8)
                        ->map(
                            fn (
                                Sale $sale
                            ) =>
                                $this
                                    ->receivableData(
                                        $sale
                                    )
                        )
                        ->values(),

                'waitingQuotations' =>
                    $waitingQuotations
                        ->map(
                            fn (
                                Quotation $quotation
                            ) =>
                                $this
                                    ->quotationData(
                                        $quotation
                                    )
                        )
                        ->values(),

                'purchaseNeeds' =>
                    $purchaseNeeds
                        ->values(),

                'recentPayments' =>
                    $recentPayments
                        ->map(
                            fn (
                                SalePayment $payment
                            ) =>
                                $this
                                    ->paymentData(
                                        $payment
                                    )
                        )
                        ->values(),
            ]
        );
    }

    private function materialState(
        WorkOrder $order
    ): array {
        $missing = [];

        foreach (
            $order->materials
            as $material
        ) {
            $required =
                (float)
                $material
                    ->quantity_planned;

            $consumed =
                (float)
                $material
                    ->quantity_consumed;

            if (
                $consumed >=
                $required
            ) {
                continue;
            }

            $reserved =
                (float)
                (
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
                $missingQuantity <= 0
            ) {
                continue;
            }

            $missing[] = [
                'name' =>
                    $material
                        ->inventoryItem
                        ?->name
                    ?? 'Material',

                'quantity' =>
                    $missingQuantity,

                'unit' =>
                    $material
                        ->inventoryItem
                        ?->measurement_unit,
            ];
        }

        return [
            'ready' =>
                empty(
                    $missing
                ),

            'missing' =>
                $missing,
        ];
    }

    private function purchaseNeeds(): Collection
    {
        $reservations =
            InventoryReservation::query()
                ->with([
                    'inventoryItem',
                    'workOrder',
                ])
                ->whereIn(
                    'status',
                    [
                        'partial',
                        'unavailable',
                    ]
                )
                ->whereHas(
                    'workOrder',
                    fn ($query) =>
                        $query
                            ->whereIn(
                                'status',
                                [
                                    'draft',
                                    'pending',
                                ]
                            )
                )
                ->orderBy('id')
                ->get();

        return $reservations
            ->groupBy(
                'inventory_item_id'
            )
            ->map(
                function (
                    Collection $items
                ) {
                    $first =
                        $items
                            ->first();

                    $missing =
                        $items
                            ->sum(
                                fn (
                                    InventoryReservation $reservation
                                ) =>
                                    max(
                                        (float)
                                        $reservation
                                            ->quantity_required
                                        -
                                        (float)
                                        $reservation
                                            ->quantity_reserved,
                                        0
                                    )
                            );

                    $orders =
                        $items
                            ->map(
                                fn (
                                    InventoryReservation $reservation
                                ) =>
                                    $reservation
                                        ->workOrder
                                        ?->work_order_number
                            )
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                    return [
                        'inventory_item_id' =>
                            $first
                                ->inventory_item_id,

                        'code' =>
                            $first
                                ->inventoryItem
                                ?->item_code,

                        'name' =>
                            $first
                                ->inventoryItem
                                ?->name
                            ?? 'Material',

                        'measurement_unit' =>
                            $first
                                ->inventoryItem
                                ?->measurement_unit,

                        'current_stock' =>
                            (float)
                            (
                                $first
                                    ->inventoryItem
                                    ?->current_stock
                                ?? 0
                            ),

                        'missing_quantity' =>
                            round(
                                $missing,
                                3
                            ),

                        'orders' =>
                            $orders,

                        'orders_count' =>
                            count(
                                $orders
                            ),
                    ];
                }
            )
            ->sortByDesc(
                'missing_quantity'
            )
            ->values();
    }

    private function orderData(
        WorkOrder $order
    ): array {
        return [
            'id' =>
                $order->id,

            'number' =>
                $order
                    ->work_order_number,

            'title' =>
                $order
                    ->title,

            'client' =>
                $order
                    ->client
                    ?->display_name,

            'status' =>
                $order
                    ->status,

            'priority' =>
                $order
                    ->priority,

            'date' =>
                $this
                    ->dateValue(
                        $order
                            ->order_date
                    ),

            'due_date' =>
                $this
                    ->dateValue(
                        $order
                            ->due_date
                    ),

            'responsible' =>
                $order
                    ->responsibleEmployee
                    ?->full_name,

            'sale_number' =>
                $order
                    ->sale
                    ?->sale_number,
        ];
    }

    private function blockedOrderData(
        WorkOrder $order
    ): array {
        $data =
            $this
                ->orderData(
                    $order
                );

        $state =
            $this
                ->materialState(
                    $order
                );

        $data['missing'] =
            $state[
                'missing'
            ];

        $data[
            'missing_count'
        ] =
            count(
                $state[
                    'missing'
                ]
            );

        return $data;
    }

    private function deliveryOrderData(
        WorkOrder $order
    ): array {
        $data =
            $this
                ->orderData(
                    $order
                );

        $paid =
            (float)
            (
                $order
                    ->sale
                    ?->payments
                    ?->sum(
                        'amount'
                    )
                ?? 0
            );

        $total =
            (float)
            (
                $order
                    ->sale
                    ?->total
                ?? 0
            );

        $data['total'] =
            round(
                $total,
                2
            );

        $data['paid'] =
            round(
                $paid,
                2
            );

        $data['balance'] =
            max(
                round(
                    $total
                    -
                    $paid,
                    2
                ),
                0
            );

        return $data;
    }

    private function taskData(
        Task $task
    ): array {
        return [
            'id' =>
                $task->id,

            'number' =>
                $task
                    ->task_number,

            'title' =>
                $task->title,

            'status' =>
                $task->status,

            'priority' =>
                $task
                    ->priority,

            'work_order_id' =>
                $task
                    ->work_order_id,

            'work_order_number' =>
                $task
                    ->workOrder
                    ?->work_order_number,

            'client' =>
                $task
                    ->workOrder
                    ?->client
                    ?->display_name,

            'responsible' =>
                $task
                    ->responsibleEmployee
                    ?->full_name,
        ];
    }

    private function receivableData(
        Sale $sale
    ): array {
        $paid =
            (float)
            (
                $sale
                    ->payments_sum_amount
                ?? 0
            );

        $total =
            (float)
            $sale->total;

        return [
            'id' =>
                $sale->id,

            'number' =>
                $sale
                    ->sale_number,

            'client' =>
                $sale
                    ->client
                    ?->display_name,

            'sale_date' =>
                $this
                    ->dateValue(
                        $sale
                            ->sale_date
                    ),

            'total' =>
                round(
                    $total,
                    2
                ),

            'paid' =>
                round(
                    $paid,
                    2
                ),

            'balance' =>
                max(
                    round(
                        $total
                        -
                        $paid,
                        2
                    ),
                    0
                ),
        ];
    }

    private function quotationData(
        Quotation $quotation
    ): array {
        return [
            'id' =>
                $quotation->id,

            'number' =>
                $quotation
                    ->quotation_number,

            'client' =>
                $quotation
                    ->client
                    ?->display_name,

            'date' =>
                $this
                    ->dateValue(
                        $quotation
                            ->quotation_date
                    ),

            'valid_until' =>
                $this
                    ->dateValue(
                        $quotation
                            ->valid_until
                    ),

            'total' =>
                round(
                    (float)
                    $quotation
                        ->total,
                    2
                ),
        ];
    }

    private function paymentData(
        SalePayment $payment
    ): array {
        return [
            'id' =>
                $payment->id,

            'receipt_number' =>
                $payment
                    ->receipt_number,

            'sale_id' =>
                $payment
                    ->sale_id,

            'sale_number' =>
                $payment
                    ->sale
                    ?->sale_number,

            'client' =>
                $payment
                    ->sale
                    ?->client
                    ?->display_name,

            'payment_date' =>
                $this
                    ->dateValue(
                        $payment
                            ->payment_date
                    ),

            'amount' =>
                round(
                    (float)
                    $payment
                        ->amount,
                    2
                ),

            'payment_method' =>
                $payment
                    ->payment_method,

            'financial_account' =>
                $payment
                    ->financialAccount
                    ?->name,
        ];
    }

    private function saleBalance(
        Sale $sale
    ): float {
        $paid =
            (float)
            (
                $sale
                    ->payments_sum_amount
                ?? 0
            );

        return max(
            round(
                (float)
                $sale->total
                -
                $paid,
                2
            ),
            0
        );
    }

    private function dateValue(
        mixed $value
    ): ?string {
        if (!$value) {
            return null;
        }

        if (
            $value instanceof
            DateTimeInterface
        ) {
            return $value->format(
                'Y-m-d'
            );
        }

        return substr(
            (string)
            $value,
            0,
            10
        );
    }
}