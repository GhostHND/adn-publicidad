<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    public function index(): Response
    {
        $quotations = Quotation::query()
            ->with('client')
            ->latest('id')
            ->get()
            ->map(fn (Quotation $quotation) => [
                'id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'client' => $quotation->client?->display_name ?? 'Sin cliente',
                'quotation_date' => $quotation->quotation_date?->format('Y-m-d'),
                'valid_until' => $quotation->valid_until?->format('Y-m-d'),
                'status' => $quotation->status,
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'total' => $quotation->total,
            ]);

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Quotations/Create', [
            'clients' => $this->clientsForForm(),
            'catalogItems' => $this->catalogForForm(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateQuotation($request);

        $quotation = DB::transaction(function () use ($validated, $request) {
            $calculation = $this->calculateItems($validated['items']);

            $nextNumber = (Quotation::withTrashed()->max('id') ?? 0) + 1;

            $quotationNumber =
                'COT-' .
                now()->format('Y') .
                '-' .
                str_pad(
                    (string) $nextNumber,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $discount = round(
                min(
                    (float) ($validated['discount'] ?? 0),
                    $calculation['subtotal']
                ),
                2
            );

            $quotation = Quotation::create([
                'quotation_number' => $quotationNumber,
                'client_id' => $validated['client_id'],
                'created_by' => $request->user()?->id,
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'status' => 'draft',
                'subtotal' => $calculation['subtotal'],
                'discount' => $discount,
                'total' => round(
                    $calculation['subtotal'] - $discount,
                    2
                ),
                'estimated_cost' => $calculation['estimated_cost'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($calculation['items'] as $index => $item) {
                $quotation->items()->create([
                    ...$item,
                    'sort_order' => $index,
                ]);
            }

            return $quotation;
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with(
                'success',
                'Cotización creada correctamente.'
            );
    }

    public function show(Quotation $quotation): Response
    {
        $quotation->load([
            'client',
            'items',
        ]);

        return Inertia::render('Quotations/Show', [
            'quotation' =>
                $this->quotationForFrontend(
                    $quotation
                ),
        ]);
    }

    public function edit(Quotation $quotation): Response
    {
        $quotation->load('items');

        return Inertia::render('Quotations/Edit', [
            'quotation' => [
                'id' =>
                    $quotation->id,

                'quotation_number' =>
                    $quotation->quotation_number,

                'client_id' =>
                    $quotation->client_id,

                'quotation_date' =>
                    $quotation
                        ->quotation_date
                        ?->format('Y-m-d'),

                'valid_until' =>
                    $quotation
                        ->valid_until
                        ?->format('Y-m-d'),

                'discount' =>
                    $quotation->discount,

                'notes' =>
                    $quotation->notes,

                'items' =>
                    $quotation
                        ->items
                        ->map(
                            fn ($item) => [
                                'catalog_item_id' =>
                                    $item
                                        ->catalog_item_id,

                                'quantity' =>
                                    $item->quantity,

                                'width' =>
                                    $item->width,

                                'height' =>
                                    $item->height,

                                'manual_price' =>
                                    $item->pricing_method === 'MANUAL'
                                        ? $item->unit_price
                                        : '',
                            ]
                        )
                        ->values(),
            ],

            'clients' =>
                $this->clientsForForm(),

            'catalogItems' =>
                $this->catalogForForm(),
        ]);
    }

    public function update(
        Request $request,
        Quotation $quotation
    ): RedirectResponse {
        $validated =
            $this->validateQuotation(
                $request
            );

        DB::transaction(function () use (
            $validated,
            $quotation
        ) {
            $calculation =
                $this->calculateItems(
                    $validated['items']
                );

            $discount = round(
                min(
                    (float) ($validated['discount'] ?? 0),
                    $calculation['subtotal']
                ),
                2
            );

            $quotation->update([
                'client_id' =>
                    $validated['client_id'],

                'quotation_date' =>
                    $validated['quotation_date'],

                'valid_until' =>
                    $validated['valid_until'] ?? null,

                'subtotal' =>
                    $calculation['subtotal'],

                'discount' =>
                    $discount,

                'total' =>
                    round(
                        $calculation['subtotal'] - $discount,
                        2
                    ),

                'estimated_cost' =>
                    $calculation['estimated_cost'],

                'notes' =>
                    $validated['notes'] ?? null,
            ]);

            $quotation
                ->items()
                ->delete();

            foreach (
                $calculation['items']
                as $index => $item
            ) {
                $quotation
                    ->items()
                    ->create([
                        ...$item,
                        'sort_order' => $index,
                    ]);
            }
        });

        return redirect()
            ->route(
                'quotations.show',
                $quotation
            )
            ->with(
                'success',
                'Cotización actualizada correctamente.'
            );
    }

    public function updateStatus(
        Request $request,
        Quotation $quotation
    ): RedirectResponse {
        $validated =
            $request->validate([
                'status' => [
                    'required',

                    Rule::in([
                        'draft',
                        'sent',
                        'approved',
                        'rejected',
                        'expired',
                    ]),
                ],
            ]);

        $quotation->update([
            'status' =>
                $validated['status'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | ENVIADA → COMPARTIR
        |--------------------------------------------------------------------------
        |
        | El observer genera el PDF.
        | Este controlador redirige el navegador a la pantalla de compartir.
        |
        */

        if (
            $validated['status'] ===
            'sent'
        ) {
            return redirect()
                ->route(
                    'quotations.share',
                    [
                        'quotation' =>
                            $quotation->id,
                    ]
                )
                ->with(
                    'success',
                    'Cotización marcada como enviada. El PDF está listo para compartir.'
                );
        }

        return redirect()
            ->route(
                'quotations.show',
                $quotation
            )
            ->with(
                'success',
                'Estado actualizado correctamente.'
            );
    }

    private function validateQuotation(
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

            'quotation_date' => [
                'required',
                'date',
            ],

            'valid_until' => [
                'nullable',
                'date',
                'after_or_equal:quotation_date',
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

            'items.*.manual_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);
    }

    private function calculateItems(
        array $items
    ): array {
        $calculatedItems = [];
        $subtotal = 0;
        $estimatedCost = 0;

        foreach (
            $items
            as $itemData
        ) {
            $catalogItem =
                CatalogItem::query()
                    ->findOrFail(
                        $itemData[
                            'catalog_item_id'
                        ]
                    );

            $quantity =
                (float)
                $itemData['quantity'];

            $width =
                isset(
                    $itemData['width']
                )
                    ? (float)
                        $itemData['width']
                    : null;

            $height =
                isset(
                    $itemData['height']
                )
                    ? (float)
                        $itemData['height']
                    : null;

            $lineSubtotal = 0;
            $lineCost = 0;
            $unitPrice = null;
            $unitCost = null;
            $saleRate = null;
            $costRate = null;

            switch (
                $catalogItem
                    ->pricing_method
            ) {
                case 'AREA':
                    if (
                        !$width
                        ||
                        !$height
                    ) {
                        abort(
                            422,
                            "El producto {$catalogItem->name} requiere ancho y alto."
                        );
                    }

                    $saleRate =
                        (float)
                        (
                            $catalogItem
                                ->sale_rate
                            ?? 0
                        );

                    $costRate =
                        (float)
                        (
                            $catalogItem
                                ->cost_rate
                            ?? 0
                        );

                    $area =
                        $width *
                        $height;

                    $lineSubtotal =
                        $area *
                        $saleRate *
                        $quantity;

                    $lineCost =
                        $area *
                        $costRate *
                        $quantity;

                    break;

                case 'LINEAR':
                    if (!$width) {
                        abort(
                            422,
                            "El producto {$catalogItem->name} requiere una medida."
                        );
                    }

                    $saleRate =
                        (float)
                        (
                            $catalogItem
                                ->sale_rate
                            ?? 0
                        );

                    $costRate =
                        (float)
                        (
                            $catalogItem
                                ->cost_rate
                            ?? 0
                        );

                    $lineSubtotal =
                        $width *
                        $saleRate *
                        $quantity;

                    $lineCost =
                        $width *
                        $costRate *
                        $quantity;

                    break;

                case 'MANUAL':
                    $unitPrice =
                        (float)
                        (
                            $itemData[
                                'manual_price'
                            ]
                            ?? 0
                        );

                    if (
                        $unitPrice < 0
                    ) {
                        abort(
                            422,
                            "El precio de {$catalogItem->name} no es válido."
                        );
                    }

                    $unitCost =
                        (float)
                        (
                            $catalogItem
                                ->cost_price
                            ?? 0
                        );

                    $lineSubtotal =
                        $unitPrice *
                        $quantity;

                    $lineCost =
                        $unitCost *
                        $quantity;

                    break;

                case 'UNIT':
                case 'FIXED':
                case 'RESALE':
                case 'COST_MARGIN':
                    $unitPrice =
                        (float)
                        (
                            $catalogItem
                                ->sale_price
                            ?? 0
                        );

                    $unitCost =
                        (float)
                        (
                            $catalogItem
                                ->cost_price
                            ?? 0
                        );

                    $lineSubtotal =
                        $unitPrice *
                        $quantity;

                    $lineCost =
                        $unitCost *
                        $quantity;

                    break;
            }

            $lineSubtotal =
                round(
                    $lineSubtotal,
                    2
                );

            $lineCost =
                round(
                    $lineCost,
                    2
                );

            $subtotal +=
                $lineSubtotal;

            $estimatedCost +=
                $lineCost;

            $calculatedItems[] = [
                'catalog_item_id' =>
                    $catalogItem->id,

                'item_code' =>
                    $catalogItem->item_code,

                'item_name' =>
                    $catalogItem->name,

                'description' =>
                    $catalogItem->description,

                'pricing_method' =>
                    $catalogItem->pricing_method,

                'measurement_unit' =>
                    $catalogItem->measurement_unit,

                'quantity' =>
                    $quantity,

                'width' =>
                    $width,

                'height' =>
                    $height,

                'unit_price' =>
                    $unitPrice,

                'unit_cost' =>
                    $unitCost,

                'sale_rate' =>
                    $saleRate,

                'cost_rate' =>
                    $costRate,

                'subtotal' =>
                    $lineSubtotal,

                'estimated_cost' =>
                    $lineCost,
            ];
        }

        return [
            'items' =>
                $calculatedItems,

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
                        $client->client_code,

                    'name' =>
                        $client->display_name,
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

                    'item_code' =>
                        $item->item_code,

                    'name' =>
                        $item->name,

                    'pricing_method' =>
                        $item->pricing_method,

                    'measurement_unit' =>
                        $item->measurement_unit,

                    'sale_price' =>
                        $item->sale_price,

                    'cost_price' =>
                        $item->cost_price,

                    'sale_rate' =>
                        $item->sale_rate,

                    'cost_rate' =>
                        $item->cost_rate,
                ]
            )
            ->values()
            ->all();
    }

    private function quotationForFrontend(
        Quotation $quotation
    ): array {
        return [
            'id' =>
                $quotation->id,

            'quotation_number' =>
                $quotation
                    ->quotation_number,

            'client' =>
                $quotation
                    ->client
                    ?->display_name,

            'client_code' =>
                $quotation
                    ->client
                    ?->client_code,

            'quotation_date' =>
                $quotation
                    ->quotation_date
                    ?->format('Y-m-d'),

            'valid_until' =>
                $quotation
                    ->valid_until
                    ?->format('Y-m-d'),

            'status' =>
                $quotation->status,

            'subtotal' =>
                $quotation->subtotal,

            'discount' =>
                $quotation->discount,

            'total' =>
                $quotation->total,

            'estimated_cost' =>
                $quotation
                    ->estimated_cost,

            'notes' =>
                $quotation->notes,

            'items' =>
                $quotation
                    ->items
                    ->map(
                        fn ($item) => [
                            'id' =>
                                $item->id,

                            'item_code' =>
                                $item->item_code,

                            'item_name' =>
                                $item->item_name,

                            'description' =>
                                $item->description,

                            'pricing_method' =>
                                $item->pricing_method,

                            'measurement_unit' =>
                                $item->measurement_unit,

                            'quantity' =>
                                $item->quantity,

                            'width' =>
                                $item->width,

                            'height' =>
                                $item->height,

                            'unit_price' =>
                                $item->unit_price,

                            'sale_rate' =>
                                $item->sale_rate,

                            'subtotal' =>
                                $item->subtotal,
                        ]
                    )
                    ->values(),
        ];
    }
}