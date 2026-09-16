<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FinanceMovementForm from '@/components/finance/FinanceMovementForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    financialAccounts: any[];
}>();

const now = new Date();
const offset = now.getTimezoneOffset();

const today = new Date(
    now.getTime() - offset * 60000,
)
    .toISOString()
    .slice(0, 10);

const form = useForm({
    income_date: today,
    category: '',
    description: '',
    amount: '',
    payment_method: 'cash',
    financial_account_id: '',
    reference: '',
    notes: '',
});

const submit = () => {
    form.post('/income');
};

const breadcrumbs = [
    {
        title: 'Ingresos',
        href: '/income',
    },
    {
        title: 'Nuevo ingreso',
        href: '/income/create',
    },
];
</script>

<template>
    <Head title="Nuevo ingreso" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1 class="text-2xl font-bold">
                    Nuevo ingreso
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    Registra un ingreso diferente a una venta.
                </p>
            </div>

            <FinanceMovementForm
                :form="form"
                :financial-accounts="
                    financialAccounts
                "
                movement-type="income"
                date-field="income_date"
                cancel-href="/income"
                submit-label="Registrar ingreso"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>