<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Boxes,
    ClipboardList,
    Plus,
    Trash2,
} from '@lucide/vue';
import { computed, watch } from 'vue';

const props = defineProps<{
    form: any;
    clients: any[];
    employees: any[];
    sales: any[];
    inventoryItems: any[];
    creating: boolean;
    materialsEditable: boolean;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const selectedSale = computed(() =>
    props.sales.find(
        (sale) =>
            Number(sale.id)
            === Number(
                props.form.sale_id,
            ),
    ),
);

watch(
    () => props.form.sale_id,
    () => {
        if (
            props.creating
            && selectedSale.value
        ) {
            props.form.client_id =
                selectedSale.value.client_id;

            if (
                !props.form.title
            ) {
                props.form.title =
                    `Producción ${selectedSale.value.sale_number}`;
            }
        }
    },
);

const addMaterial = () => {
    props.form.materials.push({
        inventory_item_id: '',
        quantity_planned: '',
        notes: '',
    });
};

const removeMaterial = (
    index: number,
) => {
    props.form.materials.splice(
        index,
        1,
    );
};

const inventoryItem = (
    id: number | string,
) =>
    props.inventoryItems.find(
        (item) =>
            Number(item.id)
            === Number(id),
    );

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
</script>

<template>
    <form
        @submit.prevent="emit('submit')"
        class="space-y-6"
    >
        <div
            class="rounded-2xl border bg-background p-6 shadow-sm"
        >
            <div
                class="mb-6 flex items-center gap-3"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                >
                    <ClipboardList
                        class="h-5 w-5"
                    />
                </div>

                <div>
                    <h2 class="font-semibold">
                        Datos de la orden
                    </h2>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Información general del
                        trabajo de producción.
                    </p>
                </div>
            </div>

            <div
                class="grid gap-5 md:grid-cols-2"
            >
                <div
                    v-if="creating"
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Venta relacionada
                    </label>

                    <select
                        v-model="form.sale_id"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    >
                        <option value="">
                            Orden sin venta asociada
                        </option>

                        <option
                            v-for="sale in sales"
                            :key="sale.id"
                            :value="sale.id"
                        >
                            {{ sale.sale_number }}
                            ·
                            {{ sale.client_name }}
                            ·
                            {{ money(sale.total) }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Cliente *
                    </label>

                    <select
                        v-model="form.client_id"
                        :disabled="
                            creating
                            && Boolean(
                                selectedSale,
                            )
                        "
                        class="w-full rounded-lg border bg-background px-4 py-2.5 disabled:opacity-60"
                    >
                        <option value="">
                            Seleccionar cliente
                        </option>

                        <option
                            v-for="client in clients"
                            :key="client.id"
                            :value="client.id"
                        >
                            {{ client.code }}
                            ·
                            {{ client.name }}
                        </option>
                    </select>

                    <p
                        v-if="
                            form.errors.client_id
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors.client_id
                        }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Responsable
                    </label>

                    <select
                        v-model="
                            form.responsible_employee_id
                        "
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    >
                        <option value="">
                            Sin asignar
                        </option>

                        <option
                            v-for="
                                employee in employees
                            "
                            :key="employee.id"
                            :value="employee.id"
                        >
                            {{ employee.name }}
                            {{
                                employee.position
                                    ? `· ${employee.position}`
                                    : ''
                            }}
                        </option>
                    </select>
                </div>

                <div
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Título del trabajo *
                    </label>

                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Ej. Fabricación e instalación de rótulo"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Prioridad *
                    </label>

                    <select
                        v-model="form.priority"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    >
                        <option value="low">
                            Baja
                        </option>

                        <option value="normal">
                            Normal
                        </option>

                        <option value="high">
                            Alta
                        </option>

                        <option value="urgent">
                            Urgente
                        </option>
                    </select>
                </div>

                <div
                    v-if="creating"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Estado inicial *
                    </label>

                    <select
                        v-model="form.status"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    >
                        <option value="draft">
                            Borrador
                        </option>

                        <option value="pending">
                            Pendiente
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Fecha de orden *
                    </label>

                    <input
                        v-model="form.order_date"
                        type="date"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Fecha límite
                    </label>

                    <input
                        v-model="form.due_date"
                        type="date"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Descripción del trabajo
                    </label>

                    <textarea
                        v-model="form.description"
                        rows="4"
                        placeholder="Detalles técnicos, medidas, acabados, colores..."
                        class="w-full rounded-lg border bg-background px-4 py-3"
                    />
                </div>

                <div
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Notas internas
                    </label>

                    <textarea
                        v-model="
                            form.internal_notes
                        "
                        rows="3"
                        class="w-full rounded-lg border bg-background px-4 py-3"
                    />
                </div>
            </div>

            <div
                v-if="
                    selectedSale
                    && creating
                "
                class="mt-6 rounded-xl border bg-muted/30 p-5"
            >
                <p class="font-semibold">
                    Productos de la venta
                </p>

                <div
                    class="mt-3 space-y-2"
                >
                    <div
                        v-for="
                            (
                                item,
                                index
                            ) in selectedSale.items
                        "
                        :key="index"
                        class="flex justify-between gap-4 text-sm"
                    >
                        <span>
                            {{ item.name }}
                        </span>

                        <strong>
                            x
                            {{
                                Number(
                                    item.quantity,
                                )
                            }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border bg-background shadow-sm"
        >
            <div
                class="flex flex-col gap-4 border-b p-6 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div
                        class="flex items-center gap-3"
                    >
                        <Boxes
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <h2 class="font-semibold">
                            Materiales planificados
                        </h2>
                    </div>

                    <p
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        Serán descontados del
                        inventario al iniciar
                        producción.
                    </p>
                </div>

                <button
                    v-if="materialsEditable"
                    type="button"
                    @click="addMaterial"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-4 py-2.5 text-sm font-semibold text-white"
                >
                    <Plus class="h-4 w-4" />
                    Agregar material
                </button>
            </div>

            <div class="p-6">
                <div
                    v-if="
                        !materialsEditable
                    "
                    class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300"
                >
                    Los materiales ya no pueden
                    modificarse porque la orden
                    salió de la etapa de
                    planificación.
                </div>

                <div
                    v-if="
                        form.materials.length ===
                        0
                    "
                    class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
                >
                    No hay materiales
                    planificados.
                </div>

                <div
                    v-else
                    class="space-y-4"
                >
                    <div
                        v-for="
                            (
                                material,
                                index
                            ) in form.materials
                        "
                        :key="index"
                        class="rounded-xl border p-5"
                    >
                        <div
                            class="grid gap-4 md:grid-cols-3"
                        >
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Material *
                                </label>

                                <select
                                    v-model="
                                        material.inventory_item_id
                                    "
                                    :disabled="
                                        !materialsEditable
                                    "
                                    class="w-full rounded-lg border bg-background px-4 py-2.5 disabled:opacity-60"
                                >
                                    <option value="">
                                        Seleccionar
                                    </option>

                                    <option
                                        v-for="
                                            item in inventoryItems
                                        "
                                        :key="item.id"
                                        :value="item.id"
                                    >
                                        {{ item.code }}
                                        ·
                                        {{ item.name }}
                                    </option>
                                </select>

                                <p
                                    v-if="
                                        inventoryItem(
                                            material.inventory_item_id,
                                        )
                                    "
                                    class="mt-2 text-xs text-muted-foreground"
                                >
                                    Disponible:
                                    {{
                                        Number(
                                            inventoryItem(
                                                material.inventory_item_id,
                                            ).current_stock,
                                        )
                                    }}
                                    {{
                                        inventoryItem(
                                            material.inventory_item_id,
                                        ).measurement_unit
                                    }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Cantidad *
                                </label>

                                <input
                                    v-model="
                                        material.quantity_planned
                                    "
                                    :disabled="
                                        !materialsEditable
                                    "
                                    type="number"
                                    min="0.001"
                                    step="0.001"
                                    class="w-full rounded-lg border bg-background px-4 py-2.5 disabled:opacity-60"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Nota
                                </label>

                                <div
                                    class="flex gap-2"
                                >
                                    <input
                                        v-model="
                                            material.notes
                                        "
                                        :disabled="
                                            !materialsEditable
                                        "
                                        type="text"
                                        class="w-full rounded-lg border bg-background px-4 py-2.5 disabled:opacity-60"
                                    />

                                    <button
                                        v-if="
                                            materialsEditable
                                        "
                                        type="button"
                                        @click="
                                            removeMaterial(
                                                index,
                                            )
                                        "
                                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border text-[#e84657]"
                                    >
                                        <Trash2
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p
                    v-if="
                        form.errors.materials
                    "
                    class="mt-3 text-sm text-red-500"
                >
                    {{
                        form.errors.materials
                    }}
                </p>
            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
        >
            <Link
                href="/work-orders"
                class="rounded-lg border px-5 py-2.5 text-center text-sm font-semibold"
            >
                Cancelar
            </Link>

            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-[#0fa7b4] px-6 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
            >
                {{
                    form.processing
                        ? 'Guardando...'
                        : creating
                          ? 'Crear orden'
                          : 'Guardar cambios'
                }}
            </button>
        </div>
    </form>
</template>