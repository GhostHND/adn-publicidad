<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    History,
    Search,
} from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps<{
    item: any | null;
    movements: any[];
}>();

const search = ref('');

const filteredMovements =
    computed(() => {
        const term =
            search.value
                .trim()
                .toLowerCase();

        if (!term) {
            return props.movements;
        }

        return props.movements.filter(
            (movement) =>
                [
                    movement.item_code ?? '',
                    movement.item_name ?? '',
                    movement.movement_label,
                    movement.reference ?? '',
                    movement.created_by ?? '',
                ].some((value) =>
                    String(value)
                        .toLowerCase()
                        .includes(term),
                ),
        );
    });

const breadcrumbs = [
    {
        title: 'Inventario',
        href: '/inventory',
    },
    {
        title:
            props.item
                ? props.item.item_code
                : 'Movimientos',
        href:
            props.item
                ? `/inventory/${props.item.id}/movements`
                : '/inventory/movements',
    },
];

const movementClass = (
    type: string,
) =>
    [
        'entry',
        'adjustment_in',
    ].includes(type)
        ? 'text-emerald-600'
        : 'text-[#e84657]';
</script>

<template>
    <Head
        :title="
            item
                ? `Movimientos ${item.item_code}`
                : 'Movimientos de inventario'
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold"
                    >
                        {{
                            item
                                ? `Movimientos de ${item.name}`
                                : 'Movimientos de inventario'
                        }}
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Historial de entradas,
                        salidas y ajustes.
                    </p>
                </div>

                <Link
                    :href="
                        item
                            ? `/inventory/${item.id}`
                            : '/inventory'
                    "
                    class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                >
                    <ArrowLeft
                        class="h-4 w-4"
                    />

                    Volver
                </Link>
            </div>

            <div
                class="overflow-hidden rounded-xl border bg-background shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b p-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <History
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <p
                            class="font-semibold"
                        >
                            {{
                                movements.length
                            }}
                            movimiento(s)
                        </p>
                    </div>

                    <div
                        class="relative w-full md:w-80"
                    >
                        <Search
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar movimiento..."
                            class="w-full rounded-lg border bg-background py-2.5 pl-10 pr-4"
                        />
                    </div>
                </div>

                <div
                    v-if="
                        filteredMovements.length ===
                        0
                    "
                    class="flex min-h-64 items-center justify-center p-8 text-sm text-muted-foreground"
                >
                    No hay movimientos.
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full text-left text-sm"
                    >
                        <thead
                            class="border-b bg-muted/50"
                        >
                            <tr>
                                <th
                                    class="px-5 py-4"
                                >
                                    Fecha
                                </th>

                                <th
                                    v-if="!item"
                                    class="px-5 py-4"
                                >
                                    Material
                                </th>

                                <th
                                    class="px-5 py-4"
                                >
                                    Tipo
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Cantidad
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Anterior
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Final
                                </th>

                                <th
                                    class="px-5 py-4"
                                >
                                    Referencia
                                </th>

                                <th
                                    class="px-5 py-4"
                                >
                                    Usuario
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    movement in filteredMovements
                                "
                                :key="
                                    movement.id
                                "
                                class="border-b last:border-b-0 hover:bg-muted/30"
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        movement.moved_at
                                    }}
                                </td>

                                <td
                                    v-if="!item"
                                    class="px-5 py-4"
                                >
                                    <Link
                                        :href="`/inventory/${movement.inventory_item_id}`"
                                        class="font-semibold text-[#0fa7b4]"
                                    >
                                        {{
                                            movement.item_code
                                        }}
                                        ·
                                        {{
                                            movement.item_name
                                        }}
                                    </Link>
                                </td>

                                <td
                                    class="px-5 py-4 font-semibold"
                                    :class="
                                        movementClass(
                                            movement.movement_type,
                                        )
                                    "
                                >
                                    {{
                                        movement.movement_label
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-bold"
                                    :class="
                                        movementClass(
                                            movement.movement_type,
                                        )
                                    "
                                >
                                    {{
                                        [
                                            'entry',
                                            'adjustment_in',
                                        ].includes(
                                            movement.movement_type,
                                        )
                                            ? '+'
                                            : '-'
                                    }}

                                    {{
                                        Number(
                                            movement.quantity,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    {{
                                        Number(
                                            movement.previous_stock,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-semibold"
                                >
                                    {{
                                        Number(
                                            movement.resulting_stock,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        movement.reference
                                        || '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        movement.created_by
                                        || '—'
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>