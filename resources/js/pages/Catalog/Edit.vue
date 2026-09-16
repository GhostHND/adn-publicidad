<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Boxes,
    CircleDollarSign,
    Layers3,
    Package,
    Plus,
    Save,
    Trash2,
    Wrench,
} from '@lucide/vue';
import {
    computed,
} from 'vue';

type InventoryItem = {
    id: number;
    code?: string | null;
    name: string;
    measurement_unit?: string | null;
    current_stock?: number | string | null;
    unit_cost?: number | string | null;
};

type ProductionStep = {
    title: string;
    description: string;
};

type MaterialRecipe = {
    inventory_item_id:
        number
        | string;

    calculation_method:
        'UNIT'
        | 'AREA'
        | 'LINEAR'
        | 'FIXED';

    quantity_rate:
        number
        | string;

    waste_percentage:
        number
        | string;

    notes: string;
};

type CatalogItem = {
    id: number;
    item_code?: string | null;
    name?: string | null;
    description?: string | null;
    item_type?: string | null;
    category?: string | null;
    pricing_method?: string | null;
    measurement_unit?: string | null;
    cost_price?: number | string | null;
    sale_price?: number | string | null;
    cost_rate?: number | string | null;
    sale_rate?: number | string | null;
    margin_percentage?: number | string | null;
    active?: boolean | number | null;
    production_steps?: ProductionStep[] | null;
    material_recipes?: MaterialRecipe[] | null;
};

const props = defineProps<{
    item: CatalogItem;
    inventoryItems?: InventoryItem[];
}>();

/*
|--------------------------------------------------------------------------
| NORMALIZACIÓN
|--------------------------------------------------------------------------
*/

const allowedItemTypes = [
    'product',
    'service',
    'custom',
];

const rawItemType =
    String(
        props.item?.item_type
        ?? 'product',
    )
        .trim()
        .toLowerCase();

const normalizedItemType =
    allowedItemTypes.includes(
        rawItemType,
    )
        ? rawItemType
        : 'product';

const normalizedPricingMethod =
    String(
        props.item?.pricing_method
        ?? 'UNIT',
    )
        .trim()
        .toUpperCase();

const productionSteps =
    Array.isArray(
        props.item
            ?.production_steps,
    )
        ? props.item.production_steps
            .map(
                (
                    step,
                ) => ({
                    title:
                        String(
                            step?.title
                            ?? '',
                        ),

                    description:
                        String(
                            step?.description
                            ?? '',
                        ),
                }),
            )
        : [];

const materialRecipes =
    Array.isArray(
        props.item
            ?.material_recipes,
    )
        ? props.item.material_recipes
            .map(
                (
                    recipe,
                ) => ({
                    inventory_item_id:
                        recipe
                            ?.inventory_item_id
                        ?? '',

                    calculation_method:
                        recipe
                            ?.calculation_method
                        ?? 'UNIT',

                    quantity_rate:
                        recipe
                            ?.quantity_rate
                        ?? 1,

                    waste_percentage:
                        recipe
                            ?.waste_percentage
                        ?? 0,

                    notes:
                        String(
                            recipe
                                ?.notes
                            ?? '',
                        ),
                }),
            )
        : [];

/*
|--------------------------------------------------------------------------
| FORMULARIO
|--------------------------------------------------------------------------
*/

const form =
    useForm({
        name:
            String(
                props.item
                    ?.name
                ?? '',
            ),

        description:
            String(
                props.item
                    ?.description
                ?? '',
            ),

        item_type:
            normalizedItemType,

        category:
            String(
                props.item
                    ?.category
                ?? '',
            ),

        pricing_method:
            normalizedPricingMethod,

        measurement_unit:
            String(
                props.item
                    ?.measurement_unit
                ?? '',
            ),

        cost_price:
            props.item
                ?.cost_price
            ?? '',

        sale_price:
            props.item
                ?.sale_price
            ?? '',

        cost_rate:
            props.item
                ?.cost_rate
            ?? '',

        sale_rate:
            props.item
                ?.sale_rate
            ?? '',

        margin_percentage:
            props.item
                ?.margin_percentage
            ?? '',

        active:
            Boolean(
                props.item
                    ?.active
                ?? true,
            ),

        production_steps:
            productionSteps,

        material_recipes:
            materialRecipes,
    });

/*
|--------------------------------------------------------------------------
| INVENTARIO
|--------------------------------------------------------------------------
*/

const inventoryItems =
    computed<InventoryItem[]>(
        () =>
            Array.isArray(
                props.inventoryItems,
            )
                ? props.inventoryItems
                : [],
    );

const hasInventory =
    computed<boolean>(
        () =>
            inventoryItems.value
                .length >
            0,
    );

/*
|--------------------------------------------------------------------------
| MÉTODOS DE PRECIO
|--------------------------------------------------------------------------
*/

const pricingMethods = [
    {
        value:
            'AREA',

        label:
            'Por área',

        description:
            'Ancho × alto × tarifa.',
    },

    {
        value:
            'UNIT',

        label:
            'Por unidad',

        description:
            'Precio por cada unidad.',
    },

    {
        value:
            'FIXED',

        label:
            'Precio fijo',

        description:
            'Un precio fijo por trabajo.',
    },

    {
        value:
            'LINEAR',

        label:
            'Medida lineal',

        description:
            'Longitud × tarifa.',
    },

    {
        value:
            'RESALE',

        label:
            'Reventa',

        description:
            'Costo y precio público por unidad.',
    },

    {
        value:
            'COST_MARGIN',

        label:
            'Costo + margen',

        description:
            'Calcula venta desde costo y margen.',
    },

    {
        value:
            'MANUAL',

        label:
            'Manual',

        description:
            'Precio definido al cotizar.',
    },
];

const itemTypes = [
    {
        value:
            'product',

        label:
            'Producto',
    },

    {
        value:
            'service',

        label:
            'Servicio',
    },

    {
        value:
            'custom',

        label:
            'Personalizado',
    },
];

/*
|--------------------------------------------------------------------------
| CAMPOS SEGÚN MÉTODO
|--------------------------------------------------------------------------
*/

const usesRate =
    computed<boolean>(
        () =>
            [
                'AREA',
                'LINEAR',
            ].includes(
                form.pricing_method,
            ),
    );

const usesUnitPrice =
    computed<boolean>(
        () =>
            [
                'UNIT',
                'FIXED',
                'RESALE',
            ].includes(
                form.pricing_method,
            ),
    );

const usesMargin =
    computed<boolean>(
        () =>
            form.pricing_method ===
            'COST_MARGIN',
    );

const isManual =
    computed<boolean>(
        () =>
            form.pricing_method ===
            'MANUAL',
    );

/*
|--------------------------------------------------------------------------
| MARGEN INFORMATIVO
|--------------------------------------------------------------------------
*/

const effectiveCost =
    computed<number>(
        () => {
            if (
                usesRate.value
            ) {
                return Number(
                    form.cost_rate
                    || 0,
                );
            }

            return Number(
                form.cost_price
                || 0,
            );
        },
    );

const effectiveSale =
    computed<number>(
        () => {
            if (
                usesRate.value
            ) {
                return Number(
                    form.sale_rate
                    || 0,
                );
            }

            if (
                usesMargin.value
            ) {
                const cost =
                    Number(
                        form.cost_price
                        || 0,
                    );

                const margin =
                    Number(
                        form.margin_percentage
                        || 0,
                    );

                return (
                    cost *
                    (
                        1
                        +
                        margin / 100
                    )
                );
            }

            return Number(
                form.sale_price
                || 0,
            );
        },
    );

const marginAmount =
    computed<number>(
        () =>
            Math.max(
                0,
                effectiveSale.value
                -
                effectiveCost.value,
            ),
    );

const marginPercent =
    computed<number>(
        () => {
            if (
                effectiveSale.value <=
                0
            ) {
                return 0;
            }

            return (
                marginAmount.value
                /
                effectiveSale.value
            )
            *
            100;
        },
    );

/*
|--------------------------------------------------------------------------
| FORMATOS
|--------------------------------------------------------------------------
*/

const money = (
    value:
        number
        | string
        | null
        | undefined,
): string => {
    const numeric =
        Number(
            value
            ?? 0,
        );

    return `L ${numeric.toLocaleString(
        'es-HN',
        {
            minimumFractionDigits:
                2,

            maximumFractionDigits:
                2,
        },
    )}`;
};

const pricingUnitLabel =
    computed<string>(
        () => {
            if (
                form.pricing_method ===
                'AREA'
            ) {
                return (
                    form.measurement_unit
                    ||
                    'unidad²'
                );
            }

            if (
                form.pricing_method ===
                'LINEAR'
            ) {
                return (
                    form.measurement_unit
                    ||
                    'unidad lineal'
                );
            }

            return 'unidad';
        },
    );

/*
|--------------------------------------------------------------------------
| PRODUCCIÓN
|--------------------------------------------------------------------------
*/

const addProductionStep =
    (): void => {
        form.production_steps
            .push({
                title:
                    '',

                description:
                    '',
            });
    };

const removeProductionStep = (
    index: number,
): void => {
    form.production_steps
        .splice(
            index,
            1,
        );
};

/*
|--------------------------------------------------------------------------
| RECETAS
|--------------------------------------------------------------------------
*/

const addMaterialRecipe =
    (): void => {
        if (
            !hasInventory.value
        ) {
            return;
        }

        form.material_recipes
            .push({
                inventory_item_id:
                    inventoryItems.value[0]
                        ?.id
                    ?? '',

                calculation_method:
                    'UNIT',

                quantity_rate:
                    1,

                waste_percentage:
                    0,

                notes:
                    '',
            });
    };

const removeMaterialRecipe = (
    index: number,
): void => {
    form.material_recipes
        .splice(
            index,
            1,
        );
};

/*
|--------------------------------------------------------------------------
| GUARDAR
|--------------------------------------------------------------------------
*/

const submit =
    (): void => {
        form.patch(
            `/catalog/${props.item.id}`,
            {
                preserveScroll:
                    true,
            },
        );
    };

/*
|--------------------------------------------------------------------------
| BREADCRUMBS
|--------------------------------------------------------------------------
*/

const breadcrumbs = [
    {
        title:
            'Catálogo',

        href:
            '/catalog',
    },

    {
        title:
            props.item
                ?.item_code
            ?? 'Editar',

        href:
            `/catalog/${props.item.id}/edit`,
    },
];
</script>

<template>
    <Head
        :title="
            `Editar ${item.name ?? 'elemento'}`
        "
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <form
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
            @submit.prevent="
                submit
            "
        >
            <!-- ========================================================= -->
            <!-- CABECERA -->
            <!-- ========================================================= -->

            <section
                class="relative overflow-hidden rounded-[1.8rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-7"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#0fa7b4]/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <Boxes
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                {{
                                    item.item_code
                                    ?? 'Catálogo'
                                }}
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Editar elemento
                            </h1>

                            <p
                                class="mt-1 max-w-2xl text-sm text-muted-foreground"
                            >
                                Configura precios, método de cálculo y automatización de producción.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col gap-2 sm:flex-row"
                    >
                        <Link
                            href="/catalog"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/10 px-5 text-sm font-black transition hover:bg-white/5"
                        >
                            <ArrowLeft
                                class="h-4 w-4"
                            />

                            Volver
                        </Link>

                        <button
                            type="submit"
                            :disabled="
                                form.processing
                            "
                            class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-6 text-sm font-black text-white disabled:opacity-50"
                        >
                            <Save
                                class="h-4 w-4"
                            />

                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar cambios'
                            }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- ERRORES -->
            <!-- ========================================================= -->

            <section
                v-if="
                    Object.keys(
                        form.errors,
                    ).length >
                    0
                "
                class="rounded-2xl border border-[#e84657]/20 bg-[#e84657]/5 p-4"
            >
                <p
                    class="text-sm font-black text-[#f06472]"
                >
                    Hay datos que necesitan revisión.
                </p>

                <ul
                    class="mt-2 space-y-1 text-xs text-white/55"
                >
                    <li
                        v-for="(
                            message,
                            key
                        ) in form.errors"
                        :key="
                            key
                        "
                    >
                        • {{
                            message
                        }}
                    </li>
                </ul>
            </section>

            <!-- ========================================================= -->
            <!-- INFORMACIÓN PRINCIPAL -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex items-center gap-2 text-[#22c6d2]"
                >
                    <Package
                        class="h-4 w-4"
                    />

                    <p
                        class="text-[10px] font-black uppercase tracking-[0.16em]"
                    >
                        Información general
                    </p>
                </div>

                <div
                    class="mt-5 grid gap-4 lg:grid-cols-2"
                >
                    <div
                        class="lg:col-span-2"
                    >
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Nombre
                        </label>

                        <input
                            v-model="
                                form.name
                            "
                            type="text"
                            maxlength="150"
                            required
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Categoría
                        </label>

                        <input
                            v-model="
                                form.category
                            "
                            type="text"
                            maxlength="100"
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Unidad de medida
                        </label>

                        <input
                            v-model="
                                form.measurement_unit
                            "
                            type="text"
                            maxlength="50"
                            placeholder="UNIDAD, PULGADA2, METRO_LINEAL..."
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />
                    </div>

                    <div
                        class="lg:col-span-2"
                    >
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Descripción
                        </label>

                        <textarea
                            v-model="
                                form.description
                            "
                            rows="4"
                            class="w-full rounded-xl border border-white/10 bg-black/15 px-4 py-3 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />
                    </div>
                </div>

                <div
                    class="mt-5"
                >
                    <p
                        class="mb-3 text-[10px] font-black uppercase tracking-wide text-white/35"
                    >
                        Tipo de elemento
                    </p>

                    <div
                        class="grid gap-2 sm:grid-cols-3"
                    >
                        <button
                            v-for="
                                type in itemTypes
                            "
                            :key="
                                type.value
                            "
                            type="button"
                            class="rounded-xl border px-4 py-3 text-sm font-black transition"
                            :class="
                                form.item_type ===
                                type.value
                                    ? 'border-[#0fa7b4]/40 bg-[#0fa7b4]/10 text-[#22c6d2]'
                                    : 'border-white/10 bg-black/10 text-white/55 hover:bg-white/[0.03]'
                            "
                            @click="
                                form.item_type =
                                    type.value
                            "
                        >
                            {{
                                type.label
                            }}
                        </button>
                    </div>
                </div>

                <label
                    class="mt-5 flex min-h-12 items-center justify-between rounded-xl border border-white/10 bg-black/15 px-4"
                >
                    <div>
                        <p
                            class="text-sm font-black"
                        >
                            Elemento activo
                        </p>

                        <p
                            class="text-[10px] text-white/30"
                        >
                            Disponible para cotizaciones y ventas.
                        </p>
                    </div>

                    <input
                        v-model="
                            form.active
                        "
                        type="checkbox"
                        class="h-5 w-5 accent-[#0fa7b4]"
                    />
                </label>
            </section>

            <!-- ========================================================= -->
            <!-- MÉTODO DE PRECIO -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex items-center gap-2 text-[#22c6d2]"
                >
                    <CircleDollarSign
                        class="h-4 w-4"
                    />

                    <p
                        class="text-[10px] font-black uppercase tracking-[0.16em]"
                    >
                        Precios y cálculo
                    </p>
                </div>

                <div
                    class="mt-5 grid gap-2 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <button
                        v-for="
                            method in pricingMethods
                        "
                        :key="
                            method.value
                        "
                        type="button"
                        class="rounded-xl border p-4 text-left transition"
                        :class="
                            form.pricing_method ===
                            method.value
                                ? 'border-[#0fa7b4]/40 bg-[#0fa7b4]/10'
                                : 'border-white/10 bg-black/10 hover:bg-white/[0.03]'
                        "
                        @click="
                            form.pricing_method =
                                method.value
                        "
                    >
                        <p
                            class="text-sm font-black"
                            :class="
                                form.pricing_method ===
                                method.value
                                    ? 'text-[#22c6d2]'
                                    : ''
                            "
                        >
                            {{
                                method.label
                            }}
                        </p>

                        <p
                            class="mt-1 text-[10px] leading-4 text-white/30"
                        >
                            {{
                                method.description
                            }}
                        </p>
                    </button>
                </div>

                <!-- TARIFA -->

                <div
                    v-if="
                        usesRate
                    "
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Precio para ADN
                        </label>

                        <input
                            v-model="
                                form.cost_rate
                            "
                            type="number"
                            min="0"
                            step="0.0001"
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none focus:border-[#0fa7b4]/50"
                        />

                        <p
                            class="mt-1.5 text-[10px] text-white/25"
                        >
                            Costo por {{
                                pricingUnitLabel
                            }}.
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Precio público
                        </label>

                        <input
                            v-model="
                                form.sale_rate
                            "
                            type="number"
                            min="0"
                            step="0.0001"
                            required
                            class="h-12 w-full rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/[0.04] px-4 text-sm font-bold outline-none focus:border-[#0fa7b4]/50"
                        />

                        <p
                            class="mt-1.5 text-[10px] text-white/25"
                        >
                            Precio de venta por {{
                                pricingUnitLabel
                            }}.
                        </p>
                    </div>
                </div>

                <!-- PRECIO POR UNIDAD -->

                <div
                    v-else-if="
                        usesUnitPrice
                    "
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Precio para ADN
                        </label>

                        <input
                            v-model="
                                form.cost_price
                            "
                            type="number"
                            min="0"
                            step="0.01"
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none focus:border-[#0fa7b4]/50"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Precio público
                        </label>

                        <input
                            v-model="
                                form.sale_price
                            "
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            class="h-12 w-full rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/[0.04] px-4 text-sm font-bold outline-none focus:border-[#0fa7b4]/50"
                        />
                    </div>
                </div>

                <!-- COSTO + MARGEN -->

                <div
                    v-else-if="
                        usesMargin
                    "
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Costo para ADN
                        </label>

                        <input
                            v-model="
                                form.cost_price
                            "
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            Margen %
                        </label>

                        <input
                            v-model="
                                form.margin_percentage
                            "
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm"
                        />
                    </div>
                </div>

                <div
                    v-else-if="
                        isManual
                    "
                    class="mt-5 rounded-xl border border-amber-500/15 bg-amber-500/[0.04] p-4"
                >
                    <p
                        class="text-sm font-black text-amber-300"
                    >
                        Precio manual
                    </p>

                    <p
                        class="mt-1 text-xs leading-5 text-white/35"
                    >
                        El precio se indicará directamente al momento de crear la cotización.
                    </p>
                </div>

                <!-- RESUMEN -->

                <div
                    v-if="
                        !isManual
                    "
                    class="mt-5 grid gap-3 sm:grid-cols-3"
                >
                    <article
                        class="rounded-xl border border-white/[0.07] bg-black/15 p-4"
                    >
                        <p
                            class="text-[9px] font-black uppercase tracking-wide text-white/30"
                        >
                            Costo ADN
                        </p>

                        <p
                            class="mt-1 text-lg font-black"
                        >
                            {{
                                money(
                                    effectiveCost,
                                )
                            }}
                        </p>
                    </article>

                    <article
                        class="rounded-xl border border-[#0fa7b4]/15 bg-[#0fa7b4]/[0.04] p-4"
                    >
                        <p
                            class="text-[9px] font-black uppercase tracking-wide text-[#22c6d2]"
                        >
                            Precio público
                        </p>

                        <p
                            class="mt-1 text-lg font-black text-[#22c6d2]"
                        >
                            {{
                                money(
                                    effectiveSale,
                                )
                            }}
                        </p>
                    </article>

                    <article
                        class="rounded-xl border border-emerald-500/15 bg-emerald-500/[0.04] p-4"
                    >
                        <p
                            class="text-[9px] font-black uppercase tracking-wide text-emerald-400"
                        >
                            Margen
                        </p>

                        <p
                            class="mt-1 text-lg font-black text-emerald-400"
                        >
                            {{
                                marginPercent.toFixed(
                                    1,
                                )
                            }}%
                        </p>
                    </article>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- PASOS DE PRODUCCIÓN -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div
                            class="flex items-center gap-2 text-[#22c6d2]"
                        >
                            <Layers3
                                class="h-4 w-4"
                            />

                            <p
                                class="text-[10px] font-black uppercase tracking-[0.16em]"
                            >
                                Producción
                            </p>
                        </div>

                        <p
                            class="mt-2 text-xs text-white/35"
                        >
                            Estos pasos generan tareas automáticamente cuando corresponda.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 px-4 text-xs font-black text-[#22c6d2]"
                        @click="
                            addProductionStep
                        "
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Agregar paso
                    </button>
                </div>

                <div
                    v-if="
                        form.production_steps.length ===
                        0
                    "
                    class="mt-5 rounded-xl border border-dashed border-white/10 p-6 text-center"
                >
                    <p
                        class="text-sm font-bold text-white/45"
                    >
                        Sin pasos de producción
                    </p>
                </div>

                <div
                    v-else
                    class="mt-5 space-y-3"
                >
                    <article
                        v-for="(
                            step,
                            index
                        ) in form.production_steps"
                        :key="
                            index
                        "
                        class="rounded-xl border border-white/[0.07] bg-black/10 p-4"
                    >
                        <div
                            class="grid gap-3 lg:grid-cols-[1fr_1.5fr_auto]"
                        >
                            <input
                                v-model="
                                    step.title
                                "
                                type="text"
                                maxlength="200"
                                placeholder="Nombre del paso"
                                class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                            />

                            <input
                                v-model="
                                    step.description
                                "
                                type="text"
                                placeholder="Descripción"
                                class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                            />

                            <button
                                type="button"
                                class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#e84657]/20 text-[#f06472]"
                                @click="
                                    removeProductionStep(
                                        index,
                                    )
                                "
                            >
                                <Trash2
                                    class="h-4 w-4"
                                />
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- MATERIALES -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div
                            class="flex items-center gap-2 text-[#22c6d2]"
                        >
                            <Wrench
                                class="h-4 w-4"
                            />

                            <p
                                class="text-[10px] font-black uppercase tracking-[0.16em]"
                            >
                                Consumo de materiales
                            </p>
                        </div>

                        <p
                            class="mt-2 text-xs text-white/35"
                        >
                            Define qué materiales del inventario consume este elemento.
                        </p>
                    </div>

                    <button
                        type="button"
                        :disabled="
                            !hasInventory
                        "
                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 px-4 text-xs font-black text-[#22c6d2] disabled:cursor-not-allowed disabled:opacity-30"
                        @click="
                            addMaterialRecipe
                        "
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Agregar material
                    </button>
                </div>

                <div
                    v-if="
                        !hasInventory
                    "
                    class="mt-5 rounded-xl border border-amber-500/15 bg-amber-500/[0.04] p-4"
                >
                    <p
                        class="text-sm font-black text-amber-300"
                    >
                        Inventario vacío
                    </p>

                    <p
                        class="mt-1 text-xs leading-5 text-white/35"
                    >
                        No hay materiales registrados actualmente. Puedes guardar y editar este producto normalmente; cuando agregues inventario podrás configurar su consumo.
                    </p>
                </div>

                <div
                    v-if="
                        form.material_recipes.length >
                        0
                    "
                    class="mt-5 space-y-3"
                >
                    <article
                        v-for="(
                            recipe,
                            index
                        ) in form.material_recipes"
                        :key="
                            index
                        "
                        class="rounded-xl border border-white/[0.07] bg-black/10 p-4"
                    >
                        <div
                            class="grid gap-3 lg:grid-cols-2"
                        >
                            <div>
                                <label
                                    class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                >
                                    Material
                                </label>

                                <select
                                    v-model="
                                        recipe.inventory_item_id
                                    "
                                    class="h-11 w-full rounded-xl border border-white/10 bg-[#071014] px-3 text-sm"
                                >
                                    <option
                                        v-for="
                                            inventory in inventoryItems
                                        "
                                        :key="
                                            inventory.id
                                        "
                                        :value="
                                            inventory.id
                                        "
                                    >
                                        {{
                                            inventory.code
                                            ? `${inventory.code} - ${inventory.name}`
                                            : inventory.name
                                        }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                >
                                    Método
                                </label>

                                <select
                                    v-model="
                                        recipe.calculation_method
                                    "
                                    class="h-11 w-full rounded-xl border border-white/10 bg-[#071014] px-3 text-sm"
                                >
                                    <option value="UNIT">
                                        Unidad
                                    </option>

                                    <option value="AREA">
                                        Área
                                    </option>

                                    <option value="LINEAR">
                                        Lineal
                                    </option>

                                    <option value="FIXED">
                                        Fijo
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                >
                                    Cantidad / factor
                                </label>

                                <input
                                    v-model="
                                        recipe.quantity_rate
                                    "
                                    type="number"
                                    min="0.000001"
                                    step="0.000001"
                                    class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                >
                                    Desperdicio %
                                </label>

                                <input
                                    v-model="
                                        recipe.waste_percentage
                                    "
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                />
                            </div>

                            <div
                                class="lg:col-span-2"
                            >
                                <label
                                    class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                >
                                    Notas
                                </label>

                                <textarea
                                    v-model="
                                        recipe.notes
                                    "
                                    rows="2"
                                    class="w-full rounded-xl border border-white/10 bg-black/15 px-3 py-2 text-sm"
                                />
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-3 inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#e84657]/20 px-4 text-xs font-black text-[#f06472]"
                            @click="
                                removeMaterialRecipe(
                                    index,
                                )
                            "
                        >
                            <Trash2
                                class="h-4 w-4"
                            />

                            Eliminar material
                        </button>
                    </article>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- GUARDAR INFERIOR -->
            <!-- ========================================================= -->

            <div
                class="flex flex-col gap-2 sm:flex-row sm:justify-end"
            >
                <Link
                    href="/catalog"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/10 px-6 text-sm font-black"
                >
                    Cancelar
                </Link>

                <button
                    type="submit"
                    :disabled="
                        form.processing
                    "
                    class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-7 text-sm font-black text-white disabled:opacity-50"
                >
                    <Save
                        class="h-4 w-4"
                    />

                    {{
                        form.processing
                            ? 'Guardando...'
                            : 'Guardar cambios'
                    }}
                </button>
            </div>
        </form>
    </AppLayout>
</template>