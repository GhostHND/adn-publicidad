<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    History,
} from '@lucide/vue';

defineProps<{
    sessions: any[];
}>();

const breadcrumbs = [
    {
        title: 'Caja',
        href: '/cash',
    },
    {
        title: 'Historial',
        href: '/cash/history',
    },
];

const money = (
    value: string | number | null,
) =>
    `L ${Number(value || 0).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
</script>

<template>
    <Head title="Historial de caja" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold">
                        Historial de caja
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Consulta aperturas, cierres y
                        diferencias anteriores.
                    </p>
                </div>

                <Link
                    href="/cash"
                    class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver a caja
                </Link>
            </div>

            <div
                class="overflow-hidden rounded-xl border bg-background shadow-sm"
            >
                <div
                    v-if="sessions.length === 0"
                    class="flex min-h-64 items-center justify-center p-8 text-center"
                >
                    <div>
                        <History
                            class="mx-auto h-10 w-10 text-muted-foreground"
                        />

                        <h2
                            class="mt-4 font-semibold"
                        >
                            No hay cierres registrados
                        </h2>
                    </div>
                </div>

                <div
                    v-else
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full text-left text-sm"
                    >
                        <thead
                            class="border-b bg-muted/50"
                        >
                            <tr>
                                <th class="px-5 py-4">
                                    Apertura
                                </th>

                                <th class="px-5 py-4">
                                    Cierre
                                </th>

                                <th class="px-5 py-4">
                                    Responsable
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Inicial
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Esperado
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Contado
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Diferencia
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="
                                    session in sessions
                                "
                                :key="session.id"
                                class="border-b last:border-b-0 hover:bg-muted/30"
                            >
                                <td class="px-5 py-4">
                                    {{
                                        session.opened_at
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        session.closed_at
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <p>
                                        {{
                                            session.opened_by ||
                                            '—'
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    {{
                                        money(
                                            session.opening_balance,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    {{
                                        money(
                                            session.expected_balance,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-semibold"
                                >
                                    {{
                                        money(
                                            session.closing_balance,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-bold"
                                    :class="
                                        Number(
                                            session.difference,
                                        ) === 0
                                            ? 'text-emerald-600'
                                            : Number(
                                                    session.difference,
                                                ) > 0
                                              ? 'text-blue-600'
                                              : 'text-[#e84657]'
                                    "
                                >
                                    {{
                                        money(
                                            session.difference,
                                        )
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>