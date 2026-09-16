<script setup lang="ts">
import WorkOrderForm from '@/components/work-orders/WorkOrderForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    clients: any[];
    employees: any[];
    sales: any[];
    inventoryItems: any[];
}>();

const now = new Date();

const offset =
    now.getTimezoneOffset();

const today = new Date(
    now.getTime()
    - offset * 60000,
)
    .toISOString()
    .slice(0, 10);

const form = useForm({
    client_id: '',
    sale_id: '',
    responsible_employee_id: '',
    title: '',
    status: 'pending',
    priority: 'normal',
    order_date: today,
    due_date: '',
    description: '',
    internal_notes: '',

    materials: [] as Array<{
        inventory_item_id:
            number | string;
        quantity_planned:
            number | string;
        notes: string;
    }>,
});

const submit = () => {
    form.post('/work-orders');
};

const breadcrumbs = [
    {
        title: 'Órdenes de trabajo',
        href: '/work-orders',
    },
    {
        title: 'Nueva orden',
        href: '/work-orders/create',
    },
];
</script>

<template>
    <Head title="Nueva orden" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1 class="text-2xl font-bold">
                    Nueva orden de trabajo
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    Planifica la producción,
                    responsable y materiales.
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
                :creating="true"
                :materials-editable="true"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>