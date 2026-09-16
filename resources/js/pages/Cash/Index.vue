<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowDownLeft,
    ArrowUpCircle,
    ArrowUpRight,
    Banknote,
    CreditCard,
    History,
    Landmark,
    ReceiptText,
    Wallet,
    WalletCards,
} from '@lucide/vue';

defineProps<{
    session: any | null;

    summary: any | null;

    movements: any[];

    financialAccounts: any[];

    digitalSummary: any;

    lastClosedSession: any | null;
}>();

const breadcrumbs = [
    {
        title: 'Caja',
        href: '/cash',
    },
];

/*
|--------------------------------------------------------------------------
| APERTURA DE CAJA
|--------------------------------------------------------------------------
*/

const openForm = useForm({
    opening_balance: 0,
    opening_notes: '',
});

const openCash = (): void => {
    openForm.post(
        '/cash/open',
        {
            preserveScroll: true,
        },
    );
};

/*
|--------------------------------------------------------------------------
| MOVIMIENTO MANUAL
|--------------------------------------------------------------------------
*/

const movementForm = useForm({
    movement_type: 'income',
    amount: '',
    description: '',
    reference: '',
});

const addMovement = (): void => {
    movementForm.post(
        '/cash/movements',
        {
            preserveScroll: true,

            onSuccess: () => {
                movementForm.reset(
                    'amount',
                    'description',
                    'reference',
                );
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| CIERRE DE CAJA
|--------------------------------------------------------------------------
*/

const closeForm = useForm({
    closing_balance: '',
    closing_notes: '',
});

const closeCash = (): void => {
    closeForm.post(
        '/cash/close',
        {
            preserveScroll: true,
        },
    );
};

/*
|--------------------------------------------------------------------------
| FORMATO MONETARIO
|--------------------------------------------------------------------------
*/

const money = (
    value:
        string
        | number
        | null
        | undefined,
): string => {
    return `L ${Number(
        value || 0,
    ).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
};

const movementTypeLabel = (
    value: string,
): string => {
    const labels:
        Record<string, string> = {
        income:
            'Entrada',

        expense:
            'Salida',

        adjustment_in:
            'Ajuste positivo',

        adjustment_out:
            'Ajuste negativo',
    };

    return labels[value]
        ?? value;
};
</script>

<template>
    <Head
        title="Caja y cuentas"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="w-full space-y-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ========================================================= -->
            <!-- CABECERA -->
            <!-- ========================================================= -->

            <section
                class="adn-enter relative overflow-hidden rounded-[1.8rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-7"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#0fa7b4]/10 blur-3xl"
                />

                <div
                    class="pointer-events-none absolute -bottom-24 right-[28%] h-52 w-52 rounded-full bg-[#e84657]/5 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
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
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Control financiero
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Caja y cuentas
                            </h1>

                            <p
                                class="mt-2 text-sm text-muted-foreground"
                            >
                                Control de efectivo, cuentas bancarias y movimientos financieros.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/cash/history"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.025] px-5 text-sm font-black text-white/80 transition hover:border-[#0fa7b4]/25 hover:bg-[#0fa7b4]/5 hover:text-white"
                    >
                        <History
                            class="h-4 w-4 text-[#22c6d2]"
                        />

                        Historial de caja
                    </Link>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- CUENTAS FINANCIERAS -->
            <!-- ========================================================= -->

            <section>
                <div
                    class="mb-4 flex items-center gap-3"
                >
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                    >
                        <Landmark
                            class="h-4 w-4"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[9px] font-black uppercase tracking-[0.16em] text-[#0fa7b4]"
                        >
                            Bancos y cuentas
                        </p>

                        <h2
                            class="mt-0.5 text-lg font-black"
                        >
                            Cuentas financieras
                        </h2>
                    </div>
                </div>

                <!--
                =============================================================
                MÓVIL Y ESCRITORIO

                Aquí NO ocultamos información.

                En teléfono las cuentas simplemente se apilan.
                En escritorio forman columnas.
                =============================================================
                -->

                <div
                    class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="
                            account in financialAccounts
                        "
                        :key="
                            account.id
                        "
                        class="relative overflow-hidden rounded-[1.45rem] border border-white/[0.075] bg-[#091317] p-4 sm:p-5"
                    >
                        <div
                            class="pointer-events-none absolute -right-12 -top-14 h-36 w-36 rounded-full bg-[#0fa7b4]/7 blur-3xl"
                        />

                        <!-- BANCO -->

                        <div
                            class="relative flex items-start justify-between gap-4"
                        >
                            <div
                                class="min-w-0"
                            >
                                <p
                                    class="text-[9px] font-black uppercase tracking-[0.14em] text-[#0fa7b4]"
                                >
                                    {{
                                        account.code
                                    }}
                                </p>

                                <h3
                                    class="mt-1 break-words text-lg font-black sm:text-xl"
                                >
                                    {{
                                        account.name
                                    }}
                                </h3>
                            </div>

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                            >
                                <Landmark
                                    class="h-4 w-4"
                                />
                            </div>
                        </div>

                        <!-- MOVIMIENTOS -->

                        <div
                            class="relative mt-5 space-y-2.5"
                        >
                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-emerald-500/[0.08] bg-emerald-500/[0.025] px-3 py-2.5"
                            >
                                <div
                                    class="flex items-center gap-2"
                                >
                                    <ArrowUpRight
                                        class="h-3.5 w-3.5 shrink-0 text-emerald-400"
                                    />

                                    <span
                                        class="text-[11px] text-white/45"
                                    >
                                        Cobros de ventas
                                    </span>
                                </div>

                                <span
                                    class="shrink-0 text-xs font-black text-emerald-400"
                                >
                                    {{
                                        money(
                                            account.sales_income,
                                        )
                                    }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-emerald-500/[0.08] bg-emerald-500/[0.025] px-3 py-2.5"
                            >
                                <div
                                    class="flex items-center gap-2"
                                >
                                    <ArrowUpCircle
                                        class="h-3.5 w-3.5 shrink-0 text-emerald-400"
                                    />

                                    <span
                                        class="text-[11px] text-white/45"
                                    >
                                        Otros ingresos
                                    </span>
                                </div>

                                <span
                                    class="shrink-0 text-xs font-black text-emerald-400"
                                >
                                    {{
                                        money(
                                            account.other_income,
                                        )
                                    }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-[#e84657]/[0.08] bg-[#e84657]/[0.025] px-3 py-2.5"
                            >
                                <div
                                    class="flex items-center gap-2"
                                >
                                    <ArrowDownLeft
                                        class="h-3.5 w-3.5 shrink-0 text-[#f06472]"
                                    />

                                    <span
                                        class="text-[11px] text-white/45"
                                    >
                                        Gastos
                                    </span>
                                </div>

                                <span
                                    class="shrink-0 text-xs font-black text-[#f06472]"
                                >
                                    -
                                    {{
                                        money(
                                            account.expenses,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- NETO -->

                        <div
                            class="relative mt-4 flex items-center justify-between border-t border-white/[0.07] pt-4"
                        >
                            <span
                                class="text-xs font-bold text-white/60"
                            >
                                Movimiento neto
                            </span>

                            <span
                                class="text-sm font-black"
                                :class="
                                    Number(
                                        account.net_movement,
                                    ) >= 0
                                        ? 'text-emerald-400'
                                        : 'text-[#f06472]'
                                "
                            >
                                {{
                                    money(
                                        account.net_movement,
                                    )
                                }}
                            </span>
                        </div>

                        <!-- SALDO -->

                        <div
                            class="relative mt-4 rounded-2xl border border-[#0fa7b4]/10 bg-[#0fa7b4]/[0.045] p-4"
                        >
                            <p
                                class="text-[9px] font-black uppercase tracking-[0.12em] text-[#22c6d2]"
                            >
                                Saldo calculado
                            </p>

                            <p
                                class="mt-1 text-2xl font-black"
                                :class="
                                    Number(
                                        account.calculated_balance,
                                    ) < 0
                                        ? 'text-[#f06472]'
                                        : 'text-white'
                                "
                            >
                                {{
                                    money(
                                        account.calculated_balance,
                                    )
                                }}
                            </p>
                        </div>
                    </article>
                </div>

                <div
                    class="mt-3 rounded-xl border border-white/[0.05] bg-white/[0.015] px-4 py-3"
                >
                    <p
                        class="text-[10px] leading-5 text-white/35"
                    >
                        El saldo calculado parte de L 0.00 hasta que posteriormente configuremos los saldos iniciales reales de cada cuenta.
                    </p>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- MOVIMIENTOS ELECTRÓNICOS -->
            <!-- ========================================================= -->

            <section>
                <div
                    class="mb-4 flex items-center gap-3"
                >
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400"
                    >
                        <CreditCard
                            class="h-4 w-4"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[9px] font-black uppercase tracking-[0.16em] text-violet-400"
                        >
                            Canales digitales
                        </p>

                        <h2
                            class="mt-0.5 text-lg font-black"
                        >
                            Movimientos electrónicos
                        </h2>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-3 xl:grid-cols-4"
                >
                    <!-- TRANSFERENCIAS ENTRANTES -->

                    <article
                        class="rounded-2xl border border-emerald-500/10 bg-emerald-500/[0.035] p-4 sm:p-5"
                    >
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400"
                        >
                            <Landmark
                                class="h-4 w-4"
                            />
                        </div>

                        <p
                            class="mt-3 text-[10px] font-semibold leading-4 text-white/45"
                        >
                            Entradas por transferencia
                        </p>

                        <p
                            class="mt-2 break-all text-lg font-black text-emerald-400 sm:text-2xl"
                        >
                            {{
                                money(
                                    digitalSummary.transfer_in,
                                )
                            }}
                        </p>
                    </article>

                    <!-- TARJETAS ENTRANTES -->

                    <article
                        class="rounded-2xl border border-emerald-500/10 bg-emerald-500/[0.035] p-4 sm:p-5"
                    >
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400"
                        >
                            <CreditCard
                                class="h-4 w-4"
                            />
                        </div>

                        <p
                            class="mt-3 text-[10px] font-semibold leading-4 text-white/45"
                        >
                            Cobros con tarjeta
                        </p>

                        <p
                            class="mt-2 break-all text-lg font-black text-emerald-400 sm:text-2xl"
                        >
                            {{
                                money(
                                    digitalSummary.card_in,
                                )
                            }}
                        </p>
                    </article>

                    <!-- TRANSFERENCIAS SALIENTES -->

                    <article
                        class="rounded-2xl border border-[#e84657]/10 bg-[#e84657]/[0.035] p-4 sm:p-5"
                    >
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e84657]/10 text-[#f06472]"
                        >
                            <Landmark
                                class="h-4 w-4"
                            />
                        </div>

                        <p
                            class="mt-3 text-[10px] font-semibold leading-4 text-white/45"
                        >
                            Gastos por transferencia
                        </p>

                        <p
                            class="mt-2 break-all text-lg font-black text-[#f06472] sm:text-2xl"
                        >
                            {{
                                money(
                                    digitalSummary.transfer_out,
                                )
                            }}
                        </p>
                    </article>

                    <!-- TARJETAS SALIENTES -->

                    <article
                        class="rounded-2xl border border-[#e84657]/10 bg-[#e84657]/[0.035] p-4 sm:p-5"
                    >
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e84657]/10 text-[#f06472]"
                        >
                            <CreditCard
                                class="h-4 w-4"
                            />
                        </div>

                        <p
                            class="mt-3 text-[10px] font-semibold leading-4 text-white/45"
                        >
                            Gastos con tarjeta
                        </p>

                        <p
                            class="mt-2 break-all text-lg font-black text-[#f06472] sm:text-2xl"
                        >
                            {{
                                money(
                                    digitalSummary.card_out,
                                )
                            }}
                        </p>
                    </article>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- CAJA FÍSICA -->
            <!-- ========================================================= -->

            <section
                class="border-t border-white/[0.07] pt-6"
            >
                <div
                    class="mb-5 flex items-center gap-3"
                >
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400"
                    >
                        <Wallet
                            class="h-4 w-4"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[9px] font-black uppercase tracking-[0.16em] text-amber-400"
                        >
                            Efectivo
                        </p>

                        <h2
                            class="mt-0.5 text-lg font-black"
                        >
                            Caja física
                        </h2>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- CAJA CERRADA -->
                <!-- ===================================================== -->

                <template
                    v-if="
                        !session
                    "
                >
                    <article
                        class="mx-auto w-full max-w-2xl rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-7"
                    >
                        <div
                            class="flex items-start gap-3"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                            >
                                <Banknote
                                    class="h-4 w-4"
                                />
                            </div>

                            <div>
                                <h3
                                    class="text-xl font-black"
                                >
                                    Abrir caja
                                </h3>

                                <p
                                    class="mt-1 text-sm text-muted-foreground"
                                >
                                    Registra el efectivo inicial de la jornada.
                                </p>
                            </div>
                        </div>

                        <form
                            class="mt-6 space-y-4"
                            @submit.prevent="
                                openCash
                            "
                        >
                            <div>
                                <label
                                    class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                                >
                                    Efectivo inicial
                                </label>

                                <input
                                    v-model="
                                        openForm.opening_balance
                                    "
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-base font-bold"
                                />

                                <p
                                    v-if="
                                        openForm.errors.opening_balance
                                    "
                                    class="mt-1 text-xs text-[#f06472]"
                                >
                                    {{
                                        openForm.errors.opening_balance
                                    }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                                >
                                    Observaciones
                                </label>

                                <textarea
                                    v-model="
                                        openForm.opening_notes
                                    "
                                    rows="3"
                                    class="w-full rounded-xl border border-white/10 bg-black/15 px-4 py-3 text-sm"
                                />
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    openForm.processing
                                "
                                class="adn-shine flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white disabled:opacity-50"
                            >
                                <Wallet
                                    class="h-4 w-4"
                                />

                                {{
                                    openForm.processing
                                        ? 'Abriendo...'
                                        : 'Abrir caja'
                                }}
                            </button>
                        </form>
                    </article>
                </template>

                <!-- ===================================================== -->
                <!-- CAJA ABIERTA -->
                <!-- ===================================================== -->

                <template
                    v-else
                >
                    <!-- RESUMEN DE EFECTIVO -->

                    <div
                        class="grid grid-cols-2 gap-3 xl:grid-cols-4"
                    >
                        <article
                            class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4 sm:p-5"
                        >
                            <Banknote
                                class="h-4 w-4 text-[#22c6d2]"
                            />

                            <p
                                class="mt-3 text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Efectivo inicial
                            </p>

                            <p
                                class="mt-1 text-lg font-black sm:text-2xl"
                            >
                                {{
                                    money(
                                        summary?.opening_balance,
                                    )
                                }}
                            </p>
                        </article>

                        <article
                            class="rounded-2xl border border-emerald-500/10 bg-emerald-500/[0.035] p-4 sm:p-5"
                        >
                            <ReceiptText
                                class="h-4 w-4 text-emerald-400"
                            />

                            <p
                                class="mt-3 text-[9px] font-black uppercase tracking-wide text-emerald-400"
                            >
                                Cobros en efectivo
                            </p>

                            <p
                                class="mt-1 text-lg font-black text-emerald-400 sm:text-2xl"
                            >
                                {{
                                    money(
                                        summary?.cash_sales,
                                    )
                                }}
                            </p>
                        </article>

                        <article
                            class="rounded-2xl border border-[#e84657]/10 bg-[#e84657]/[0.035] p-4 sm:p-5"
                        >
                            <ArrowDownCircle
                                class="h-4 w-4 text-[#f06472]"
                            />

                            <p
                                class="mt-3 text-[9px] font-black uppercase tracking-wide text-[#f06472]"
                            >
                                Salidas
                            </p>

                            <p
                                class="mt-1 text-lg font-black text-[#f06472] sm:text-2xl"
                            >
                                {{
                                    money(
                                        summary?.manual_expense,
                                    )
                                }}
                            </p>
                        </article>

                        <article
                            class="col-span-2 rounded-2xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/[0.055] p-4 sm:p-5 xl:col-span-1"
                        >
                            <p
                                class="text-[9px] font-black uppercase tracking-wide text-[#22c6d2]"
                            >
                                Efectivo esperado
                            </p>

                            <p
                                class="mt-2 text-2xl font-black sm:text-3xl"
                            >
                                {{
                                    money(
                                        summary?.expected_cash,
                                    )
                                }}
                            </p>
                        </article>
                    </div>

                    <!-- MOVIMIENTOS + FORMULARIO -->

                    <div
                        class="mt-5 grid gap-5 xl:grid-cols-[1.6fr_.8fr]"
                    >
                        <!-- MOVIMIENTOS -->

                        <article
                            class="rounded-[1.5rem] border border-white/[0.07] bg-[#091317] p-4 sm:p-5"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-[9px] font-black uppercase tracking-wide text-[#0fa7b4]"
                                    >
                                        Jornada actual
                                    </p>

                                    <h3
                                        class="mt-1 text-lg font-black"
                                    >
                                        Movimientos de efectivo
                                    </h3>
                                </div>

                                <span
                                    class="rounded-full border border-white/10 bg-white/[0.025] px-3 py-1 text-[10px] font-black text-white/50"
                                >
                                    {{
                                        movements.length
                                    }}
                                </span>
                            </div>

                            <div
                                v-if="
                                    movements.length ===
                                    0
                                "
                                class="mt-5 rounded-xl border border-dashed border-white/10 p-8 text-center"
                            >
                                <Banknote
                                    class="mx-auto h-7 w-7 text-white/15"
                                />

                                <p
                                    class="mt-3 text-sm font-bold text-white/60"
                                >
                                    No hay movimientos
                                </p>

                                <p
                                    class="mt-1 text-xs text-white/30"
                                >
                                    Los movimientos de efectivo de esta jornada aparecerán aquí.
                                </p>
                            </div>

                            <div
                                v-else
                                class="mt-5 space-y-2"
                            >
                                <div
                                    v-for="
                                        movement in movements
                                    "
                                    :key="
                                        movement.id
                                    "
                                    class="rounded-xl border border-white/[0.06] bg-black/15 p-3.5 sm:flex sm:items-center sm:justify-between sm:gap-4"
                                >
                                    <div
                                        class="min-w-0"
                                    >
                                        <div
                                            class="flex items-center gap-2"
                                        >
                                            <ArrowUpCircle
                                                v-if="
                                                    movement.direction ===
                                                    'in'
                                                "
                                                class="h-3.5 w-3.5 shrink-0 text-emerald-400"
                                            />

                                            <ArrowDownCircle
                                                v-else
                                                class="h-3.5 w-3.5 shrink-0 text-[#f06472]"
                                            />

                                            <p
                                                class="text-xs font-black"
                                            >
                                                {{
                                                    movementTypeLabel(
                                                        movement.type,
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <p
                                            class="mt-1 break-words text-xs leading-5 text-white/40"
                                        >
                                            {{
                                                movement.description
                                                || 'Sin descripción'
                                            }}
                                        </p>

                                        <p
                                            v-if="
                                                movement.reference
                                            "
                                            class="mt-1 text-[10px] text-white/25"
                                        >
                                            Ref:
                                            {{
                                                movement.reference
                                            }}
                                        </p>
                                    </div>

                                    <p
                                        class="mt-3 shrink-0 text-lg font-black sm:mt-0"
                                        :class="
                                            movement.direction ===
                                            'in'
                                                ? 'text-emerald-400'
                                                : 'text-[#f06472]'
                                        "
                                    >
                                        {{
                                            movement.direction ===
                                            'in'
                                                ? '+'
                                                : '-'
                                        }}
                                        {{
                                            money(
                                                movement.amount,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </article>

                        <!-- MOVIMIENTO MANUAL -->

                        <article
                            class="rounded-[1.5rem] border border-white/[0.07] bg-[#091317] p-4 sm:p-5"
                        >
                            <p
                                class="text-[9px] font-black uppercase tracking-wide text-[#0fa7b4]"
                            >
                                Ajuste manual
                            </p>

                            <h3
                                class="mt-1 text-lg font-black"
                            >
                                Registrar movimiento
                            </h3>

                            <form
                                class="mt-5 space-y-3"
                                @submit.prevent="
                                    addMovement
                                "
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Tipo
                                    </label>

                                    <select
                                        v-model="
                                            movementForm.movement_type
                                        "
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                    >
                                        <option
                                            value="income"
                                        >
                                            Entrada
                                        </option>

                                        <option
                                            value="expense"
                                        >
                                            Salida
                                        </option>

                                        <option
                                            value="adjustment_in"
                                        >
                                            Ajuste positivo
                                        </option>

                                        <option
                                            value="adjustment_out"
                                        >
                                            Ajuste negativo
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Monto
                                    </label>

                                    <input
                                        v-model="
                                            movementForm.amount
                                        "
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm font-bold"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Descripción
                                    </label>

                                    <input
                                        v-model="
                                            movementForm.description
                                        "
                                        type="text"
                                        placeholder="Descripción del movimiento"
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Referencia
                                    </label>

                                    <input
                                        v-model="
                                            movementForm.reference
                                        "
                                        type="text"
                                        placeholder="Referencia opcional"
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    :disabled="
                                        movementForm.processing
                                    "
                                    class="adn-shine flex h-11 w-full items-center justify-center rounded-xl bg-[#0fa7b4] text-sm font-black text-white disabled:opacity-50"
                                >
                                    {{
                                        movementForm.processing
                                            ? 'Registrando...'
                                            : 'Registrar movimiento'
                                    }}
                                </button>
                            </form>
                        </article>
                    </div>

                    <!-- CIERRE DE CAJA -->

                    <article
                        class="mt-5 overflow-hidden rounded-[1.5rem] border border-[#e84657]/15 bg-[#091317]"
                    >
                        <div
                            class="h-[2px] bg-gradient-to-r from-[#e84657] via-[#e84657]/60 to-transparent"
                        />

                        <div
                            class="grid gap-6 p-5 lg:grid-cols-[.75fr_1.25fr] lg:p-6"
                        >
                            <div>
                                <p
                                    class="text-[9px] font-black uppercase tracking-[0.16em] text-[#f06472]"
                                >
                                    Fin de jornada
                                </p>

                                <h3
                                    class="mt-1 text-xl font-black"
                                >
                                    Cerrar caja
                                </h3>

                                <p
                                    class="mt-4 text-xs font-semibold text-white/35"
                                >
                                    Efectivo esperado
                                </p>

                                <p
                                    class="mt-1 text-3xl font-black"
                                >
                                    {{
                                        money(
                                            summary?.expected_cash,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-3 max-w-sm text-xs leading-5 text-white/30"
                                >
                                    Ingresa el efectivo contado físicamente para registrar cualquier diferencia de caja.
                                </p>
                            </div>

                            <form
                                class="space-y-3"
                                @submit.prevent="
                                    closeCash
                                "
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Efectivo contado
                                    </label>

                                    <input
                                        v-model="
                                            closeForm.closing_balance
                                        "
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-base font-black"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Observaciones
                                    </label>

                                    <textarea
                                        v-model="
                                            closeForm.closing_notes
                                        "
                                        rows="3"
                                        placeholder="Observaciones del cierre"
                                        class="w-full rounded-xl border border-white/10 bg-black/15 px-4 py-3 text-sm"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    :disabled="
                                        closeForm.processing
                                    "
                                    class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#e84657] text-sm font-black text-white shadow-[0_10px_28px_rgba(232,70,87,.12)] disabled:opacity-50"
                                >
                                    <ArrowDownCircle
                                        class="h-4 w-4"
                                    />

                                    {{
                                        closeForm.processing
                                            ? 'Cerrando...'
                                            : 'Cerrar caja'
                                    }}
                                </button>
                            </form>
                        </div>
                    </article>
                </template>
            </section>
        </div>
    </AppLayout>
</template>