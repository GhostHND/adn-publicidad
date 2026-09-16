<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3';
import {
    FileText,
    Pencil,
    Printer,
    Share2,
    ShoppingCart,
} from '@lucide/vue';

const props = defineProps<{
    quotation: any;
}>();

const breadcrumbs = [
    {
        title: 'Cotizaciones',
        href: '/quotations',
    },
    {
        title: props.quotation.quotation_number,
        href: `/quotations/${props.quotation.id}`,
    },
];

const money = (
    value: string | number,
): string => {
    return `L ${Number(value).toLocaleString(
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
    const labels: Record<string, string> = {
        draft: 'Borrador',
        sent: 'Enviada',
        approved: 'Aprobada',
        rejected: 'Rechazada',
        expired: 'Vencida',
    };

    return labels[status] ?? status;
};

const changeStatus = (
    event: Event,
): void => {
    const target =
        event.target as HTMLSelectElement;

    const status =
        target.value;

    router.patch(
        `/quotations/${props.quotation.id}/status`,
        {
            status,
        },
    );
};

const convertToSale = (): void => {
    const confirmed =
        window.confirm(
            '¿Deseas convertir esta cotización aprobada en una venta?',
        );

    if (!confirmed) {
        return;
    }

    router.post(
        `/quotations/${props.quotation.id}/convert-to-sale`,
    );
};

const printQuotation = (): void => {
    window.print();
};
</script>

<template>
    <Head
        :title="
            quotation.quotation_number
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
            <!-- ENCABEZADO -->

            <div
                class="flex flex-col gap-4 print:hidden md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold"
                    >
                        {{
                            quotation.quotation_number
                        }}
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Cotización para
                        {{ quotation.client }}
                    </p>
                </div>

                <!-- ACCIONES -->

                <div
                    class="flex flex-wrap gap-2"
                >
                    <!-- ESTADO -->

                    <select
                        :value="
                            quotation.status
                        "
                        @change="
                            changeStatus
                        "
                        class="rounded-lg border bg-background px-4 py-2.5 text-sm"
                    >
                        <option
                            value="draft"
                        >
                            Borrador
                        </option>

                        <option
                            value="sent"
                        >
                            Enviada
                        </option>

                        <option
                            value="approved"
                        >
                            Aprobada
                        </option>

                        <option
                            value="rejected"
                        >
                            Rechazada
                        </option>

                        <option
                            value="expired"
                        >
                            Vencida
                        </option>
                    </select>

                    <!-- PDF -->

                    <a
                        :href="`/quotations/${quotation.id}/pdf`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4]"
                    >
                        <FileText
                            class="h-4 w-4"
                        />

                        Ver PDF
                    </a>

                    <!-- COMPARTIR -->

                    <Link
                        :href="`/quotations/${quotation.id}/share`"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600"
                    >
                        <Share2
                            class="h-4 w-4"
                        />

                        Compartir PDF
                    </Link>

                    <!-- CONVERTIR EN VENTA -->

                    <button
                        v-if="
                            quotation.status ===
                            'approved'
                        "
                        type="button"
                        @click="
                            convertToSale
                        "
                        class="inline-flex items-center gap-2 rounded-lg bg-[#e84657] px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                    >
                        <ShoppingCart
                            class="h-4 w-4"
                        />

                        Convertir en venta
                    </button>

                    <!-- EDITAR -->

                    <Link
                        :href="`/quotations/${quotation.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4]"
                    >
                        <Pencil
                            class="h-4 w-4"
                        />

                        Editar
                    </Link>

                    <!-- IMPRIMIR -->

                    <button
                        type="button"
                        @click="
                            printQuotation
                        "
                        class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                    >
                        <Printer
                            class="h-4 w-4"
                        />

                        Imprimir
                    </button>
                </div>
            </div>

            <!-- DOCUMENTO -->

            <div
                class="rounded-xl border bg-background p-8 shadow-sm"
            >
                <!-- ENCABEZADO DOCUMENTO -->

                <div
                    class="mb-8 flex justify-between gap-8"
                >
                    <div>
                        <h2
                            class="text-2xl font-bold"
                        >
                            ADN Publicidad
                        </h2>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Cotización
                        </p>
                    </div>

                    <div
                        class="text-right"
                    >
                        <p
                            class="font-bold"
                        >
                            {{
                                quotation.quotation_number
                            }}
                        </p>

                        <p
                            class="text-sm"
                        >
                            Fecha:
                            {{
                                quotation.quotation_date
                            }}
                        </p>

                        <p
                            v-if="
                                quotation.valid_until
                            "
                            class="text-sm"
                        >
                            Válida hasta:
                            {{
                                quotation.valid_until
                            }}
                        </p>

                        <p
                            class="mt-1 text-sm font-semibold"
                        >
                            {{
                                statusLabel(
                                    quotation.status,
                                )
                            }}
                        </p>
                    </div>
                </div>

                <!-- CLIENTE -->

                <div
                    class="mb-8 rounded-lg bg-muted/40 p-4"
                >
                    <p
                        class="text-xs uppercase text-muted-foreground"
                    >
                        Cliente
                    </p>

                    <p
                        class="mt-1 text-lg font-semibold"
                    >
                        {{
                            quotation.client
                        }}
                    </p>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        {{
                            quotation.client_code
                        }}
                    </p>
                </div>

                <!-- ITEMS -->

                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full text-sm"
                    >
                        <thead
                            class="border-b"
                        >
                            <tr>
                                <th
                                    class="py-3 text-left"
                                >
                                    Descripción
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Cantidad
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Medida
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    item in quotation.items
                                "
                                :key="
                                    item.id
                                "
                                class="border-b"
                            >
                                <td
                                    class="py-4"
                                >
                                    <p
                                        class="font-semibold"
                                    >
                                        {{
                                            item.item_name
                                        }}
                                    </p>

                                    <p
                                        v-if="
                                            item.description
                                        "
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{
                                            item.description
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="py-4 text-right"
                                >
                                    {{
                                        Number(
                                            item.quantity,
                                        )
                                    }}
                                </td>

                                <td
                                    class="py-4 text-right"
                                >
                                    <span
                                        v-if="
                                            item.pricing_method ===
                                            'AREA'
                                        "
                                    >
                                        {{
                                            Number(
                                                item.width,
                                            )
                                        }}
                                        ×
                                        {{
                                            Number(
                                                item.height,
                                            )
                                        }}
                                        {{
                                            item.measurement_unit
                                        }}
                                    </span>

                                    <span
                                        v-else-if="
                                            item.pricing_method ===
                                            'LINEAR'
                                        "
                                    >
                                        {{
                                            Number(
                                                item.width,
                                            )
                                        }}
                                        {{
                                            item.measurement_unit
                                        }}
                                    </span>

                                    <span
                                        v-else
                                    >
                                        —
                                    </span>
                                </td>

                                <td
                                    class="py-4 text-right font-semibold"
                                >
                                    {{
                                        money(
                                            item.subtotal,
                                        )
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TOTALES -->

                <div
                    class="ml-auto mt-8 max-w-sm space-y-3"
                >
                    <div
                        class="flex justify-between"
                    >
                        <span>
                            Subtotal
                        </span>

                        <span>
                            {{
                                money(
                                    quotation.subtotal,
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="flex justify-between"
                    >
                        <span>
                            Descuento
                        </span>

                        <span>
                            {{
                                money(
                                    quotation.discount,
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="flex justify-between border-t pt-4 text-xl font-bold"
                    >
                        <span>
                            Total
                        </span>

                        <span
                            class="text-[#0fa7b4]"
                        >
                            {{
                                money(
                                    quotation.total,
                                )
                            }}
                        </span>
                    </div>
                </div>

                <!-- NOTAS -->

                <div
                    v-if="
                        quotation.notes
                    "
                    class="mt-8 border-t pt-6"
                >
                    <h3
                        class="font-semibold"
                    >
                        Notas
                    </h3>

                    <p
                        class="mt-2 whitespace-pre-line text-sm text-muted-foreground"
                    >
                        {{
                            quotation.notes
                        }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    aside,
    header,
    nav {
        display: none !important;
    }

    body {
        background: white !important;
    }
}
</style>