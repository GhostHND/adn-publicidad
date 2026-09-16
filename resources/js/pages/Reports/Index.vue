<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    router,
} from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowUpRight,
    Banknote,
    ChartNoAxesCombined,
    HandCoins,
    RefreshCw,
    ShoppingCart,
    WalletCards,
} from '@lucide/vue';
import {
    computed,
    ref,
} from 'vue';

const props = defineProps<{
    summary?: Record<string, any>;
    stats?: Record<string, any>;
    report?: Record<string, any>;
    filters?: Record<string, any>;
}>();

const breadcrumbs = [
    {
        title: 'Reportes',
        href: '/reports',
    },
];

const dateFrom =
    ref(
        props.filters?.date_from
        ?? '',
    );

const dateTo =
    ref(
        props.filters?.date_to
        ?? '',
    );

const source =
    computed<Record<string, any>>(
        () => ({
            ...(props.report ?? {}),
            ...(props.summary ?? {}),
            ...(props.stats ?? {}),
        }),
    );

const value = (
    keys: string[],
): number => {
    for (const key of keys) {
        const result =
            source.value?.[key];

        if (
            result !== null
            &&
            result !== undefined
            &&
            result !== ''
        ) {
            return Number(result);
        }
    }

    return 0;
};

const income =
    computed(() =>
        value([
            'income',
            'total_income',
            'incomes',
        ]),
    );

const expenses =
    computed(() =>
        value([
            'expenses',
            'total_expenses',
            'expense',
        ]),
    );

const sales =
    computed(() =>
        value([
            'sales',
            'total_sales',
            'sales_total',
        ]),
    );

const receivable =
    computed(() =>
        value([
            'receivable',
            'accounts_receivable',
            'receivable_balance',
            'pending_receivable',
        ]),
    );

const balance =
    computed(() => {
        const explicit =
            value([
                'balance',
                'net_balance',
                'profit',
            ]);

        if (
            explicit !== 0
        ) {
            return explicit;
        }

        return (
            income.value
            -
            expenses.value
        );
    });

const money = (
    amount: number,
): string => {
    return `L ${Number(
        amount ?? 0,
    ).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
};

const applyFilters =
    (): void => {
        router.get(
            '/reports',
            {
                date_from:
                    dateFrom.value
                    || undefined,

                date_to:
                    dateTo.value
                    || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

const clearFilters =
    (): void => {
        dateFrom.value = '';
        dateTo.value = '';

        router.get(
            '/reports',
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };
</script>

<template>
    <Head title="Reportes" />

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
                    class="pointer-events-none absolute -right-20 -top-20 h-52 w-52 rounded-full bg-[#0fa7b4]/10 blur-3xl"
                />

                <div
                    class="relative flex items-start gap-4"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                    >
                        <ChartNoAxesCombined
                            class="h-5 w-5"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                        >
                            Análisis
                        </p>

                        <h1
                            class="mt-1 text-2xl font-black sm:text-3xl"
                        >
                            Reportes
                        </h1>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Resumen financiero y comercial de ADN Publicidad.
                        </p>
                    </div>
                </div>
            </section>

            <!-- FILTROS -->

            <section
                class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
            >
                <div
                    class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[220px_220px_auto_auto]"
                >
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                    />

                    <input
                        v-model="dateTo"
                        type="date"
                        class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                    />

                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 text-sm font-black text-white"
                        @click="applyFilters"
                    >
                        <RefreshCw
                            class="h-4 w-4"
                        />

                        Aplicar
                    </button>

                    <button
                        type="button"
                        class="h-11 rounded-xl border border-white/10 px-5 text-sm font-bold"
                        @click="clearFilters"
                    >
                        Limpiar
                    </button>
                </div>
            </section>

            <!-- RESUMEN PRINCIPAL -->

            <section
                class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
            >
                <article
                    class="adn-card p-5"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400"
                    >
                        <ArrowUpRight
                            class="h-5 w-5"
                        />
                    </div>

                    <p
                        class="mt-4 text-xs font-semibold text-muted-foreground"
                    >
                        Ingresos
                    </p>

                    <p
                        class="mt-1 text-2xl font-black text-emerald-400"
                    >
                        {{ money(income) }}
                    </p>
                </article>

                <article
                    class="adn-card p-5"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#e84657]/10 text-[#f06472]"
                    >
                        <ArrowDownRight
                            class="h-5 w-5"
                        />
                    </div>

                    <p
                        class="mt-4 text-xs font-semibold text-muted-foreground"
                    >
                        Egresos
                    </p>

                    <p
                        class="mt-1 text-2xl font-black text-[#f06472]"
                    >
                        {{ money(expenses) }}
                    </p>
                </article>

                <article
                    class="adn-card p-5"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400"
                    >
                        <ShoppingCart
                            class="h-5 w-5"
                        />
                    </div>

                    <p
                        class="mt-4 text-xs font-semibold text-muted-foreground"
                    >
                        Ventas
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ money(sales) }}
                    </p>
                </article>

                <article
                    class="adn-card p-5"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400"
                    >
                        <HandCoins
                            class="h-5 w-5"
                        />
                    </div>

                    <p
                        class="mt-4 text-xs font-semibold text-muted-foreground"
                    >
                        Cuentas por cobrar
                    </p>

                    <p
                        class="mt-1 text-2xl font-black text-amber-400"
                    >
                        {{ money(receivable) }}
                    </p>
                </article>
            </section>

            <!-- BALANCE -->

            <section
                class="relative overflow-hidden rounded-[1.7rem] border border-[#0fa7b4]/15 bg-[#091317] p-6"
            >
                <div
                    class="pointer-events-none absolute -right-24 top-1/2 h-56 w-56 -translate-y-1/2 rounded-full bg-[#0fa7b4]/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="flex items-center gap-4"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <WalletCards
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold text-muted-foreground"
                            >
                                Balance del período
                            </p>

                            <p
                                class="mt-1 text-3xl font-black"
                                :class="
                                    balance < 0
                                        ? 'text-[#f06472]'
                                        : 'text-emerald-400'
                                "
                            >
                                {{ money(balance) }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-white/[0.06] bg-black/15 px-5 py-4"
                    >
                        <div
                            class="flex items-center gap-2 text-xs text-white/40"
                        >
                            <Banknote
                                class="h-4 w-4 text-[#0fa7b4]"
                            />

                            Ingresos − Egresos
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>