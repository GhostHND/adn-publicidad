<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3';

import {
    CheckCircle2,
    Clock3,
    Eye,
    FileText,
    Inbox,
    MessageCircle,
    Search,
    Trash2,
    Trophy,
    UserCheck,
    UserRound,
} from '@lucide/vue';

import {
    reactive,
} from 'vue';

type Lead = {
    id: number;
    lead_number: string;
    name: string;
    business_name: string | null;
    phone: string;
    email: string | null;
    service_interest: string | null;
    message: string;
    status: string;
    source: string;
    assigned_employee: string | null;
    client_id: number | null;
    client_name: string | null;
    quotation_id: number | null;
    quotation_number: string | null;
    reviewed_at: string | null;
    attended_at: string | null;
    created_at: string | null;
};

const props =
    defineProps<{
        leads: any;

        filters: {
            search: string;
            status: string;
        };

        highlightId: number;

        stats: {
            total: number;
            new: number;
            open: number;
            won: number;
        };
    }>();

const breadcrumbs = [
    {
        title:
            'Solicitudes',

        href:
            '/website/leads',
    },
];

const filters =
    reactive({
        search:
            props.filters.search
            ?? '',

        status:
            props.filters.status
            ?? '',
    });

const statusLabels:
    Record<string, string> = {
    new:
        'Nueva',

    reviewed:
        'Revisada',

    attending:
        'Atendiendo',

    quoted:
        'Cotizada',

    won:
        'Ganada',

    lost:
        'Perdida',
};

const statusClasses:
    Record<string, string> = {
    new:
        'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',

    reviewed:
        'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',

    attending:
        'bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300',

    quoted:
        'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',

    won:
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',

    lost:
        'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300',
};

const applyFilters =
    (): void => {
        router.get(
            '/website/leads',
            {
                search:
                    filters.search,

                status:
                    filters.status,
            },
            {
                preserveState:
                    true,

                replace:
                    true,
            },
        );
    };

const clearFilters =
    (): void => {
        filters.search =
            '';

        filters.status =
            '';

        router.get(
            '/website/leads',
        );
    };

const changeStatus = (
    lead: Lead,
    event: Event,
): void => {
    const target =
        event.target;

    if (
        !target
        ||
        !(
            'value'
            in target
        )
    ) {
        return;
    }

    const status =
        String(
            Reflect.get(
                target,
                'value',
            )
            ?? '',
        );

    router.patch(
        `/website/leads/${lead.id}/status`,
        {
            status,
        },
        {
            preserveScroll:
                true,
        },
    );
};

const attend = (
    lead: Lead,
): void => {
    router.post(
        `/website/leads/${lead.id}/attend`,
        {},
    );
};

const markReviewed = (
    lead: Lead,
): void => {
    router.patch(
        `/website/leads/${lead.id}/reviewed`,
        {},
        {
            preserveScroll:
                true,
        },
    );
};

const whatsappUrl = (
    lead: Lead,
): string => {
    let digits =
        String(
            lead.phone
            ?? '',
        ).replace(
            /\D/g,
            '',
        );

    if (
        digits.length ===
        8
    ) {
        digits =
            `504${digits}`;
    }

    const message =
        encodeURIComponent(
            `Hola ${lead.name}, te contactamos de ADN Publicidad con relación a tu solicitud ${lead.lead_number}.`,
        );

    return digits
        ? `https://wa.me/${digits}?text=${message}`
        : '#';
};

const removeLead = (
    lead: Lead,
): void => {
    if (
        !window.confirm(
            `¿Eliminar la solicitud ${lead.lead_number}?`,
        )
    ) {
        return;
    }

    router.delete(
        `/website/leads/${lead.id}`,
        {
            preserveScroll:
                true,
        },
    );
};
</script>

<template>
    <Head
        title="Solicitudes"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div>
                <p
                    class="text-sm font-semibold text-[#0fa7b4]"
                >
                    ADN Publicidad
                </p>

                <h1
                    class="text-3xl font-bold"
                >
                    Solicitudes
                </h1>

                <p
                    class="mt-1 text-sm text-muted-foreground"
                >
                    Solicitudes recibidas desde el sitio web y otros canales.
                </p>
            </div>

            <div
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >
                <div
                    class="rounded-2xl border bg-background p-5 shadow-sm"
                >
                    <Inbox
                        class="h-5 w-5 text-[#0fa7b4]"
                    />

                    <p
                        class="mt-4 text-sm text-muted-foreground"
                    >
                        Total
                    </p>

                    <p
                        class="text-3xl font-bold"
                    >
                        {{
                            stats.total
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-background p-5 shadow-sm"
                >
                    <Clock3
                        class="h-5 w-5 text-red-500"
                    />

                    <p
                        class="mt-4 text-sm text-muted-foreground"
                    >
                        Sin revisar
                    </p>

                    <p
                        class="text-3xl font-bold"
                    >
                        {{
                            stats.new
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-background p-5 shadow-sm"
                >
                    <CheckCircle2
                        class="h-5 w-5 text-amber-500"
                    />

                    <p
                        class="mt-4 text-sm text-muted-foreground"
                    >
                        En proceso
                    </p>

                    <p
                        class="text-3xl font-bold"
                    >
                        {{
                            stats.open
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-background p-5 shadow-sm"
                >
                    <Trophy
                        class="h-5 w-5 text-emerald-500"
                    />

                    <p
                        class="mt-4 text-sm text-muted-foreground"
                    >
                        Ganadas
                    </p>

                    <p
                        class="text-3xl font-bold"
                    >
                        {{
                            stats.won
                        }}
                    </p>
                </div>
            </div>

            <section
                class="rounded-2xl border bg-background p-5"
            >
                <form
                    class="flex flex-col gap-3 md:flex-row"
                    @submit.prevent="
                        applyFilters
                    "
                >
                    <div
                        class="relative flex-1"
                    >
                        <Search
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />

                        <input
                            v-model="
                                filters.search
                            "
                            type="text"
                            placeholder="Buscar cliente, teléfono, correo, servicio o detalle..."
                            class="w-full rounded-xl border bg-background py-3 pl-10 pr-4"
                        >
                    </div>

                    <select
                        v-model="
                            filters.status
                        "
                        class="rounded-xl border bg-background px-4 py-3"
                    >
                        <option value="">
                            Todos
                        </option>

                        <option value="new">
                            Nuevas
                        </option>

                        <option value="reviewed">
                            Revisadas
                        </option>

                        <option value="attending">
                            Atendiendo
                        </option>

                        <option value="quoted">
                            Cotizadas
                        </option>

                        <option value="won">
                            Ganadas
                        </option>

                        <option value="lost">
                            Perdidas
                        </option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-[#0fa7b4] px-5 py-3 font-semibold text-white"
                    >
                        Filtrar
                    </button>

                    <button
                        type="button"
                        class="rounded-xl border px-5 py-3 font-semibold"
                        @click="
                            clearFilters
                        "
                    >
                        Limpiar
                    </button>
                </form>
            </section>

            <section
                class="space-y-4"
            >
                <div
                    v-if="
                        leads.data.length ===
                        0
                    "
                    class="rounded-2xl border p-12 text-center"
                >
                    <Inbox
                        class="mx-auto h-10 w-10 text-muted-foreground"
                    />

                    <p
                        class="mt-4 font-bold"
                    >
                        No hay solicitudes
                    </p>
                </div>

                <article
                    v-for="
                        lead in leads.data
                    "
                    :key="
                        lead.id
                    "
                    class="rounded-2xl border bg-background p-6 shadow-sm transition"
                    :class="
                        highlightId ===
                        lead.id
                            ? 'ring-2 ring-[#e84657] ring-offset-2'
                            : ''
                    "
                >
                    <div
                        class="flex flex-col gap-6 xl:flex-row xl:justify-between"
                    >
                        <div
                            class="min-w-0 flex-1"
                        >
                            <div
                                class="flex flex-wrap items-center gap-2"
                            >
                                <p
                                    class="font-black text-[#0fa7b4]"
                                >
                                    {{
                                        lead.lead_number
                                    }}
                                </p>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold"
                                    :class="
                                        statusClasses[
                                            lead.status
                                        ]
                                    "
                                >
                                    {{
                                        statusLabels[
                                            lead.status
                                        ]
                                    }}
                                </span>
                            </div>

                            <h2
                                class="mt-3 text-xl font-bold"
                            >
                                {{
                                    lead.name
                                }}
                            </h2>

                            <p
                                v-if="
                                    lead.business_name
                                "
                                class="text-sm text-muted-foreground"
                            >
                                {{
                                    lead.business_name
                                }}
                            </p>

                            <div
                                class="mt-3 flex flex-wrap gap-4 text-sm"
                            >
                                <span>
                                    {{
                                        lead.phone
                                    }}
                                </span>

                                <span
                                    v-if="
                                        lead.email
                                    "
                                >
                                    {{
                                        lead.email
                                    }}
                                </span>
                            </div>

                            <p
                                v-if="
                                    lead.service_interest
                                "
                                class="mt-4 font-bold text-[#e84657]"
                            >
                                {{
                                    lead.service_interest
                                }}
                            </p>

                            <div
                                class="mt-4 whitespace-pre-line rounded-xl bg-muted/40 p-4 text-sm leading-6"
                            >
                                {{
                                    lead.message
                                }}
                            </div>

                            <div
                                class="mt-4 flex flex-wrap gap-4 text-xs text-muted-foreground"
                            >
                                <span>
                                    Recibida:
                                    {{
                                        lead.created_at
                                    }}
                                </span>

                                <span
                                    v-if="
                                        lead.assigned_employee
                                    "
                                >
                                    Responsable:
                                    {{
                                        lead.assigned_employee
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex min-w-[230px] flex-col gap-3"
                        >
                            <Link
                                v-if="
                                    lead.source ===
                                    'adn_web'
                                "
                                :href="
                                    `/website/leads/${lead.id}/detail`
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#0fa7b4]/30 bg-[#0fa7b4]/10 px-5 py-3 font-bold text-[#0fa7b4] transition hover:-translate-y-0.5 hover:bg-[#0fa7b4]/15"
                            >
                                <Eye
                                    class="h-4 w-4"
                                />

                                Ver solicitud completa
                            </Link>

                            <button
                                v-if="
                                    !lead.quotation_id
                                "
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#e84657] px-5 py-3 font-bold text-white shadow-sm transition hover:-translate-y-0.5"
                                @click="
                                    attend(
                                        lead,
                                    )
                                "
                            >
                                <UserCheck
                                    class="h-4 w-4"
                                />

                                Atender solicitud
                            </button>

                            <Link
                                v-else
                                :href="
                                    `/quotations/${lead.quotation_id}`
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 font-bold text-white"
                            >
                                <FileText
                                    class="h-4 w-4"
                                />

                                {{
                                    lead.quotation_number
                                    ||
                                    'Ver cotización'
                                }}
                            </Link>

                            <Link
                                v-if="
                                    lead.client_id
                                "
                                :href="
                                    `/clients/${lead.client_id}`
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 font-semibold"
                            >
                                <UserRound
                                    class="h-4 w-4"
                                />

                                Ver cliente
                            </Link>

                            <button
                                v-if="
                                    lead.status ===
                                    'new'
                                "
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 font-semibold"
                                @click="
                                    markReviewed(
                                        lead,
                                    )
                                "
                            >
                                <Eye
                                    class="h-4 w-4"
                                />

                                Marcar revisada
                            </button>

                            <a
                                :href="
                                    whatsappUrl(
                                        lead,
                                    )
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 font-semibold text-white"
                            >
                                <MessageCircle
                                    class="h-4 w-4"
                                />

                                WhatsApp
                            </a>

                            <select
                                :value="
                                    lead.status
                                "
                                class="rounded-xl border bg-background px-4 py-3"
                                @change="
                                    changeStatus(
                                        lead,
                                        $event,
                                    )
                                "
                            >
                                <option value="new">
                                    Nueva
                                </option>

                                <option value="reviewed">
                                    Revisada
                                </option>

                                <option value="attending">
                                    Atendiendo
                                </option>

                                <option value="quoted">
                                    Cotizada
                                </option>

                                <option value="won">
                                    Ganada
                                </option>

                                <option value="lost">
                                    Perdida
                                </option>
                            </select>

                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 px-4 py-3 font-semibold text-red-600"
                                @click="
                                    removeLead(
                                        lead,
                                    )
                                "
                            >
                                <Trash2
                                    class="h-4 w-4"
                                />

                                Eliminar
                            </button>
                        </div>
                    </div>
                </article>
            </section>

            <div
                v-if="
                    leads.links
                    &&
                    leads.links.length >
                    3
                "
                class="flex flex-wrap justify-center gap-2"
            >
                <template
                    v-for="
                        link in leads.links
                    "
                    :key="
                        link.label
                    "
                >
                    <Link
                        v-if="
                            link.url
                        "
                        :href="
                            link.url
                        "
                        preserve-scroll
                        class="rounded-lg border px-3 py-2 text-sm"
                        :class="
                            link.active
                                ? 'bg-[#0fa7b4] text-white'
                                : ''
                        "
                        v-html="
                            link.label
                        "
                    />

                    <span
                        v-else
                        class="rounded-lg border px-3 py-2 text-sm opacity-40"
                        v-html="
                            link.label
                        "
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>