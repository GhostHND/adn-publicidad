<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useLeadNotifications } from '@/composables/useLeadNotifications';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowRight,
    ArrowUpRight,
    Banknote,
    BellRing,
    Boxes,
    BriefcaseBusiness,
    CircleDollarSign,
    ClipboardCheck,
    FilePlus2,
    FileText,
    HandCoins,
    RefreshCw,
    ShoppingCart,
    UsersRound,
    WalletCards,
} from '@lucide/vue';
import {
    computed,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
} from 'vue';

type DashboardMetrics = {
    money: {
        today_income: number;
        today_expenses: number;
        today_balance: number;
        today_sales: number;
        month_income: number;
        month_expenses: number;
        month_balance: number;
        receivable_balance: number;
    };

    counts: {
        clients: number;
        new_leads: number;
        quotations_active: number;
        work_orders_active: number;
    };

    generated_at: string | null;
};

const {
    newCount,
} =
    useLeadNotifications();

const breadcrumbs = [
    {
        title:
            'Dashboard',

        href:
            '/dashboard',
    },
];

const metrics =
    reactive<DashboardMetrics>({
        money: {
            today_income: 0,
            today_expenses: 0,
            today_balance: 0,
            today_sales: 0,
            month_income: 0,
            month_expenses: 0,
            month_balance: 0,
            receivable_balance: 0,
        },

        counts: {
            clients: 0,
            new_leads: 0,
            quotations_active: 0,
            work_orders_active: 0,
        },

        generated_at: null,
    });

const loading =
    ref(false);

let intervalId:
    ReturnType<typeof setInterval>
    | null = null;

const currency = (
    value: number,
): string => {
    return new Intl
        .NumberFormat(
            'es-HN',
            {
                style:
                    'currency',

                currency:
                    'HNL',

                minimumFractionDigits:
                    2,

                maximumFractionDigits:
                    2,
            },
        )
        .format(
            Number(
                value ?? 0,
            ),
        );
};

const currentDate =
    computed(() => {
        return new Intl
            .DateTimeFormat(
                'es-HN',
                {
                    weekday:
                        'long',

                    day:
                        'numeric',

                    month:
                        'long',

                    year:
                        'numeric',
                },
            )
            .format(
                new Date(),
            );
    });

const greeting =
    computed(() => {
        const hour =
            new Date()
                .getHours();

        if (
            hour < 12
        ) {
            return 'Buenos días';
        }

        if (
            hour < 18
        ) {
            return 'Buenas tardes';
        }

        return 'Buenas noches';
    });

const loadMetrics =
    async (): Promise<void> => {
        loading.value =
            true;

        try {
            const response =
                await fetch(
                    '/dashboard/metrics',
                    {
                        headers: {
                            Accept:
                                'application/json',
                        },

                        credentials:
                            'same-origin',

                        cache:
                            'no-store',
                    },
                );

            if (
                !response.ok
            ) {
                return;
            }

            const data =
                await response.json();

            Object.assign(
                metrics.money,
                data.money ?? {},
            );

            Object.assign(
                metrics.counts,
                data.counts ?? {},
            );

            metrics.generated_at =
                data.generated_at
                ?? null;
        } finally {
            loading.value =
                false;
        }
    };

const shortcuts = [
    {
        title:
            'Nueva cotización',

        text:
            'Crear propuesta',

        href:
            '/quotations/create',

        icon:
            FilePlus2,
    },

    {
        title:
            'Solicitudes',

        text:
            'Clientes potenciales',

        href:
            '/website/leads',

        icon:
            BellRing,
    },

    {
        title:
            'Órdenes de trabajo',

        text:
            'Producción activa',

        href:
            '/work-orders',

        icon:
            ClipboardCheck,
    },

    {
        title:
            'Inventario',

        text:
            'Materiales y reservas',

        href:
            '/inventory',

        icon:
            Boxes,
    },

    {
        title:
            'Ventas',

        text:
            'Movimientos comerciales',

        href:
            '/sales',

        icon:
            ShoppingCart,
    },

    {
        title:
            'Clientes',

        text:
            'Base comercial',

        href:
            '/clients',

        icon:
            UsersRound,
    },
];

onMounted(
    () => {
        loadMetrics();

        intervalId =
            setInterval(
                loadMetrics,
                30000,
            );
    },
);

onBeforeUnmount(
    () => {
        if (
            intervalId
        ) {
            clearInterval(
                intervalId,
            );
        }
    },
);
</script>

<template>
    <Head
        title="Dashboard"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="relative w-full min-w-0 flex-1 px-4 py-5 sm:px-6 lg:px-8"
        >
            <div
                class="mx-auto w-full max-w-[1800px] space-y-6"
            >
                <!-- HERO -->

                <section
                    class="adn-glass adn-enter relative overflow-hidden rounded-[2rem] p-6 sm:p-8 lg:p-10"
                >
                    <div
                        class="pointer-events-none absolute -right-24 -top-28 h-96 w-96 rounded-full bg-[#0fa7b4]/15 blur-[90px]"
                    />

                    <div
                        class="pointer-events-none absolute -bottom-32 right-[28%] h-80 w-80 rounded-full bg-[#e84657]/10 blur-[100px]"
                    />

                    <div
                        class="relative grid items-center gap-8 lg:grid-cols-[1fr_230px]"
                    >
                        <div>
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-[#0fa7b4]/20 bg-[#0fa7b4]/8 px-3 py-1.5 text-xs font-bold text-[#22c6d2]"
                            >
                                <span
                                    class="adn-pulse-dot h-2 w-2 rounded-full bg-emerald-500"
                                />

                                Centro de operaciones
                            </div>

                            <p
                                class="mt-6 text-sm font-semibold capitalize text-muted-foreground"
                            >
                                {{ currentDate }}
                            </p>

                            <h1
                                class="mt-2 max-w-5xl text-3xl font-black tracking-tight sm:text-4xl xl:text-5xl"
                            >
                                {{ greeting }}.

                                <span
                                    class="adn-gradient-text"
                                >
                                    ADN Publicidad
                                </span>
                            </h1>

                            <p
                                class="mt-4 max-w-3xl text-sm leading-7 text-muted-foreground sm:text-base"
                            >
                                Una vista completa de ventas, ingresos, egresos,
                                solicitudes, producción y cuentas pendientes.
                            </p>

                            <div
                                class="mt-7 flex flex-wrap gap-3"
                            >
                                <Link
                                    href="/quotations/create"
                                    class="adn-shine inline-flex items-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-bold text-white shadow-[0_10px_30px_rgba(15,167,180,0.25)] hover:-translate-y-0.5"
                                >
                                    <FilePlus2
                                        class="h-4 w-4"
                                    />

                                    Nueva cotización
                                </Link>

                                <Link
                                    href="/website/leads"
                                    class="inline-flex items-center gap-2 rounded-xl border border-[#e84657]/25 bg-[#e84657]/5 px-5 py-3 text-sm font-bold text-[#f25c6b] hover:-translate-y-0.5 hover:bg-[#e84657]/10"
                                >
                                    <BellRing
                                        class="h-4 w-4"
                                    />

                                    Solicitudes

                                    <span
                                        v-if="
                                            newCount > 0
                                        "
                                        class="rounded-full bg-[#e84657] px-2 py-0.5 text-[10px] text-white"
                                    >
                                        {{ newCount }}
                                    </span>
                                </Link>

                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-3 text-sm font-semibold text-muted-foreground hover:bg-white/5 hover:text-white"
                                    @click="
                                        loadMetrics
                                    "
                                >
                                    <RefreshCw
                                        class="h-4 w-4"
                                        :class="
                                            loading
                                                ? 'animate-spin'
                                                : ''
                                        "
                                    />

                                    Actualizar
                                </button>
                            </div>
                        </div>

                        <div
                            class="relative mx-auto hidden lg:block"
                        >
                            <div
                                class="absolute inset-0 scale-125 rounded-full bg-[#0fa7b4]/15 blur-3xl"
                            />

                            <div
                                class="adn-shine relative flex h-48 w-48 items-center justify-center rounded-[2.4rem] border border-white/10 bg-white/[0.94] p-7 shadow-[0_25px_80px_rgba(15,167,180,0.18)]"
                            >
                                <img
                                    src="/icons/logo_adn.svg"
                                    alt="ADN Publicidad"
                                    class="h-full w-full object-contain"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- DINERO HOY -->

                <section
                    class="adn-enter adn-enter-delay-1"
                >
                    <div
                        class="mb-4 flex items-center justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Finanzas
                            </p>

                            <h2
                                class="mt-1 text-xl font-black"
                            >
                                Resumen de hoy
                            </h2>
                        </div>

                        <Link
                            href="/reports"
                            class="inline-flex items-center gap-1 text-xs font-bold text-[#0fa7b4]"
                        >
                            Ver reportes

                            <ArrowRight
                                class="h-3.5 w-3.5"
                            />
                        </Link>
                    </div>

                    <div
                        class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <article
                            class="adn-card p-5"
                        >
                            <div
                                class="flex items-start justify-between"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400"
                                >
                                    <ArrowUpRight
                                        class="h-5 w-5"
                                    />
                                </div>

                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-emerald-400"
                                >
                                    Ingresos
                                </span>
                            </div>

                            <p
                                class="mt-5 text-xs font-semibold text-muted-foreground"
                            >
                                Ingresos de hoy
                            </p>

                            <p
                                class="mt-1 text-2xl font-black"
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .today_income,
                                    )
                                }}
                            </p>
                        </article>

                        <article
                            class="adn-card p-5"
                        >
                            <div
                                class="flex items-start justify-between"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#e84657]/10 text-[#e84657]"
                                >
                                    <ArrowDownRight
                                        class="h-5 w-5"
                                    />
                                </div>

                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-[#e84657]"
                                >
                                    Egresos
                                </span>
                            </div>

                            <p
                                class="mt-5 text-xs font-semibold text-muted-foreground"
                            >
                                Egresos de hoy
                            </p>

                            <p
                                class="mt-1 text-2xl font-black"
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .today_expenses,
                                    )
                                }}
                            </p>
                        </article>

                        <article
                            class="adn-card p-5"
                        >
                            <div
                                class="flex items-start justify-between"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                                >
                                    <WalletCards
                                        class="h-5 w-5"
                                    />
                                </div>

                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-[#0fa7b4]"
                                >
                                    Balance
                                </span>
                            </div>

                            <p
                                class="mt-5 text-xs font-semibold text-muted-foreground"
                            >
                                Balance de hoy
                            </p>

                            <p
                                class="mt-1 text-2xl font-black"
                                :class="
                                    metrics.money
                                        .today_balance < 0
                                        ? 'text-[#e84657]'
                                        : 'text-emerald-400'
                                "
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .today_balance,
                                    )
                                }}
                            </p>
                        </article>

                        <article
                            class="adn-card p-5"
                        >
                            <div
                                class="flex items-start justify-between"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400"
                                >
                                    <ShoppingCart
                                        class="h-5 w-5"
                                    />
                                </div>

                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-violet-400"
                                >
                                    Ventas
                                </span>
                            </div>

                            <p
                                class="mt-5 text-xs font-semibold text-muted-foreground"
                            >
                                Ventas registradas hoy
                            </p>

                            <p
                                class="mt-1 text-2xl font-black"
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .today_sales,
                                    )
                                }}
                            </p>
                        </article>
                    </div>
                </section>

                <!-- MES + CXC -->

                <section
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <Link
                        href="/income"
                        class="adn-card flex items-center gap-4 p-5"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400"
                        >
                            <Banknote
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Ingresos del mes
                            </p>

                            <p
                                class="mt-1 text-xl font-black"
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .month_income,
                                    )
                                }}
                            </p>
                        </div>
                    </Link>

                    <Link
                        href="/expenses"
                        class="adn-card flex items-center gap-4 p-5"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#e84657]/10 text-[#e84657]"
                        >
                            <CircleDollarSign
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Egresos del mes
                            </p>

                            <p
                                class="mt-1 text-xl font-black"
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .month_expenses,
                                    )
                                }}
                            </p>
                        </div>
                    </Link>

                    <Link
                        href="/cash"
                        class="adn-card flex items-center gap-4 p-5"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <WalletCards
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Balance del mes
                            </p>

                            <p
                                class="mt-1 text-xl font-black"
                                :class="
                                    metrics.money
                                        .month_balance < 0
                                        ? 'text-[#e84657]'
                                        : 'text-emerald-400'
                                "
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .month_balance,
                                    )
                                }}
                            </p>
                        </div>
                    </Link>

                    <Link
                        href="/accounts-receivable"
                        class="adn-card flex items-center gap-4 p-5"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-400"
                        >
                            <HandCoins
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Cuentas por cobrar
                            </p>

                            <p
                                class="mt-1 text-xl font-black"
                            >
                                {{
                                    currency(
                                        metrics.money
                                            .receivable_balance,
                                    )
                                }}
                            </p>
                        </div>
                    </Link>
                </section>

                <!-- OPERACIÓN -->

                <section
                    class="adn-enter adn-enter-delay-2"
                >
                    <div
                        class="mb-4"
                    >
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-[#e84657]"
                        >
                            Operación
                        </p>

                        <h2
                            class="mt-1 text-xl font-black"
                        >
                            Estado del negocio
                        </h2>
                    </div>

                    <div
                        class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <Link
                            href="/website/leads"
                            class="adn-card p-5"
                        >
                            <BellRing
                                class="h-5 w-5 text-[#e84657]"
                            />

                            <p
                                class="mt-4 text-xs text-muted-foreground"
                            >
                                Solicitudes nuevas
                            </p>

                            <p
                                class="mt-1 text-3xl font-black"
                            >
                                {{
                                    newCount
                                }}
                            </p>
                        </Link>

                        <Link
                            href="/clients"
                            class="adn-card p-5"
                        >
                            <UsersRound
                                class="h-5 w-5 text-[#0fa7b4]"
                            />

                            <p
                                class="mt-4 text-xs text-muted-foreground"
                            >
                                Clientes activos
                            </p>

                            <p
                                class="mt-1 text-3xl font-black"
                            >
                                {{
                                    metrics.counts
                                        .clients
                                }}
                            </p>
                        </Link>

                        <Link
                            href="/quotations"
                            class="adn-card p-5"
                        >
                            <FileText
                                class="h-5 w-5 text-[#0fa7b4]"
                            />

                            <p
                                class="mt-4 text-xs text-muted-foreground"
                            >
                                Cotizaciones
                            </p>

                            <p
                                class="mt-1 text-3xl font-black"
                            >
                                {{
                                    metrics.counts
                                        .quotations_active
                                }}
                            </p>
                        </Link>

                        <Link
                            href="/work-orders"
                            class="adn-card p-5"
                        >
                            <BriefcaseBusiness
                                class="h-5 w-5 text-[#e84657]"
                            />

                            <p
                                class="mt-4 text-xs text-muted-foreground"
                            >
                                Trabajos activos
                            </p>

                            <p
                                class="mt-1 text-3xl font-black"
                            >
                                {{
                                    metrics.counts
                                        .work_orders_active
                                }}
                            </p>
                        </Link>
                    </div>
                </section>

                <!-- ACCESOS -->

                <section
                    class="adn-enter adn-enter-delay-3 pb-8"
                >
                    <p
                        class="text-xs font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                    >
                        Accesos rápidos
                    </p>

                    <div
                        class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <Link
                            v-for="
                                item in shortcuts
                            "
                            :key="
                                item.href
                            "
                            :href="
                                item.href
                            "
                            class="adn-card group flex items-center gap-4 p-5"
                        >
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                            >
                                <component
                                    :is="
                                        item.icon
                                    "
                                    class="h-5 w-5"
                                />
                            </div>

                            <div
                                class="min-w-0 flex-1"
                            >
                                <p
                                    class="font-bold"
                                >
                                    {{
                                        item.title
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    {{
                                        item.text
                                    }}
                                </p>
                            </div>

                            <ArrowRight
                                class="h-4 w-4 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-[#0fa7b4]"
                            />
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>