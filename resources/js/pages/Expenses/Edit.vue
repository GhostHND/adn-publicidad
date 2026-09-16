<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FinanceMovementForm from '@/components/finance/FinanceMovementForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    expense: any;
    financialAccounts: any[];
}>();

const form = useForm({
    expense_date:
        props.expense.expense_date,
    category:
        props.expense.category,
    description:
        props.expense.description,
    supplier:
        props.expense.supplier ?? '',
    amount:
        props.expense.amount,
    payment_method:
        props.expense.payment_method,
    financial_account_id:
        props.expense
            .financial_account_id ?? '',
    reference:
        props.expense.reference ?? '',
    notes:
        props.expense.notes ?? '',
});

const submit = () => {
    form.patch(
        `/expenses/${props.expense.id}`,
    );
};

const breadcrumbs = [
    {
        title: 'Gastos',
        href: '/expenses',
    },
    {
        title:
            props.expense
                .expense_number,
        href:
            `/expenses/${props.expense.id}/edit`,
    },
];
</script>

<template>
    <Head title="Editar gasto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1 class="text-2xl font-bold">
                    Editar gasto
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    {{
                        expense.expense_number
                    }}
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
                submit-label="Guardar cambios"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>