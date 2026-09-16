<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';
import { useMobileAccordion } from '@/composables/useMobileAccordion';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3';
import {
    Activity,
    AlertTriangle,
    Eye,
    Search,
    ShieldCheck,
    UserRound,
} from '@lucide/vue';
import {
    computed,
    ref,
} from 'vue';

type User = {
    id?: number;
    name?: string;
    email?: string;
};

type Employee = {
    id?: number;
    full_name?: string;
    name?: string;
};

type AuditLog = {
    id: number;

    user_id?: number | null;
    employee_id?: number | null;

    module?: string | null;
    action?: string | null;
    description?: string | null;

    route_name?: string | null;
    method?: string | null;
    url?: string | null;

    status_code?: number | null;

    entity_type?: string | null;
    entity_id?: number | null;

    ip_address?: string | null;
    user_agent?: string | null;

    created_at?: string | null;

    user?: User | null;
    employee?: Employee | null;
};

type PaginatedLogs = {
    data?: AuditLog[];

    current_page?: number;
    last_page?: number;

    total?: number;

    links?: Array<{
        url?: string | null;
        label?: string;
        active?: boolean;
    }>;
};

const props = defineProps<{
    logs:
        AuditLog[]
        | PaginatedLogs;

    filters?: {
        search?: string | null;
        module?: string | null;
        action?: string | null;
        user_id?: string | number | null;
        date_from?: string | null;
        date_to?: string | null;
    };

    modules?: string[];
    actions?: string[];
    users?: User[];

    stats?: {
        total?: number;
        today?: number;
        distinct_users?: number;
        errors?: number;
    };
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title:
            'Auditoría',

        href:
            '/audit',
    },
];

const search =
    ref(
        props.filters?.search
        ?? '',
    );

const module =
    ref(
        props.filters?.module
        ?? '',
    );

const action =
    ref(
        props.filters?.action
        ?? '',
    );

const userId =
    ref(
        props.filters?.user_id
        ?? '',
    );

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

const logs =
    computed<AuditLog[]>(() => {
        if (
            Array.isArray(
                props.logs,
            )
        ) {
            return props.logs;
        }

        return props.logs.data
            ?? [];
    });

const total =
    computed(() => {
        return Number(
            props.stats?.total
            ??
            (
                Array.isArray(
                    props.logs,
                )
                    ? props.logs.length
                    : props.logs.total
            )
            ??
            logs.value.length,
        );
    });

const today =
    computed(() => {
        return Number(
            props.stats?.today
            ?? 0,
        );
    });

const distinctUsers =
    computed(() => {
        return Number(
            props.stats?.distinct_users
            ?? 0,
        );
    });

const errors =
    computed(() => {
        return Number(
            props.stats?.errors
            ?? 0,
        );
    });

const titleFor = (
    log: AuditLog,
): string => {
    if (
        log.description
    ) {
        return log.description;
    }

    const actionName =
        log.action
        ?? 'acción';

    const moduleName =
        log.module
        ?? 'sistema';

    return `${actionName} · ${moduleName}`;
};

const moduleLabel = (
    value?: string | null,
): string => {
    if (!value) {
        return 'Sistema';
    }

    const labels:
        Record<string, string> = {
        employees:
            'Empleados',

        clients:
            'Clientes',

        catalog:
            'Catálogo',

        inventory:
            'Inventario',

        quotations:
            'Cotizaciones',

        sales:
            'Ventas',

        receipts:
            'Recibos',

        'accounts-receivable':
            'Cuentas por cobrar',

        'work-orders':
            'Órdenes de trabajo',

        tasks:
            'Tareas',

        installations:
            'Instalaciones',

        cctv:
            'CCTV',

        cash:
            'Caja',

        income:
            'Ingresos',

        expenses:
            'Gastos',

        website:
            'Sitio web',

        users:
            'Usuarios',

        'system-settings':
            'Configuración',
    };

    return labels[value]
        ?? value;
};

const actionLabel = (
    value?: string | null,
): string => {
    if (!value) {
        return 'Acción';
    }

    const labels:
        Record<string, string> = {
        create:
            'Creación',

        store:
            'Creación',

        update:
            'Actualización',

        delete:
            'Eliminación',

        destroy:
            'Eliminación',

        status:
            'Cambio de estado',

        cancel:
            'Cancelación',

        start:
            'Inicio',

        pause:
            'Pausa',

        resume:
            'Reanudación',

        finish:
            'Finalización',

        'send-for-review':
            'Envío a revisión',

        'report-issue':
            'Incidencia',

        open:
            'Apertura',

        close:
            'Cierre',

        'reset-password':
            'Restablecimiento de contraseña',
    };

    return labels[value]
        ?? value;
};

const userName = (
    log: AuditLog,
): string => {
    return (
        log.user?.name
        ||
        log.employee?.full_name
        ||
        log.employee?.name
        ||
        'Sistema'
    );
};

const isError = (
    log: AuditLog,
): boolean => {
    return Number(
        log.status_code
        ?? 0,
    ) >= 400;
};

const toneFor = (
    log: AuditLog,
):
    | 'success'
    | 'danger'
    | 'warning'
    | 'info'
    | 'neutral' => {
    if (
        isError(log)
    ) {
        return 'danger';
    }

    const method =
        String(
            log.method
            ?? '',
        ).toUpperCase();

    if (
        method ===
        'DELETE'
    ) {
        return 'danger';
    }

    if (
        method ===
        'POST'
    ) {
        return 'success';
    }

    if (
        [
            'PATCH',
            'PUT',
        ].includes(
            method,
        )
    ) {
        return 'info';
    }

    return 'neutral';
};

const fieldsFor = (
    log: AuditLog,
): MobileRecordField[] => {
    return [
        {
            label:
                'Módulo',

            value:
                moduleLabel(
                    log.module,
                ),
        },

        {
            label:
                'Acción',

            value:
                actionLabel(
                    log.action,
                ),
        },

        {
            label:
                'Usuario',

            value:
                userName(
                    log,
                ),

            wide:
                true,
        },

        {
            label:
                'Método',

            value:
                log.method
                ?? '—',
        },

        {
            label:
                'Respuesta',

            value:
                log.status_code
                ?? '—',
        },

        {
            label:
                'Ruta',

            value:
                log.route_name
                ?? log.url
                ?? '—',

            wide:
                true,

            copyable:
                Boolean(
                    log.route_name
                    || log.url,
                ),
        },

        {
            label:
                'Entidad',

            value:
                log.entity_type
                    ? `${log.entity_type}${
                        log.entity_id
                            ? ` #${log.entity_id}`
                            : ''
                    }`
                    : '—',

            wide:
                true,
        },

        {
            label:
                'IP',

            value:
                log.ip_address
                ?? '—',

            copyable:
                Boolean(
                    log.ip_address,
                ),
        },

        {
            label:
                'Fecha',

            value:
                log.created_at
                ?? '—',
        },
    ];
};

const applyFilters =
    (): void => {
        router.get(
            '/audit',
            {
                search:
                    search.value
                    || undefined,

                module:
                    module.value
                    || undefined,

                action:
                    action.value
                    || undefined,

                user_id:
                    userId.value
                    || undefined,

                date_from:
                    dateFrom.value
                    || undefined,

                date_to:
                    dateTo.value
                    || undefined,
            },
            {
                preserveState:
                    true,

                preserveScroll:
                    true,

                replace:
                    true,
            },
        );
    };

const clearFilters =
    (): void => {
        search.value =
            '';

        module.value =
            '';

        action.value =
            '';

        userId.value =
            '';

        dateFrom.value =
            '';

        dateTo.value =
            '';

        router.get(
            '/audit',
            {},
            {
                preserveState:
                    true,

                replace:
                    true,
            },
        );
    };
</script>

<template>
    <Head
        title="Auditoría"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <!-- CABECERA -->

            <section
                class="adn-enter relative overflow-hidden rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-60 w-60 rounded-full bg-[#0fa7b4]/10 blur-3xl"
                />

                <div
                    class="relative flex items-start gap-4"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                    >
                        <Activity
                            class="h-5 w-5"
                        />
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                        >
                            Trazabilidad
                        </p>

                        <h1
                            class="mt-1 text-2xl font-black sm:text-3xl"
                        >
                            Auditoría
                        </h1>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Historial de cambios y operaciones realizadas dentro del sistema.
                        </p>
                    </div>
                </div>
            </section>

            <!-- MÉTRICAS -->

            <section
                class="grid grid-cols-2 gap-3 xl:grid-cols-4"
            >
                <article
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase tracking-wide text-white/30"
                    >
                        Registros
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ total }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-[#0fa7b4]/15 bg-[#0fa7b4]/[0.05] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase tracking-wide text-[#22c6d2]"
                    >
                        Hoy
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ today }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-violet-500/15 bg-violet-500/[0.05] p-4"
                >
                    <div
                        class="flex items-center gap-2 text-violet-400"
                    >
                        <UserRound
                            class="h-3.5 w-3.5"
                        />

                        <p
                            class="text-[9px] font-black uppercase"
                        >
                            Usuarios
                        </p>
                    </div>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ distinctUsers }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-[#e84657]/15 bg-[#e84657]/[0.05] p-4"
                >
                    <div
                        class="flex items-center gap-2 text-[#f06472]"
                    >
                        <AlertTriangle
                            class="h-3.5 w-3.5"
                        />

                        <p
                            class="text-[9px] font-black uppercase"
                        >
                            Errores
                        </p>
                    </div>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ errors }}
                    </p>
                </article>
            </section>

            <!-- FILTROS -->

            <section
                class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
            >
                <div
                    class="grid gap-2 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div
                        class="relative md:col-span-2"
                    >
                        <Search
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/25"
                        />

                        <input
                            v-model="
                                search
                            "
                            type="search"
                            placeholder="Buscar ruta, descripción, IP..."
                            class="h-11 w-full rounded-xl border border-white/10 bg-black/15 pl-10 pr-3 text-sm"
                            @keyup.enter="
                                applyFilters
                            "
                        />
                    </div>

                    <select
                        v-model="
                            module
                        "
                        class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                    >
                        <option value="">
                            Todos los módulos
                        </option>

                        <option
                            v-for="
                                item in modules
                            "
                            :key="
                                item
                            "
                            :value="
                                item
                            "
                        >
                            {{
                                moduleLabel(
                                    item,
                                )
                            }}
                        </option>
                    </select>

                    <select
                        v-model="
                            action
                        "
                        class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                    >
                        <option value="">
                            Todas las acciones
                        </option>

                        <option
                            v-for="
                                item in actions
                            "
                            :key="
                                item
                            "
                            :value="
                                item
                            "
                        >
                            {{
                                actionLabel(
                                    item,
                                )
                            }}
                        </option>
                    </select>

                    <select
                        v-model="
                            userId
                        "
                        class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                    >
                        <option value="">
                            Todos los usuarios
                        </option>

                        <option
                            v-for="
                                user in users
                            "
                            :key="
                                user.id
                            "
                            :value="
                                user.id
                            "
                        >
                            {{
                                user.name
                            }}
                        </option>
                    </select>

                    <input
                        v-model="
                            dateFrom
                        "
                        type="date"
                        class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                    />

                    <input
                        v-model="
                            dateTo
                        "
                        type="date"
                        class="h-11 rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                    />

                    <div
                        class="grid grid-cols-2 gap-2"
                    >
                        <button
                            type="button"
                            class="h-11 rounded-xl bg-[#0fa7b4] px-4 text-sm font-black text-white"
                            @click="
                                applyFilters
                            "
                        >
                            Filtrar
                        </button>

                        <button
                            type="button"
                            class="h-11 rounded-xl border border-white/10 px-4 text-sm font-bold"
                            @click="
                                clearFilters
                            "
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </section>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        log in logs
                    "
                    :key="
                        log.id
                    "
                    :title="
                        titleFor(
                            log,
                        )
                    "
                    :code="
                        `AUD-${String(
                            log.id,
                        ).padStart(
                            5,
                            '0',
                        )}`
                    "
                    :subtitle="
                        `${moduleLabel(
                            log.module,
                        )} · ${userName(
                            log,
                        )}`
                    "
                    :status="
                        isError(log)
                            ? `Error ${log.status_code}`
                            : actionLabel(
                                log.action,
                            )
                    "
                    :status-tone="
                        toneFor(
                            log,
                        )
                    "
                    :fields="
                        fieldsFor(
                            log,
                        )
                    "
                    :expanded="
                        isExpanded(
                            log.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            log.id,
                        )
                    "
                >
                    <template
                        #badges
                    >
                        <ShieldCheck
                            v-if="
                                !isError(
                                    log,
                                )
                            "
                            class="h-4 w-4 text-emerald-400"
                        />

                        <AlertTriangle
                            v-else
                            class="h-4 w-4 text-[#f06472]"
                        />
                    </template>

                    <template
                        #actions
                    >
                        <Link
                            :href="
                                `/audit/${log.id}`
                            "
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white"
                        >
                            <Eye
                                class="h-4 w-4"
                            />

                            Ver registro completo
                        </Link>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- DESKTOP -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full text-sm"
                    >
                        <thead>
                            <tr>
                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Fecha
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Usuario
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Módulo
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Acción
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Descripción
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    IP
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    log in logs
                                "
                                :key="
                                    log.id
                                "
                            >
                                <td
                                    class="px-5 py-4 text-xs"
                                >
                                    {{
                                        log.created_at
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{
                                        userName(
                                            log,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <span
                                        class="rounded-full border border-[#0fa7b4]/15 bg-[#0fa7b4]/5 px-3 py-1 text-[10px] font-black text-[#22c6d2]"
                                    >
                                        {{
                                            moduleLabel(
                                                log.module,
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        actionLabel(
                                            log.action,
                                        )
                                    }}
                                </td>

                                <td
                                    class="max-w-md px-5 py-4"
                                >
                                    {{
                                        titleFor(
                                            log,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-mono text-xs text-white/50"
                                >
                                    {{
                                        log.ip_address
                                        ?? '—'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    <Link
                                        :href="
                                            `/audit/${log.id}`
                                        "
                                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-2 text-xs font-bold hover:border-[#0fa7b4]/25 hover:bg-[#0fa7b4]/5"
                                    >
                                        <Eye
                                            class="h-4 w-4"
                                        />

                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- VACÍO -->

            <div
                v-if="
                    logs.length === 0
                "
                class="rounded-2xl border border-dashed border-white/10 p-12 text-center"
            >
                <Activity
                    class="mx-auto h-9 w-9 text-white/20"
                />

                <p
                    class="mt-4 font-black"
                >
                    No hay registros
                </p>

                <p
                    class="mt-1 text-xs text-muted-foreground"
                >
                    Las operaciones del sistema aparecerán aquí.
                </p>
            </div>
        </div>
    </AppLayout>
</template>