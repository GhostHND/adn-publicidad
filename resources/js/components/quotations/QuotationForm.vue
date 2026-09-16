<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';

type Client = {
    id: number;
    code: string;
    name: string;
};

type CatalogItem = {
    id: number;
    item_code: string;
    name: string;
    pricing_method: string;
    measurement_unit: string | null;
    sale_price: string | null;
    cost_price: string | null;
    sale_rate: string | null;
    cost_rate: string | null;
};

const props = defineProps<{
    form: any;
    clients: Client[];
    catalogItems: CatalogItem[];
    submitLabel: string;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const getCatalogItem = (id: number | string) => {
    return props.catalogItems.find(
        (item) => Number(item.id) === Number(id),
    );
};

const addItem = () => {
    props.form.items.push({
        catalog_item_id: '',
        quantity: 1,
        width: '',
        height: '',
        manual_price: '',
    });
};

const removeItem = (index: number) => {
    if (props.form.items.length === 1) {
        return;
    }

    props.form.items.splice(index, 1);
};

const resetItemData = (index: number) => {
    props.form.items[index].quantity = 1;
    props.form.items[index].width = '';
    props.form.items[index].height = '';
    props.form.items[index].manual_price = '';
};

const lineTotal = (line: any) => {
    const item = getCatalogItem(line.catalog_item_id);

    if (!item) {
        return 0;
    }

    const quantity = Number(line.quantity || 0);

    switch (item.pricing_method) {
        case 'AREA':
            return (
                Number(line.width || 0) *
                Number(line.height || 0) *
                Number(item.sale_rate || 0) *
                quantity
            );

        case 'LINEAR':
            return (
                Number(line.width || 0) *
                Number(item.sale_rate || 0) *
                quantity
            );

        case 'MANUAL':
            return Number(line.manual_price || 0) * quantity;

        default:
            return Number(item.sale_price || 0) * quantity;
    }
};

const subtotal = computed(() => {
    return props.form.items.reduce(
        (total: number, line: any) => total + lineTotal(line),
        0,
    );
});

const total = computed(() => {
    return Math.max(
        subtotal.value - Number(props.form.discount || 0),
        0,
    );
});

const money = (value: number) => {
    return `L ${value.toLocaleString('es-HN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
};

const pricingLabel = (method: string) => {
    const labels: Record<string, string> = {
        AREA: 'Por área',
        UNIT: 'Por unidad',
        FIXED: 'Precio fijo',
        LINEAR: 'Medida lineal',
        COST_MARGIN: 'Costo + margen',
        RESALE: 'Reventa',
        MANUAL: 'Manual',
    };

    return labels[method] ?? method;
};
</script>

<template>
    <form
        @submit.prevent="emit('submit')"
        class="space-y-6"
    >
        <div class="rounded-xl border bg-background p-6 shadow-sm">
            <h2 class="mb-5 text-lg font-semibold">
                Información de la cotización
            </h2>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium">
                        Cliente *
                    </label>

                    <select
                        v-model="form.client_id"
                        class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    >
                        <option value="">
                            Seleccionar cliente
                        </option>

                        <option
                            v-for="client in clients"
                            :key="client.id"
                            :value="client.id"
                        >
                            {{ client.code }} — {{ client.name }}
                        </option>
                    </select>

                    <p
                        v-if="form.errors.client_id"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.client_id }}
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Fecha *
                    </label>

                    <input
                        v-model="form.quotation_date"
                        type="date"
                        class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Válida hasta
                    </label>

                    <input
                        v-model="form.valid_until"
                        type="date"
                        class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    />
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-background p-6 shadow-sm">
            <div
                class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2 class="text-lg font-semibold">
                        Productos y servicios
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Agrega los trabajos que se incluirán en la cotización.
                    </p>
                </div>

                <button
                    type="button"
                    @click="addItem"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4]"
                >
                    <Plus class="h-4 w-4" />
                    Agregar línea
                </button>
            </div>

            <div class="space-y-4">
                <div
                    v-for="(line, index) in form.items"
                    :key="index"
                    class="rounded-xl border p-4"
                >
                    <div class="grid gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-4">
                            <label class="mb-2 block text-sm font-medium">
                                Producto / servicio
                            </label>

                            <select
                                v-model="line.catalog_item_id"
                                @change="resetItemData(index)"
                                class="w-full rounded-lg border bg-background px-3 py-2.5"
                            >
                                <option value="">
                                    Seleccionar
                                </option>

                                <option
                                    v-for="item in catalogItems"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.item_code }} — {{ item.name }}
                                </option>
                            </select>

                            <p
                                v-if="getCatalogItem(line.catalog_item_id)"
                                class="mt-2 text-xs text-muted-foreground"
                            >
                                {{
                                    pricingLabel(
                                        getCatalogItem(line.catalog_item_id)!
                                            .pricing_method,
                                    )
                                }}
                            </p>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-sm font-medium">
                                Cantidad
                            </label>

                            <input
                                v-model="line.quantity"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="w-full rounded-lg border bg-background px-3 py-2.5"
                            />
                        </div>

                        <template
                            v-if="
                                getCatalogItem(line.catalog_item_id)
                                    ?.pricing_method === 'AREA'
                            "
                        >
                            <div class="lg:col-span-2">
                                <label class="mb-2 block text-sm font-medium">
                                    Ancho
                                </label>

                                <input
                                    v-model="line.width"
                                    type="number"
                                    min="0.0001"
                                    step="0.0001"
                                    class="w-full rounded-lg border bg-background px-3 py-2.5"
                                />
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-2 block text-sm font-medium">
                                    Alto
                                </label>

                                <input
                                    v-model="line.height"
                                    type="number"
                                    min="0.0001"
                                    step="0.0001"
                                    class="w-full rounded-lg border bg-background px-3 py-2.5"
                                />
                            </div>
                        </template>

                        <div
                            v-if="
                                getCatalogItem(line.catalog_item_id)
                                    ?.pricing_method === 'LINEAR'
                            "
                            class="lg:col-span-2"
                        >
                            <label class="mb-2 block text-sm font-medium">
                                Medida
                            </label>

                            <input
                                v-model="line.width"
                                type="number"
                                min="0.0001"
                                step="0.0001"
                                class="w-full rounded-lg border bg-background px-3 py-2.5"
                            />
                        </div>

                        <div
                            v-if="
                                getCatalogItem(line.catalog_item_id)
                                    ?.pricing_method === 'MANUAL'
                            "
                            class="lg:col-span-2"
                        >
                            <label class="mb-2 block text-sm font-medium">
                                Precio unitario
                            </label>

                            <input
                                v-model="line.manual_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border bg-background px-3 py-2.5"
                            />
                        </div>

                        <div class="flex items-end lg:col-span-2">
                            <div class="w-full">
                                <p class="mb-2 text-sm font-medium">
                                    Subtotal
                                </p>

                                <div
                                    class="rounded-lg bg-muted px-3 py-2.5 font-semibold"
                                >
                                    {{ money(lineTotal(line)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-end">
                        <button
                            type="button"
                            @click="removeItem(index)"
                            :disabled="form.items.length === 1"
                            class="inline-flex items-center gap-2 text-sm text-red-500 transition hover:text-red-600 disabled:opacity-30"
                        >
                            <Trash2 class="h-4 w-4" />
                            Quitar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-xl border bg-background p-6 shadow-sm">
                <label class="mb-2 block text-sm font-medium">
                    Notas
                </label>

                <textarea
                    v-model="form.notes"
                    rows="6"
                    placeholder="Condiciones, observaciones o información adicional..."
                    class="w-full rounded-lg border bg-background px-4 py-3 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                />
            </div>

            <div class="rounded-xl border bg-background p-6 shadow-sm">
                <h2 class="mb-5 text-lg font-semibold">
                    Resumen
                </h2>

                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">
                            Subtotal
                        </span>

                        <span class="font-semibold">
                            {{ money(subtotal) }}
                        </span>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Descuento
                        </label>

                        <input
                            v-model="form.discount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border bg-background px-4 py-2.5"
                        />
                    </div>

                    <div
                        class="flex justify-between border-t pt-4 text-lg"
                    >
                        <span class="font-semibold">
                            Total
                        </span>

                        <span class="font-bold text-[#0fa7b4]">
                            {{ money(total) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <Link
                href="/quotations"
                class="inline-flex items-center justify-center rounded-lg border px-5 py-2.5 text-sm font-semibold transition hover:bg-muted"
            >
                Cancelar
            </Link>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center justify-center rounded-lg bg-[#0fa7b4] px-6 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
            >
                {{
                    form.processing
                        ? 'Guardando...'
                        : submitLabel
                }}
            </button>
        </div>
    </form>
</template>