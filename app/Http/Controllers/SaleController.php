<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\FinancialAccount;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function index(): Response
    {
        $sales = Sale::query()
            ->with([
                'client',
                'payments',
            ])
            ->latest('sale_date')
            ->latest('id')
            ->get()
            ->map(
                function (
                    Sale $sale
                ) {
                    return [
                        'id' =>
                            $sale->id,

                        'sale_number' =>
                            $sale->sale_number,

                        'sale_date' =>
                            $sale
                                ->sale_date
                                ?->format('Y-m-d'),

                        'client' =>
                            $sale
                                ->client
                                ?->display_name
                            ?? 'Sin cliente',

                        'client_code' =>
                            $sale
                                ->client
                                ?->client_code,

                        'status' =>
                            $sale->status,

                        'subtotal' =>
                            $sale->subtotal,

                        'discount' =>
                            $sale->discount,

                        'total' =>
                            $sale->total,

                        'paid_amount' =>
                            $sale->paid_amount,

                        'balance' =>
                            $sale->balance,
                    ];
                }
            )
            ->values();

        return Inertia::render(
            'Sales/Index',
            [
                'sales' =>
                    $sales,
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Sales/Create',
            [
                'clients' =>
                    $this->clientsForForm(),

                'catalogItems' =>
                    $this->catalogForForm(),

                'financialAccounts' =>
                    $this
                        ->financialAccountsForForm(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validateSale(
                $request
            );

        $result =
            DB::transaction(
                function () use (
                    $validated,
                    $request
                ) {
                    $calculation =
                        $this->calculateItems(
                            $validated['items']
                        );

                    $discount =
                        round(
                            (float)
                            (
                                $validated[
                                    'discount'
                                ]
                                ?? 0
                            ),
                            2
                        );

                    if (
                        $discount >
                        $calculation[
                            'subtotal'
                        ]
                    ) {
                        throw ValidationException::withMessages([
                            'discount' =>
                                'El descuento no puede superar el subtotal.',
                        ]);
                    }

                    $total =
                        round(
                            $calculation[
                                'subtotal'
                            ]
                            -
                            $discount,
                            2
                        );

                    $sale =
                        Sale::create([
                            'sale_number' =>
                                $this
                                    ->nextSaleNumber(),

                            'client_id' =>
                                $validated[
                                    'client_id'
                                ],

                            'quotation_id' =>
                                null,

                            'created_by' =>
                                $request
                                    ->user()?->id,

                            'sale_date' =>
                                $validated[
                                    'sale_date'
                                ],

                            'status' =>
                                'pending',

                            'subtotal' =>
                                $calculation[
                                    'subtotal'
                                ],

                            'discount' =>
                                $discount,

                            'total' =>
                                $total,

                            'estimated_cost' =>
                                $calculation[
                                    'estimated_cost'
                                ],

                            'notes' =>
                                $validated[
                                    'notes'
                                ]
                                ?? null,
                        ]);

                    foreach (
                        $calculation[
                            'items'
                        ]
                        as $index => $item
                    ) {
                        $sale
                            ->items()
                            ->create([
                                ...$item,
                                'sort_order' =>
                                    $index + 1,
                            ]);
                    }

                    $payment = null;

                    $initialPayment =
                        round(
                            (float)
                            (
                                $validated[
                                    'initial_payment_amount'
                                ]
                                ?? 0
                            ),
                            2
                        );

                    if (
                        $initialPayment > 0
                    ) {
                        if (
                            $initialPayment >
                            $total
                        ) {
                            throw ValidationException::withMessages([
                                'initial_payment_amount' =>
                                    'El pago inicial no puede superar el total de la venta.',
                            ]);
                        }

                        $payment =
                            $this
                                ->createPayment(
                                    sale:
                                        $sale,

                                    amount:
                                        $initialPayment,

                                    paymentDate:
                                        $validated[
                                            'sale_date'
                                        ],

                                    paymentMethod:
                                        $validated[
                                            'initial_payment_method'
                                        ],

                                    financialAccountId:
                                        $validated[
                                            'initial_payment_financial_account_id'
                                        ]
                                        ?? null,

                                    reference:
                                        $validated[
                                            'initial_payment_reference'
                                        ]
                                        ?? null,

                                    notes:
                                        $validated[
                                            'initial_payment_notes'
                                        ]
                                        ?? null,

                                    userId:
                                        $request
                                            ->user()?->id
                                );
                    }

                    $this
                        ->refreshSaleStatus(
                            $sale
                        );

                    return [
                        'sale' =>
                            $sale,

                        'payment' =>
                            $payment,
                    ];
                }
            );

        /** @var Sale $sale */
        $sale =
            $result['sale'];

        /** @var SalePayment|null $payment */
        $payment =
            $result['payment'];

        /*
        |--------------------------------------------------------------------------
        | SI HUBO PAGO INICIAL → COMPARTIR RECIBO
        |--------------------------------------------------------------------------
        */

        if ($payment) {
            return redirect()
                ->route(
                    'receipts.share',
                    [
                        'payment' =>
                            $payment->id,
                    ]
                )
                ->with(
                    'success',
                    'Venta y pago registrados. El recibo está listo para compartir.'
                );
        }

        return redirect()
            ->route(
                'sales.show',
                $sale
            )
            ->with(
                'success',
                'Venta registrada correctamente.'
            );
    }

    public function show(
        Sale $sale
    ): Response {
        $sale->load([
            'client',
            'items',
            'payments.financialAccount',
            'payments.receiver',
            'quotation',
        ]);

        return Inertia::render(
            'Sales/Show',
            [
                'sale' =>
                    $this
                        ->saleForFrontend(
                            $sale
                        ),

                'financialAccounts' =>
                    $this
                        ->financialAccountsForForm(),
            ]
        );
    }

    public function addPayment(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        $sale->load(
            'payments'
        );

        if (
            $sale->status ===
            'cancelled'
        ) {
            return back()
                ->withErrors([
                    'amount' =>
                        'No se pueden registrar pagos en una venta cancelada.',
                ]);
        }

        if (
            $sale->balance <= 0
        ) {
            return back()
                ->withErrors([
                    'amount' =>
                        'La venta ya está pagada completamente.',
                ]);
        }

        $validated =
            $request->validate([
                'payment_date' => [
                    'required',
                    'date',
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],

                'payment_method' => [
                    'required',

                    Rule::in([
                        'cash',
                        'transfer',
                        'card',
                        'other',
                    ]),
                ],

                'financial_account_id' => [
                    Rule::requiredIf(
                        fn () =>
                            $request
                                ->payment_method
                            !==
                            'cash'
                    ),

                    'nullable',
                    'integer',

                    Rule::exists(
                        'financial_accounts',
                        'id'
                    )->where(
                        fn ($query) =>
                            $query
                                ->where(
                                    'active',
                                    true
                                )
                                ->whereNull(
                                    'deleted_at'
                                )
                    ),
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        $amount =
            round(
                (float)
                $validated['amount'],
                2
            );

        if (
            $amount >
            $sale->balance
        ) {
            return back()
                ->withErrors([
                    'amount' =>
                        'El pago no puede superar el saldo pendiente de L ' .
                        number_format(
                            $sale->balance,
                            2
                        ) .
                        '.',
                ]);
        }

        $payment =
            DB::transaction(
                function () use (
                    $sale,
                    $validated,
                    $amount,
                    $request
                ) {
                    $payment =
                        $this
                            ->createPayment(
                                sale:
                                    $sale,

                                amount:
                                    $amount,

                                paymentDate:
                                    $validated[
                                        'payment_date'
                                    ],

                                paymentMethod:
                                    $validated[
                                        'payment_method'
                                    ],

                                financialAccountId:
                                    $validated[
                                        'payment_method'
                                    ] === 'cash'
                                        ? null
                                        : (
                                            $validated[
                                                'financial_account_id'
                                            ]
                                            ?? null
                                        ),

                                reference:
                                    $validated[
                                        'reference'
                                    ]
                                    ?? null,

                                notes:
                                    $validated[
                                        'notes'
                                    ]
                                    ?? null,

                                userId:
                                    $request
                                        ->user()?->id
                            );

                    $this
                        ->refreshSaleStatus(
                            $sale
                        );

                    return $payment;
                }
            );

        /*
        |--------------------------------------------------------------------------
        | PAGO → COMPARTIR RECIBO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'receipts.share',
                [
                    'payment' =>
                        $payment->id,
                ]
            )
            ->with(
                'success',
                'Pago registrado correctamente. El recibo está listo para compartir.'
            );
    }

    public function convertFromQuotation(
        Quotation $quotation
    ): RedirectResponse {
        $quotation->load([
            'items',
            'client',
        ]);

        if (
            $quotation->status !==
            'approved'
        ) {
            return back()
                ->withErrors([
                    'quotation' =>
                        'Solo las cotizaciones aprobadas pueden convertirse en venta.',
                ]);
        }

        $existing =
            Sale::query()
                ->where(
                    'quotation_id',
                    $quotation->id
                )
                ->first();

        if ($existing) {
            return redirect()
                ->route(
                    'sales.show',
                    $existing
                );
        }

        $sale =
            DB::transaction(
                function () use (
                    $quotation
                ) {
                    $sale =
                        Sale::create([
                            'sale_number' =>
                                $this
                                    ->nextSaleNumber(),

                            'client_id' =>
                                $quotation
                                    ->client_id,

                            'quotation_id' =>
                                $quotation
                                    ->id,

                            'created_by' =>
                                auth()->id(),

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

                    foreach (
                        $quotation->items
                        as $quotationItem
                    ) {
                        $sale
                            ->items()
                            ->create([
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
                                    $quotationItem
                                        ->quantity,

                                'width' =>
                                    $quotationItem
                                        ->width,

                                'height' =>
                                    $quotationItem
                                        ->height,

                                'unit_price' =>
                                    $quotationItem
                                        ->unit_price,

                                'unit_cost' =>
                                    $quotationItem
                                        ->unit_cost,

                                'sale_rate' =>
                                    $quotationItem
                                        ->sale_rate,

                                'cost_rate' =>
                                    $quotationItem
                                        ->cost_rate,

                                'subtotal' =>
                                    $quotationItem
                                        ->subtotal,

                                'estimated_cost' =>
                                    $quotationItem
                                        ->estimated_cost,

                                'sort_order' =>
                                    $quotationItem
                                        ->sort_order,
                            ]);
                    }

                    return $sale;
                }
            );

        return redirect()
            ->route(
                'sales.show',
                $sale
            )
            ->with(
                'success',
                'Cotización convertida en venta correctamente.'
            );
    }

    public function cancel(
        Sale $sale
    ): RedirectResponse {
        $sale->load(
            'payments'
        );

        if (
            $sale
                ->payments
                ->isNotEmpty()
        ) {
            return back()
                ->withErrors([
                    'sale' =>
                        'No puedes cancelar una venta que ya tiene pagos registrados.',
                ]);
        }

        $sale->update([
            'status' =>
                'cancelled',
        ]);

        return back()
            ->with(
                'success',
                'Venta cancelada correctamente.'
            );
    }

    private function validateSale(
        Request $request
    ): array {
        return $request->validate([
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

            'sale_date' => [
                'required',
                'date',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.catalog_item_id' => [
                'required',
                'integer',

                Rule::exists(
                    'catalog_items',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.width' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'items.*.height' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'items.*.manual_unit_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'initial_payment_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'initial_payment_method' => [
                Rule::requiredIf(
                    fn () =>
                        (float)
                        $request
                            ->initial_payment_amount
                        > 0
                ),

                'nullable',

                Rule::in([
                    'cash',
                    'transfer',
                    'card',
                    'other',
                ]),
            ],

            'initial_payment_financial_account_id' => [
                Rule::requiredIf(
                    fn () =>
                        (float)
                        $request
                            ->initial_payment_amount
                        > 0
                        &&
                        $request
                            ->initial_payment_method
                        !== 'cash'
                ),

                'nullable',
                'integer',

                Rule::exists(
                    'financial_accounts',
                    'id'
                )->where(
                    fn ($query) =>
                        $query
                            ->where(
                                'active',
                                true
                            )
                            ->whereNull(
                                'deleted_at'
                            )
                ),
            ],

            'initial_payment_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'initial_payment_notes' => [
                'nullable',
                'string',
            ],
        ]);
    }

    private function calculateItems(
        array $items
    ): array {
        $calculated = [];

        $subtotal = 0;
        $estimatedCost = 0;

        foreach (
            $items
            as $requestItem
        ) {
            $catalogItem =
                CatalogItem::query()
                    ->findOrFail(
                        $requestItem[
                            'catalog_item_id'
                        ]
                    );

            $quantity =
                (float)
                $requestItem[
                    'quantity'
                ];

            $width =
                isset(
                    $requestItem[
                        'width'
                    ]
                )
                &&
                $requestItem[
                    'width'
                ] !== ''
                    ? (float)
                        $requestItem[
                            'width'
                        ]
                    : null;

            $height =
                isset(
                    $requestItem[
                        'height'
                    ]
                )
                &&
                $requestItem[
                    'height'
                ] !== ''
                    ? (float)
                        $requestItem[
                            'height'
                        ]
                    : null;

            $method =
                $catalogItem
                    ->pricing_method;

            $unitPrice = 0;
            $unitCost = 0;

            if (
                $method ===
                'AREA'
            ) {
                if (
                    !$width
                    ||
                    !$height
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            "El artículo {$catalogItem->name} requiere ancho y alto.",
                    ]);
                }

                $area =
                    $width *
                    $height;

                $unitPrice =
                    $area *
                    (float)
                    $catalogItem
                        ->sale_rate;

                $unitCost =
                    $area *
                    (float)
                    $catalogItem
                        ->cost_rate;
            } elseif (
                $method ===
                'LINEAR'
            ) {
                if (
                    !$width
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            "El artículo {$catalogItem->name} requiere una medida lineal.",
                    ]);
                }

                $unitPrice =
                    $width *
                    (float)
                    $catalogItem
                        ->sale_rate;

                $unitCost =
                    $width *
                    (float)
                    $catalogItem
                        ->cost_rate;
            } elseif (
                $method ===
                'MANUAL'
            ) {
                $unitPrice =
                    (float)
                    (
                        $requestItem[
                            'manual_unit_price'
                        ]
                        ?? 0
                    );

                $unitCost =
                    (float)
                    $catalogItem
                        ->cost_price;
            } else {
                $unitPrice =
                    (float)
                    $catalogItem
                        ->sale_price;

                $unitCost =
                    (float)
                    $catalogItem
                        ->cost_price;
            }

            $itemSubtotal =
                round(
                    $unitPrice *
                    $quantity,
                    2
                );

            $itemCost =
                round(
                    $unitCost *
                    $quantity,
                    2
                );

            $subtotal +=
                $itemSubtotal;

            $estimatedCost +=
                $itemCost;

            $calculated[] = [
                'catalog_item_id' =>
                    $catalogItem->id,

                'item_code' =>
                    $catalogItem->item_code,

                'item_name' =>
                    $catalogItem->name,

                'description' =>
                    $catalogItem->description,

                'pricing_method' =>
                    $catalogItem
                        ->pricing_method,

                'measurement_unit' =>
                    $catalogItem
                        ->measurement_unit,

                'quantity' =>
                    $quantity,

                'width' =>
                    $width,

                'height' =>
                    $height,

                'unit_price' =>
                    round(
                        $unitPrice,
                        2
                    ),

                'unit_cost' =>
                    round(
                        $unitCost,
                        2
                    ),

                'sale_rate' =>
                    $catalogItem
                        ->sale_rate,

                'cost_rate' =>
                    $catalogItem
                        ->cost_rate,

                'subtotal' =>
                    $itemSubtotal,

                'estimated_cost' =>
                    $itemCost,
            ];
        }

        return [
            'items' =>
                $calculated,

            'subtotal' =>
                round(
                    $subtotal,
                    2
                ),

            'estimated_cost' =>
                round(
                    $estimatedCost,
                    2
                ),
        ];
    }

    private function createPayment(
        Sale $sale,
        float $amount,
        string $paymentDate,
        string $paymentMethod,
        ?int $financialAccountId,
        ?string $reference,
        ?string $notes,
        ?int $userId
    ): SalePayment {
        if (
            $paymentMethod !==
            'cash'
            &&
            !$financialAccountId
        ) {
            throw ValidationException::withMessages([
                'financial_account_id' =>
                    'Debes seleccionar la cuenta bancaria donde se recibió el pago.',
            ]);
        }

        if (
            $paymentMethod ===
            'cash'
        ) {
            $financialAccountId =
                null;
        }

        return SalePayment::create([
            'receipt_number' =>
                $this
                    ->nextReceiptNumber(),

            'sale_id' =>
                $sale->id,

            'financial_account_id' =>
                $financialAccountId,

            'received_by' =>
                $userId,

            'payment_date' =>
                $paymentDate,

            'amount' =>
                round(
                    $amount,
                    2
                ),

            'payment_method' =>
                $paymentMethod,

            'reference' =>
                $reference,

            'notes' =>
                $notes,
        ]);
    }

    private function refreshSaleStatus(
        Sale $sale
    ): void {
        $sale->load(
            'payments'
        );

        if (
            $sale->status ===
            'cancelled'
        ) {
            return;
        }

        $paid =
            (float)
            $sale->paid_amount;

        $total =
            (float)
            $sale->total;

        if (
            $paid <= 0
        ) {
            $status =
                'pending';
        } elseif (
            $paid <
            $total
        ) {
            $status =
                'partial';
        } else {
            $status =
                'paid';
        }

        $sale->update([
            'status' =>
                $status,
        ]);
    }

    private function nextSaleNumber(): string
    {
        $next =
            (
                Sale::withTrashed()
                    ->max('id')
                ?? 0
            )
            + 1;

        return
            'VEN-' .
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

    private function nextReceiptNumber(): string
    {
        $next =
            (
                SalePayment::withTrashed()
                    ->max('id')
                ?? 0
            )
            + 1;

        return
            'REC-' .
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

    private function catalogForForm(): array
    {
        return CatalogItem::query()
            ->active()
            ->orderBy(
                'name'
            )
            ->get()
            ->map(
                fn (
                    CatalogItem $item
                ) => [
                    'id' =>
                        $item->id,

                    'code' =>
                        $item
                            ->item_code,

                    'name' =>
                        $item
                            ->name,

                    'description' =>
                        $item
                            ->description,

                    'pricing_method' =>
                        $item
                            ->pricing_method,

                    'measurement_unit' =>
                        $item
                            ->measurement_unit,

                    'sale_price' =>
                        $item
                            ->sale_price,

                    'cost_price' =>
                        $item
                            ->cost_price,

                    'sale_rate' =>
                        $item
                            ->sale_rate,

                    'cost_rate' =>
                        $item
                            ->cost_rate,
                ]
            )
            ->values()
            ->all();
    }

    private function financialAccountsForForm(): array
    {
        return FinancialAccount::query()
            ->active()
            ->orderBy(
                'name'
            )
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

    private function saleForFrontend(
        Sale $sale
    ): array {
        return [
            'id' =>
                $sale->id,

            'sale_number' =>
                $sale
                    ->sale_number,

            'sale_date' =>
                $sale
                    ->sale_date
                    ?->format('Y-m-d'),

            'status' =>
                $sale->status,

            'subtotal' =>
                $sale->subtotal,

            'discount' =>
                $sale->discount,

            'total' =>
                $sale->total,

            'estimated_cost' =>
                $sale
                    ->estimated_cost,

            'paid_amount' =>
                $sale
                    ->paid_amount,

            'balance' =>
                $sale
                    ->balance,

            'notes' =>
                $sale->notes,

            'quotation_id' =>
                $sale
                    ->quotation_id,

            'quotation_number' =>
                $sale
                    ->quotation
                    ?->quotation_number,

            'client' => [
                'id' =>
                    $sale
                        ->client?->id,

                'code' =>
                    $sale
                        ->client
                        ?->client_code,

                'name' =>
                    $sale
                        ->client
                        ?->display_name,

                'phone' =>
                    $sale
                        ->client
                        ?->phone,

                'email' =>
                    $sale
                        ->client
                        ?->email,

                'address' =>
                    $sale
                        ->client
                        ?->address,
            ],

            'items' =>
                $sale
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

                            'pricing_method' =>
                                $item
                                    ->pricing_method,

                            'measurement_unit' =>
                                $item
                                    ->measurement_unit,

                            'quantity' =>
                                $item
                                    ->quantity,

                            'width' =>
                                $item
                                    ->width,

                            'height' =>
                                $item
                                    ->height,

                            'unit_price' =>
                                $item
                                    ->unit_price,

                            'subtotal' =>
                                $item
                                    ->subtotal,
                        ]
                    )
                    ->values(),

            'payments' =>
                $sale
                    ->payments
                    ->map(
                        fn (
                            SalePayment $payment
                        ) => [
                            'id' =>
                                $payment->id,

                            'receipt_number' =>
                                $payment
                                    ->receipt_number,

                            'payment_date' =>
                                $payment
                                    ->payment_date
                                    ?->format('Y-m-d'),

                            'amount' =>
                                $payment
                                    ->amount,

                            'payment_method' =>
                                $payment
                                    ->payment_method,

                            'financial_account_id' =>
                                $payment
                                    ->financial_account_id,

                            'financial_account' =>
                                $payment
                                    ->financialAccount
                                    ?->name,

                            'reference' =>
                                $payment
                                    ->reference,

                            'notes' =>
                                $payment
                                    ->notes,

                            'received_by' =>
                                $payment
                                    ->receiver
                                    ?->name,
                        ]
                    )
                    ->values(),
        ];
    }
}