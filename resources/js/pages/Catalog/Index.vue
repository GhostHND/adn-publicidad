<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';

import {
    useMobileAccordion,
} from '@/composables/useMobileAccordion';

import AppLayout from '@/layouts/AppLayout.vue';

import {
    Head,
    Link,
} from '@inertiajs/vue3';

import {
    Boxes,
    Pencil,
    Plus,
    Search,
    Tags,
    TrendingUp,
} from '@lucide/vue';

import {
    computed,
    ref,
} from 'vue';

type CatalogItem = {
    id: number;

    item_code?:
        string
        | null;

    code?:
        string
        | null;

    name: string;

    item_type?:
        string
        | null;

    type?:
        string
        | null;

    category?:
        string
        | null;

    category_name?:
        string
        | null;

    pricing_method?:
        string
        | null;

    measurement_unit?:
        string
        | null;

    cost_price?:
        number
        | string
        | null;

    sale_price?:
        number
        | string
        | null;

    cost_rate?:
        number
        | string
        | null;

    sale_rate?:
        number
        | string
        | null;

    margin_percentage?:
        number
        | string
        | null;

    active?:
        boolean
        | number
        | null;
};

type CollectionProp = {
    data?:
        CatalogItem[];
};

const props =
    defineProps<{
        items?:
            CatalogItem[]
            | CollectionProp;

        catalogItems?:
            CatalogItem[]
            | CollectionProp;
    }>();

const search =
    ref(
        '',
    );

const {
    toggleCard,
    isExpanded,
} =
    useMobileAccordion();

const breadcrumbs = [
    {
        title:
            'Catálogo',

        href:
            '/catalog',
    },
];

/*
|--------------------------------------------------------------------------
| CÓDIGO
|--------------------------------------------------------------------------
*/

const itemCode = (
    item:
        CatalogItem,
): string => {
    return (
        item.item_code
        ||
        item.code
        ||
        `CAT-${String(
            item.id,
        ).padStart(
            4,
            '0',
        )}`
    );
};

/*
|--------------------------------------------------------------------------
| TIPO DE ELEMENTO
|--------------------------------------------------------------------------
*/

const itemType = (
    value?:
        string
        | null,
): string => {
    const labels:
        Record<
            string,
            string
        > = {
        PRODUCT:
            'Producto',

        product:
            'Producto',

        SERVICE:
            'Servicio',

        service:
            'Servicio',

        CUSTOM:
            'Personalizado',

        custom:
            'Personalizado',

        RESALE:
            'Reventa',

        resale:
            'Reventa',
    };

    if (
        !value
    ) {
        return 'Elemento';
    }

    return labels[
        value
    ]
    ??
    value;
};

/*
|--------------------------------------------------------------------------
| MÉTODO DE PRECIO
|--------------------------------------------------------------------------
*/

const pricingLabel = (
    value?:
        string
        | null,
): string => {
    const labels:
        Record<
            string,
            string
        > = {
        AREA:
            'Por área',

        UNIT:
            'Por unidad',

        FIXED:
            'Precio fijo',

        LINEAR:
            'Medida lineal',

        RESALE:
            'Reventa',

        COST_MARGIN:
            'Costo + margen',

        MANUAL:
            'Precio manual',
    };

    const method =
        String(
            value
            ?? '',
        ).toUpperCase();

    return method
        ? (
            labels[
                method
            ]
            ??
            method
        )
        : '—';
};

/*
|--------------------------------------------------------------------------
| CATEGORÍA
|--------------------------------------------------------------------------
*/

const categoryName = (
    item:
        CatalogItem,
): string => {
    return (
        item.category_name
        ||
        item.category
        ||
        'Sin categoría'
    );
};

/*
|--------------------------------------------------------------------------
| UNIDADES
|--------------------------------------------------------------------------
*/

const unitLabel = (
    item:
        CatalogItem,
): string => {
    const method =
        String(
            item.pricing_method
            ?? '',
        ).toUpperCase();

    if (
        method ===
        'FIXED'
    ) {
        return 'trabajo';
    }

    if (
        method ===
        'MANUAL'
    ) {
        return '—';
    }

    const unit =
        String(
            item.measurement_unit
            ?? '',
        ).toUpperCase();

    const labels:
        Record<
            string,
            string
        > = {
        UNIDAD:
            'unidad',

        PULGADA:
            'pulg',

        PULGADA2:
            'pulg²',

        PULGADA_2:
            'pulg²',

        PULGADA_CUADRADA:
            'pulg²',

        METRO:
            'metro',

        METRO2:
            'm²',

        METRO_2:
            'm²',

        METRO_LINEAL:
            'metro lineal',

        CENTIMETRO:
            'cm',

        CENTIMETRO2:
            'cm²',

        CM:
            'cm',

        CM2:
            'cm²',

        PIE:
            'pie',

        PIE2:
            'pie²',

        YARDA:
            'yarda',
    };

    if (
        unit
    ) {
        return (
            labels[
                unit
            ]
            ??
            item.measurement_unit
            ??
            'unidad'
        );
    }

    if (
        method ===
        'AREA'
    ) {
        return 'área';
    }

    if (
        method ===
        'LINEAR'
    ) {
        return 'medida lineal';
    }

    return 'unidad';
};

/*
|--------------------------------------------------------------------------
| CONVERTIR A NÚMERO
|--------------------------------------------------------------------------
*/

const numberValue = (
    value:
        string
        | number
        | null
        | undefined,
): number | null => {
    if (
        value ===
            null
        ||
        value ===
            undefined
        ||
        value ===
            ''
    ) {
        return null;
    }

    const numeric =
        Number(
            value,
        );

    return Number
        .isFinite(
            numeric,
        )
            ? numeric
            : null;
};

/*
|--------------------------------------------------------------------------
| COSTO EFECTIVO
|--------------------------------------------------------------------------
*/

const effectiveCost = (
    item:
        CatalogItem,
): number | null => {
    const method =
        String(
            item.pricing_method
            ?? '',
        ).toUpperCase();

    if (
        method ===
            'AREA'
        ||
        method ===
            'LINEAR'
    ) {
        return numberValue(
            item.cost_rate,
        );
    }

    return numberValue(
        item.cost_price,
    );
};

/*
|--------------------------------------------------------------------------
| PRECIO PÚBLICO EFECTIVO
|--------------------------------------------------------------------------
*/

const effectiveSale = (
    item:
        CatalogItem,
): number | null => {
    const method =
        String(
            item.pricing_method
            ?? '',
        ).toUpperCase();

    if (
        method ===
            'AREA'
        ||
        method ===
            'LINEAR'
    ) {
        return numberValue(
            item.sale_rate,
        );
    }

    return numberValue(
        item.sale_price,
    );
};

/*
|--------------------------------------------------------------------------
| MONEDA
|--------------------------------------------------------------------------
*/

const money = (
    value:
        number
        | null,
): string => {
    if (
        value ===
        null
    ) {
        return '—';
    }

    return `L ${value.toLocaleString(
        'es-HN',
        {
            minimumFractionDigits:
                2,

            maximumFractionDigits:
                2,
        },
    )}`;
};

/*
|--------------------------------------------------------------------------
| PRECIO CON UNIDAD
|--------------------------------------------------------------------------
*/

const priceDisplay = (
    item:
        CatalogItem,

    kind:
        'cost'
        | 'sale',
): string => {
    const value =
        kind ===
            'cost'
            ? effectiveCost(
                item,
            )
            : effectiveSale(
                item,
            );

    if (
        value ===
        null
    ) {
        return '—';
    }

    const method =
        String(
            item.pricing_method
            ?? '',
        ).toUpperCase();

    if (
        method ===
        'MANUAL'
    ) {
        return 'Manual';
    }

    return (
        `${money(
            value,
        )} / ${unitLabel(
            item,
        )}`
    );
};

/*
|--------------------------------------------------------------------------
| MARGEN BRUTO
|--------------------------------------------------------------------------
*/

const grossMargin = (
    item:
        CatalogItem,
): number | null => {
    const cost =
        effectiveCost(
            item,
        );

    const sale =
        effectiveSale(
            item,
        );

    if (
        cost ===
            null
        ||
        sale ===
            null
        ||
        sale <=
            0
    ) {
        return null;
    }

    return (
        (
            sale
            -
            cost
        )
        /
        sale
    )
    *
    100;
};

const marginDisplay = (
    item:
        CatalogItem,
): string => {
    const margin =
        grossMargin(
            item,
        );

    return margin ===
        null
            ? '—'
            : `${margin.toFixed(
                1,
            )}%`;
};

/*
|--------------------------------------------------------------------------
| CATÁLOGO
|--------------------------------------------------------------------------
*/

const catalogItems =
    computed<
        CatalogItem[]
    >(
        () => {
            const source =
                props.items
                ??
                props.catalogItems
                ??
                [];

            const items =
                Array.isArray(
                    source,
                )
                    ? source
                    : (
                        source.data
                        ??
                        []
                    );

            /*
            |--------------------------------------------------------------------------
            | ORDENAR POR CÓDIGO
            |--------------------------------------------------------------------------
            |
            | REV-001
            | REV-002
            | ...
            | REV-037
            |
            */

            return [
                ...items,
            ].sort(
                (
                    a,
                    b,
                ) => {
                    return itemCode(
                        a,
                    ).localeCompare(
                        itemCode(
                            b,
                        ),
                        'es',
                        {
                            numeric:
                                true,
                        },
                    );
                },
            );
        },
    );

/*
|--------------------------------------------------------------------------
| FILTRADO
|--------------------------------------------------------------------------
*/

const filteredItems =
    computed<
        CatalogItem[]
    >(
        () => {
            const term =
                search.value
                    .trim()
                    .toLowerCase();

            if (
                !term
            ) {
                return catalogItems
                    .value;
            }

            return catalogItems
                .value
                .filter(
                    (
                        item,
                    ) => {
                        const haystack = [
                            itemCode(
                                item,
                            ),

                            item.name,

                            categoryName(
                                item,
                            ),

                            itemType(
                                item.item_type
                                ??
                                item.type,
                            ),

                            pricingLabel(
                                item.pricing_method,
                            ),

                            unitLabel(
                                item,
                            ),
                        ]
                            .join(
                                ' ',
                            )
                            .toLowerCase();

                        return haystack
                            .includes(
                                term,
                            );
                    },
                );
        },
    );

/*
|--------------------------------------------------------------------------
| INDICADORES
|--------------------------------------------------------------------------
*/

const activeCount =
    computed(
        () => {
            return catalogItems
                .value
                .filter(
                    (
                        item,
                    ) =>
                        Boolean(
                            item.active,
                        ),
                )
                .length;
        },
    );

const averageMargin =
    computed<
        number
        | null
    >(
        () => {
            const margins =
                catalogItems
                    .value
                    .map(
                        grossMargin,
                    )
                    .filter(
                        (
                            value,
                        ): value is number =>
                            value !==
                            null,
                    );

            if (
                margins.length ===
                0
            ) {
                return null;
            }

            return margins
                .reduce(
                    (
                        sum,
                        value,
                    ) =>
                        sum
                        +
                        value,
                    0,
                )
                /
                margins.length;
        },
    );

/*
|--------------------------------------------------------------------------
| CAMPOS MÓVILES
|--------------------------------------------------------------------------
*/

const fieldsFor = (
    item:
        CatalogItem,
): MobileRecordField[] => {
    return [
        {
            label:
                'Tipo',

            value:
                itemType(
                    item.item_type
                    ??
                    item.type,
                ),
        },

        {
            label:
                'Categoría',

            value:
                categoryName(
                    item,
                ),

            wide:
                true,
        },

        {
            label:
                'Cálculo',

            value:
                pricingLabel(
                    item.pricing_method,
                ),
        },

        {
            label:
                'Unidad',

            value:
                unitLabel(
                    item,
                ),
        },

        {
            label:
                'Precio para ADN',

            value:
                priceDisplay(
                    item,
                    'cost',
                ),

            wide:
                true,
        },

        {
            label:
                'Precio público',

            value:
                priceDisplay(
                    item,
                    'sale',
                ),

            wide:
                true,
        },

        {
            label:
                'Margen bruto',

            value:
                marginDisplay(
                    item,
                ),
        },
    ];
};
</script>

<template>
    <Head
        title="Catálogo"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <!-- ===================================================== -->
            <!-- CABECERA -->
            <!-- ===================================================== -->

            <section
                class="adn-enter relative overflow-hidden rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-[#0fa7b4]/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
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
                                Productos y servicios
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Catálogo
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Tarifas, costos y precios públicos de ADN Publicidad.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/catalog/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Nuevo elemento
                    </Link>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- INDICADORES -->
            <!-- ===================================================== -->

            <section
                class="grid gap-3 sm:grid-cols-3"
            >
                <article
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wider text-white/30"
                    >
                        Elementos
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{
                            catalogItems.length
                        }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-emerald-500/10 bg-emerald-500/[0.04] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wider text-emerald-400"
                    >
                        Activos
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{
                            activeCount
                        }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-[#0fa7b4]/15 bg-[#0fa7b4]/[0.04] p-4"
                >
                    <div
                        class="flex items-center gap-2 text-[#22c6d2]"
                    >
                        <TrendingUp
                            class="h-4 w-4"
                        />

                        <p
                            class="text-[10px] font-black uppercase tracking-wider"
                        >
                            Margen promedio
                        </p>
                    </div>

                    <p
                        class="mt-1 text-2xl font-black text-[#22c6d2]"
                    >
                        {{
                            averageMargin ===
                            null
                                ? '—'
                                : `${averageMargin.toFixed(
                                    1,
                                )}%`
                        }}
                    </p>
                </article>
            </section>

            <!-- ===================================================== -->
            <!-- BÚSQUEDA -->
            <!-- ===================================================== -->

            <section
                class="rounded-2xl border border-white/[0.07] bg-[#091317] p-3"
            >
                <div
                    class="flex min-h-11 items-center gap-3 rounded-xl border border-white/[0.08] bg-black/15 px-4"
                >
                    <Search
                        class="h-4 w-4 shrink-0 text-[#22c6d2]"
                    />

                    <input
                        v-model="
                            search
                        "
                        type="search"
                        autocomplete="off"
                        placeholder="Buscar por código, producto, categoría, método o unidad..."
                        class="h-11 min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-white outline-none placeholder:text-white/25 focus:ring-0"
                    />

                    <span
                        class="shrink-0 text-[10px] font-black text-white/25"
                    >
                        {{
                            filteredItems.length
                        }}
                        /
                        {{
                            catalogItems.length
                        }}
                    </span>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- MÓVIL -->
            <!-- ===================================================== -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <div
                    v-if="
                        filteredItems.length ===
                        0
                    "
                    class="rounded-2xl border border-dashed border-white/10 p-10 text-center"
                >
                    <Tags
                        class="mx-auto h-8 w-8 text-white/25"
                    />

                    <p
                        class="mt-3 font-bold"
                    >
                        No se encontraron elementos
                    </p>
                </div>

                <MobileRecordCard
                    v-for="
                        item in filteredItems
                    "
                    :key="
                        item.id
                    "
                    :title="
                        item.name
                    "
                    :code="
                        itemCode(
                            item,
                        )
                    "
                    :subtitle="
                        categoryName(
                            item,
                        )
                    "
                    :status="
                        item.active
                            ? 'Activo'
                            : 'Inactivo'
                    "
                    :status-tone="
                        item.active
                            ? 'success'
                            : 'neutral'
                    "
                    :fields="
                        fieldsFor(
                            item,
                        )
                    "
                    :expanded="
                        isExpanded(
                            item.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            item.id,
                        )
                    "
                >
                    <template
                        #actions
                    >
                        <Link
                            :href="
                                `/catalog/${item.id}/edit`
                            "
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white"
                        >
                            <Pencil
                                class="h-4 w-4"
                            />

                            Editar elemento
                        </Link>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ===================================================== -->
            <!-- ESCRITORIO -->
            <!-- ===================================================== -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full min-w-[1180px] text-sm"
                    >
                        <thead
                            class="bg-white/[0.02]"
                        >
                            <tr>
                                <th
                                    class="px-4 py-4 text-left"
                                >
                                    Código
                                </th>

                                <th
                                    class="px-4 py-4 text-left"
                                >
                                    Producto
                                </th>

                                <th
                                    class="px-4 py-4 text-left"
                                >
                                    Tipo
                                </th>

                                <th
                                    class="px-4 py-4 text-left"
                                >
                                    Cálculo
                                </th>

                                <th
                                    class="px-4 py-4 text-left"
                                >
                                    Unidad
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Precio para ADN
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Precio público
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Margen
                                </th>

                                <th
                                    class="px-4 py-4 text-left"
                                >
                                    Estado
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    item in filteredItems
                                "
                                :key="
                                    item.id
                                "
                                class="transition hover:bg-white/[0.025]"
                            >
                                <td
                                    class="whitespace-nowrap px-4 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        itemCode(
                                            item,
                                        )
                                    }}
                                </td>

                                <td
                                    class="min-w-[220px] px-4 py-4"
                                >
                                    <p
                                        class="font-bold text-white"
                                    >
                                        {{
                                            item.name
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-[10px] text-white/30"
                                    >
                                        {{
                                            categoryName(
                                                item,
                                            )
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-white/65"
                                >
                                    {{
                                        itemType(
                                            item.item_type
                                            ??
                                            item.type,
                                        )
                                    }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4"
                                >
                                    <span
                                        class="rounded-lg border border-white/[0.07] bg-white/[0.03] px-2.5 py-1.5 text-[11px] font-bold text-white/70"
                                    >
                                        {{
                                            pricingLabel(
                                                item.pricing_method,
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-white/55"
                                >
                                    {{
                                        unitLabel(
                                            item,
                                        )
                                    }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-right"
                                >
                                    <p
                                        class="font-black text-white/80"
                                    >
                                        {{
                                            money(
                                                effectiveCost(
                                                    item,
                                                ),
                                            )
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-[9px] text-white/25"
                                    >
                                        por
                                        {{
                                            unitLabel(
                                                item,
                                            )
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-right"
                                >
                                    <p
                                        class="font-black text-[#22c6d2]"
                                    >
                                        {{
                                            money(
                                                effectiveSale(
                                                    item,
                                                ),
                                            )
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-[9px] text-[#22c6d2]/45"
                                    >
                                        por
                                        {{
                                            unitLabel(
                                                item,
                                            )
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-right"
                                >
                                    <span
                                        class="rounded-lg border border-emerald-500/15 bg-emerald-500/[0.05] px-2.5 py-1.5 text-[11px] font-black text-emerald-400"
                                    >
                                        {{
                                            marginDisplay(
                                                item,
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4"
                                >
                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black"
                                        :class="
                                            item.active
                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                                : 'border-white/10 bg-white/5 text-white/40'
                                        "
                                    >
                                        {{
                                            item.active
                                                ? 'Activo'
                                                : 'Inactivo'
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-right"
                                >
                                    <Link
                                        :href="
                                            `/catalog/${item.id}/edit`
                                        "
                                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-2 font-bold transition hover:border-[#0fa7b4]/30 hover:bg-[#0fa7b4]/10 hover:text-[#22c6d2]"
                                    >
                                        <Pencil
                                            class="h-4 w-4"
                                        />

                                        Editar
                                    </Link>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    filteredItems.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="10"
                                    class="px-6 py-14 text-center text-sm text-white/35"
                                >
                                    No se encontraron elementos con ese criterio.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>