<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    ExternalLink,
    FileText,
    Printer,
    Share2,
} from '@lucide/vue';

const props = defineProps<{
    receipt: any;
}>();

const breadcrumbs = [
    {
        title: 'Recibos',
        href: '/receipts',
    },
    {
        title:
            props.receipt
                .receipt_number,

        href:
            `/receipts/${props.receipt.id}`,
    },
];

const money = (
    value: string | number,
) => {
    return `L ${Number(value).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
};

const paymentMethodLabel = (
    method: string,
) => {
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
</script>

<template>
    <Head
        :title="
            receipt.receipt_number
        "
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold"
                    >
                        {{
                            receipt.receipt_number
                        }}
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Recibo de venta
                    </p>
                </div>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <Link
                        href="/receipts"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <ArrowLeft
                            class="h-4 w-4"
                        />

                        Volver
                    </Link>

                    <Link
                        :href="`/receipts/${receipt.id}/pdf`"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4]"
                    >
                        <FileText
                            class="h-4 w-4"
                        />

                        Ver PDF
                    </Link>

                    <Link
                        :href="`/receipts/${receipt.id}/share`"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600"
                    >
                        <Share2
                            class="h-4 w-4"
                        />

                        Compartir PDF
                    </Link>

                    <Link
                        :href="`/receipts/${receipt.id}/print`"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-4 py-2.5 text-sm font-semibold text-white"
                    >
                        <Printer
                            class="h-4 w-4"
                        />

                        Imprimir
                    </Link>
                </div>
            </div>

            <div
                class="mx-auto w-full max-w-4xl rounded-2xl border bg-background p-8 shadow-sm"
            >
                <div
                    class="flex flex-col gap-6 border-b pb-8 md:flex-row md:items-start md:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase tracking-wider text-[#0fa7b4]"
                        >
                            ADN Publicidad
                        </p>

                        <h2
                            class="mt-2 text-3xl font-bold"
                        >
                            Recibo de venta
                        </h2>

                        <p
                            class="mt-2 text-sm text-muted-foreground"
                        >
                            Documento no fiscal
                        </p>
                    </div>

                    <div
                        class="md:text-right"
                    >
                        <p
                            class="text-xl font-bold text-[#e84657]"
                        >
                            {{
                                receipt.receipt_number
                            }}
                        </p>

                        <p
                            class="mt-2 text-sm"
                        >
                            Fecha:
                            {{
                                receipt.payment_date
                            }}
                        </p>
                    </div>
                </div>

                <div
                    class="grid gap-6 border-b py-8 md:grid-cols-2"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            Recibimos de
                        </p>

                        <p
                            class="mt-2 text-xl font-bold"
                        >
                            {{
                                receipt.client.name
                                ||
                                'Cliente'
                            }}
                        </p>

                        <div
                            class="mt-3 space-y-1 text-sm text-muted-foreground"
                        >
                            <p
                                v-if="
                                    receipt.client.code
                                "
                            >
                                Código:
                                {{
                                    receipt.client.code
                                }}
                            </p>

                            <p
                                v-if="
                                    receipt.client.identity_number
                                "
                            >
                                Identidad:
                                {{
                                    receipt.client
                                        .identity_number
                                }}
                            </p>

                            <p
                                v-if="
                                    receipt.client.rtn
                                "
                            >
                                RTN:
                                {{
                                    receipt.client.rtn
                                }}
                            </p>

                            <p
                                v-if="
                                    receipt.client.phone
                                "
                            >
                                Teléfono:
                                {{
                                    receipt.client.phone
                                }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            Por concepto de
                        </p>

                        <p
                            class="mt-2 text-lg font-semibold"
                        >
                            Pago correspondiente a
                            {{
                                receipt.sale
                                    .sale_number
                            }}
                        </p>

                        <Link
                            v-if="
                                receipt.sale.id
                            "
                            :href="`/sales/${receipt.sale.id}`"
                            class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-[#0fa7b4]"
                        >
                            Ver venta

                            <ExternalLink
                                class="h-4 w-4"
                            />
                        </Link>
                    </div>
                </div>

                <div
                    class="grid gap-6 border-b py-8 md:grid-cols-2"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            Forma de pago
                        </p>

                        <p
                            class="mt-2 text-lg font-semibold"
                        >
                            {{
                                paymentMethodLabel(
                                    receipt.payment_method,
                                )
                            }}
                        </p>

                        <p
                            v-if="
                                receipt.reference
                            "
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Referencia:
                            {{
                                receipt.reference
                            }}
                        </p>
                    </div>

                    <div
                        class="md:text-right"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            Monto recibido
                        </p>

                        <p
                            class="mt-2 text-4xl font-bold text-[#0fa7b4]"
                        >
                            {{
                                money(
                                    receipt.amount,
                                )
                            }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="
                        receipt.notes
                    "
                    class="border-b py-8"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        Observaciones
                    </p>

                    <p
                        class="mt-2 whitespace-pre-line text-sm"
                    >
                        {{
                            receipt.notes
                        }}
                    </p>
                </div>

                <div
                    class="flex flex-col gap-6 pt-8 md:flex-row md:items-end md:justify-between"
                >
                    <div
                        class="text-sm text-muted-foreground"
                    >
                        <p
                            v-if="
                                receipt.received_by
                            "
                        >
                            Recibido por:
                            {{
                                receipt.received_by
                            }}
                        </p>

                        <p>
                            ADN Publicidad
                        </p>
                    </div>

                    <div
                        class="w-full max-w-xs border-t pt-2 text-center text-sm"
                    >
                        Firma / sello
                    </div>
                </div>
            </div>

            <div
                class="mx-auto grid w-full max-w-4xl gap-3 sm:grid-cols-2"
            >
                <Link
                    :href="`/receipts/${receipt.id}/pdf`"
                    target="_blank"
                    class="flex items-center justify-center gap-2 rounded-xl border p-4 font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4]"
                >
                    <FileText
                        class="h-5 w-5"
                    />

                    Abrir PDF
                </Link>

                <Link
                    :href="`/receipts/${receipt.id}/share`"
                    class="flex items-center justify-center gap-2 rounded-xl bg-emerald-500 p-4 font-semibold text-white transition hover:bg-emerald-600"
                >
                    <Share2
                        class="h-5 w-5"
                    />

                    Compartir por WhatsApp
                </Link>
            </div>
        </div>
    </AppLayout>
</template>