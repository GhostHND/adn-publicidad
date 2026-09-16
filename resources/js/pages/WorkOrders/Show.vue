<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
    useForm,
} from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Banknote,
    Boxes,
    CheckCircle2,
    CirclePause,
    ClipboardCheck,
    CreditCard,
    Eye,
    Landmark,
    PackageCheck,
    Pencil,
    Play,
    ReceiptText,
    RotateCcw,
    ShoppingCart,
    Warehouse,
    XCircle,
} from '@lucide/vue';
import { computed, watch } from 'vue';

const props = defineProps<{
    order: any;
    allowedStatuses: string[];
    financialAccounts: any[];
}>();

const today =
    new Date()
        .toISOString()
        .slice(0, 10);

const deliveryForm = useForm({
    status: 'delivered',

    delivery_payment_amount:
        props.order.sale?.balance > 0
            ? Number(
                props.order.sale.balance,
            ).toFixed(2)
            : '',

    delivery_payment_method:
        'cash',

    delivery_financial_account_id:
        '',

    delivery_payment_date:
        today,

    delivery_reference:
        '',

    delivery_payment_notes:
        '',
});

const breadcrumbs = [
    {
        title:
            'Órdenes de trabajo',
        href:
            '/work-orders',
    },
    {
        title:
            props.order
                .work_order_number,
        href:
            `/work-orders/${props.order.id}`,
    },
];

const materialsSummary =
    computed(() => {
        return (
            props.order
                .materials_summary
            ?? {
                ready: true,
                missing_count: 0,
                missing: [],
            }
        );
    });

const materialsReady =
    computed(
        () =>
            materialsSummary
                .value
                .ready,
    );

const purchaseList =
    computed(
        () =>
            materialsSummary
                .value
                .missing
            ?? [],
    );

const hasMaterials =
    computed(
        () =>
            (
                props.order
                    .materials
                ?? []
            ).length > 0,
    );

const hasPendingBalance =
    computed(() => {
        return (
            props.order.sale
            &&
            Number(
                props.order
                    .sale
                    .balance
                || 0,
            ) > 0
        );
    });

const isDeliveryAvailable =
    computed(() => {
        return props
            .allowedStatuses
            .includes(
                'delivered',
            );
    });

const requiresFinancialAccount =
    computed(() => {
        return (
            hasPendingBalance.value
            &&
            deliveryForm
                .delivery_payment_method
            !== 'cash'
        );
    });

const normalStatuses =
    computed(() => {
        return props
            .allowedStatuses
            .filter(
                (status) =>
                    status
                    !==
                    'delivered',
            );
    });

watch(
    () =>
        deliveryForm
            .delivery_payment_method,
    (method) => {
        if (
            method ===
            'cash'
        ) {
            deliveryForm
                .delivery_financial_account_id =
                '';
        }
    },
);

const money = (
    value:
        string
        | number,
) =>
    `L ${Number(
        value || 0,
    ).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits:
                2,
            maximumFractionDigits:
                2,
        },
    )}`;

const quantity = (
    value:
        string
        | number,
) =>
    Number(
        value || 0,
    ).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits:
                0,
            maximumFractionDigits:
                3,
        },
    );

const statusLabel = (
    status: string,
) => {
    const labels:
        Record<
            string,
            string
        > = {
        draft:
            'Borrador',

        pending:
            'Pendiente',

        in_progress:
            'En producción',

        paused:
            'Pausada',

        review:
            'En revisión',

        completed:
            'Finalizada',

        delivered:
            'Entregada',

        cancelled:
            'Cancelada',
    };

    return (
        labels[status]
        ?? status
    );
};

const taskStatusLabel = (
    status: string,
) => {
    const labels:
        Record<
            string,
            string
        > = {
        blocked:
            'En espera',

        pending:
            'Pendiente',

        in_progress:
            'En progreso',

        paused:
            'Pausada',

        review:
            'En revisión',

        issue:
            'Inconveniente',

        completed:
            'Finalizada',
    };

    return (
        labels[status]
        ?? status
    );
};

const paymentMethodLabel = (
    method: string,
) => {
    const labels:
        Record<
            string,
            string
        > = {
        cash:
            'Efectivo',

        transfer:
            'Transferencia',

        card:
            'Tarjeta',

        other:
            'Otro',
    };

    return (
        labels[method]
        ?? method
    );
};

const priorityLabel = (
    priority: string,
) => {
    const labels:
        Record<
            string,
            string
        > = {
        low:
            'Baja',

        normal:
            'Normal',

        high:
            'Alta',

        urgent:
            'Urgente',
    };

    return (
        labels[priority]
        ?? priority
    );
};

const reservationLabel = (
    status:
        string
        | null,
) => {
    const labels:
        Record<
            string,
            string
        > = {
        reserved:
            'Reservado',

        partial:
            'Parcial',

        unavailable:
            'Sin existencia',

        consumed:
            'Consumido',

        released:
            'Liberado',
    };

    if (!status) {
        return 'Sin reserva';
    }

    return (
        labels[status]
        ?? status
    );
};

const reservationClass = (
    status:
        string
        | null,
) => {
    if (
        status ===
        'reserved'
    ) {
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300';
    }

    if (
        status ===
        'consumed'
    ) {
        return 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300';
    }

    if (
        status ===
        'partial'
    ) {
        return 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300';
    }

    if (
        status ===
        'unavailable'
    ) {
        return 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300';
    }

    return 'bg-muted text-muted-foreground';
};

const statusIcon = (
    status: string,
) => {
    if (
        status ===
        'in_progress'
    ) {
        return Play;
    }

    if (
        status ===
        'paused'
    ) {
        return CirclePause;
    }

    if (
        status ===
        'review'
    ) {
        return Eye;
    }

    if (
        status ===
        'completed'
    ) {
        return CheckCircle2;
    }

    if (
        status ===
        'delivered'
    ) {
        return PackageCheck;
    }

    if (
        status ===
        'cancelled'
    ) {
        return XCircle;
    }

    if (
        status ===
        'pending'
    ) {
        return RotateCcw;
    }

    return ClipboardCheck;
};

const changeStatus = (
    status: string,
) => {
    /*
    |--------------------------------------------------------------------------
    | Evitar intento inútil si faltan materiales
    |--------------------------------------------------------------------------
    */

    if (
        status ===
            'in_progress'
        &&
        !materialsReady.value
    ) {
        window.alert(
            'No puedes iniciar producción todavía. Hay materiales pendientes de compra.',
        );

        return;
    }

    let message =
        `¿Cambiar la orden a "${statusLabel(status)}"?`;

    if (
        status ===
            'in_progress'
        &&
        !props.order
            .started_at
    ) {
        message =
            'Al iniciar producción se consumirán automáticamente los materiales reservados. ¿Continuar?';
    }

    if (
        !window.confirm(
            message,
        )
    ) {
        return;
    }

    router.patch(
        `/work-orders/${props.order.id}/status`,
        {
            status,
        },
        {
            preserveScroll:
                true,
        },
    );
};

const submitDelivery = () => {
    let message =
        '¿Confirmar la entrega de este trabajo?';

    if (
        hasPendingBalance.value
    ) {
        message =
            `Se registrará un pago de ${money(
                deliveryForm
                    .delivery_payment_amount,
            )} y el trabajo quedará marcado como entregado. ¿Continuar?`;
    }

    if (
        !window.confirm(
            message,
        )
    ) {
        return;
    }

    deliveryForm.patch(
        `/work-orders/${props.order.id}/status`,
        {
            preserveScroll:
                true,
        },
    );
};
</script>

<template>
    <Head
        :title="
            order.work_order_number
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
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        {{
                            order.work_order_number
                        }}
                    </p>

                    <h1
                        class="text-2xl font-bold"
                    >
                        {{
                            order.title
                        }}
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        {{
                            order.client.name
                        }}
                        ·
                        {{
                            statusLabel(
                                order.status,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <Link
                        href="/work-orders"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <ArrowLeft
                            class="h-4 w-4"
                        />

                        Volver
                    </Link>

                    <Link
                        v-if="
                            ![
                                'completed',
                                'delivered',
                                'cancelled',
                            ].includes(
                                order.status,
                            )
                        "
                        :href="`/work-orders/${order.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <Pencil
                            class="h-4 w-4"
                        />

                        Editar
                    </Link>
                </div>
            </div>

            <!-- ESTADO DE MATERIALES -->

            <div
                v-if="
                    hasMaterials
                    &&
                    materialsReady
                "
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/30"
            >
                <div
                    class="flex items-start gap-4"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white"
                    >
                        <CheckCircle2
                            class="h-5 w-5"
                        />
                    </div>

                    <div>
                        <p
                            class="font-bold text-emerald-700 dark:text-emerald-300"
                        >
                            Materiales disponibles
                        </p>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Todo el material
                            necesario para este
                            trabajo se encuentra
                            reservado.
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="
                    hasMaterials
                    &&
                    !materialsReady
                "
                class="overflow-hidden rounded-2xl border border-amber-300 bg-background shadow-sm dark:border-amber-900"
            >
                <div
                    class="border-b border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/30"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white"
                        >
                            <ShoppingCart
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="font-bold text-amber-700 dark:text-amber-300"
                            >
                                Materiales por
                                comprar
                            </p>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                El sistema detectó
                                que no existe
                                suficiente inventario
                                para iniciar este
                                trabajo.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="divide-y"
                >
                    <div
                        v-for="
                            item in purchaseList
                        "
                        :key="
                            item.name
                        "
                        class="flex flex-col gap-2 p-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="font-semibold"
                            >
                                {{
                                    item.name
                                }}
                            </p>

                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Compra pendiente
                            </p>
                        </div>

                        <div
                            class="text-left sm:text-right"
                        >
                            <p
                                class="text-lg font-bold text-[#e84657]"
                            >
                                {{
                                    quantity(
                                        item.missing,
                                    )
                                }}

                                {{
                                    item.measurement_unit
                                }}
                            </p>

                            <p
                                class="text-xs text-muted-foreground"
                            >
                                faltantes
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="border-t bg-muted/20 p-4 text-sm text-muted-foreground"
                >
                    Cuando ingreses nuevas
                    existencias al Inventario,
                    el sistema intentará completar
                    estas reservas
                    automáticamente.
                </div>
            </div>

            <div
                v-if="
                    !hasMaterials
                "
                class="rounded-xl border border-dashed p-5"
            >
                <div
                    class="flex items-center gap-3"
                >
                    <Warehouse
                        class="h-5 w-5 text-muted-foreground"
                    />

                    <div>
                        <p
                            class="font-semibold"
                        >
                            Sin materiales de
                            inventario
                        </p>

                        <p
                            class="text-sm text-muted-foreground"
                        >
                            Esta orden no tiene
                            materiales planificados.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ACCIONES -->

            <div
                v-if="
                    normalStatuses.length
                    > 0
                "
                class="rounded-xl border bg-background p-5 shadow-sm"
            >
                <p
                    class="mb-3 text-sm font-semibold"
                >
                    Acciones de producción
                </p>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <button
                        v-for="
                            status in normalStatuses
                        "
                        :key="
                            status
                        "
                        type="button"
                        :disabled="
                            status ===
                                'in_progress'
                            &&
                            !materialsReady
                        "
                        @click="
                            changeStatus(
                                status,
                            )
                        "
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4] disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <component
                            :is="
                                statusIcon(
                                    status,
                                )
                            "
                            class="h-4 w-4"
                        />

                        {{
                            statusLabel(
                                status,
                            )
                        }}
                    </button>
                </div>

                <p
                    v-if="
                        !materialsReady
                        &&
                        normalStatuses.includes(
                            'in_progress',
                        )
                    "
                    class="mt-3 text-sm text-amber-600"
                >
                    Debes completar los
                    materiales faltantes antes de
                    iniciar producción.
                </p>
            </div>

            <!-- ENTREGA -->

            <div
                v-if="
                    isDeliveryAvailable
                "
                class="overflow-hidden rounded-2xl border border-[#0fa7b4]/30 bg-background shadow-sm"
            >
                <div
                    class="border-b bg-gradient-to-r from-[#0fa7b4]/10 to-transparent p-6"
                >
                    <div
                        class="flex items-center gap-4"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#0fa7b4] text-white"
                        >
                            <PackageCheck
                                class="h-6 w-6"
                            />
                        </div>

                        <div>
                            <h2
                                class="text-xl font-bold"
                            >
                                Entregar trabajo
                            </h2>

                            <p
                                class="text-sm text-muted-foreground"
                            >
                                La entrega cerrará
                                automáticamente el
                                proceso operativo y
                                financiero.
                            </p>
                        </div>
                    </div>
                </div>

                <form
                    @submit.prevent="
                        submitDelivery
                    "
                    class="p-6"
                >
                    <div
                        v-if="
                            order.sale
                        "
                        class="mb-6 grid gap-4 sm:grid-cols-3"
                    >
                        <div
                            class="rounded-xl bg-muted/40 p-5"
                        >
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Total venta
                            </p>

                            <p
                                class="mt-2 text-xl font-bold"
                            >
                                {{
                                    money(
                                        order
                                            .sale
                                            .total,
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-emerald-50 p-5 dark:bg-emerald-950/30"
                        >
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Ya pagado
                            </p>

                            <p
                                class="mt-2 text-xl font-bold text-emerald-600"
                            >
                                {{
                                    money(
                                        order
                                            .sale
                                            .paid_amount,
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-[#e84657]/5 p-5"
                        >
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Saldo pendiente
                            </p>

                            <p
                                class="mt-2 text-xl font-bold text-[#e84657]"
                            >
                                {{
                                    money(
                                        order
                                            .sale
                                            .balance,
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="
                            hasPendingBalance
                        "
                        class="space-y-5"
                    >
                        <div
                            class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/30"
                        >
                            <p
                                class="font-semibold"
                            >
                                La venta tiene saldo
                                pendiente.
                            </p>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Registra el pago
                                recibido. El recibo
                                se generará
                                automáticamente.
                            </p>
                        </div>

                        <div
                            class="grid gap-5 md:grid-cols-2"
                        >
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Monto recibido *
                                </label>

                                <input
                                    v-model="
                                        deliveryForm.delivery_payment_amount
                                    "
                                    type="number"
                                    min="0.01"
                                    :max="
                                        order
                                            .sale
                                            .balance
                                    "
                                    step="0.01"
                                    class="w-full rounded-lg border bg-background px-4 py-3"
                                />

                                <p
                                    v-if="
                                        deliveryForm
                                            .errors
                                            .delivery_payment_amount
                                    "
                                    class="mt-1 text-sm text-red-500"
                                >
                                    {{
                                        deliveryForm
                                            .errors
                                            .delivery_payment_amount
                                    }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Fecha del pago *
                                </label>

                                <input
                                    v-model="
                                        deliveryForm.delivery_payment_date
                                    "
                                    type="date"
                                    class="w-full rounded-lg border bg-background px-4 py-3"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Método de pago *
                                </label>

                                <select
                                    v-model="
                                        deliveryForm.delivery_payment_method
                                    "
                                    class="w-full rounded-lg border bg-background px-4 py-3"
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
                                    requiresFinancialAccount
                                "
                            >
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Banco / cuenta *
                                </label>

                                <select
                                    v-model="
                                        deliveryForm.delivery_financial_account_id
                                    "
                                    class="w-full rounded-lg border bg-background px-4 py-3"
                                >
                                    <option
                                        value=""
                                    >
                                        Seleccionar
                                        cuenta
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
                            </div>

                            <div
                                class="md:col-span-2"
                            >
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Referencia
                                </label>

                                <input
                                    v-model="
                                        deliveryForm.delivery_reference
                                    "
                                    type="text"
                                    placeholder="Transferencia, autorización, comprobante..."
                                    class="w-full rounded-lg border bg-background px-4 py-3"
                                />
                            </div>

                            <div
                                class="md:col-span-2"
                            >
                                <label
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Observación
                                </label>

                                <textarea
                                    v-model="
                                        deliveryForm.delivery_payment_notes
                                    "
                                    rows="3"
                                    class="w-full rounded-lg border bg-background px-4 py-3"
                                />
                            </div>
                        </div>

                        <div
                            v-if="
                                deliveryForm.delivery_payment_method ===
                                'cash'
                            "
                            class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30"
                        >
                            <Banknote
                                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                            />

                            <p
                                class="text-sm"
                            >
                                El pago se registrará
                                en la caja abierta.
                            </p>
                        </div>

                        <div
                            v-else
                            class="flex items-start gap-3 rounded-lg border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 p-4"
                        >
                            <Landmark
                                v-if="
                                    deliveryForm.delivery_payment_method ===
                                    'transfer'
                                "
                                class="mt-0.5 h-5 w-5 shrink-0 text-[#0fa7b4]"
                            />

                            <CreditCard
                                v-else
                                class="mt-0.5 h-5 w-5 shrink-0 text-[#0fa7b4]"
                            />

                            <p
                                class="text-sm"
                            >
                                El ingreso se
                                asociará a la cuenta
                                financiera
                                seleccionada.
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/30"
                    >
                        <div
                            class="flex items-start gap-3"
                        >
                            <CheckCircle2
                                class="mt-0.5 h-5 w-5 text-emerald-600"
                            />

                            <div>
                                <p
                                    class="font-semibold"
                                >
                                    No hay saldo por
                                    cobrar.
                                </p>

                                <p
                                    class="mt-1 text-sm text-muted-foreground"
                                >
                                    Solo confirma la
                                    entrega.
                                </p>
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="
                            deliveryForm.processing
                        "
                        class="mt-6 inline-flex w-full items-center justify-center gap-3 rounded-xl bg-[#0fa7b4] px-6 py-4 text-base font-bold text-white transition hover:opacity-90 disabled:opacity-50"
                    >
                        <PackageCheck
                            class="h-5 w-5"
                        />

                        {{
                            deliveryForm.processing
                                ? 'Procesando entrega...'
                                : hasPendingBalance
                                  ? 'Confirmar entrega y pago'
                                  : 'Confirmar entrega'
                        }}
                    </button>
                </form>
            </div>

            <!-- RESUMEN -->

            <div
                class="grid gap-4 md:grid-cols-4"
            >
                <div
                    class="rounded-xl border bg-background p-5"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Prioridad
                    </p>

                    <p
                        class="mt-2 text-xl font-bold"
                    >
                        {{
                            priorityLabel(
                                order.priority,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Fecha de orden
                    </p>

                    <p
                        class="mt-2 text-xl font-bold"
                    >
                        {{
                            order.order_date
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Fecha límite
                    </p>

                    <p
                        class="mt-2 text-xl font-bold"
                    >
                        {{
                            order.due_date
                            || '—'
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5"
                >
                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Costo materiales
                    </p>

                    <p
                        class="mt-2 text-xl font-bold text-[#e84657]"
                    >
                        {{
                            money(
                                order.material_cost,
                            )
                        }}
                    </p>
                </div>
            </div>

            <div
                class="grid gap-6 lg:grid-cols-2"
            >
                <div
                    class="rounded-xl border bg-background p-6"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        Cliente y trabajo
                    </h2>

                    <div
                        class="mt-5 space-y-4"
                    >
                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Cliente
                            </p>

                            <p
                                class="font-bold"
                            >
                                {{
                                    order.client.name
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Responsable
                            </p>

                            <p
                                class="font-bold"
                            >
                                {{
                                    order.responsible
                                        ?.name
                                    || 'Operador principal'
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                order.sale
                            "
                        >
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Venta relacionada
                            </p>

                            <Link
                                :href="`/sales/${order.sale.id}`"
                                class="font-bold text-[#0fa7b4]"
                            >
                                {{
                                    order.sale
                                        .sale_number
                                }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border bg-background p-6"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        Tiempos
                    </h2>

                    <div
                        class="mt-5 space-y-3 text-sm"
                    >
                        <div
                            class="flex justify-between gap-4"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Inicio
                            </span>

                            <strong>
                                {{
                                    order.started_at
                                    || 'No iniciado'
                                }}
                            </strong>
                        </div>

                        <div
                            class="flex justify-between gap-4"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Finalización
                            </span>

                            <strong>
                                {{
                                    order.completed_at
                                    || '—'
                                }}
                            </strong>
                        </div>

                        <div
                            class="flex justify-between gap-4"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Entrega
                            </span>

                            <strong>
                                {{
                                    order.delivered_at
                                    || '—'
                                }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAREAS -->

            <div
                v-if="
                    order.tasks.length
                    > 0
                "
                class="rounded-xl border bg-background p-6"
            >
                <h2
                    class="mb-5 text-lg font-semibold"
                >
                    Flujo de producción
                </h2>

                <div
                    class="space-y-3"
                >
                    <Link
                        v-for="
                            task in order.tasks
                        "
                        :key="
                            task.id
                        "
                        :href="`/tasks/${task.id}`"
                        class="flex items-center justify-between gap-4 rounded-lg border p-4 transition hover:border-[#0fa7b4]/50"
                    >
                        <div>
                            <p
                                class="font-semibold"
                            >
                                {{
                                    task.title
                                }}
                            </p>

                            <p
                                class="text-xs text-muted-foreground"
                            >
                                {{
                                    task.task_number
                                }}
                            </p>
                        </div>

                        <span
                            class="text-sm font-semibold"
                        >
                            {{
                                taskStatusLabel(
                                    task.status,
                                )
                            }}
                        </span>
                    </Link>
                </div>
            </div>

            <!-- PRODUCTOS -->

            <div
                v-if="
                    order.items.length
                    > 0
                "
                class="rounded-xl border bg-background p-6"
            >
                <h2
                    class="mb-5 text-lg font-semibold"
                >
                    Productos del trabajo
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
                                    Producto
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Cantidad
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Valor
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    item in order.items
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
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{
                                            item.item_code
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="py-4 text-right"
                                >
                                    {{
                                        quantity(
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

            <!-- CONTROL DE MATERIALES -->

            <div
                class="overflow-hidden rounded-2xl border bg-background shadow-sm"
            >
                <div
                    class="border-b p-6"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <Boxes
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <div>
                            <h2
                                class="text-lg font-semibold"
                            >
                                Control de materiales
                            </h2>

                            <p
                                class="text-sm text-muted-foreground"
                            >
                                Inventario físico,
                                reservas, disponibilidad
                                y consumo real.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        order.materials.length
                        === 0
                    "
                    class="p-10 text-center text-sm text-muted-foreground"
                >
                    Esta orden no tiene materiales
                    planificados.
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="min-w-[1150px] w-full text-sm"
                    >
                        <thead
                            class="border-b bg-muted/40"
                        >
                            <tr>
                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Material
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Físico
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Requerido
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Reservado OT
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Disponible global
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Faltante
                                </th>

                                <th
                                    class="px-4 py-4 text-right"
                                >
                                    Consumido
                                </th>

                                <th
                                    class="px-5 py-4 text-center"
                                >
                                    Estado
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    material in order.materials
                                "
                                :key="
                                    material.id
                                "
                                class="border-b last:border-b-0"
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    <p
                                        class="font-semibold"
                                    >
                                        {{
                                            material.name
                                        }}
                                    </p>

                                    <p
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{
                                            material.code
                                        }}

                                        ·

                                        {{
                                            material.source ===
                                            'catalog_recipe'
                                                ? 'Automático'
                                                : 'Manual'
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-4 py-4 text-right"
                                >
                                    {{
                                        quantity(
                                            material.current_stock,
                                        )
                                    }}

                                    <span
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{
                                            material.measurement_unit
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-4 py-4 text-right font-semibold"
                                >
                                    {{
                                        quantity(
                                            material.quantity_planned,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-4 py-4 text-right font-semibold text-[#0fa7b4]"
                                >
                                    {{
                                        quantity(
                                            material.quantity_reserved,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-4 py-4 text-right"
                                >
                                    {{
                                        quantity(
                                            material.available_stock,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-4 py-4 text-right"
                                >
                                    <span
                                        :class="
                                            Number(
                                                material.quantity_missing,
                                            ) > 0
                                                ? 'font-bold text-[#e84657]'
                                                : 'text-emerald-600'
                                        "
                                    >
                                        {{
                                            quantity(
                                                material.quantity_missing,
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-4 py-4 text-right"
                                >
                                    {{
                                        quantity(
                                            material.quantity_consumed,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-center"
                                >
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            reservationClass(
                                                material.reservation_status,
                                            )
                                        "
                                    >
                                        {{
                                            reservationLabel(
                                                material.reservation_status,
                                            )
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="
                        order.materials.length
                        > 0
                    "
                    class="border-t bg-muted/20 px-6 py-4 text-xs text-muted-foreground"
                >
                    El stock físico no disminuye
                    mientras el material solamente
                    esté reservado. Se descuenta
                    cuando inicia la producción.
                </div>
            </div>

            <!-- PAGOS -->

            <div
                v-if="
                    order.sale
                    &&
                    order.sale
                        .payments
                        .length
                    > 0
                "
                class="rounded-xl border bg-background p-6"
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
                        Pagos y recibos
                    </h2>
                </div>

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
                                    Recibo
                                </th>

                                <th
                                    class="py-3 text-left"
                                >
                                    Fecha
                                </th>

                                <th
                                    class="py-3 text-left"
                                >
                                    Método
                                </th>

                                <th
                                    class="py-3 text-left"
                                >
                                    Cuenta
                                </th>

                                <th
                                    class="py-3 text-right"
                                >
                                    Monto
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    payment in order
                                        .sale
                                        .payments
                                "
                                :key="
                                    payment.id
                                "
                                class="border-b last:border-b-0"
                            >
                                <td
                                    class="py-4"
                                >
                                    <Link
                                        :href="`/receipts/${payment.id}`"
                                        class="font-semibold text-[#0fa7b4]"
                                    >
                                        {{
                                            payment.receipt_number
                                        }}
                                    </Link>
                                </td>

                                <td
                                    class="py-4"
                                >
                                    {{
                                        payment.payment_date
                                    }}
                                </td>

                                <td
                                    class="py-4"
                                >
                                    {{
                                        paymentMethodLabel(
                                            payment.payment_method,
                                        )
                                    }}
                                </td>

                                <td
                                    class="py-4"
                                >
                                    {{
                                        payment.financial_account
                                        ||
                                        (
                                            payment.payment_method ===
                                            'cash'
                                                ? 'Caja'
                                                : '—'
                                        )
                                    }}
                                </td>

                                <td
                                    class="py-4 text-right font-bold"
                                >
                                    {{
                                        money(
                                            payment.amount,
                                        )
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-if="
                    order.description
                    ||
                    order.internal_notes
                "
                class="grid gap-6 lg:grid-cols-2"
            >
                <div
                    v-if="
                        order.description
                    "
                    class="rounded-xl border bg-background p-6"
                >
                    <h2
                        class="font-semibold"
                    >
                        Descripción
                    </h2>

                    <p
                        class="mt-3 whitespace-pre-line text-sm"
                    >
                        {{
                            order.description
                        }}
                    </p>
                </div>

                <div
                    v-if="
                        order.internal_notes
                    "
                    class="rounded-xl border bg-background p-6"
                >
                    <h2
                        class="font-semibold"
                    >
                        Notas internas
                    </h2>

                    <p
                        class="mt-3 whitespace-pre-line text-sm"
                    >
                        {{
                            order.internal_notes
                        }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>