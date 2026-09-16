<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    Banknote,
    Calculator,
    CreditCard,
    Landmark,
    Plus,
    ShoppingCart,
    Trash2,
} from '@lucide/vue';
import { computed } from 'vue';

type Client = {
    id: number;
    code: string;
    name: string;
};

type CatalogItem = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    pricing_method: string;
    measurement_unit: string | null;
    sale_price: string | number | null;
    cost_price: string | number | null;
    sale_rate: string | number | null;
    cost_rate: string | number | null;
};

type FinancialAccount = {
    id: number;
    code: string;
    name: string;
    institution: string | null;
    account_type: string;
};

type SaleLine = {
    catalog_item_id: number | '';
    quantity: number | string;
    width: number | string;
    height: number | string;
    manual_unit_price: number | string;
};

const props = defineProps<{
    clients: Client[];
    catalogItems: CatalogItem[];
    financialAccounts: FinancialAccount[];
}>();

const now = new Date();
const offset = now.getTimezoneOffset();

const today = new Date(
    now.getTime() - offset * 60000,
)
    .toISOString()
    .slice(0, 10);

const emptyLine = (): SaleLine => ({
    catalog_item_id: '',
    quantity: 1,
    width: '',
    height: '',
    manual_unit_price: '',
});

const form = useForm({
    client_id: '' as number | '',
    sale_date: today,
    discount: 0,
    notes: '',

    items: [
        emptyLine(),
    ] as SaleLine[],

    initial_payment_amount: '',
    initial_payment_method: 'cash',
    initial_payment_financial_account_id: '',
    initial_payment_reference: '',
    initial_payment_notes: '',
});

const selectedCatalogItem = (
    line: SaleLine,
): CatalogItem | undefined => {
    return props.catalogItems.find(
        (item) =>
            Number(item.id) ===
            Number(line.catalog_item_id),
    );
};

const pricingMethodLabel = (
    method?: string,
) => {
    const labels: Record<string, string> = {
        AREA: 'Por área',
        LINEAR: 'Por medida lineal',
        UNIT: 'Por unidad',
        FIXED: 'Precio fijo',
        COST_MARGIN: 'Costo + margen',
        RESALE: 'Reventa',
        MANUAL: 'Precio manual',
    };

    return method
        ? labels[method] ?? method
        : '—';
};

const requiresWidth = (
    line: SaleLine,
) => {
    const item =
        selectedCatalogItem(line);

    return (
        item?.pricing_method === 'AREA'
        || item?.pricing_method === 'LINEAR'
    );
};

const requiresHeight = (
    line: SaleLine,
) => {
    const item =
        selectedCatalogItem(line);

    return item?.pricing_method === 'AREA';
};

const requiresManualPrice = (
    line: SaleLine,
) => {
    const item =
        selectedCatalogItem(line);

    return item?.pricing_method === 'MANUAL';
};

const unitPrice = (
    line: SaleLine,
) => {
    const item =
        selectedCatalogItem(line);

    if (!item) {
        return 0;
    }

    const method =
        item.pricing_method;

    if (method === 'AREA') {
        const width =
            Number(line.width || 0);

        const height =
            Number(line.height || 0);

        const rate =
            Number(item.sale_rate || 0);

        return width * height * rate;
    }

    if (method === 'LINEAR') {
        const width =
            Number(line.width || 0);

        const rate =
            Number(item.sale_rate || 0);

        return width * rate;
    }

    if (method === 'MANUAL') {
        return Number(
            line.manual_unit_price || 0,
        );
    }

    return Number(
        item.sale_price || 0,
    );
};

const lineSubtotal = (
    line: SaleLine,
) => {
    const quantity =
        Number(line.quantity || 0);

    return (
        unitPrice(line)
        * quantity
    );
};

const subtotal = computed(() => {
    return form.items.reduce(
        (sum, line) =>
            sum + lineSubtotal(line),
        0,
    );
});

const discountValue = computed(() => {
    return Math.max(
        Number(form.discount || 0),
        0,
    );
});

const total = computed(() => {
    return Math.max(
        subtotal.value
        - discountValue.value,
        0,
    );
});

const paymentAmount = computed(() => {
    return Number(
        form.initial_payment_amount
        || 0,
    );
});

const requiresFinancialAccount =
    computed(() => {
        return (
            paymentAmount.value > 0
            && form.initial_payment_method
                !== 'cash'
        );
    });

const remainingAfterInitialPayment =
    computed(() => {
        return Math.max(
            total.value
            - paymentAmount.value,
            0,
        );
    });

const addItem = () => {
    form.items.push(
        emptyLine(),
    );
};

const removeItem = (
    index: number,
) => {
    if (form.items.length <= 1) {
        return;
    }

    form.items.splice(
        index,
        1,
    );
};

const onCatalogChange = (
    line: SaleLine,
) => {
    line.width = '';
    line.height = '';
    line.manual_unit_price = '';
};

const onPaymentMethodChange = () => {
    if (
        form.initial_payment_method
        === 'cash'
    ) {
        form.initial_payment_financial_account_id =
            '';
    }
};

const payFullAmount = () => {
    form.initial_payment_amount =
        total.value.toFixed(2);
};

const clearPayment = () => {
    form.initial_payment_amount = '';
    form.initial_payment_method = 'cash';
    form.initial_payment_financial_account_id =
        '';
    form.initial_payment_reference = '';
    form.initial_payment_notes = '';
};

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

const itemError = (
    index: number,
    field: string,
): string | undefined => {
    const errors =
        form.errors as Record<
            string,
            string | undefined
        >;

    return errors[
        `items.${index}.${field}`
    ];
};

const submit = () => {
    if (
        paymentAmount.value <= 0
    ) {
        form.initial_payment_amount = '';
        form.initial_payment_method =
            'cash';
        form.initial_payment_financial_account_id =
            '';
        form.initial_payment_reference =
            '';
        form.initial_payment_notes = '';
    }

    if (
        form.initial_payment_method
        === 'cash'
    ) {
        form.initial_payment_financial_account_id =
            '';
    }

    form.post('/sales', {
        preserveScroll: true,
    });
};
</script>

<template>
    <form
        @submit.prevent="submit"
        class="space-y-6"
    >
        <div
            class="rounded-2xl border bg-background p-6 shadow-sm"
        >
            <div
                class="mb-6 flex items-center gap-3"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                >
                    <ShoppingCart
                        class="h-5 w-5"
                    />
                </div>

                <div>
                    <h2
                        class="font-semibold"
                    >
                        Información de la venta
                    </h2>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Selecciona el cliente y
                        la fecha.
                    </p>
                </div>
            </div>

            <div
                class="grid gap-5 md:grid-cols-2"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Cliente *
                    </label>

                    <select
                        v-model="
                            form.client_id
                        "
                        class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    >
                        <option value="">
                            Seleccionar cliente
                        </option>

                        <option
                            v-for="
                                client in clients
                            "
                            :key="
                                client.id
                            "
                            :value="
                                client.id
                            "
                        >
                            {{
                                client.code
                            }}
                            ·
                            {{
                                client.name
                            }}
                        </option>
                    </select>

                    <p
                        v-if="
                            form.errors
                                .client_id
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors
                                .client_id
                        }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Fecha *
                    </label>

                    <input
                        v-model="
                            form.sale_date
                        "
                        type="date"
                        class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    />

                    <p
                        v-if="
                            form.errors
                                .sale_date
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors
                                .sale_date
                        }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border bg-background shadow-sm"
        >
            <div
                class="flex flex-col gap-4 border-b p-6 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="font-semibold"
                    >
                        Productos y servicios
                    </h2>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        El precio se calcula según
                        el método definido en el
                        catálogo.
                    </p>
                </div>

                <button
                    type="button"
                    @click="addItem"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0fa7b4] px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                >
                    <Plus
                        class="h-4 w-4"
                    />

                    Agregar
                </button>
            </div>

            <div
                class="space-y-4 p-6"
            >
                <div
                    v-if="
                        form.errors.items
                    "
                    class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600 dark:border-red-900 dark:bg-red-950/30"
                >
                    {{
                        form.errors.items
                    }}
                </div>

                <div
                    v-for="
                        (
                            line,
                            index
                        ) in form.items
                    "
                    :key="index"
                    class="rounded-xl border p-5"
                >
                    <div
                        class="mb-5 flex items-center justify-between"
                    >
                        <p
                            class="font-semibold"
                        >
                            Ítem
                            {{
                                index + 1
                            }}
                        </p>

                        <button
                            v-if="
                                form.items
                                    .length >
                                1
                            "
                            type="button"
                            @click="
                                removeItem(
                                    index,
                                )
                            "
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border text-[#e84657] transition hover:bg-[#e84657]/10"
                        >
                            <Trash2
                                class="h-4 w-4"
                            />
                        </button>
                    </div>

                    <div
                        class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Producto /
                                servicio *
                            </label>

                            <select
                                v-model="
                                    line.catalog_item_id
                                "
                                @change="
                                    onCatalogChange(
                                        line,
                                    )
                                "
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            >
                                <option
                                    value=""
                                >
                                    Seleccionar
                                </option>

                                <option
                                    v-for="
                                        item in catalogItems
                                    "
                                    :key="
                                        item.id
                                    "
                                    :value="
                                        item.id
                                    "
                                >
                                    {{
                                        item.code
                                    }}
                                    ·
                                    {{
                                        item.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    itemError(
                                        index,
                                        'catalog_item_id',
                                    )
                                "
                                class="mt-1 text-sm text-red-500"
                            >
                                {{
                                    itemError(
                                        index,
                                        'catalog_item_id',
                                    )
                                }}
                            </p>

                            <div
                                v-if="
                                    selectedCatalogItem(
                                        line,
                                    )
                                "
                                class="mt-2"
                            >
                                <span
                                    class="rounded-full bg-muted px-2.5 py-1 text-xs font-medium"
                                >
                                    {{
                                        pricingMethodLabel(
                                            selectedCatalogItem(
                                                line,
                                            )
                                                ?.pricing_method,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Cantidad *
                            </label>

                            <input
                                v-model="
                                    line.quantity
                                "
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />

                            <p
                                v-if="
                                    itemError(
                                        index,
                                        'quantity',
                                    )
                                "
                                class="mt-1 text-sm text-red-500"
                            >
                                {{
                                    itemError(
                                        index,
                                        'quantity',
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                requiresWidth(
                                    line,
                                )
                            "
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                {{
                                    selectedCatalogItem(
                                        line,
                                    )
                                        ?.pricing_method ===
                                    'LINEAR'
                                        ? 'Medida'
                                        : 'Ancho'
                                }}
                                *
                            </label>

                            <div
                                class="relative"
                            >
                                <input
                                    v-model="
                                        line.width
                                    "
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    class="w-full rounded-lg border bg-background px-4 py-2.5 pr-16"
                                />

                                <span
                                    v-if="
                                        selectedCatalogItem(
                                            line,
                                        )
                                            ?.measurement_unit
                                    "
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground"
                                >
                                    {{
                                        selectedCatalogItem(
                                            line,
                                        )
                                            ?.measurement_unit
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="
                                requiresHeight(
                                    line,
                                )
                            "
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Alto *
                            </label>

                            <div
                                class="relative"
                            >
                                <input
                                    v-model="
                                        line.height
                                    "
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    class="w-full rounded-lg border bg-background px-4 py-2.5 pr-16"
                                />

                                <span
                                    v-if="
                                        selectedCatalogItem(
                                            line,
                                        )
                                            ?.measurement_unit
                                    "
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground"
                                >
                                    {{
                                        selectedCatalogItem(
                                            line,
                                        )
                                            ?.measurement_unit
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="
                                requiresManualPrice(
                                    line,
                                )
                            "
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Precio unitario *
                            </label>

                            <input
                                v-model="
                                    line.manual_unit_price
                                "
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-lg border bg-background px-4 py-2.5"
                            />
                        </div>
                    </div>

                    <div
                        v-if="
                            selectedCatalogItem(
                                line,
                            )
                        "
                        class="mt-5 flex flex-col gap-3 rounded-lg bg-muted/40 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Precio unitario
                            </p>

                            <p
                                class="font-semibold"
                            >
                                {{
                                    money(
                                        unitPrice(
                                            line,
                                        ),
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="sm:text-right"
                        >
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Subtotal del ítem
                            </p>

                            <p
                                class="text-xl font-bold text-[#0fa7b4]"
                            >
                                {{
                                    money(
                                        lineSubtotal(
                                            line,
                                        ),
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="grid gap-6 lg:grid-cols-2"
        >
            <div
                class="rounded-2xl border bg-background p-6 shadow-sm"
            >
                <h2
                    class="mb-5 font-semibold"
                >
                    Información adicional
                </h2>

                <div
                    class="space-y-5"
                >
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Descuento
                        </label>

                        <input
                            v-model="
                                form.discount
                            "
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border bg-background px-4 py-2.5"
                        />

                        <p
                            v-if="
                                form.errors
                                    .discount
                            "
                            class="mt-1 text-sm text-red-500"
                        >
                            {{
                                form.errors
                                    .discount
                            }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Notas de la venta
                        </label>

                        <textarea
                            v-model="
                                form.notes
                            "
                            rows="4"
                            placeholder="Indicaciones, observaciones o detalles..."
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border bg-background p-6 shadow-sm"
            >
                <div
                    class="mb-5 flex items-center gap-3"
                >
                    <Calculator
                        class="h-5 w-5 text-[#0fa7b4]"
                    />

                    <h2
                        class="font-semibold"
                    >
                        Resumen de venta
                    </h2>
                </div>

                <div
                    class="space-y-4"
                >
                    <div
                        class="flex justify-between"
                    >
                        <span
                            class="text-muted-foreground"
                        >
                            Subtotal
                        </span>

                        <span
                            class="font-semibold"
                        >
                            {{
                                money(
                                    subtotal,
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
                                    discountValue,
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between border-t pt-4"
                    >
                        <span
                            class="text-lg font-bold"
                        >
                            Total
                        </span>

                        <span
                            class="text-3xl font-bold text-[#0fa7b4]"
                        >
                            {{
                                money(
                                    total,
                                )
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border bg-background p-6 shadow-sm"
        >
            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold"
                    >
                        Pago inicial
                    </h2>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Puedes dejar la venta
                        pendiente o registrar un
                        pago al crearla.
                    </p>
                </div>

                <div
                    class="flex gap-2"
                >
                    <button
                        type="button"
                        @click="
                            payFullAmount
                        "
                        class="rounded-lg border px-3 py-2 text-sm font-semibold transition hover:bg-muted"
                    >
                        Pagar total
                    </button>

                    <button
                        type="button"
                        @click="
                            clearPayment
                        "
                        class="rounded-lg border px-3 py-2 text-sm transition hover:bg-muted"
                    >
                        Sin pago
                    </button>
                </div>
            </div>

            <div
                class="grid gap-5 md:grid-cols-2"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Monto recibido
                    </label>

                    <input
                        v-model="
                            form.initial_payment_amount
                        "
                        type="number"
                        min="0"
                        :max="total"
                        step="0.01"
                        placeholder="0.00"
                        class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    />

                    <p
                        v-if="
                            form.errors
                                .initial_payment_amount
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors
                                .initial_payment_amount
                        }}
                    </p>
                </div>

                <div
                    v-if="
                        paymentAmount >
                        0
                    "
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Método de pago *
                    </label>

                    <select
                        v-model="
                            form.initial_payment_method
                        "
                        @change="
                            onPaymentMethodChange
                        "
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    >
                        <option value="cash">
                            Efectivo
                        </option>

                        <option
                            value="transfer"
                        >
                            Transferencia
                        </option>

                        <option value="card">
                            Tarjeta
                        </option>

                        <option value="other">
                            Otro
                        </option>
                    </select>
                </div>

                <div
                    v-if="
                        requiresFinancialAccount
                    "
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Banco / cuenta donde ingresó *
                    </label>

                    <select
                        v-model="
                            form.initial_payment_financial_account_id
                        "
                        class="w-full rounded-lg border bg-background px-4 py-3 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    >
                        <option value="">
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
                            form.errors
                                .initial_payment_financial_account_id
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors
                                .initial_payment_financial_account_id
                        }}
                    </p>

                    <div
                        class="mt-3 flex items-start gap-3 rounded-lg border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 p-4"
                    >
                        <Landmark
                            v-if="
                                form.initial_payment_method ===
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
                            Este dinero será
                            registrado en la cuenta
                            seleccionada y no
                            aumentará el efectivo
                            físico de Caja.
                        </p>
                    </div>
                </div>

                <div
                    v-if="
                        paymentAmount >
                        0
                        &&
                        form.initial_payment_method ===
                            'cash'
                    "
                    class="md:col-span-2"
                >
                    <div
                        class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30"
                    >
                        <Banknote
                            class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                        />

                        <p
                            class="text-sm"
                        >
                            El pago en efectivo
                            será asociado
                            automáticamente a la
                            caja física abierta.
                        </p>
                    </div>
                </div>

                <div
                    v-if="
                        paymentAmount >
                        0
                    "
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Referencia
                    </label>

                    <input
                        v-model="
                            form.initial_payment_reference
                        "
                        type="text"
                        placeholder="Transferencia, autorización, comprobante..."
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div
                    v-if="
                        paymentAmount >
                        0
                    "
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Nota del pago
                    </label>

                    <input
                        v-model="
                            form.initial_payment_notes
                        "
                        type="text"
                        placeholder="Observación opcional"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>
            </div>

            <div
                v-if="
                    paymentAmount >
                    0
                "
                class="mt-6 grid gap-4 sm:grid-cols-3"
            >
                <div
                    class="rounded-lg bg-muted/40 p-4"
                >
                    <p
                        class="text-xs text-muted-foreground"
                    >
                        Total
                    </p>

                    <p
                        class="mt-1 font-bold"
                    >
                        {{
                            money(
                                total,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-lg bg-emerald-50 p-4 dark:bg-emerald-950/30"
                >
                    <p
                        class="text-xs text-muted-foreground"
                    >
                        Recibido
                    </p>

                    <p
                        class="mt-1 font-bold text-emerald-600"
                    >
                        {{
                            money(
                                paymentAmount,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="rounded-lg bg-[#e84657]/5 p-4"
                >
                    <p
                        class="text-xs text-muted-foreground"
                    >
                        Saldo restante
                    </p>

                    <p
                        class="mt-1 font-bold text-[#e84657]"
                    >
                        {{
                            money(
                                remainingAfterInitialPayment,
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
        >
            <Link
                href="/sales"
                class="inline-flex items-center justify-center rounded-lg border px-6 py-3 text-sm font-semibold transition hover:bg-muted"
            >
                Cancelar
            </Link>

            <button
                type="submit"
                :disabled="
                    form.processing
                "
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0fa7b4] px-7 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
            >
                <ShoppingCart
                    class="h-4 w-4"
                />

                {{
                    form.processing
                        ? 'Registrando...'
                        : 'Registrar venta'
                }}
            </button>
        </div>
    </form>
</template>