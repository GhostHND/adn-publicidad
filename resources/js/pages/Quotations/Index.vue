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
    Pencil,
    Plus,
    Send,
    Share2,
} from '@lucide/vue';

type Quotation = {
    id: number;
    quotation_number: string;
    client: string;
    quotation_date: string | null;
    valid_until: string | null;
    status: string;
    subtotal: number | string;
    discount: number | string;
    total: number | string;
};

defineProps<{
    quotations: Quotation[];
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Cotizaciones',
        href: '/quotations',
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
        draft: 'Borrador',
        sent: 'Enviada',
        approved: 'Aprobada',
        rejected: 'Rechazada',
        expired: 'Vencida',
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
    const tones:
        Record<
            string,
            | 'success'
            | 'danger'
            | 'warning'
            | 'info'
            | 'neutral'
        > = {
        draft: 'neutral',
        sent: 'info',
        approved: 'success',
        rejected: 'danger',
        expired: 'warning',
    };

    return tones[status]
        ?? 'neutral';
};

const fieldsFor = (
    quotation: Quotation,
): MobileRecordField[] => {
    return [
        {
            label: 'Cliente',
            value:
                quotation.client,
            wide: true,
        },
        {
            label: 'Fecha',
            value:
                quotation.quotation_date
                ?? '—',
        },
        {
            label: 'Válida hasta',
            value:
                quotation.valid_until
                ?? '—',
        },
        {
            label: 'Subtotal',
            value:
                money(
                    quotation.subtotal,
                ),
        },
        {
            label: 'Descuento',
            value:
                money(
                    quotation.discount,
                ),
        },
        {
            label: 'Total',
            value:
                money(
                    quotation.total,
                ),
            wide: true,
        },
    ];
};
</script>

<template>
    <Head title="Cotizaciones" />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <section
                class="adn-enter relative overflow-hidden rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <FileText
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Gestión comercial
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Cotizaciones
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Propuestas comerciales de ADN Publicidad.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/quotations/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus class="h-4 w-4" />
                        Nueva cotización
                    </Link>
                </div>
            </section>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        quotation in quotations
                    "
                    :key="quotation.id"
                    :title="
                        `${quotation.quotation_number} · ${quotation.client}`
                    "
                    :code="
                        quotation.quotation_number
                    "
                    :subtitle="
                        quotation.client
                    "
                    :status="
                        statusLabel(
                            quotation.status,
                        )
                    "
                    :status-tone="
                        tone(
                            quotation.status,
                        )
                    "
                    :fields="
                        fieldsFor(
                            quotation,
                        )
                    "
                    :expanded="
                        isExpanded(
                            quotation.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            quotation.id,
                        )
                    "
                >
                    <template #actions>
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/quotations/${quotation.id}`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white"
                            >
                                <Eye class="h-4 w-4" />
                                Ver
                            </Link>

                            <Link
                                :href="
                                    `/quotations/${quotation.id}/edit`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <Pencil class="h-4 w-4" />
                                Editar
                            </Link>

                            <Link
                                :href="
                                    `/quotations/${quotation.id}/pdf`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <FileText class="h-4 w-4" />
                                PDF
                            </Link>

                            <Link
                                :href="
                                    `/quotations/${quotation.id}/share`
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

            <!-- ESCRITORIO -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-5 py-4 text-left">
                                    Cotización
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Cliente
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Fecha
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Válida hasta
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Estado
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Total
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
                                    quotation in quotations
                                "
                                :key="quotation.id"
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        quotation.quotation_number
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{ quotation.client }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        quotation.quotation_date
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        quotation.valid_until
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        statusLabel(
                                            quotation.status,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-black"
                                >
                                    {{
                                        money(
                                            quotation.total,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="
                                                `/quotations/${quotation.id}`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>

                                        <Link
                                            :href="
                                                `/quotations/${quotation.id}/edit`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>

                                        <Link
                                            :href="
                                                `/quotations/${quotation.id}/share`
                                            "
                                            class="rounded-xl border border-[#e84657]/20 p-2.5 text-[#e84657]"
                                        >
                                            <Send class="h-4 w-4" />
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