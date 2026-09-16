<script setup lang="ts">
import InventoryItemForm from '@/components/inventory/InventoryItemForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    category: '',
    measurement_unit: '',
    initial_stock: 0,
    minimum_stock: 0,
    unit_cost: 0,
    supplier: '',
    location: '',
    active: true,
    notes: '',
});

const submit = () => {
    form.post('/inventory');
};

const breadcrumbs = [
    {
        title: 'Inventario',
        href: '/inventory',
    },
    {
        title: 'Nuevo material',
        href: '/inventory/create',
    },
];
</script>

<template>
    <Head title="Nuevo material" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1
                    class="text-2xl font-bold"
                >
                    Nuevo material
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    Agrega un material o insumo
                    al inventario.
                </p>
            </div>

            <InventoryItemForm
                :form="form"
                :creating="true"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>