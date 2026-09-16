<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Catálogo',
        href: '/catalog',
    },
    {
        title: 'Nuevo elemento',
        href: '/catalog/create',
    },
];

const form = useForm({
    name: '',
    description: '',
    item_type: 'service',
    category: '',
    pricing_method: 'FIXED',
    measurement_unit: '',
    cost_price: '',
    sale_price: '',
    cost_rate: '',
    sale_rate: '',
    margin_percentage: '',
    active: true,
});

const usesRates = () =>
    ['AREA', 'LINEAR'].includes(form.pricing_method);

const usesSalePrice = () =>
    ['FIXED', 'UNIT', 'RESALE'].includes(form.pricing_method);

const usesCostPrice = () =>
    ['FIXED', 'UNIT', 'RESALE', 'COST_MARGIN'].includes(
        form.pricing_method,
    );

const usesMargin = () =>
    form.pricing_method === 'COST_MARGIN';

const submit = () => {
    form.post('/catalog');
};
</script>

<template>
    <Head title="Nuevo elemento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold">
                        Nuevo elemento
                    </h1>

                    <p class="text-sm text-muted-foreground">
                        Registra un producto o servicio de ADN Publicidad.
                    </p>
                </div>

                <Link
                    href="/catalog"
                    class="inline-flex items-center justify-center rounded-lg border px-5 py-2.5 text-sm font-semibold transition hover:bg-muted"
                >
                    Volver al catálogo
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="rounded-xl border bg-background p-6 shadow-sm"
            >
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Nombre *
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Ej. Sticker impreso"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Categoría
                        </label>

                        <input
                            v-model="form.category"
                            type="text"
                            placeholder="Ej. Stickers"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Tipo *
                        </label>

                        <select
                            v-model="form.item_type"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        >
                            <option value="product">
                                Producto
                            </option>

                            <option value="service">
                                Servicio
                            </option>

                            <option value="custom">
                                Trabajo personalizado
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Método de precio *
                        </label>

                        <select
                            v-model="form.pricing_method"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        >
                            <option value="AREA">
                                Por área
                            </option>

                            <option value="UNIT">
                                Por unidad
                            </option>

                            <option value="FIXED">
                                Precio fijo
                            </option>

                            <option value="LINEAR">
                                Medida lineal
                            </option>

                            <option value="COST_MARGIN">
                                Costo + margen
                            </option>

                            <option value="RESALE">
                                Reventa
                            </option>

                            <option value="MANUAL">
                                Precio manual
                            </option>
                        </select>
                    </div>

                    <template v-if="usesRates()">
                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Unidad de medida *
                            </label>

                            <select
                                v-model="form.measurement_unit"
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            >
                                <option value="">
                                    Seleccionar
                                </option>

                                <option value="in²">
                                    Pulgada²
                                </option>

                                <option value="ft²">
                                    Pie²
                                </option>

                                <option value="cm²">
                                    Centímetro²
                                </option>

                                <option value="m²">
                                    Metro²
                                </option>

                                <option value="cm">
                                    Centímetro
                                </option>

                                <option value="m">
                                    Metro
                                </option>

                                <option value="in">
                                    Pulgada
                                </option>
                            </select>

                            <p
                                v-if="form.errors.measurement_unit"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.measurement_unit }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Costo por medida *
                            </label>

                            <input
                                v-model="form.cost_rate"
                                type="number"
                                min="0"
                                step="0.0001"
                                placeholder="0.1600"
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />

                            <p
                                v-if="form.errors.cost_rate"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.cost_rate }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Precio de venta por medida *
                            </label>

                            <input
                                v-model="form.sale_rate"
                                type="number"
                                min="0"
                                step="0.0001"
                                placeholder="0.6100"
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />

                            <p
                                v-if="form.errors.sale_rate"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.sale_rate }}
                            </p>
                        </div>
                    </template>

                    <div v-if="usesCostPrice()">
                        <label class="mb-2 block text-sm font-medium">
                            Costo
                            <span
                                v-if="
                                    ['RESALE', 'COST_MARGIN'].includes(
                                        form.pricing_method,
                                    )
                                "
                            >
                                *
                            </span>
                        </label>

                        <input
                            v-model="form.cost_price"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.cost_price"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.cost_price }}
                        </p>
                    </div>

                    <div v-if="usesSalePrice()">
                        <label class="mb-2 block text-sm font-medium">
                            Precio de venta *
                        </label>

                        <input
                            v-model="form.sale_price"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.sale_price"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.sale_price }}
                        </p>
                    </div>

                    <div v-if="usesMargin()">
                        <label class="mb-2 block text-sm font-medium">
                            Margen de ganancia (%) *
                        </label>

                        <input
                            v-model="form.margin_percentage"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="30"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.margin_percentage"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.margin_percentage }}
                        </p>
                    </div>

                    <div
                        v-if="form.pricing_method === 'MANUAL'"
                        class="md:col-span-2 rounded-lg border border-dashed p-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            El precio se definirá manualmente al momento de
                            realizar la cotización o venta.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Descripción
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Descripción del producto o servicio..."
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.active"
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                            />

                            <span class="text-sm font-medium">
                                Elemento activo
                            </span>
                        </label>
                    </div>
                </div>

                <div
                    class="mt-8 flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/catalog"
                        class="inline-flex items-center justify-center rounded-lg border px-5 py-2.5 text-sm font-semibold transition hover:bg-muted"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-lg bg-[#0fa7b4] px-6 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Guardando...'
                                : 'Guardar elemento'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>