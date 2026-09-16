<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';
import { useMobileAccordion } from '@/composables/useMobileAccordion';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    Eye,
    HandCoins,
} from '@lucide/vue';
import { computed } from 'vue';

type Account = {
    id: number;
    sale_number: string;
    client: string;
    client_code: string | null;
    sale_date: string | null;
    status: string;
    total: string | number;
    paid_amount: string | number;
    balance: string | number;
    days_outstanding: number;
};

const props = defineProps<{
    accounts: Account[];
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title:
            'Cuentas por cobrar',
        href:
            '/accounts-receivable',
    },
];

const money = (
    value: string | number,
): string => {
    return `L ${Number(value)
        .toLocaleString(
            'es-HN',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            },
        )}`;
};

const totalPending =
    computed(() => {
        return props.accounts
            .reduce(
                (
                    total,
                    account,
                ) =>
                    total +
                    Number(
                        account.balance,
                    ),
                0,
            );
    });

const fieldsFor = (
    account: Account,
): MobileRecordField[] => {
    return [
        {
            label: 'Cliente',
            value:
                account.client,
            wide: true,
        },
        {
            label: 'Fecha',
            value:
                account.sale_date
                ?? '—',
        },
        {
            label: 'Días pendiente',
            value:
                account.days_outstanding,
        },
        {
            label: 'Venta total',
            value:
                money(
                    account.total,
                ),
        },
        {
            label: 'Pagado',
            value:
                money(
                    account.paid_amount,
                ),
        },
        {
            label:
                'Saldo por cobrar',
            value:
                money(
                    account.balance,
                ),
            wide: true,
        },
    ];
};
</script>

<template>
    <Head
        title="Cuentas por cobrar"
    />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <section
                class="adn-enter rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="flex items-start gap-4"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-400"
                    >
                        <HandCoins
                            class="h-5 w-5"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-400"
                        >
                            Cobranza
                        </p>

                        <h1
                            class="mt-1 text-2xl font-black sm:text-3xl"
                        >
                            Cuentas por cobrar
                        </h1>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Saldos pendientes de clientes.
                        </p>
                    </div>
                </div>
            </section>

            <div
                class="grid grid-cols-2 gap-3 md:max-w-md"
            >
                <div
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wide text-white/30"
                    >
                        Pendientes
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ accounts.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-amber-500/15 bg-amber-500/[0.05] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wide text-amber-400"
                    >
                        Por cobrar
                    </p>

                    <p
                        class="mt-1 text-lg font-black text-amber-400"
                    >
                        {{
                            money(
                                totalPending,
                            )
                        }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        account in accounts
                    "
                    :key="account.id"
                    :title="
                        `${account.sale_number} · ${account.client}`
                    "
                    :code="
                        account.sale_number
                    "
                    :subtitle="
                        account.client
                    "
                    status="Pendiente"
                    status-tone="warning"
                    :fields="
                        fieldsFor(account)
                    "
                    :expanded="
                        isExpanded(
                            account.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            account.id,
                        )
                    "
                >
                    <template #actions>
                        <Link
                            :href="
                                `/accounts-receivable/${account.id}`
                            "
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-amber-500 text-sm font-black text-black"
                        >
                            <Eye class="h-4 w-4" />

                            Ver / Registrar pago
                        </Link>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- DESKTOP -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-5 py-4 text-left">
                                    Venta
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Cliente
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Fecha
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Total
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Pagado
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Saldo
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Días
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    account in accounts
                                "
                                :key="account.id"
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        account.sale_number
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{ account.client }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        account.sale_date
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    {{
                                        money(
                                            account.total,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right text-emerald-400"
                                >
                                    {{
                                        money(
                                            account.paid_amount,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-black text-amber-400"
                                >
                                    {{
                                        money(
                                            account.balance,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    {{
                                        account.days_outstanding
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    <Link
                                        :href="
                                            `/accounts-receivable/${account.id}`
                                        "
                                        class="inline-flex items-center gap-2 rounded-xl border border-amber-500/20 px-4 py-2 font-bold text-amber-400"
                                    >
                                        <Eye class="h-4 w-4" />
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>