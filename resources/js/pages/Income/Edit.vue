<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FinanceMovementForm from '@/components/finance/FinanceMovementForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    income: any;
    financialAccounts: any[];
}>();

const form = useForm({
    income_date:
        props.income.income_date,
    category:
        props.income.category,
    description:
        props.income.description,
    amount:
        props.income.amount,
    payment_method:
        props.income.payment_method,
    financial_account_id:
        props.income
            .financial_account_id ?? '',
    reference:
        props.income.reference ?? '',
    notes:
        props.income.notes ?? '',
});

const submit = () => {
    form.patch(
        `/income/${props.income.id}`,
    );
};

const breadcrumbs = [
    {
        title: 'Ingresos',
        href: '/income',
    },
    {
        title:
            props.income
                .income_number,
        href:
            `/income/${props.income.id}/edit`,
    },
];
</script>

<template>
    <Head title="Editar ingreso" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1 class="text-2xl font-bold">
                    Editar ingreso
                </h1>

                <p
                    class="text-sm text-muted-foreground"
                >
                    {{
                        income.income_number
                    }}
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
                submit-label="Guardar cambios"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>