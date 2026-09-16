<script setup lang="ts">
import SaleForm from '@/components/sales/SaleForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    clients: any[];
    catalogItems: any[];
    financialAccounts: any[];
}>();

const breadcrumbs = [
    {
        title: 'Ventas',
        href: '/sales',
    },
    {
        title: 'Nueva venta',
        href: '/sales/create',
    },
];
</script>

<template>
    <Head title="Nueva venta" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1
                    class="text-2xl font-bold"
                >
                    Nueva venta
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    Registra una venta y
                    especifica dónde se
                    recibió el pago.
                </p>
            </div>

            <SaleForm
                :clients="clients"
                :catalog-items="
                    catalogItems
                "
                :financial-accounts="
                    financialAccounts
                "
            />
        </div>
    </AppLayout>
</template>