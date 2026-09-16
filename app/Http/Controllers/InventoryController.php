<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(): Response
    {
        $items = InventoryItem::query()
            ->orderBy('name')
            ->get()
            ->map(
                fn (InventoryItem $item) =>
                    $this->itemForFrontend($item)
            )
            ->values();

        return Inertia::render('Inventory/Index', [
            'items' => $items,
        ]);
    }

    public function movements(): Response
    {
        $movements = InventoryMovement::query()
            ->with([
                'item',
                'creator',
            ])
            ->latest('moved_at')
            ->latest('id')
            ->get()
            ->map(
                fn (InventoryMovement $movement) =>
                    $this->movementForFrontend($movement)
            )
            ->values();

        return Inertia::render(
            'Inventory/Movements',
            [
                'item' => null,
                'movements' => $movements,
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Inventory/Create'
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validateItem($request, true);

        $item = DB::transaction(
            function () use (
                $validated,
                $request
            ) {
                $initialStock = round(
                    (float) (
                        $validated[
                            'initial_stock'
                        ] ?? 0
                    ),
                    3
                );

                $item = InventoryItem::create([
                    'item_code' =>
                        $this->nextItemCode(),

                    'name' =>
                        $validated['name'],

                    'category' =>
                        $validated['category']
                        ?? null,

                    'measurement_unit' =>
                        $validated[
                            'measurement_unit'
                        ],

                    'current_stock' =>
                        $initialStock,

                    'minimum_stock' =>
                        round(
                            (float) (
                                $validated[
                                    'minimum_stock'
                                ] ?? 0
                            ),
                            3
                        ),

                    'unit_cost' =>
                        round(
                            (float) (
                                $validated[
                                    'unit_cost'
                                ] ?? 0
                            ),
                            4
                        ),

                    'supplier' =>
                        $validated['supplier']
                        ?? null,

                    'location' =>
                        $validated['location']
                        ?? null,

                    'active' =>
                        $validated['active']
                        ?? true,

                    'notes' =>
                        $validated['notes']
                        ?? null,
                ]);

                if ($initialStock > 0) {
                    InventoryMovement::create([
                        'inventory_item_id' =>
                            $item->id,

                        'created_by' =>
                            $request->user()?->id,

                        'movement_type' =>
                            'entry',

                        'quantity' =>
                            $initialStock,

                        'previous_stock' =>
                            0,

                        'resulting_stock' =>
                            $initialStock,

                        'unit_cost' =>
                            $item->unit_cost,

                        'reference' =>
                            'STOCK-INICIAL',

                        'notes' =>
                            'Stock inicial del material.',

                        'moved_at' =>
                            now(),
                    ]);
                }

                return $item;
            }
        );

        return redirect(
            '/inventory/' .
            $item->id
        )->with(
            'success',
            'Material creado correctamente.'
        );
    }

    public function show(
        InventoryItem $inventoryItem
    ): Response {
        $inventoryItem->load([
            'movements' => fn ($query) =>
                $query
                    ->with('creator')
                    ->latest('moved_at')
                    ->latest('id')
                    ->limit(20),
        ]);

        return Inertia::render(
            'Inventory/Show',
            [
                'item' =>
                    $this->itemForFrontend(
                        $inventoryItem
                    ),

                'movements' =>
                    $inventoryItem
                        ->movements
                        ->map(
                            fn (
                                InventoryMovement $movement
                            ) =>
                                $this
                                    ->movementForFrontend(
                                        $movement
                                    )
                        )
                        ->values(),
            ]
        );
    }

    public function edit(
        InventoryItem $inventoryItem
    ): Response {
        return Inertia::render(
            'Inventory/Edit',
            [
                'item' =>
                    $this->itemForFrontend(
                        $inventoryItem
                    ),
            ]
        );
    }

    public function update(
        Request $request,
        InventoryItem $inventoryItem
    ): RedirectResponse {
        $validated =
            $this->validateItem(
                $request,
                false
            );

        $inventoryItem->update([
            'name' =>
                $validated['name'],

            'category' =>
                $validated['category']
                ?? null,

            'measurement_unit' =>
                $validated[
                    'measurement_unit'
                ],

            'minimum_stock' =>
                round(
                    (float) (
                        $validated[
                            'minimum_stock'
                        ] ?? 0
                    ),
                    3
                ),

            'unit_cost' =>
                round(
                    (float) (
                        $validated[
                            'unit_cost'
                        ] ?? 0
                    ),
                    4
                ),

            'supplier' =>
                $validated['supplier']
                ?? null,

            'location' =>
                $validated['location']
                ?? null,

            'active' =>
                $validated['active']
                ?? true,

            'notes' =>
                $validated['notes']
                ?? null,
        ]);

        return redirect(
            '/inventory/' .
            $inventoryItem->id
        )->with(
            'success',
            'Material actualizado correctamente.'
        );
    }

    public function storeAdjustment(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'inventory_item_id' => [
                'required',
                'integer',
                Rule::exists(
                    'inventory_items',
                    'id'
                )->whereNull('deleted_at'),
            ],

            'movement_type' => [
                'required',
                Rule::in([
                    'entry',
                    'exit',
                    'adjustment_in',
                    'adjustment_out',
                ]),
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
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

        DB::transaction(
            function () use (
                $validated,
                $request
            ) {
                $item = InventoryItem::query()
                    ->whereKey(
                        $validated[
                            'inventory_item_id'
                        ]
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = round(
                    (float)
                    $validated['quantity'],
                    3
                );

                $previousStock =
                    (float)
                    $item->current_stock;

                $isIncoming = in_array(
                    $validated['movement_type'],
                    [
                        'entry',
                        'adjustment_in',
                    ],
                    true
                );

                $resultingStock =
                    $isIncoming
                        ? $previousStock
                            + $quantity
                        : $previousStock
                            - $quantity;

                if ($resultingStock < 0) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                            'No hay suficiente existencia. Stock disponible: ' .
                            number_format(
                                $previousStock,
                                3
                            ) .
                            ' ' .
                            $item
                                ->measurement_unit .
                            '.',
                    ]);
                }

                $movementUnitCost =
                    isset(
                        $validated['unit_cost']
                    )
                    && $validated['unit_cost']
                        !== null
                        ? round(
                            (float)
                            $validated[
                                'unit_cost'
                            ],
                            4
                        )
                        : (float)
                            $item->unit_cost;

                $newAverageCost =
                    (float)
                    $item->unit_cost;

                if (
                    $isIncoming
                    && $movementUnitCost > 0
                    && $resultingStock > 0
                ) {
                    $previousValue =
                        $previousStock *
                        (float)
                        $item->unit_cost;

                    $incomingValue =
                        $quantity *
                        $movementUnitCost;

                    $newAverageCost =
                        round(
                            (
                                $previousValue
                                +
                                $incomingValue
                            )
                            /
                            $resultingStock,
                            4
                        );
                }

                InventoryMovement::create([
                    'inventory_item_id' =>
                        $item->id,

                    'created_by' =>
                        $request
                            ->user()?->id,

                    'movement_type' =>
                        $validated[
                            'movement_type'
                        ],

                    'quantity' =>
                        $quantity,

                    'previous_stock' =>
                        $previousStock,

                    'resulting_stock' =>
                        $resultingStock,

                    'unit_cost' =>
                        $movementUnitCost,

                    'reference' =>
                        $validated[
                            'reference'
                        ] ?? null,

                    'notes' =>
                        $validated[
                            'notes'
                        ] ?? null,

                    'moved_at' =>
                        now(),
                ]);

                $item->update([
                    'current_stock' =>
                        round(
                            $resultingStock,
                            3
                        ),

                    'unit_cost' =>
                        $newAverageCost,
                ]);
            }
        );

        return back()->with(
            'success',
            'Movimiento de inventario registrado correctamente.'
        );
    }

    public function itemMovements(
        InventoryItem $inventoryItem
    ): Response {
        $movements =
            InventoryMovement::query()
                ->with([
                    'item',
                    'creator',
                ])
                ->where(
                    'inventory_item_id',
                    $inventoryItem->id
                )
                ->latest('moved_at')
                ->latest('id')
                ->get()
                ->map(
                    fn (
                        InventoryMovement $movement
                    ) =>
                        $this
                            ->movementForFrontend(
                                $movement
                            )
                )
                ->values();

        return Inertia::render(
            'Inventory/Movements',
            [
                'item' =>
                    $this->itemForFrontend(
                        $inventoryItem
                    ),

                'movements' =>
                    $movements,
            ]
        );
    }

    private function validateItem(
        Request $request,
        bool $creating
    ): array {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'measurement_unit' => [
                'required',
                'string',
                'max:50',
            ],

            'minimum_stock' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'supplier' => [
                'nullable',
                'string',
                'max:150',
            ],

            'location' => [
                'nullable',
                'string',
                'max:150',
            ],

            'active' => [
                'boolean',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];

        if ($creating) {
            $rules['initial_stock'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        return $request->validate(
            $rules
        );
    }

    private function nextItemCode(): string
    {
        $next =
            (
                InventoryItem::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'MAT-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    private function itemForFrontend(
        InventoryItem $item
    ): array {
        $stock =
            (float)
            $item->current_stock;

        $minimum =
            (float)
            $item->minimum_stock;

        if ($stock <= 0) {
            $stockStatus =
                'out';
        } elseif (
            $minimum > 0
            && $stock <= $minimum
        ) {
            $stockStatus =
                'low';
        } else {
            $stockStatus =
                'ok';
        }

        return [
            'id' =>
                $item->id,

            'item_code' =>
                $item->item_code,

            'name' =>
                $item->name,

            'category' =>
                $item->category,

            'measurement_unit' =>
                $item->measurement_unit,

            'current_stock' =>
                $item->current_stock,

            'minimum_stock' =>
                $item->minimum_stock,

            'unit_cost' =>
                $item->unit_cost,

            'stock_value' =>
                round(
                    $stock *
                    (float)
                    $item->unit_cost,
                    2
                ),

            'stock_status' =>
                $stockStatus,

            'supplier' =>
                $item->supplier,

            'location' =>
                $item->location,

            'active' =>
                $item->active,

            'notes' =>
                $item->notes,
        ];
    }

    private function movementForFrontend(
        InventoryMovement $movement
    ): array {
        return [
            'id' =>
                $movement->id,

            'inventory_item_id' =>
                $movement
                    ->inventory_item_id,

            'item_code' =>
                $movement
                    ->item
                    ?->item_code,

            'item_name' =>
                $movement
                    ->item
                    ?->name,

            'measurement_unit' =>
                $movement
                    ->item
                    ?->measurement_unit,

            'movement_type' =>
                $movement
                    ->movement_type,

            'movement_label' =>
                $this
                    ->movementLabel(
                        $movement
                            ->movement_type
                    ),

            'quantity' =>
                $movement->quantity,

            'previous_stock' =>
                $movement
                    ->previous_stock,

            'resulting_stock' =>
                $movement
                    ->resulting_stock,

            'unit_cost' =>
                $movement->unit_cost,

            'reference' =>
                $movement->reference,

            'notes' =>
                $movement->notes,

            'moved_at' =>
                $movement
                    ->moved_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'created_by' =>
                $movement
                    ->creator
                    ?->name,
        ];
    }

    private function movementLabel(
        string $type
    ): string {
        return match ($type) {
            'entry' =>
                'Entrada',

            'exit' =>
                'Salida',

            'adjustment_in' =>
                'Ajuste positivo',

            'adjustment_out' =>
                'Ajuste negativo',

            default =>
                'Movimiento',
        };
    }
}