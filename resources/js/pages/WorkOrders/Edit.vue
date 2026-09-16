<script setup lang="ts">
import WorkOrderForm from '@/components/work-orders/WorkOrderForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    order: any;
    clients: any[];
    employees: any[];
    sales: any[];
    inventoryItems: any[];
    materialsEditable: boolean;
}>();

const form = useForm({
    client_id:
        props.order.client_id,

    responsible_employee_id:
        props.order
            .responsible_employee_id
        ?? '',

    title:
        props.order.title,

    priority:
        props.order.priority,

    order_date:
        props.order.order_date,

    due_date:
        props.order.due_date ?? '',

    description:
        props.order.description ?? '',

    internal_notes:
        props.order
            .internal_notes ?? '',

    materials:
        props.order.materials.map(
            (material: any) => ({
                inventory_item_id:
                    material
                        .inventory_item_id,

                quantity_planned:
                    material
                        .quantity_planned,

                notes:
                    material.notes ?? '',
            }),
        ),
});

const submit = () => {
    form.patch(
        `/work-orders/${props.order.id}`,
    );
};

const breadcrumbs = [
    {
        title: 'Órdenes de trabajo',
        href: '/work-orders',
    },
    {
        title:
            props.order
                .work_order_number,
        href:
            `/work-orders/${props.order.id}`,
    },
    {
        title: 'Editar',
        href:
            `/work-orders/${props.order.id}/edit`,
    },
];
</script>

<template>
    <Head title="Editar orden" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1 class="text-2xl font-bold">
                    Editar orden
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    {{
                        order.work_order_number
                    }}
                </p>
            </div>

            <WorkOrderForm
                :form="form"
                :clients="clients"
                :employees="employees"
                :sales="sales"
                :inventory-items="
                    inventoryItems
                "
                :creating="false"
                :materials-editable="
                    materialsEditable
                "
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>