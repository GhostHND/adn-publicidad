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
    ArrowUpRight,
    Banknote,
    Pencil,
    Plus,
} from '@lucide/vue';
import { computed } from 'vue';

type Income = Record<string, any>;

type PaginatedData = {
    data?: Income[];
};

const props = defineProps<{
    incomes?:
        Income[]
        | PaginatedData;

    items?:
        Income[]
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
        title: 'Ingresos',
        href: '/income',
    },
];

const incomes =
    computed<Income[]>(() => {
        const source =
            props.incomes
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
    item: Income,
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
    income: Income,
): number => {
    return Number(
        pick(
            income,
            [
                'amount',
                'total',
                'value',
            ],
            0,
        ),
    );
};

const incomeTitle = (
    income: Income,
): string => {
    return String(
        pick(
            income,
            [
                'description',
                'concept',
                'source',
                'category',
            ],
            'Ingreso registrado',
        ),
    );
};

const incomeCode = (
    income: Income,
): string => {
    return String(
        pick(
            income,
            [
                'income_number',
                'reference',
                'code',
            ],
            `ING-${String(
                income.id,
            ).padStart(4, '0')}`,
        ),
    );
};

const totalIncome =
    computed(() => {
        const explicit =
            pick(
                props.stats
                ?? props.summary
                ?? {},
                [
                    'total',
                    'total_income',
                    'income',
                ],
                null,
            );

        if (
            explicit !== null
        ) {
            return Number(explicit);
        }

        return incomes.value
            .reduce(
                (total, income) =>
                    total + amount(income),
                0,
            );
    });

const fieldsFor = (
    income: Income,
): MobileRecordField[] => {
    return [
        {
            label: 'Monto',
            value:
                money(
                    amount(income),
                ),
            wide: true,
        },
        {
            label: 'Fecha',
            value:
                pick(
                    income,
                    [
                        'income_date',
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
                    income,
                    [
                        'category',
                        'category_name',
                    ],
                    '—',
                ),
        },
        {
            label: 'Cuenta',
            value:
                pick(
                    income,
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
            label: 'Referencia',
            value:
                pick(
                    income,
                    [
                        'reference',
                        'document_number',
                    ],
                    '—',
                ),
            copyable: true,
        },
        {
            label: 'Descripción',
            value:
                incomeTitle(income),
            wide: true,
        },
    ];
};
</script>

<template>
    <Head title="Ingresos" />

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
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400"
                        >
                            <ArrowUpRight
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-400"
                            >
                                Finanzas
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Ingresos
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Entradas de dinero registradas en ADN Publicidad.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/income/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-black text-black"
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Nuevo ingreso
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
                        {{ incomes.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-emerald-500/15 bg-emerald-500/[0.05] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase text-emerald-400"
                    >
                        Total
                    </p>

                    <p
                        class="mt-1 text-lg font-black text-emerald-400"
                    >
                        {{ money(totalIncome) }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        income in incomes
                    "
                    :key="income.id"
                    :title="
                        incomeTitle(income)
                    "
                    :code="
                        incomeCode(income)
                    "
                    :subtitle="
                        money(
                            amount(income),
                        )
                    "
                    status="Ingreso"
                    status-tone="success"
                    :fields="
                        fieldsFor(income)
                    "
                    :expanded="
                        isExpanded(income.id)
                    "
                    @toggle="
                        toggleCard(income.id)
                    "
                >
                    <template #actions>
                        <Link
                            :href="
                                `/income/${income.id}/edit`
                            "
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.06] text-sm font-black text-emerald-400"
                        >
                            <Pencil
                                class="h-4 w-4"
                            />

                            Editar ingreso
                        </Link>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ESCRITORIO -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <table
                    class="w-full text-sm"
                >
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
                                Cuenta
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
                                income in incomes
                            "
                            :key="income.id"
                        >
                            <td
                                class="px-5 py-4 font-black text-[#22c6d2]"
                            >
                                {{ incomeCode(income) }}
                            </td>

                            <td
                                class="px-5 py-4 font-bold"
                            >
                                {{ incomeTitle(income) }}
                            </td>

                            <td class="px-5 py-4">
                                {{
                                    pick(
                                        income,
                                        [
                                            'income_date',
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
                                        income,
                                        [
                                            'financial_account',
                                            'account_name',
                                            'account',
                                        ],
                                        '—',
                                    )
                                }}
                            </td>

                            <td
                                class="px-5 py-4 text-right text-base font-black text-emerald-400"
                            >
                                {{
                                    money(
                                        amount(income),
                                    )
                                }}
                            </td>

                            <td class="px-5 py-4 text-right">
                                <Link
                                    :href="
                                        `/income/${income.id}/edit`
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