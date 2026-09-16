<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CatalogItemController extends Controller
{
    public function index(): Response
    {
        $items =
            CatalogItem::query()
                ->orderBy('name')
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

                        'description' =>
                            $item->description,

                        'item_type' =>
                            $item->item_type,

                        'category' =>
                            $item->category,

                        'pricing_method' =>
                            $item
                                ->pricing_method,

                        'measurement_unit' =>
                            $item
                                ->measurement_unit,

                        'cost_price' =>
                            $item->cost_price,

                        'sale_price' =>
                            $item->sale_price,

                        'cost_rate' =>
                            $item->cost_rate,

                        'sale_rate' =>
                            $item->sale_rate,

                        'margin_percentage' =>
                            $item
                                ->margin_percentage,

                        'active' =>
                            $item->active,
                    ]
                )
                ->values();

        return Inertia::render(
            'Catalog/Index',
            [
                'items' =>
                    $items,
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Catalog/Create',
            [
                'inventoryItems' =>
                    $this
                        ->inventoryItemsForForm(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this
                ->validateCatalogItem(
                    $request
                );

        $item = DB::transaction(
            function () use (
                $validated
            ) {
                $item =
                    CatalogItem::create(
                        array_merge(
                            [
                                'item_code' =>
                                    $this
                                        ->nextItemCode(),
                            ],
                            $this
                                ->catalogData(
                                    $validated
                                )
                        )
                    );

                $this
                    ->syncProductionConfiguration(
                        $item,
                        $validated
                    );

                return $item;
            }
        );

        return redirect(
            '/catalog/' .
            $item->id .
            '/edit'
        )->with(
            'success',
            'Producto o servicio creado correctamente.'
        );
    }

    public function show(
        CatalogItem $catalogItem
    ): RedirectResponse {
        return redirect(
            '/catalog/' .
            $catalogItem->id .
            '/edit'
        );
    }

    public function edit(
        CatalogItem $catalogItem
    ): Response {
        $catalogItem->load([
            'productionSteps',
            'materialRecipes.inventoryItem',
        ]);

        return Inertia::render(
            'Catalog/Edit',
            [
                'item' =>
                    $this
                        ->itemForEdit(
                            $catalogItem
                        ),

                'inventoryItems' =>
                    $this
                        ->inventoryItemsForForm(),
            ]
        );
    }

    public function update(
        Request $request,
        CatalogItem $catalogItem
    ): RedirectResponse {
        $validated =
            $this
                ->validateCatalogItem(
                    $request
                );

        DB::transaction(
            function () use (
                $validated,
                $catalogItem
            ) {
                $catalogItem->update(
                    $this
                        ->catalogData(
                            $validated
                        )
                );

                $this
                    ->syncProductionConfiguration(
                        $catalogItem,
                        $validated
                    );
            }
        );

        return redirect(
            '/catalog/' .
            $catalogItem->id .
            '/edit'
        )->with(
            'success',
            'Catálogo y configuración de producción actualizados correctamente.'
        );
    }

    private function validateCatalogItem(
        Request $request
    ): array {
        $method =
            $request->input(
                'pricing_method'
            );

        $rules = [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'item_type' => [
                'required',
                Rule::in([
                    'product',
                    'service',
                    'custom',
                ]),
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'pricing_method' => [
                'required',
                Rule::in([
                    'AREA',
                    'UNIT',
                    'FIXED',
                    'LINEAR',
                    'COST_MARGIN',
                    'RESALE',
                    'MANUAL',
                ]),
            ],

            'measurement_unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'cost_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'cost_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'sale_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'margin_percentage' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'active' => [
                'boolean',
            ],

            'production_steps' => [
                'nullable',
                'array',
            ],

            'production_steps.*.title' => [
                'required',
                'string',
                'max:200',
            ],

            'production_steps.*.description' => [
                'nullable',
                'string',
            ],

            'material_recipes' => [
                'nullable',
                'array',
            ],

            'material_recipes.*.inventory_item_id' => [
                'required',
                'integer',
                'distinct',

                Rule::exists(
                    'inventory_items',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ],

            'material_recipes.*.calculation_method' => [
                'required',

                Rule::in([
                    'UNIT',
                    'AREA',
                    'LINEAR',
                    'FIXED',
                ]),
            ],

            'material_recipes.*.quantity_rate' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'material_recipes.*.waste_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'material_recipes.*.notes' => [
                'nullable',
                'string',
            ],
        ];

        if (
            in_array(
                $method,
                [
                    'AREA',
                    'LINEAR',
                ],
                true
            )
        ) {
            $rules[
                'measurement_unit'
            ] = [
                'required',
                'string',
                'max:50',
            ];

            $rules[
                'sale_rate'
            ] = [
                'required',
                'numeric',
                'min:0',
            ];
        }

        if (
            in_array(
                $method,
                [
                    'UNIT',
                    'FIXED',
                    'RESALE',
                ],
                true
            )
        ) {
            $rules[
                'sale_price'
            ] = [
                'required',
                'numeric',
                'min:0',
            ];
        }

        if (
            $method ===
            'COST_MARGIN'
        ) {
            $rules[
                'cost_price'
            ] = [
                'required',
                'numeric',
                'min:0',
            ];

            $rules[
                'margin_percentage'
            ] = [
                'required',
                'numeric',
                'min:0',
            ];
        }

        return $request
            ->validate(
                $rules
            );
    }

    private function catalogData(
        array $validated
    ): array {
        $method =
            $validated[
                'pricing_method'
            ];

        $data = [
            'name' =>
                $validated['name'],

            'description' =>
                $validated[
                    'description'
                ] ?? null,

            'item_type' =>
                $validated[
                    'item_type'
                ],

            'category' =>
                $validated[
                    'category'
                ] ?? null,

            'pricing_method' =>
                $method,

            'measurement_unit' =>
                $validated[
                    'measurement_unit'
                ] ?? null,

            'cost_price' =>
                $validated[
                    'cost_price'
                ] ?? null,

            'sale_price' =>
                $validated[
                    'sale_price'
                ] ?? null,

            'cost_rate' =>
                $validated[
                    'cost_rate'
                ] ?? null,

            'sale_rate' =>
                $validated[
                    'sale_rate'
                ] ?? null,

            'margin_percentage' =>
                $validated[
                    'margin_percentage'
                ] ?? null,

            'active' =>
                $validated[
                    'active'
                ] ?? true,
        ];

        if (
            in_array(
                $method,
                [
                    'AREA',
                    'LINEAR',
                ],
                true
            )
        ) {
            $data['cost_price'] =
                null;

            $data['sale_price'] =
                null;

            $data['margin_percentage'] =
                null;
        }

        if (
            in_array(
                $method,
                [
                    'UNIT',
                    'FIXED',
                    'RESALE',
                ],
                true
            )
        ) {
            $data['cost_rate'] =
                null;

            $data['sale_rate'] =
                null;

            $data['margin_percentage'] =
                null;
        }

        if (
            $method ===
            'COST_MARGIN'
        ) {
            $cost =
                (float)
                $validated[
                    'cost_price'
                ];

            $margin =
                (float)
                $validated[
                    'margin_percentage'
                ];

            $data['sale_price'] =
                round(
                    $cost *
                    (
                        1 +
                        $margin / 100
                    ),
                    2
                );

            $data['cost_rate'] =
                null;

            $data['sale_rate'] =
                null;
        }

        if (
            $method ===
            'MANUAL'
        ) {
            $data['sale_price'] =
                null;

            $data['cost_rate'] =
                null;

            $data['sale_rate'] =
                null;

            $data['margin_percentage'] =
                null;
        }

        return $data;
    }

    private function syncProductionConfiguration(
        CatalogItem $item,
        array $validated
    ): void {
        $item
            ->productionSteps()
            ->delete();

        foreach (
            $validated[
                'production_steps'
            ] ?? []
            as $index =>
                $step
        ) {
            $item
                ->productionSteps()
                ->create([
                    'title' =>
                        trim(
                            $step[
                                'title'
                            ]
                        ),

                    'description' =>
                        $step[
                            'description'
                        ] ?? null,

                    'sort_order' =>
                        ($index + 1)
                        * 10,

                    'active' =>
                        true,
                ]);
        }

        $item
            ->materialRecipes()
            ->delete();

        foreach (
            $validated[
                'material_recipes'
            ] ?? []
            as $recipe
        ) {
            $item
                ->materialRecipes()
                ->create([
                    'inventory_item_id' =>
                        $recipe[
                            'inventory_item_id'
                        ],

                    'calculation_method' =>
                        $recipe[
                            'calculation_method'
                        ],

                    'quantity_rate' =>
                        round(
                            (float)
                            $recipe[
                                'quantity_rate'
                            ],
                            6
                        ),

                    'waste_percentage' =>
                        round(
                            (float)
                            (
                                $recipe[
                                    'waste_percentage'
                                ]
                                ?? 0
                            ),
                            2
                        ),

                    'notes' =>
                        $recipe[
                            'notes'
                        ] ?? null,

                    'active' =>
                        true,
                ]);
        }
    }

    private function inventoryItemsForForm(): array
    {
        return InventoryItem::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(
                fn (
                    InventoryItem $item
                ) => [
                    'id' =>
                        $item->id,

                    'code' =>
                        $item->item_code,

                    'name' =>
                        $item->name,

                    'measurement_unit' =>
                        $item
                            ->measurement_unit,

                    'current_stock' =>
                        $item
                            ->current_stock,

                    'unit_cost' =>
                        $item
                            ->unit_cost,
                ]
            )
            ->values()
            ->all();
    }

    private function itemForEdit(
        CatalogItem $item
    ): array {
        return [
            'id' =>
                $item->id,

            'item_code' =>
                $item
                    ->item_code,

            'name' =>
                $item->name,

            'description' =>
                $item
                    ->description,

            'item_type' =>
                $item
                    ->item_type,

            'category' =>
                $item
                    ->category,

            'pricing_method' =>
                $item
                    ->pricing_method,

            'measurement_unit' =>
                $item
                    ->measurement_unit,

            'cost_price' =>
                $item
                    ->cost_price,

            'sale_price' =>
                $item
                    ->sale_price,

            'cost_rate' =>
                $item
                    ->cost_rate,

            'sale_rate' =>
                $item
                    ->sale_rate,

            'margin_percentage' =>
                $item
                    ->margin_percentage,

            'active' =>
                $item
                    ->active,

            'production_steps' =>
                $item
                    ->productionSteps
                    ->map(
                        fn ($step) => [
                            'title' =>
                                $step
                                    ->title,

                            'description' =>
                                $step
                                    ->description,
                        ]
                    )
                    ->values(),

            'material_recipes' =>
                $item
                    ->materialRecipes
                    ->map(
                        fn ($recipe) => [
                            'inventory_item_id' =>
                                $recipe
                                    ->inventory_item_id,

                            'calculation_method' =>
                                $recipe
                                    ->calculation_method,

                            'quantity_rate' =>
                                $recipe
                                    ->quantity_rate,

                            'waste_percentage' =>
                                $recipe
                                    ->waste_percentage,

                            'notes' =>
                                $recipe
                                    ->notes,
                        ]
                    )
                    ->values(),
        ];
    }

    private function nextItemCode(): string
    {
        $next =
            (
                CatalogItem::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'CAT-' .
            str_pad(
                (string)
                $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}