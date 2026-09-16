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
    ArrowDownLeft,
    Pencil,
    Plus,
    ReceiptText,
} from '@lucide/vue';
import { computed } from 'vue';

type Expense = Record<string, any>;

type PaginatedData = {
    data?: Expense[];
};

const props = defineProps<{
    expenses?:
        Expense[]
        | PaginatedData;

    items?:
        Expense[]
        | PaginatedData;

    stats?: Record<string, any>;
    summary?: Record<string, any>;
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Gastos',
        href: '/expenses',
    },
];

const expenses =
    computed<Expense[]>(() => {
        const source =
            props.expenses
            ??
            props.items
            ??
            [];

        if (
            Array.isArray(source)
        ) {
            return source;
        }

        return source.data ?? [];
    });

const pick = (
    item: Expense,
    keys: string[],
    fallback: any = null,
): any => {
    for (const key of keys) {
        const result = item?.[key];

        if (
            result !== undefined
            &&
            result !== null
            &&
            result !== ''
        ) {
            return result;
        }
    }

    return fallback;
};

const money = (
    value: any,
): string => {
    return `L ${Number(
        value ?? 0,
    ).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
};

const amount = (
    expense: Expense,
): number => {
    return Number(
        pick(
            expense,
            [
                'amount',
                'total',
                'value',
            ],
            0,
        ),
    );
};

const expenseTitle = (
    expense: Expense,
): string => {
    return String(
        pick(
            expense,
            [
                'description',
                'concept',
                'supplier',
                'category',
            ],
            'Gasto registrado',
        ),
    );
};

const expenseCode = (
    expense: Expense,
): string => {
    return String(
        pick(
            expense,
            [
                'expense_number',
                'reference',
                'code',
            ],
            `GAS-${String(
                expense.id,
            ).padStart(4, '0')}`,
        ),
    );
};

const totalExpenses =
    computed(() => {
        const explicit =
            pick(
                props.stats
                ?? props.summary
                ?? {},
                [
                    'total',
                    'total_expenses',
                    'expenses',
                ],
                null,
            );

        if (
            explicit !== null
        ) {
            return Number(explicit);
        }

        return expenses.value
            .reduce(
                (total, expense) =>
                    total + amount(expense),
                0,
            );
    });

const fieldsFor = (
    expense: Expense,
): MobileRecordField[] => {
    return [
        {
            label: 'Monto',
            value:
                money(
                    amount(expense),
                ),
            wide: true,
        },
        {
            label: 'Fecha',
            value:
                pick(
                    expense,
                    [
                        'expense_date',
                        'date',
                        'created_at',
                    ],
                    '—',
                ),
        },
        {
            label: 'Categoría',
            value:
                pick(
                    expense,
                    [
                        'category',
                        'category_name',
                    ],
                    '—',
                ),
        },
        {
            label: 'Proveedor',
            value:
                pick(
                    expense,
                    [
                        'supplier',
                        'vendor',
                    ],
                    '—',
                ),
            wide: true,
        },
        {
            label: 'Cuenta',
            value:
                pick(
                    expense,
                    [
                        'financial_account',
                        'account_name',
                        'account',
                    ],
                    '—',
                ),
            wide: true,
        },
        {
            label: 'Descripción',
            value:
                expenseTitle(
                    expense,
                ),
            wide: true,
        },
    ];
};
</script>

<template>
    <Head title="Gastos" />

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
                            <ArrowDownLeft
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#e84657]"
                            >
                                Finanzas
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Gastos
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Control de egresos y costos del negocio.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/expenses/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#e84657] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Nuevo gasto
                    </Link>
                </div>
            </section>

            <div
                class="grid grid-cols-2 gap-3 md:max-w-md"
            >
                <div
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase text-white/30"
                    >
                        Registros
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ expenses.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-[#e84657]/15 bg-[#e84657]/[0.05] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase text-[#f06472]"
                    >
                        Total
                    </p>

                    <p
                        class="mt-1 text-lg font-black text-[#f06472]"
                    >
                        {{ money(totalExpenses) }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        expense in expenses
                    "
                    :key="expense.id"
                    :title="
                        expenseTitle(
                            expense,
                        )
                    "
                    :code="
                        expenseCode(
                            expense,
                        )
                    "
                    :subtitle="
                        money(
                            amount(expense),
                        )
                    "
                    status="Egreso"
                    status-tone="danger"
                    :fields="
                        fieldsFor(
                            expense,
                        )
                    "
                    :expanded="
                        isExpanded(
                            expense.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            expense.id,
                        )
                    "
                >
                    <template #actions>
                        <Link
                            :href="
                                `/expenses/${expense.id}/edit`
                            "
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-[#e84657]/20 bg-[#e84657]/[0.06] text-sm font-black text-[#f06472]"
                        >
                            <Pencil
                                class="h-4 w-4"
                            />

                            Editar gasto
                        </Link>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- DESKTOP -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-left">
                                Referencia
                            </th>

                            <th class="px-5 py-4 text-left">
                                Concepto
                            </th>

                            <th class="px-5 py-4 text-left">
                                Fecha
                            </th>

                            <th class="px-5 py-4 text-left">
                                Categoría
                            </th>

                            <th class="px-5 py-4 text-right">
                                Monto
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
                            v-for="
                                expense in expenses
                            "
                            :key="expense.id"
                        >
                            <td
                                class="px-5 py-4 font-black text-[#22c6d2]"
                            >
                                {{ expenseCode(expense) }}
                            </td>

                            <td
                                class="px-5 py-4 font-bold"
                            >
                                {{ expenseTitle(expense) }}
                            </td>

                            <td class="px-5 py-4">
                                {{
                                    pick(
                                        expense,
                                        [
                                            'expense_date',
                                            'date',
                                            'created_at',
                                        ],
                                        '—',
                                    )
                                }}
                            </td>

                            <td class="px-5 py-4">
                                {{
                                    pick(
                                        expense,
                                        [
                                            'category',
                                            'category_name',
                                        ],
                                        '—',
                                    )
                                }}
                            </td>

                            <td
                                class="px-5 py-4 text-right text-base font-black text-[#f06472]"
                            >
                                {{
                                    money(
                                        amount(expense),
                                    )
                                }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="
                                        `/expenses/${expense.id}/edit`
                                    "
                                    class="inline-flex rounded-xl border border-white/10 p-2.5"
                                >
                                    <Pencil
                                        class="h-4 w-4"
                                    />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </AppLayout>
</template>