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
    expense_date: today,
    category: '',
    description: '',
    supplier: '',
    amount: '',
    payment_method: 'cash',
    financial_account_id: '',
    reference: '',
    notes: '',
});

const submit = () => {
    form.post('/expenses');
};

const breadcrumbs = [
    {
        title: 'Gastos',
        href: '/expenses',
    },
    {
        title: 'Nuevo gasto',
        href: '/expenses/create',
    },
];
</script>

<template>
    <Head title="Nuevo gasto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1 class="text-2xl font-bold">
                    Nuevo gasto
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    Registra un egreso del negocio.
                </p>
            </div>

            <FinanceMovementForm
                :form="form"
                :financial-accounts="
                    financialAccounts
                "
                movement-type="expense"
                date-field="expense_date"
                cancel-href="/expenses"
                submit-label="Registrar gasto"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>