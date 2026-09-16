<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowLeft,
    ArrowUpCircle,
    History,
    Pencil,
    Package,
} from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    item: any;
    movements: any[];
}>();

const movementForm = useForm({
    inventory_item_id:
        props.item.id,
    movement_type: 'entry',
    quantity: '',
    unit_cost:
        props.item.unit_cost,
    reference: '',
    notes: '',
});

const isIncoming = computed(() =>
    [
        'entry',
        'adjustment_in',
    ].includes(
        movementForm.movement_type,
    ),
);

const submitMovement = () => {
    movementForm.post(
        '/inventory/adjustments',
        {
            preserveScroll: true,

            onSuccess: () => {
                movementForm.reset(
                    'quantity',
                    'reference',
                    'notes',
                );

                movementForm.unit_cost =
                    props.item.unit_cost;
            },
        },
    );
};

const breadcrumbs = [
    {
        title: 'Inventario',
        href: '/inventory',
    },
    {
        title:
            props.item.item_code,
        href:
            `/inventory/${props.item.id}`,
    },
];

const money = (
    value: string | number,
) =>
    `L ${Number(value || 0).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;

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
    <Head :title="item.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        {{ item.item_code }}
                    </p>

                    <h1
                        class="text-2xl font-bold"
                    >
                        {{ item.name }}
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        {{
                            item.category
                            || 'Sin categoría'
                        }}
                    </p>
                </div>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <Link
                        href="/inventory"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <ArrowLeft
                            class="h-4 w-4"
                        />
                        Volver
                    </Link>

                    <Link
                        :href="`/inventory/${item.id}/movements`"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <History
                            class="h-4 w-4"
                        />
                        Historial
                    </Link>

                    <Link
                        :href="`/inventory/${item.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-4 py-2.5 text-sm font-semibold text-white"
                    >
                        <Pencil
                            class="h-4 w-4"
                        />
                        Editar
                    </Link>
                </div>
            </div>

            <div
                class="grid gap-4 md:grid-cols-4"
            >
                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <Package
                        class="h-5 w-5 text-[#0fa7b4]"
                    />

                    <p
                        class="mt-3 text-sm text-muted-foreground"
                    >
                        Existencia actual
                    </p>

                    <p
                        class="mt-1 text-2xl font-bold"
                    >
                        {{
                            Number(
                                item.current_stock,
                            ).toLocaleString(
                                'es-HN',
                            )
                        }}
                        {{
                            item.measurement_unit
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Stock mínimo
                    </p>

                    <p
                        class="mt-3 text-2xl font-bold"
                    >
                        {{
                            Number(
                                item.minimum_stock,
                            ).toLocaleString(
                                'es-HN',
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Costo promedio
                    </p>

                    <p
                        class="mt-3 text-2xl font-bold"
                    >
                        {{
                            money(
                                item.unit_cost,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Valor en inventario
                    </p>

                    <p
                        class="mt-3 text-2xl font-bold text-[#0fa7b4]"
                    >
                        {{
                            money(
                                item.stock_value,
                            )
                        }}
                    </p>
                </div>
            </div>

            <div
                class="grid gap-6 xl:grid-cols-3"
            >
                <div
                    class="rounded-xl border bg-background p-6 shadow-sm xl:col-span-2"
                >
                    <h2
                        class="mb-5 text-lg font-semibold"
                    >
                        Información
                    </h2>

                    <div
                        class="grid gap-4 sm:grid-cols-2"
                    >
                        <div>
                            <p
                                class="text-sm text-muted-foreground"
                            >
                                Unidad
                            </p>

                            <p
                                class="font-semibold"
                            >
                                {{
                                    item.measurement_unit
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-sm text-muted-foreground"
                            >
                                Proveedor
                            </p>

                            <p
                                class="font-semibold"
                            >
                                {{
                                    item.supplier
                                    || '—'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-sm text-muted-foreground"
                            >
                                Ubicación
                            </p>

                            <p
                                class="font-semibold"
                            >
                                {{
                                    item.location
                                    || '—'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-sm text-muted-foreground"
                            >
                                Estado
                            </p>

                            <p
                                class="font-semibold"
                            >
                                {{
                                    item.active
                                        ? 'Activo'
                                        : 'Inactivo'
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="item.notes"
                        class="mt-6 border-t pt-5"
                    >
                        <p
                            class="text-sm text-muted-foreground"
                        >
                            Notas
                        </p>

                        <p
                            class="mt-2 whitespace-pre-line"
                        >
                            {{ item.notes }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border bg-background p-6 shadow-sm"
                >
                    <h2
                        class="mb-5 text-lg font-semibold"
                    >
                        Registrar movimiento
                    </h2>

                    <form
                        @submit.prevent="
                            submitMovement
                        "
                        class="space-y-4"
                    >
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Tipo *
                            </label>

                            <select
                                v-model="
                                    movementForm.movement_type
                                "
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            >
                                <option
                                    value="entry"
                                >
                                    Entrada
                                </option>

                                <option
                                    value="exit"
                                >
                                    Salida
                                </option>

                                <option
                                    value="adjustment_in"
                                >
                                    Ajuste positivo
                                </option>

                                <option
                                    value="adjustment_out"
                                >
                                    Ajuste negativo
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Cantidad *
                            </label>

                            <div
                                class="relative"
                            >
                                <input
                                    v-model="
                                        movementForm.quantity
                                    "
                                    type="number"
                                    min="0.001"
                                    step="0.001"
                                    class="w-full rounded-lg border bg-background px-4 py-2.5 pr-20"
                                />

                                <span
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground"
                                >
                                    {{
                                        item.measurement_unit
                                    }}
                                </span>
                            </div>

                            <p
                                v-if="
                                    movementForm
                                        .errors
                                        .quantity
                                "
                                class="mt-1 text-sm text-red-500"
                            >
                                {{
                                    movementForm
                                        .errors
                                        .quantity
                                }}
                            </p>
                        </div>

                        <div
                            v-if="isIncoming"
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Costo unitario
                            </label>

                            <input
                                v-model="
                                    movementForm.unit_cost
                                "
                                type="number"
                                min="0"
                                step="0.0001"
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Referencia
                            </label>

                            <input
                                v-model="
                                    movementForm.reference
                                "
                                type="text"
                                placeholder="Factura, compra, orden..."
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Nota
                            </label>

                            <textarea
                                v-model="
                                    movementForm.notes
                                "
                                rows="3"
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="
                                movementForm.processing
                            "
                            class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-3 font-semibold text-white disabled:opacity-50"
                            :class="
                                isIncoming
                                    ? 'bg-emerald-600'
                                    : 'bg-[#e84657]'
                            "
                        >
                            <ArrowUpCircle
                                v-if="
                                    isIncoming
                                "
                                class="h-4 w-4"
                            />

                            <ArrowDownCircle
                                v-else
                                class="h-4 w-4"
                            />

                            Registrar movimiento
                        </button>
                    </form>
                </div>
            </div>

            <div
                class="rounded-xl border bg-background p-6 shadow-sm"
            >
                <div
                    class="mb-5 flex items-center justify-between"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        Movimientos recientes
                    </h2>

                    <Link
                        :href="`/inventory/${item.id}/movements`"
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        Ver todos
                    </Link>
                </div>

                <div
                    v-if="
                        movements.length ===
                        0
                    "
                    class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
                >
                    No hay movimientos.
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full text-sm"
                    >
                        <thead
                            class="border-b"
                        >
                            <tr>
                                <th
                                    class="py-3 text-left"
                                >
                                    Fecha
                                </th>

                                <th
                                    class="py-3 text-left"
                                >
                                    Tipo
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Cantidad
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Stock anterior
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Stock final
                                </th>

                                <th
                                    class="py-3 text-left"
                                >
                                    Referencia
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    movement in movements
                                "
                                :key="
                                    movement.id
                                "
                                class="border-b last:border-b-0"
                            >
                                <td
                                    class="py-4"
                                >
                                    {{
                                        movement.moved_at
                                    }}
                                </td>

                                <td
                                    class="py-4 font-semibold"
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
                                    class="py-4 text-right font-bold"
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
                                    class="py-4 text-right"
                                >
                                    {{
                                        Number(
                                            movement.previous_stock,
                                        )
                                    }}
                                </td>

                                <td
                                    class="py-4 text-right font-semibold"
                                >
                                    {{
                                        Number(
                                            movement.resulting_stock,
                                        )
                                    }}
                                </td>

                                <td
                                    class="py-4"
                                >
                                    {{
                                        movement.reference
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