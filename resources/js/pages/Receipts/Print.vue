<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Printer,
} from '@lucide/vue';

const props = defineProps<{
    receipt: any;
}>();

const money = (value: string | number) => {
    return `L ${Number(value).toLocaleString('es-HN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
};

const paymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        cash: 'Efectivo',
        transfer: 'Transferencia',
        card: 'Tarjeta',
        other: 'Otro',
    };

    return labels[method] ?? method;
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head :title="receipt.receipt_number" />

    <div class="min-h-screen bg-neutral-100 p-6 print:bg-white print:p-0">
        <div
            class="mx-auto mb-5 flex max-w-3xl justify-between print:hidden"
        >
            <Link
                :href="`/receipts/${receipt.id}`"
                class="inline-flex items-center gap-2 rounded-lg border bg-white px-4 py-2.5 text-sm font-semibold"
            >
                <ArrowLeft class="h-4 w-4" />

                Volver
            </Link>

            <button
                type="button"
                @click="printReceipt"
                class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-5 py-2.5 text-sm font-semibold text-white"
            >
                <Printer class="h-4 w-4" />

                Imprimir recibo
            </button>
        </div>

        <main
            class="mx-auto max-w-3xl bg-white p-10 text-neutral-900 shadow-lg print:max-w-none print:shadow-none"
        >
            <header
                class="flex items-start justify-between gap-8 border-b-2 border-neutral-900 pb-6"
            >
                <div>
                    <p
                        class="text-sm font-bold uppercase tracking-[0.18em]"
                    >
                        ADN Publicidad
                    </p>

                    <h1
                        class="mt-2 text-3xl font-bold"
                    >
                        Recibo de venta
                    </h1>

                    <p
                        class="mt-1 text-xs uppercase tracking-wide text-neutral-500"
                    >
                        Documento no fiscal
                    </p>
                </div>

                <div class="text-right">
                    <p
                        class="text-xl font-bold"
                    >
                        {{
                            receipt.receipt_number
                        }}
                    </p>

                    <p class="mt-2 text-sm">
                        Fecha:
                        {{
                            receipt.payment_date
                        }}
                    </p>
                </div>
            </header>

            <section
                class="grid grid-cols-2 gap-10 border-b py-7"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase text-neutral-500"
                    >
                        Recibimos de
                    </p>

                    <p
                        class="mt-2 text-xl font-bold"
                    >
                        {{
                            receipt.client.name ||
                            'Cliente'
                        }}
                    </p>

                    <div
                        class="mt-3 space-y-1 text-sm"
                    >
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
                            v-if="receipt.client.rtn"
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
                        class="text-xs font-bold uppercase text-neutral-500"
                    >
                        Concepto
                    </p>

                    <p
                        class="mt-2 font-semibold"
                    >
                        Pago correspondiente a
                        {{
                            receipt.sale.sale_number
                        }}
                    </p>
                </div>
            </section>

            <section
                class="grid grid-cols-2 gap-10 border-b py-7"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase text-neutral-500"
                    >
                        Método de pago
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
                        v-if="receipt.reference"
                        class="mt-1 text-sm"
                    >
                        Referencia:
                        {{
                            receipt.reference
                        }}
                    </p>
                </div>

                <div class="text-right">
                    <p
                        class="text-xs font-bold uppercase text-neutral-500"
                    >
                        Monto recibido
                    </p>

                    <p
                        class="mt-2 text-4xl font-bold"
                    >
                        {{
                            money(
                                receipt.amount,
                            )
                        }}
                    </p>
                </div>
            </section>

            <section
                v-if="receipt.notes"
                class="border-b py-7"
            >
                <p
                    class="text-xs font-bold uppercase text-neutral-500"
                >
                    Observaciones
                </p>

                <p
                    class="mt-2 whitespace-pre-line text-sm"
                >
                    {{ receipt.notes }}
                </p>
            </section>

            <footer
                class="mt-14 grid grid-cols-2 gap-16"
            >
                <div>
                    <p class="text-sm">
                        ADN Publicidad
                    </p>

                    <p
                        v-if="receipt.received_by"
                        class="mt-1 text-xs text-neutral-500"
                    >
                        Recibido por:
                        {{
                            receipt.received_by
                        }}
                    </p>
                </div>

                <div
                    class="border-t border-neutral-900 pt-2 text-center text-sm"
                >
                    Firma / sello
                </div>
            </footer>
        </main>
    </div>
</template>

<style>
@media print {
    @page {
        size: auto;
        margin: 12mm;
    }

    html,
    body {
        background: white !important;
    }
}
</style>