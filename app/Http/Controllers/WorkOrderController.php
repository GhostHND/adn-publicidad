<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\FinancialAccount;
use App\Models\InventoryItem;
use App\Models\Sale;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use App\Services\InventoryReservationService;
use App\Services\WorkOrderDeliveryService;
use App\Services\WorkOrderExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function __construct(
        private WorkOrderDeliveryService $deliveryService,
        private WorkOrderExecutionService $executionService,
        private InventoryReservationService $reservationService
    ) {
    }

    public function index(): Response
    {
        $orders =
            WorkOrder::query()
                ->with([
                    'client',
                    'responsibleEmployee',
                    'sale',
                    'materials.inventoryItem',
                    'materials.reservation',
                ])
                ->latest('order_date')
                ->latest('id')
                ->get()
                ->map(
                    fn (WorkOrder $order) =>
                        $this
                            ->orderForIndex(
                                $order
                            )
                )
                ->values();

        return Inertia::render(
            'WorkOrders/Index',
            [
                'orders' =>
                    $orders,
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'WorkOrders/Create',
            [
                'clients' =>
                    $this
                        ->clientsForForm(),

                'employees' =>
                    $this
                        ->employeesForForm(),

                'sales' =>
                    $this
                        ->salesForForm(),

                'inventoryItems' =>
                    $this
                        ->inventoryForForm(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this
                ->validateOrder(
                    $request
                );

        $order =
            DB::transaction(
                function () use (
                    $validated,
                    $request
                ) {
                    $sale = null;

                    if (
                        !empty(
                            $validated[
                                'sale_id'
                            ]
                        )
                    ) {
                        $sale =
                            Sale::query()
                                ->with('items')
                                ->findOrFail(
                                    $validated[
                                        'sale_id'
                                    ]
                                );

                        if (
                            $sale->status ===
                            'cancelled'
                        ) {
                            throw ValidationException::withMessages([
                                'sale_id' =>
                                    'No puedes crear una orden desde una venta cancelada.',
                            ]);
                        }
                    }

                    $clientId =
                        $sale
                            ? $sale
                                ->client_id
                            : $validated[
                                'client_id'
                            ];

                    $order =
                        WorkOrder::create([
                            'work_order_number' =>
                                $this
                                    ->nextWorkOrderNumber(),

                            'client_id' =>
                                $clientId,

                            'sale_id' =>
                                $sale?->id,

                            'responsible_employee_id' =>
                                $validated[
                                    'responsible_employee_id'
                                ] ?? null,

                            'created_by' =>
                                $request
                                    ->user()
                                    ?->id,

                            'title' =>
                                $validated[
                                    'title'
                                ],

                            'status' =>
                                $validated[
                                    'status'
                                ],

                            'priority' =>
                                $validated[
                                    'priority'
                                ],

                            'order_date' =>
                                $validated[
                                    'order_date'
                                ],

                            'due_date' =>
                                $validated[
                                    'due_date'
                                ] ?? null,

                            'description' =>
                                $validated[
                                    'description'
                                ] ?? null,

                            'internal_notes' =>
                                $validated[
                                    'internal_notes'
                                ] ?? null,
                        ]);

                    if ($sale) {
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
                                        /
                                        $quantity,
                                        2
                                    );

                            $order
                                ->items()
                                ->create([
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
                                        $saleItem
                                            ->subtotal,

                                    'sort_order' =>
                                        $index + 1,
                                ]);
                        }
                    }

                    $this->syncMaterials(
                        $order,
                        $validated[
                            'materials'
                        ] ?? []
                    );

                    $this
                        ->reservationService
                        ->syncForWorkOrder(
                            $order
                        );

                    return $order;
                }
            );

        return redirect(
            '/work-orders/' .
            $order->id
        )->with(
            'success',
            'Orden de trabajo creada correctamente.'
        );
    }

    public function show(
        WorkOrder $workOrder
    ): Response {
        $workOrder->load([
            'client',
            'sale.payments.financialAccount',
            'responsibleEmployee',
            'creator',
            'items',
            'materials.inventoryItem',
            'materials.reservation',
            'tasks.responsibleEmployee',
        ]);

        return Inertia::render(
            'WorkOrders/Show',
            [
                'order' =>
                    $this
                        ->orderForFrontend(
                            $workOrder
                        ),

                'allowedStatuses' =>
                    $this
                        ->allowedNextStatuses(
                            $workOrder
                                ->status
                        ),

                'financialAccounts' =>
                    $this
                        ->financialAccountsForForm(),
            ]
        );
    }

    public function edit(
        WorkOrder $workOrder
    ): Response {
        $workOrder->load([
            'client',
            'sale',
            'materials.inventoryItem',
            'materials.reservation',
        ]);

        return Inertia::render(
            'WorkOrders/Edit',
            [
                'order' =>
                    $this
                        ->orderForFrontend(
                            $workOrder
                        ),

                'clients' =>
                    $this
                        ->clientsForForm(),

                'employees' =>
                    $this
                        ->employeesForForm(),

                'sales' =>
                    $this
                        ->salesForForm(),

                'inventoryItems' =>
                    $this
                        ->inventoryForForm(),

                'materialsEditable' =>
                    in_array(
                        $workOrder
                            ->status,
                        [
                            'draft',
                            'pending',
                        ],
                        true
                    ),
            ]
        );
    }

    public function update(
        Request $request,
        WorkOrder $workOrder
    ): RedirectResponse {
        $validated =
            $this
                ->validateOrder(
                    $request,
                    $workOrder
                );

        DB::transaction(
            function () use (
                $validated,
                $workOrder
            ) {
                $clientId =
                    $workOrder
                        ->sale_id
                        ? $workOrder
                            ->client_id
                        : $validated[
                            'client_id'
                        ];

                $workOrder->update([
                    'client_id' =>
                        $clientId,

                    'responsible_employee_id' =>
                        $validated[
                            'responsible_employee_id'
                        ] ?? null,

                    'title' =>
                        $validated[
                            'title'
                        ],

                    'priority' =>
                        $validated[
                            'priority'
                        ],

                    'order_date' =>
                        $validated[
                            'order_date'
                        ],

                    'due_date' =>
                        $validated[
                            'due_date'
                        ] ?? null,

                    'description' =>
                        $validated[
                            'description'
                        ] ?? null,

                    'internal_notes' =>
                        $validated[
                            'internal_notes'
                        ] ?? null,
                ]);

                if (
                    in_array(
                        $workOrder
                            ->status,
                        [
                            'draft',
                            'pending',
                        ],
                        true
                    )
                ) {
                    $this->syncMaterials(
                        $workOrder,
                        $validated[
                            'materials'
                        ] ?? []
                    );

                    $this
                        ->reservationService
                        ->syncForWorkOrder(
                            $workOrder
                        );
                }
            }
        );

        return redirect(
            '/work-orders/' .
            $workOrder->id
        )->with(
            'success',
            'Orden actualizada correctamente.'
        );
    }

    public function updateStatus(
        Request $request,
        WorkOrder $workOrder
    ): RedirectResponse {
        $validated =
            $request->validate([
                'status' => [
                    'required',
                    'string',
                ],
            ]);

        $newStatus =
            $validated[
                'status'
            ];

        $allowed =
            $this
                ->allowedNextStatuses(
                    $workOrder
                        ->status
                );

        if (
            !in_array(
                $newStatus,
                $allowed,
                true
            )
        ) {
            return back()->withErrors([
                'status' =>
                    'El cambio de estado solicitado no es válido.',
            ]);
        }

        if (
            $newStatus ===
            'delivered'
        ) {
            return $this
                ->processDelivery(
                    $request,
                    $workOrder
                );
        }

        if (
            $newStatus ===
            'in_progress'
        ) {
            $this
                ->executionService
                ->start(
                    $workOrder,
                    $request
                        ->user()
                        ?->id
                );

            return back()->with(
                'success',
                'Producción iniciada. Los materiales reservados fueron consumidos automáticamente.'
            );
        }

        if (
            $newStatus ===
            'cancelled'
        ) {
            DB::transaction(
                function () use (
                    $workOrder
                ) {
                    $this
                        ->reservationService
                        ->releaseForWorkOrder(
                            $workOrder
                        );

                    $workOrder->update([
                        'status' =>
                            'cancelled',
                    ]);
                }
            );

            return back()->with(
                'success',
                'Orden cancelada y materiales reservados liberados.'
            );
        }

        DB::transaction(
            function () use (
                $workOrder,
                $newStatus
            ) {
                if (
                    $newStatus ===
                    'completed'
                ) {
                    $workOrder
                        ->completed_at =
                        now();
                }

                $workOrder
                    ->status =
                    $newStatus;

                $workOrder
                    ->save();
            }
        );

        return back()->with(
            'success',
            'Estado actualizado correctamente.'
        );
    }

    private function processDelivery(
        Request $request,
        WorkOrder $workOrder
    ): RedirectResponse {
        $workOrder->load(
            'sale.payments'
        );

        $balance = 0;

        if ($workOrder->sale) {
            $paid =
                (float)
                $workOrder
                    ->sale
                    ->payments
                    ->sum('amount');

            $balance =
                max(
                    round(
                        (float)
                        $workOrder
                            ->sale
                            ->total
                        -
                        $paid,
                        2
                    ),
                    0
                );
        }

        $rules = [
            'delivery_payment_date' => [
                'nullable',
                'date',
            ],

            'delivery_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'delivery_payment_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];

        if ($balance > 0) {
            $rules[
                'delivery_payment_amount'
            ] = [
                'required',
                'numeric',
                'gt:0',
                'lte:' . $balance,
            ];

            $rules[
                'delivery_payment_method'
            ] = [
                'required',

                Rule::in([
                    'cash',
                    'transfer',
                    'card',
                    'other',
                ]),
            ];

            $rules[
                'delivery_financial_account_id'
            ] = [
                'nullable',
                'integer',

                Rule::exists(
                    'financial_accounts',
                    'id'
                )
                    ->where(
                        'active',
                        true
                    )
                    ->whereNull(
                        'deleted_at'
                    ),
            ];
        }

        $data =
            $request->validate(
                $rules
            );

        if (
            $balance > 0
            &&
            (
                $data[
                    'delivery_payment_method'
                ] ?? null
            ) !== 'cash'
            &&
            empty(
                $data[
                    'delivery_financial_account_id'
                ]
            )
        ) {
            throw ValidationException::withMessages([
                'delivery_financial_account_id' =>
                    'Selecciona el banco o cuenta donde ingresó el dinero.',
            ]);
        }

        $payment =
            $this
                ->deliveryService
                ->deliver(
                    $workOrder,
                    $data,
                    $request
                        ->user()
                        ?->id
                );

        if ($payment) {
            return redirect(
                '/work-orders/' .
                $workOrder->id
            )->with(
                'success',
                'Trabajo entregado. Pago registrado y recibo ' .
                $payment
                    ->receipt_number .
                ' generado automáticamente.'
            );
        }

        return redirect(
            '/work-orders/' .
            $workOrder->id
        )->with(
            'success',
            'Trabajo entregado correctamente.'
        );
    }

    private function validateOrder(
        Request $request,
        ?WorkOrder $workOrder = null
    ): array {
        $rules = [
            'client_id' => [
                'required',
                'integer',

                Rule::exists(
                    'clients',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ],

            'responsible_employee_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'employees',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ],

            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'priority' => [
                'required',

                Rule::in([
                    'low',
                    'normal',
                    'high',
                    'urgent',
                ]),
            ],

            'order_date' => [
                'required',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:order_date',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'internal_notes' => [
                'nullable',
                'string',
            ],

            'materials' => [
                'nullable',
                'array',
            ],

            'materials.*.inventory_item_id' => [
                'required',
                'integer',

                Rule::exists(
                    'inventory_items',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ],

            'materials.*.quantity_planned' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'materials.*.notes' => [
                'nullable',
                'string',
            ],
        ];

        if (!$workOrder) {
            $rules[
                'sale_id'
            ] = [
                'nullable',
                'integer',

                Rule::exists(
                    'sales',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ];

            $rules[
                'status'
            ] = [
                'required',

                Rule::in([
                    'draft',
                    'pending',
                ]),
            ];
        }

        return $request
            ->validate(
                $rules
            );
    }

    private function syncMaterials(
        WorkOrder $order,
        array $materials
    ): void {
        $ids =
            collect(
                $materials
            )->pluck(
                'inventory_item_id'
            );

        if (
            $ids->count()
            !==
            $ids
                ->unique()
                ->count()
        ) {
            throw ValidationException::withMessages([
                'materials' =>
                    'No puedes agregar el mismo material más de una vez.',
            ]);
        }

        $order
            ->materials()
            ->delete();

        foreach (
            $materials
            as $material
        ) {
            $inventoryItem =
                InventoryItem::query()
                    ->findOrFail(
                        $material[
                            'inventory_item_id'
                        ]
                    );

            $order
                ->materials()
                ->create([
                    'inventory_item_id' =>
                        $inventoryItem
                            ->id,

                    'source' =>
                        'manual',

                    'quantity_planned' =>
                        round(
                            (float)
                            $material[
                                'quantity_planned'
                            ],
                            3
                        ),

                    'quantity_consumed' =>
                        0,

                    'unit_cost_snapshot' =>
                        $inventoryItem
                            ->unit_cost,

                    'notes' =>
                        $material[
                            'notes'
                        ] ?? null,
                ]);
        }
    }

    private function allowedNextStatuses(
        string $status
    ): array {
        return match ($status) {
            'draft' => [
                'pending',
                'in_progress',
                'cancelled',
            ],

            'pending' => [
                'in_progress',
                'cancelled',
            ],

            'in_progress' => [
                'paused',
                'review',
                'completed',
                'cancelled',
            ],

            'paused' => [
                'in_progress',
                'cancelled',
            ],

            'review' => [
                'in_progress',
                'completed',
                'cancelled',
            ],

            'completed' => [
                'delivered',
            ],

            default => [],
        };
    }

    private function clientsForForm(): array
    {
        return Client::query()
            ->active()
            ->orderBy(
                'first_name'
            )
            ->orderBy(
                'business_name'
            )
            ->get()
            ->map(
                fn (
                    Client $client
                ) => [
                    'id' =>
                        $client->id,

                    'code' =>
                        $client
                            ->client_code,

                    'name' =>
                        $client
                            ->display_name,
                ]
            )
            ->values()
            ->all();
    }

    private function employeesForForm(): array
    {
        return Employee::query()
            ->active()
            ->orderBy(
                'first_name'
            )
            ->orderBy(
                'last_name'
            )
            ->get()
            ->map(
                fn (
                    Employee $employee
                ) => [
                    'id' =>
                        $employee->id,

                    'code' =>
                        $employee
                            ->employee_code,

                    'name' =>
                        $employee
                            ->full_name,

                    'position' =>
                        $employee
                            ->position,
                ]
            )
            ->values()
            ->all();
    }

    private function salesForForm(): array
    {
        return Sale::query()
            ->with([
                'client',
                'items',
            ])
            ->where(
                'status',
                '!=',
                'cancelled'
            )
            ->latest(
                'sale_date'
            )
            ->get()
            ->map(
                fn (
                    Sale $sale
                ) => [
                    'id' =>
                        $sale->id,

                    'sale_number' =>
                        $sale
                            ->sale_number,

                    'client_id' =>
                        $sale
                            ->client_id,

                    'client_name' =>
                        $sale
                            ->client
                            ?->display_name,

                    'total' =>
                        $sale
                            ->total,

                    'items' =>
                        $sale
                            ->items
                            ->map(
                                fn (
                                    $item
                                ) => [
                                    'name' =>
                                        $item
                                            ->item_name,

                                    'quantity' =>
                                        $item
                                            ->quantity,
                                ]
                            )
                            ->values(),
                ]
            )
            ->values()
            ->all();
    }

    private function inventoryForForm(): array
    {
        return InventoryItem::query()
            ->active()
            ->orderBy(
                'name'
            )
            ->get()
            ->map(
                fn (
                    InventoryItem $item
                ) => [
                    'id' =>
                        $item->id,

                    'code' =>
                        $item
                            ->item_code,

                    'name' =>
                        $item
                            ->name,

                    'measurement_unit' =>
                        $item
                            ->measurement_unit,

                    'current_stock' =>
                        $item
                            ->current_stock,

                    'reserved_stock' =>
                        $item
                            ->reserved_stock,

                    'available_stock' =>
                        $item
                            ->available_stock,

                    'unit_cost' =>
                        $item
                            ->unit_cost,
                ]
            )
            ->values()
            ->all();
    }

    private function financialAccountsForForm(): array
    {
        return FinancialAccount::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(
                fn (
                    FinancialAccount $account
                ) => [
                    'id' =>
                        $account->id,

                    'code' =>
                        $account
                            ->account_code,

                    'name' =>
                        $account
                            ->name,

                    'institution' =>
                        $account
                            ->institution,

                    'account_type' =>
                        $account
                            ->account_type,
                ]
            )
            ->values()
            ->all();
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
                (string)
                $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    private function orderForIndex(
        WorkOrder $order
    ): array {
        $missingMaterials =
            $order
                ->materials
                ->filter(
                    function (
                        WorkOrderMaterial $material
                    ) {
                        if (
                            (float)
                            $material
                                ->quantity_consumed
                            >=
                            (float)
                            $material
                                ->quantity_planned
                        ) {
                            return false;
                        }

                        return (
                            (float)
                            (
                                $material
                                    ->reservation
                                    ?->quantity_reserved
                                ?? 0
                            )
                            <
                            (float)
                            $material
                                ->quantity_planned
                        );
                    }
                )
                ->count();

        return [
            'id' =>
                $order->id,

            'work_order_number' =>
                $order
                    ->work_order_number,

            'title' =>
                $order
                    ->title,

            'client' =>
                $order
                    ->client
                    ?->display_name,

            'sale_number' =>
                $order
                    ->sale
                    ?->sale_number,

            'responsible' =>
                $order
                    ->responsibleEmployee
                    ?->full_name,

            'status' =>
                $order->status,

            'priority' =>
                $order
                    ->priority,

            'order_date' =>
                $order
                    ->order_date
                    ?->format(
                        'Y-m-d'
                    ),

            'due_date' =>
                $order
                    ->due_date
                    ?->format(
                        'Y-m-d'
                    ),

            'materials_count' =>
                $order
                    ->materials
                    ->count(),

            'materials_ready' =>
                $missingMaterials === 0,

            'missing_materials_count' =>
                $missingMaterials,
        ];
    }

    private function orderForFrontend(
        WorkOrder $order
    ): array {
        $materialCost =
            $order
                ->materials
                ->sum(
                    fn (
                        WorkOrderMaterial $material
                    ) =>
                        (float)
                        $material
                            ->quantity_consumed
                        *
                        (float)
                        $material
                            ->unit_cost_snapshot
                );

        $materials =
            $order
                ->materials
                ->map(
                    function (
                        WorkOrderMaterial $material
                    ) {
                        $required =
                            (float)
                            $material
                                ->quantity_planned;

                        $reserved =
                            (float)
                            (
                                $material
                                    ->reservation
                                    ?->quantity_reserved
                                ?? 0
                            );

                        $consumed =
                            (float)
                            $material
                                ->quantity_consumed;

                        $missing =
                            $consumed >=
                            $required
                                ? 0
                                : max(
                                    round(
                                        $required -
                                        $reserved,
                                        3
                                    ),
                                    0
                                );

                        return [
                            'id' =>
                                $material
                                    ->id,

                            'inventory_item_id' =>
                                $material
                                    ->inventory_item_id,

                            'source' =>
                                $material
                                    ->source,

                            'code' =>
                                $material
                                    ->inventoryItem
                                    ?->item_code,

                            'name' =>
                                $material
                                    ->inventoryItem
                                    ?->name,

                            'measurement_unit' =>
                                $material
                                    ->inventoryItem
                                    ?->measurement_unit,

                            'current_stock' =>
                                $material
                                    ->inventoryItem
                                    ?->current_stock,

                            'available_stock' =>
                                $material
                                    ->inventoryItem
                                    ?->available_stock,

                            'quantity_planned' =>
                                $material
                                    ->quantity_planned,

                            'quantity_reserved' =>
                                round(
                                    $reserved,
                                    3
                                ),

                            'quantity_missing' =>
                                $missing,

                            'reservation_status' =>
                                $material
                                    ->reservation
                                    ?->status,

                            'quantity_consumed' =>
                                $material
                                    ->quantity_consumed,

                            'unit_cost' =>
                                $material
                                    ->unit_cost_snapshot,

                            'consumed_at' =>
                                $material
                                    ->consumed_at
                                    ?->format(
                                        'Y-m-d H:i'
                                    ),

                            'notes' =>
                                $material
                                    ->notes,
                        ];
                    }
                )
                ->values();

        $missingMaterials =
            $materials
                ->filter(
                    fn ($material) =>
                        (float)
                        $material[
                            'quantity_missing'
                        ] > 0
                )
                ->map(
                    fn ($material) => [
                        'name' =>
                            $material['name'],

                        'missing' =>
                            $material[
                                'quantity_missing'
                            ],

                        'measurement_unit' =>
                            $material[
                                'measurement_unit'
                            ],
                    ]
                )
                ->values();

        $saleData = null;

        if ($order->sale) {
            $paidAmount =
                (float)
                $order
                    ->sale
                    ->payments
                    ->sum('amount');

            $balance =
                max(
                    round(
                        (float)
                        $order
                            ->sale
                            ->total
                        -
                        $paidAmount,
                        2
                    ),
                    0
                );

            $saleData = [
                'id' =>
                    $order
                        ->sale
                        ->id,

                'sale_number' =>
                    $order
                        ->sale
                        ->sale_number,

                'status' =>
                    $order
                        ->sale
                        ->status,

                'total' =>
                    $order
                        ->sale
                        ->total,

                'paid_amount' =>
                    round(
                        $paidAmount,
                        2
                    ),

                'balance' =>
                    $balance,

                'payments' =>
                    $order
                        ->sale
                        ->payments
                        ->sortByDesc('id')
                        ->map(
                            fn (
                                $payment
                            ) => [
                                'id' =>
                                    $payment->id,

                                'receipt_number' =>
                                    $payment
                                        ->receipt_number,

                                'payment_date' =>
                                    $payment
                                        ->payment_date
                                        ?->format(
                                            'Y-m-d'
                                        ),

                                'amount' =>
                                    $payment
                                        ->amount,

                                'payment_method' =>
                                    $payment
                                        ->payment_method,

                                'reference' =>
                                    $payment
                                        ->reference,

                                'financial_account' =>
                                    $payment
                                        ->financialAccount
                                        ?->name,
                            ]
                        )
                        ->values(),
            ];
        }

        return [
            'id' =>
                $order->id,

            'work_order_number' =>
                $order
                    ->work_order_number,

            'client_id' =>
                $order
                    ->client_id,

            'sale_id' =>
                $order
                    ->sale_id,

            'responsible_employee_id' =>
                $order
                    ->responsible_employee_id,

            'title' =>
                $order->title,

            'status' =>
                $order->status,

            'priority' =>
                $order
                    ->priority,

            'order_date' =>
                $order
                    ->order_date
                    ?->format(
                        'Y-m-d'
                    ),

            'due_date' =>
                $order
                    ->due_date
                    ?->format(
                        'Y-m-d'
                    ),

            'started_at' =>
                $order
                    ->started_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'completed_at' =>
                $order
                    ->completed_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'delivered_at' =>
                $order
                    ->delivered_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'description' =>
                $order
                    ->description,

            'internal_notes' =>
                $order
                    ->internal_notes,

            'material_cost' =>
                round(
                    $materialCost,
                    2
                ),

            'materials_summary' => [
                'ready' =>
                    $missingMaterials
                        ->isEmpty(),

                'missing_count' =>
                    $missingMaterials
                        ->count(),

                'missing' =>
                    $missingMaterials,
            ],

            'client' => [
                'id' =>
                    $order
                        ->client
                        ?->id,

                'code' =>
                    $order
                        ->client
                        ?->client_code,

                'name' =>
                    $order
                        ->client
                        ?->display_name,

                'phone' =>
                    $order
                        ->client
                        ?->phone,
            ],

            'sale' =>
                $saleData,

            'responsible' =>
                $order
                    ->responsibleEmployee
                    ? [
                        'id' =>
                            $order
                                ->responsibleEmployee
                                ->id,

                        'name' =>
                            $order
                                ->responsibleEmployee
                                ->full_name,

                        'position' =>
                            $order
                                ->responsibleEmployee
                                ->position,
                    ]
                    : null,

            'items' =>
                $order
                    ->items
                    ->map(
                        fn ($item) => [
                            'id' =>
                                $item->id,

                            'item_code' =>
                                $item
                                    ->item_code,

                            'item_name' =>
                                $item
                                    ->item_name,

                            'description' =>
                                $item
                                    ->description,

                            'quantity' =>
                                $item
                                    ->quantity,

                            'unit_price' =>
                                $item
                                    ->unit_price,

                            'subtotal' =>
                                $item
                                    ->subtotal,
                        ]
                    )
                    ->values(),

            'materials' =>
                $materials,

            'tasks' =>
                $order
                    ->tasks
                    ->map(
                        fn ($task) => [
                            'id' =>
                                $task->id,

                            'task_number' =>
                                $task
                                    ->task_number,

                            'title' =>
                                $task
                                    ->title,

                            'status' =>
                                $task
                                    ->status,

                            'responsible' =>
                                $task
                                    ->responsibleEmployee
                                    ?->full_name,
                        ]
                    )
                    ->values(),
        ];
    }
}