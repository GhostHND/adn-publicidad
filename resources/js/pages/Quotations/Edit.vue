<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import QuotationForm from '@/components/quotations/QuotationForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    quotation: any;
    clients: any[];
    catalogItems: any[];
}>();

const form = useForm({
    client_id: props.quotation.client_id,
    quotation_date: props.quotation.quotation_date,
    valid_until: props.quotation.valid_until ?? '',
    discount: props.quotation.discount ?? 0,
    notes: props.quotation.notes ?? '',
    items: props.quotation.items,
});

const submit = () => {
    form.patch(`/quotations/${props.quotation.id}`);
};

const breadcrumbs = [
    {
        title: 'Cotizaciones',
        href: '/quotations',
    },
    {
        title: props.quotation.quotation_number,
        href: `/quotations/${props.quotation.id}`,
    },
    {
        title: 'Editar',
        href: `/quotations/${props.quotation.id}/edit`,
    },
];
</script>

<template>
    <Head title="Editar cotización" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold">
                    Editar cotización
                </h1>

                <p class="text-sm text-muted-foreground">
                    {{ quotation.quotation_number }}
                </p>
            </div>

            <QuotationForm
                :form="form"
                :clients="clients"
                :catalog-items="catalogItems"
                submit-label="Guardar cambios"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>