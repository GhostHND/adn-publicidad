<script setup lang="ts">
import InventoryItemForm from '@/components/inventory/InventoryItemForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    item: any;
}>();

const form = useForm({
    name: props.item.name,
    category:
        props.item.category ?? '',
    measurement_unit:
        props.item.measurement_unit,
    minimum_stock:
        props.item.minimum_stock,
    unit_cost:
        props.item.unit_cost,
    supplier:
        props.item.supplier ?? '',
    location:
        props.item.location ?? '',
    active:
        props.item.active,
    notes:
        props.item.notes ?? '',
});

const submit = () => {
    form.patch(
        `/inventory/${props.item.id}`,
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
    {
        title: 'Editar',
        href:
            `/inventory/${props.item.id}/edit`,
    },
];
</script>

<template>
    <Head title="Editar material" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1
                    class="text-2xl font-bold"
                >
                    Editar material
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    {{ item.item_code }}
                    ·
                    {{ item.name }}
                </p>
            </div>

            <InventoryItemForm
                :form="form"
                :creating="false"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>