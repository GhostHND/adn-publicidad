<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import QuotationForm from '@/components/quotations/QuotationForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    clients: any[];
    catalogItems: any[];
}>();

const today = new Date();

const localDate = (date: Date) => {
    const offset = date.getTimezoneOffset();
    const adjusted = new Date(date.getTime() - offset * 60000);

    return adjusted.toISOString().slice(0, 10);
};

const validUntil = new Date();
validUntil.setDate(validUntil.getDate() + 15);

const form = useForm({
    client_id: '',
    quotation_date: localDate(today),
    valid_until: localDate(validUntil),
    discount: 0,
    notes: '',
    items: [
        {
            catalog_item_id: '',
            quantity: 1,
            width: '',
            height: '',
            manual_price: '',
        },
    ],
});

const submit = () => {
    form.post('/quotations');
};

const breadcrumbs = [
    {
        title: 'Cotizaciones',
        href: '/quotations',
    },
    {
        title: 'Nueva cotización',
        href: '/quotations/create',
    },
];
</script>

<template>
    <Head title="Nueva cotización" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold">
                    Nueva cotización
                </h1>

                <p class="text-sm text-muted-foreground">
                    Prepara una cotización para un cliente.
                </p>
            </div>

            <QuotationForm
                :form="form"
                :clients="clients"
                :catalog-items="catalogItems"
                submit-label="Guardar cotización"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>