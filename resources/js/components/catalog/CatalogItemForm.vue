<script setup lang="ts">
import CatalogItemForm from '@/components/catalog/CatalogItemForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    inventoryItems: any[];
}>();

const form = useForm({
    name: '',
    description: '',
    item_type: 'service',
    category: '',
    pricing_method: 'AREA',
    measurement_unit: 'in²',
    cost_price: '',
    sale_price: '',
    cost_rate: '',
    sale_rate: '',
    margin_percentage: '',
    active: true,

    production_steps: [
        {
            title:
                'Diseño / preparación',
            description:
                'Preparar y verificar los archivos antes de producción.',
        },
        {
            title: 'Producción',
            description:
                'Realizar el proceso principal del trabajo.',
        },
        {
            title: 'Acabado',
            description:
                'Realizar acabados y preparar el producto para revisión.',
        },
    ],

    material_recipes: [] as Array<{
        inventory_item_id:
            number | string;
        calculation_method:
            string;
        quantity_rate:
            number | string;
        waste_percentage:
            number | string;
        notes: string;
    }>,
});

const submit = () => {
    form.post('/catalog');
};

const breadcrumbs = [
    {
        title: 'Catálogo',
        href: '/catalog',
    },
    {
        title: 'Nuevo',
        href: '/catalog/create',
    },
];
</script>

<template>
    <Head
        title="Nuevo producto"
    />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1
                    class="text-2xl font-bold"
                >
                    Nuevo producto o servicio
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    Precio, flujo de producción
                    y materiales se configuran
                    una sola vez.
                </p>
            </div>

            <CatalogItemForm
                :form="form"
                :inventory-items="
                    inventoryItems
                "
                :creating="true"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>