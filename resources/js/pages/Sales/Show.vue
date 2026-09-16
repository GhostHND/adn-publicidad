<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    CreditCard,
    Landmark,
    ReceiptText,
    WalletCards,
} from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    sale: any;
    financialAccounts: any[];
}>();

const now = new Date();
const offset = now.getTimezoneOffset();

const today = new Date(
    now.getTime() - offset * 60000,
)
    .toISOString()
    .slice(0, 10);

const paymentForm = useForm({
    payment_date: today,
    amount: '',
    payment_method: 'cash',
    financial_account_id: '',
    reference: '',
    notes: '',
});

const breadcrumbs = [
    {
        title: 'Ventas',
        href: '/sales',
    },
    {
        title: props.sale.sale_number,
        href: `/sales/${props.sale.id}`,
    },
];

const needsAccount = computed(() => {
    return (
        paymentForm.payment_method
        !== 'cash'
    );
});

const money = (
    value: string | number,
) =>
    `L ${Number(value || 0).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;

const statusLabel = (
    status: string,
) => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        partial: 'Pago parcial',
        paid: 'Pagada',
        cancelled: 'Cancelada',
    };

    return labels[status] ?? status;
};

const statusClass = (
    status: string,
) => {
    const classes: Record<string, string> = {
        pending:
            'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',

        partial:
            'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',

        paid:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',

        cancelled:
            'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    };

    return (
        classes[status]
        ?? 'bg-muted'
    );
};

const paymentMethodLabel = (
    method: string,
) => {
    const labels: Record<string, string> = {
        cash: 'Efectivo',
        transfer: 'Transferencia',
        card: 'Tarjeta',
        other: 'Otro',
    };

    return labels[method] ?? method;
};

const onPaymentMethodChange = () => {
    if (
        paymentForm.payment_method
        === 'cash'
    ) {
        paymentForm.financial_account_id =
            '';
    }
};

const payFullBalance = () => {
    paymentForm.amount =
        Number(
            props.sale.balance || 0,
        ).toFixed(2);
};

const submitPayment = () => {
    if (
        paymentForm.payment_method
        === 'cash'
    ) {
        paymentForm.financial_account_id =
            '';
    }

    paymentForm.post(
        `/sales/${props.sale.id}/payments`,
        {
            preserveScroll: true,

            onSuccess: () => {
                paymentForm.reset(
                    'amount',
                    'reference',
                    'notes',
                    'financial_account_id',
                );

                paymentForm.payment_method =
                    'cash';
            },
        },
    );
};

const cancelSale = () => {
    const confirmed = window.confirm(
        '¿Deseas cancelar esta venta?',
    );

    if (!confirmed) {
        return;
    }

    router.patch(
        `/sales/${props.sale.id}/cancel`,
        {},
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head
        :title="
            sale.sale_number
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <div
                        class="flex flex-wrap items-center gap-3"
                    >
                        <h1
                            class="text-2xl font-bold"
                        >
                            {{
                                sale.sale_number
                            }}
                        </h1>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="
                                statusClass(
                                    sale.status,
                                )
                            "
                        >
                            {{
                                statusLabel(
                                    sale.status,
                                )
                            }}
                        </span>
                    </div>

                    <p
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        Venta del
                        {{ sale.sale_date }}
                    </p>
                </div>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <Link
                        href="/sales"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <ArrowLeft
                            class="h-4 w-4"
                        />

                        Volver
                    </Link>

                    <button
                        v-if="
                            sale.status ===
                                'pending'
                            &&
                            Number(
                                sale.paid_amount,
                            ) === 0
                        "
                        type="button"
                        @click="
                            cancelSale
                        "
                        class="rounded-lg border border-[#e84657]/30 px-4 py-2.5 text-sm font-semibold text-[#e84657]"
                    >
                        Cancelar venta
                    </button>
                </div>
            </div>

            <div
                class="grid gap-4 md:grid-cols-4"
            >
                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Total
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold"
                    >
                        {{
                            money(
                                sale.total,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Recibido
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-emerald-600"
                    >
                        {{
                            money(
                                sale.paid_amount,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Saldo
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-[#e84657]"
                    >
                        {{
                            money(
                                sale.balance,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5 shadow-sm"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Estado
                    </p>

                    <p
                        class="mt-2 text-lg font-bold"
                    >
                        {{
                            statusLabel(
                                sale.status,
                            )
                        }}
                    </p>
                </div>
            </div>

            <div
                class="grid gap-6 lg:grid-cols-2"
            >
                <div
                    class="rounded-xl border bg-background p-6 shadow-sm"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        Cliente
                    </h2>

                    <div
                        class="mt-5 space-y-2"
                    >
                        <p
                            class="text-lg font-bold"
                        >
                            {{
                                sale.client.name
                            }}
                        </p>

                        <p
                            v-if="
                                sale.client.code
                            "
                            class="text-sm text-muted-foreground"
                        >
                            {{
                                sale.client.code
                            }}
                        </p>

                        <p
                            v-if="
                                sale.client.phone
                            "
                        >
                            Teléfono:
                            {{
                                sale.client.phone
                            }}
                        </p>

                        <p
                            v-if="
                                sale.client.email
                            "
                        >
                            Correo:
                            {{
                                sale.client.email
                            }}
                        </p>

                        <p
                            v-if="
                                sale.client.address
                            "
                        >
                            Dirección:
                            {{
                                sale.client.address
                            }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border bg-background p-6 shadow-sm"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        Resumen
                    </h2>

                    <div
                        class="mt-5 space-y-3"
                    >
                        <div
                            class="flex justify-between"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Subtotal
                            </span>

                            <span>
                                {{
                                    money(
                                        sale.subtotal,
                                    )
                                }}
                            </span>
                        </div>

                        <div
                            class="flex justify-between"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Descuento
                            </span>

                            <span>
                                -
                                {{
                                    money(
                                        sale.discount,
                                    )
                                }}
                            </span>
                        </div>

                        <div
                            class="flex justify-between border-t pt-3 font-bold"
                        >
                            <span>
                                Total
                            </span>

                            <span>
                                {{
                                    money(
                                        sale.total,
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border bg-background p-6 shadow-sm"
            >
                <h2
                    class="mb-5 text-lg font-semibold"
                >
                    Productos y servicios
                </h2>

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
                                    Precio
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Subtotal
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    item in sale.items
                                "
                                :key="
                                    item.id
                                "
                                class="border-b last:border-b-0"
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
                                    {{
                                        money(
                                            item.unit_price,
                                        )
                                    }}
                                </td>

                                <td
                                    class="py-4 text-right font-bold"
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
            </div>

            <div
                class="grid gap-6 xl:grid-cols-2"
            >
                <div
                    class="rounded-xl border bg-background p-6 shadow-sm"
                >
                    <div
                        class="mb-5 flex items-center gap-3"
                    >
                        <ReceiptText
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <h2
                            class="text-lg font-semibold"
                        >
                            Pagos registrados
                        </h2>
                    </div>

                    <div
                        v-if="
                            sale.payments
                                .length === 0
                        "
                        class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
                    >
                        Esta venta todavía no
                        tiene pagos.
                    </div>

                    <div
                        v-else
                        class="space-y-3"
                    >
                        <div
                            v-for="
                                payment in sale.payments
                            "
                            :key="
                                payment.id
                            "
                            class="rounded-xl border p-4"
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div>
                                    <Link
                                        :href="`/receipts/${payment.id}`"
                                        class="font-semibold text-[#0fa7b4]"
                                    >
                                        {{
                                            payment.receipt_number
                                        }}
                                    </Link>

                                    <p
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        {{
                                            payment.payment_date
                                        }}
                                        ·
                                        {{
                                            paymentMethodLabel(
                                                payment.payment_method,
                                            )
                                        }}
                                    </p>
                                </div>

                                <p
                                    class="text-lg font-bold text-emerald-600"
                                >
                                    {{
                                        money(
                                            payment.amount,
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                v-if="
                                    payment.financial_account
                                "
                                class="mt-3 flex items-center gap-2 rounded-lg bg-muted/50 px-3 py-2 text-sm"
                            >
                                <Landmark
                                    class="h-4 w-4 text-[#0fa7b4]"
                                />

                                Cuenta:
                                <strong>
                                    {{
                                        payment.financial_account
                                    }}
                                </strong>
                            </div>

                            <p
                                v-if="
                                    payment.reference
                                "
                                class="mt-2 text-sm"
                            >
                                Referencia:
                                {{
                                    payment.reference
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        Number(
                            sale.balance,
                        ) > 0
                        &&
                        sale.status !==
                            'cancelled'
                    "
                    class="rounded-xl border bg-background p-6 shadow-sm"
                >
                    <div
                        class="mb-5 flex items-center gap-3"
                    >
                        <WalletCards
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <div>
                            <h2
                                class="text-lg font-semibold"
                            >
                                Registrar pago
                            </h2>

                            <p
                                class="text-sm text-muted-foreground"
                            >
                                Saldo:
                                {{
                                    money(
                                        sale.balance,
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <form
                        @submit.prevent="
                            submitPayment
                        "
                        class="space-y-4"
                    >
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Fecha *
                            </label>

                            <input
                                v-model="
                                    paymentForm.payment_date
                                "
                                type="date"
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />
                        </div>

                        <div>
                            <div
                                class="mb-2 flex items-center justify-between"
                            >
                                <label
                                    class="text-sm font-medium"
                                >
                                    Monto *
                                </label>

                                <button
                                    type="button"
                                    @click="
                                        payFullBalance
                                    "
                                    class="text-xs font-semibold text-[#0fa7b4]"
                                >
                                    Pagar saldo completo
                                </button>
                            </div>

                            <input
                                v-model="
                                    paymentForm.amount
                                "
                                type="number"
                                min="0.01"
                                :max="
                                    sale.balance
                                "
                                step="0.01"
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />

                            <p
                                v-if="
                                    paymentForm
                                        .errors
                                        .amount
                                "
                                class="mt-1 text-sm text-red-500"
                            >
                                {{
                                    paymentForm
                                        .errors
                                        .amount
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Método *
                            </label>

                            <select
                                v-model="
                                    paymentForm.payment_method
                                "
                                @change="
                                    onPaymentMethodChange
                                "
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            >
                                <option
                                    value="cash"
                                >
                                    Efectivo
                                </option>

                                <option
                                    value="transfer"
                                >
                                    Transferencia
                                </option>

                                <option
                                    value="card"
                                >
                                    Tarjeta
                                </option>

                                <option
                                    value="other"
                                >
                                    Otro
                                </option>
                            </select>
                        </div>

                        <div
                            v-if="
                                needsAccount
                            "
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Banco / cuenta *
                            </label>

                            <select
                                v-model="
                                    paymentForm.financial_account_id
                                "
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            >
                                <option
                                    value=""
                                >
                                    Seleccionar cuenta
                                </option>

                                <option
                                    v-for="
                                        account in financialAccounts
                                    "
                                    :key="
                                        account.id
                                    "
                                    :value="
                                        account.id
                                    "
                                >
                                    {{
                                        account.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    paymentForm
                                        .errors
                                        .financial_account_id
                                "
                                class="mt-1 text-sm text-red-500"
                            >
                                {{
                                    paymentForm
                                        .errors
                                        .financial_account_id
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                paymentForm.payment_method ===
                                'cash'
                            "
                            class="flex gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30"
                        >
                            <Banknote
                                class="h-5 w-5 shrink-0 text-emerald-600"
                            />

                            <p
                                class="text-sm"
                            >
                                Este pago afectará
                                la caja física.
                            </p>
                        </div>

                        <div
                            v-else-if="
                                paymentForm.payment_method ===
                                'transfer'
                            "
                            class="flex gap-3 rounded-lg border p-4"
                        >
                            <Landmark
                                class="h-5 w-5 shrink-0 text-[#0fa7b4]"
                            />

                            <p
                                class="text-sm"
                            >
                                El dinero se
                                registrará en el
                                banco seleccionado.
                            </p>
                        </div>

                        <div
                            v-else-if="
                                paymentForm.payment_method ===
                                'card'
                            "
                            class="flex gap-3 rounded-lg border p-4"
                        >
                            <CreditCard
                                class="h-5 w-5 shrink-0 text-[#0fa7b4]"
                            />

                            <p
                                class="text-sm"
                            >
                                El cobro se asociará
                                a la cuenta
                                seleccionada.
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Referencia
                            </label>

                            <input
                                v-model="
                                    paymentForm.reference
                                "
                                type="text"
                                placeholder="Transferencia, autorización..."
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Nota
                            </label>

                            <textarea
                                v-model="
                                    paymentForm.notes
                                "
                                rows="3"
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="
                                paymentForm.processing
                            "
                            class="w-full rounded-lg bg-[#0fa7b4] px-5 py-3 font-semibold text-white disabled:opacity-50"
                        >
                            {{
                                paymentForm.processing
                                    ? 'Registrando...'
                                    : 'Registrar pago'
                            }}
                        </button>
                    </form>
                </div>
            </div>

            <div
                v-if="
                    sale.notes
                "
                class="rounded-xl border bg-background p-6"
            >
                <h2
                    class="font-semibold"
                >
                    Notas
                </h2>

                <p
                    class="mt-3 whitespace-pre-line text-sm text-muted-foreground"
                >
                    {{ sale.notes }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>