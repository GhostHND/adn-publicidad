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
    Plus,
    ShoppingCart,
} from '@lucide/vue';

type Sale = {
    id: number;
    sale_number: string;
    sale_date: string | null;
    client: string;
    client_code: string | null;
    status: string;
    subtotal: string | number;
    discount: string | number;
    total: string | number;
    paid_amount: string | number;
    balance: string | number;
};

defineProps<{
    sales: Sale[];
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Ventas',
        href: '/sales',
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

const statusLabel = (
    status: string,
): string => {
    const labels:
        Record<string, string> = {
        pending: 'Pendiente',
        partial: 'Pago parcial',
        paid: 'Pagada',
        cancelled: 'Cancelada',
    };

    return labels[status]
        ?? status;
};

const tone = (
    status: string,
):
    | 'success'
    | 'danger'
    | 'warning'
    | 'info'
    | 'neutral' => {
    if (status === 'paid') {
        return 'success';
    }

    if (
        status === 'cancelled'
    ) {
        return 'danger';
    }

    if (
        status === 'partial'
    ) {
        return 'warning';
    }

    return 'info';
};

const fieldsFor = (
    sale: Sale,
): MobileRecordField[] => {
    return [
        {
            label: 'Cliente',
            value: sale.client,
            wide: true,
        },
        {
            label: 'Fecha',
            value:
                sale.sale_date
                ?? '—',
        },
        {
            label: 'Estado',
            value:
                statusLabel(
                    sale.status,
                ),
        },
        {
            label: 'Total',
            value:
                money(sale.total),
        },
        {
            label: 'Pagado',
            value:
                money(
                    sale.paid_amount,
                ),
        },
        {
            label: 'Saldo pendiente',
            value:
                money(
                    sale.balance,
                ),
            wide: true,
        },
    ];
};
</script>

<template>
    <Head title="Ventas" />

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
                    class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#e84657]/10 text-[#f06472]"
                        >
                            <ShoppingCart
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#e84657]"
                            >
                                Comercial
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Ventas
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Ventas, pagos y saldos de clientes.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/sales/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus class="h-4 w-4" />
                        Nueva venta
                    </Link>
                </div>
            </section>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="sale in sales"
                    :key="sale.id"
                    :title="
                        `${sale.sale_number} · ${sale.client}`
                    "
                    :code="
                        sale.sale_number
                    "
                    :subtitle="
                        sale.client
                    "
                    :status="
                        statusLabel(
                            sale.status,
                        )
                    "
                    :status-tone="
                        tone(sale.status)
                    "
                    :fields="
                        fieldsFor(sale)
                    "
                    :expanded="
                        isExpanded(sale.id)
                    "
                    @toggle="
                        toggleCard(sale.id)
                    "
                >
                    <template #actions>
                        <Link
                            :href="
                                `/sales/${sale.id}`
                            "
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white"
                        >
                            <Eye class="h-4 w-4" />
                            Ver venta y pagos
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
                                <th class="px-5 py-4 text-left">
                                    Estado
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
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="sale in sales"
                                :key="sale.id"
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{ sale.sale_number }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{ sale.client }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        sale.sale_date
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        statusLabel(
                                            sale.status,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-bold"
                                >
                                    {{ money(sale.total) }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right text-emerald-400"
                                >
                                    {{
                                        money(
                                            sale.paid_amount,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-black text-amber-400"
                                >
                                    {{
                                        money(
                                            sale.balance,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    <Link
                                        :href="
                                            `/sales/${sale.id}`
                                        "
                                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-2 font-bold"
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