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
    FileText,
    Printer,
    ReceiptText,
    Share2,
} from '@lucide/vue';

type Receipt = {
    id: number;
    receipt_number: string;
    sale_id: number;
    sale_number: string | null;
    client: string;
    payment_date: string | null;
    amount: string | number;
    payment_method: string;
    reference: string | null;
};

defineProps<{
    receipts: Receipt[];
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Recibos',
        href: '/receipts',
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

const methodLabel = (
    method: string,
): string => {
    const labels:
        Record<string, string> = {
        cash: 'Efectivo',
        transfer: 'Transferencia',
        card: 'Tarjeta',
        other: 'Otro',
    };

    return labels[method]
        ?? method;
};

const fieldsFor = (
    receipt: Receipt,
): MobileRecordField[] => {
    return [
        {
            label: 'Cliente',
            value:
                receipt.client,
            wide: true,
        },
        {
            label: 'Venta',
            value:
                receipt.sale_number
                ?? '—',
        },
        {
            label: 'Fecha',
            value:
                receipt.payment_date
                ?? '—',
        },
        {
            label: 'Método',
            value:
                methodLabel(
                    receipt.payment_method,
                ),
        },
        {
            label: 'Referencia',
            value:
                receipt.reference
                ?? '—',
        },
        {
            label: 'Monto recibido',
            value:
                money(
                    receipt.amount,
                ),
            wide: true,
        },
    ];
};
</script>

<template>
    <Head title="Recibos" />

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
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400"
                    >
                        <ReceiptText
                            class="h-5 w-5"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-400"
                        >
                            Documentos
                        </p>

                        <h1
                            class="mt-1 text-2xl font-black sm:text-3xl"
                        >
                            Recibos
                        </h1>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Comprobantes de pagos recibidos.
                        </p>
                    </div>
                </div>
            </section>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        receipt in receipts
                    "
                    :key="receipt.id"
                    :title="
                        `${receipt.receipt_number} · ${receipt.client}`
                    "
                    :code="
                        receipt.receipt_number
                    "
                    :subtitle="
                        receipt.client
                    "
                    status="Recibido"
                    status-tone="success"
                    :fields="
                        fieldsFor(receipt)
                    "
                    :expanded="
                        isExpanded(
                            receipt.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            receipt.id,
                        )
                    "
                >
                    <template #actions>
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/receipts/${receipt.id}`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white"
                            >
                                <Eye class="h-4 w-4" />
                                Ver
                            </Link>

                            <Link
                                :href="
                                    `/receipts/${receipt.id}/print`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <Printer class="h-4 w-4" />
                                Imprimir
                            </Link>

                            <Link
                                :href="
                                    `/receipts/${receipt.id}/pdf`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <FileText class="h-4 w-4" />
                                PDF
                            </Link>

                            <Link
                                :href="
                                    `/receipts/${receipt.id}/share`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#e84657]/20 bg-[#e84657]/5 text-xs font-bold text-[#f06472]"
                            >
                                <Share2 class="h-4 w-4" />
                                Compartir
                            </Link>
                        </div>
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
                                    Recibo
                                </th>
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
                                    Método
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Monto
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    receipt in receipts
                                "
                                :key="receipt.id"
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        receipt.receipt_number
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        receipt.sale_number
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{ receipt.client }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        receipt.payment_date
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        methodLabel(
                                            receipt.payment_method,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-black text-emerald-400"
                                >
                                    {{
                                        money(
                                            receipt.amount,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="
                                                `/receipts/${receipt.id}`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>

                                        <Link
                                            :href="
                                                `/receipts/${receipt.id}/share`
                                            "
                                            class="rounded-xl border border-[#e84657]/20 p-2.5 text-[#e84657]"
                                        >
                                            <Share2 class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>