<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    CreditCard,
    ExternalLink,
    Landmark,
    ReceiptText,
} from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    account: any;
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

const needsFinancialAccount =
    computed(() => {
        return (
            paymentForm.payment_method
            !== 'cash'
        );
    });

const submitPayment = () => {
    if (
        paymentForm.payment_method
        === 'cash'
    ) {
        paymentForm.financial_account_id =
            '';
    }

    paymentForm.post(
        `/accounts-receivable/${props.account.id}/payments`,
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

const payFullBalance = () => {
    paymentForm.amount =
        Number(
            props.account.balance || 0,
        ).toFixed(2);
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

const breadcrumbs = [
    {
        title: 'Cuentas por cobrar',
        href: '/accounts-receivable',
    },
    {
        title:
            props.account.sale_number,
        href:
            `/accounts-receivable/${props.account.id}`,
    },
];

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
</script>

<template>
    <Head
        :title="
            account.sale_number
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
                    <h1
                        class="text-2xl font-bold"
                    >
                        {{
                            account.sale_number
                        }}
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Cuenta por cobrar de
                        {{
                            account.client.name
                        }}
                    </p>
                </div>

                <div
                    class="flex gap-2"
                >
                    <Link
                        href="/accounts-receivable"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <ArrowLeft
                            class="h-4 w-4"
                        />

                        Volver
                    </Link>

                    <Link
                        :href="`/sales/${account.id}`"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold text-[#0fa7b4]"
                    >
                        Ver venta

                        <ExternalLink
                            class="h-4 w-4"
                        />
                    </Link>
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
                        Total de venta
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold"
                    >
                        {{
                            money(
                                account.total,
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
                                account.paid_amount,
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
                        Saldo pendiente
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-[#e84657]"
                    >
                        {{
                            money(
                                account.balance,
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
                        Días transcurridos
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold"
                    >
                        {{
                            account.days_outstanding
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
                                account.client.name
                            }}
                        </p>

                        <p
                            v-if="
                                account.client.code
                            "
                            class="text-sm text-muted-foreground"
                        >
                            {{
                                account.client.code
                            }}
                        </p>

                        <p
                            v-if="
                                account.client.phone
                            "
                        >
                            Teléfono:
                            {{
                                account.client.phone
                            }}
                        </p>

                        <p
                            v-if="
                                account.client.email
                            "
                        >
                            Correo:
                            {{
                                account.client.email
                            }}
                        </p>

                        <p
                            v-if="
                                account.client.address
                            "
                        >
                            Dirección:
                            {{
                                account.client.address
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
                        Información de la venta
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
                                Fecha
                            </span>

                            <span>
                                {{
                                    account.sale_date
                                }}
                            </span>
                        </div>

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
                                        account.subtotal,
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
                                {{
                                    money(
                                        account.discount,
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
                                        account.total,
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
                                    Subtotal
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    item in account.items
                                "
                                :key="
                                    item.id
                                "
                                class="border-b last:border-b-0"
                            >
                                <td
                                    class="py-4 font-medium"
                                >
                                    {{
                                        item.item_name
                                    }}
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
            </div>

            <div
                class="grid gap-6 lg:grid-cols-2"
            >
                <div
                    class="rounded-xl border bg-background p-6 shadow-sm"
                >
                    <h2
                        class="mb-5 text-lg font-semibold"
                    >
                        Historial de pagos
                    </h2>

                    <div
                        v-if="
                            account.payments
                                .length === 0
                        "
                        class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground"
                    >
                        Esta cuenta todavía no
                        tiene pagos.
                    </div>

                    <div
                        v-else
                        class="space-y-3"
                    >
                        <div
                            v-for="
                                payment in account.payments
                            "
                            :key="
                                payment.id
                            "
                            class="rounded-lg border p-4"
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div>
                                    <Link
                                        :href="`/receipts/${payment.id}`"
                                        class="inline-flex items-center gap-2 font-semibold text-[#0fa7b4]"
                                    >
                                        <ReceiptText
                                            class="h-4 w-4"
                                        />

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
                                    class="font-bold text-emerald-600"
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
                            account.balance,
                        ) > 0
                    "
                    class="rounded-xl border bg-background p-6 shadow-sm"
                >
                    <h2
                        class="mb-1 text-lg font-semibold"
                    >
                        Registrar abono
                    </h2>

                    <p
                        class="mb-5 text-sm text-muted-foreground"
                    >
                        Saldo actual:
                        {{
                            money(
                                account.balance,
                            )
                        }}
                    </p>

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
                                    Pagar saldo
                                </button>
                            </div>

                            <input
                                v-model="
                                    paymentForm.amount
                                "
                                type="number"
                                min="0.01"
                                :max="
                                    account.balance
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
                                Método de pago *
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
                                needsFinancialAccount
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
                                        bank in financialAccounts
                                    "
                                    :key="
                                        bank.id
                                    "
                                    :value="
                                        bank.id
                                    "
                                >
                                    {{
                                        bank.name
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
                                El abono afectará la
                                caja física abierta.
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
                                El abono será
                                registrado en el
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
                                placeholder="Transferencia, comprobante..."
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
                            class="w-full rounded-lg bg-[#0fa7b4] px-5 py-3 font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                        >
                            {{
                                paymentForm.processing
                                    ? 'Registrando...'
                                    : 'Registrar abono'
                            }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>