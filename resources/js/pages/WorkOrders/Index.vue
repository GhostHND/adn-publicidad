<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';
import { useMobileAccordion } from '@/composables/useMobileAccordion';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    AlertTriangle,
    Boxes,
    Eye,
    History,
    Pencil,
    Plus,
} from '@lucide/vue';
import { computed } from 'vue';

type InventoryItem = Record<string, any>;

const props = defineProps<{
    items?: InventoryItem[];
    inventory?: InventoryItem[];
    inventoryItems?: InventoryItem[];
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Inventario',
        href: '/inventory',
    },
];

const items = computed<InventoryItem[]>(() => {
    return (
        props.items
        ??
        props.inventoryItems
        ??
        props.inventory
        ??
        []
    );
});

const value = (
    item: InventoryItem,
    keys: string[],
    fallback: any = null,
): any => {
    for (const key of keys) {
        const result = item?.[key];

        if (
            result !== undefined
            &&
            result !== null
            &&
            result !== ''
        ) {
            return result;
        }
    }

    return fallback;
};

const numberValue = (
    item: InventoryItem,
    keys: string[],
): number => {
    return Number(
        value(
            item,
            keys,
            0,
        ),
    );
};

const itemName = (
    item: InventoryItem,
): string => {
    return String(
        value(
            item,
            [
                'name',
                'item_name',
                'material_name',
                'description',
            ],
            'Material sin nombre',
        ),
    );
};

const itemCode = (
    item: InventoryItem,
): string => {
    return String(
        value(
            item,
            [
                'item_code',
                'inventory_code',
                'code',
                'sku',
            ],
            `INV-${String(item.id).padStart(4, '0')}`,
        ),
    );
};

const stock = (
    item: InventoryItem,
): number => {
    return numberValue(
        item,
        [
            'current_stock',
            'stock',
            'quantity',
            'stock_quantity',
        ],
    );
};

const reserved = (
    item: InventoryItem,
): number => {
    return numberValue(
        item,
        [
            'reserved_stock',
            'reserved_quantity',
            'reserved',
        ],
    );
};

const available = (
    item: InventoryItem,
): number => {
    const explicit =
        value(
            item,
            [
                'available_stock',
                'available_quantity',
                'available',
            ],
            null,
        );

    if (
        explicit !== null
    ) {
        return Number(explicit);
    }

    return Math.max(
        0,
        stock(item) - reserved(item),
    );
};

const minimum = (
    item: InventoryItem,
): number => {
    return numberValue(
        item,
        [
            'minimum_stock',
            'min_stock',
            'reorder_level',
        ],
    );
};

const isLowStock = (
    item: InventoryItem,
): boolean => {
    const min =
        minimum(item);

    return (
        min > 0
        &&
        available(item) <= min
    );
};

const money = (
    amount: any,
): string => {
    if (
        amount === null
        ||
        amount === undefined
        ||
        amount === ''
    ) {
        return '—';
    }

    return `L ${Number(amount).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
};

const lowStockCount =
    computed(() => {
        return items.value.filter(
            isLowStock,
        ).length;
    });

const fieldsFor = (
    item: InventoryItem,
): MobileRecordField[] => {
    return [
        {
            label: 'Categoría',
            value:
                value(
                    item,
                    [
                        'category',
                        'category_name',
                        'material_type',
                    ],
                    '—',
                ),
        },

        {
            label: 'Unidad',
            value:
                value(
                    item,
                    [
                        'measurement_unit',
                        'unit',
                        'unit_name',
                    ],
                    '—',
                ),
        },

        {
            label: 'Existencia',
            value:
                stock(item),
        },

        {
            label: 'Reservado',
            value:
                reserved(item),
        },

        {
            label: 'Disponible',
            value:
                available(item),
        },

        {
            label: 'Mínimo',
            value:
                minimum(item),
        },

        {
            label: 'Costo unitario',
            value:
                money(
                    value(
                        item,
                        [
                            'unit_cost',
                            'cost_price',
                            'cost',
                        ],
                    ),
                ),
            wide: true,
        },
    ];
};
</script>

<template>
    <Head title="Inventario" />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <!-- CABECERA -->

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
                                Materiales
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Inventario
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Existencias, reservas y disponibilidad de materiales.
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid gap-2 sm:flex"
                    >
                        <Link
                            href="/inventory/movements"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 py-3 text-sm font-bold"
                        >
                            <History
                                class="h-4 w-4"
                            />

                            Movimientos
                        </Link>

                        <Link
                            href="/inventory/create"
                            class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                        >
                            <Plus
                                class="h-4 w-4"
                            />

                            Nuevo material
                        </Link>
                    </div>
                </div>
            </section>

            <!-- MÉTRICAS -->

            <div
                class="grid grid-cols-2 gap-3 md:max-w-md"
            >
                <div
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wider text-white/30"
                    >
                        Materiales
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ items.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-500/15 bg-amber-500/[0.05] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wider text-amber-400"
                    >
                        Stock bajo
                    </p>

                    <p
                        class="mt-1 text-2xl font-black text-amber-400"
                    >
                        {{ lowStockCount }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="item in items"
                    :key="item.id"
                    :title="itemName(item)"
                    :code="itemCode(item)"
                    :subtitle="
                        value(
                            item,
                            [
                                'category',
                                'category_name',
                            ],
                            'Material',
                        )
                    "
                    :status="
                        isLowStock(item)
                            ? 'Stock bajo'
                            : 'Disponible'
                    "
                    :status-tone="
                        isLowStock(item)
                            ? 'warning'
                            : 'success'
                    "
                    :fields="
                        fieldsFor(item)
                    "
                    :expanded="
                        isExpanded(item.id)
                    "
                    @toggle="
                        toggleCard(item.id)
                    "
                >
                    <template #badges>
                        <AlertTriangle
                            v-if="
                                isLowStock(item)
                            "
                            class="h-4 w-4 text-amber-400"
                        />
                    </template>

                    <template #actions>
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/inventory/${item.id}`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white"
                            >
                                <Eye
                                    class="h-4 w-4"
                                />

                                Ver
                            </Link>

                            <Link
                                :href="
                                    `/inventory/${item.id}/edit`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <Pencil
                                    class="h-4 w-4"
                                />

                                Editar
                            </Link>

                            <Link
                                :href="
                                    `/inventory/${item.id}/movements`
                                "
                                class="col-span-2 inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 text-xs font-bold text-[#22c6d2]"
                            >
                                <History
                                    class="h-4 w-4"
                                />

                                Historial de movimientos
                            </Link>
                        </div>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ESCRITORIO -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-5 py-4 text-left">
                                    Código
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Material
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Unidad
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Existencia
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Reservado
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Disponible
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Estado
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="item in items"
                                :key="item.id"
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{ itemCode(item) }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{ itemName(item) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        value(
                                            item,
                                            [
                                                'measurement_unit',
                                                'unit',
                                            ],
                                            '—',
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-bold"
                                >
                                    {{ stock(item) }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    {{ reserved(item) }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-black text-emerald-400"
                                >
                                    {{ available(item) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black"
                                        :class="
                                            isLowStock(item)
                                                ? 'border-amber-500/20 bg-amber-500/10 text-amber-400'
                                                : 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                        "
                                    >
                                        {{
                                            isLowStock(item)
                                                ? 'Stock bajo'
                                                : 'Disponible'
                                        }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="
                                                `/inventory/${item.id}`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Eye
                                                class="h-4 w-4"
                                            />
                                        </Link>

                                        <Link
                                            :href="
                                                `/inventory/${item.id}/edit`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Pencil
                                                class="h-4 w-4"
                                            />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>