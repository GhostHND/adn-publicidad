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
    Camera,
    Eye,
    HardDrive,
    Pencil,
    Plus,
    ShieldCheck,
} from '@lucide/vue';
import { computed } from 'vue';

type CctvProject = Record<string, any>;

type PaginatedData = {
    data?: CctvProject[];
};

const props = defineProps<{
    projects?:
        CctvProject[]
        | PaginatedData;

    cctvProjects?:
        CctvProject[]
        | PaginatedData;

    jobs?:
        CctvProject[]
        | PaginatedData;
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'CCTV',
        href: '/cctv',
    },
];

const projects =
    computed<CctvProject[]>(() => {
        const source =
            props.projects
            ??
            props.cctvProjects
            ??
            props.jobs
            ??
            [];

        if (
            Array.isArray(
                source,
            )
        ) {
            return source;
        }

        return source.data
            ?? [];
    });

const pick = (
    project: CctvProject,
    keys: string[],
    fallback: any = null,
): any => {
    for (const key of keys) {
        const result =
            project?.[key];

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

const projectCode = (
    project: CctvProject,
): string => {
    return String(
        pick(
            project,
            [
                'project_number',
                'cctv_number',
                'code',
            ],
            `CCTV-${String(
                project.id,
            ).padStart(
                4,
                '0',
            )}`,
        ),
    );
};

const clientName = (
    project: CctvProject,
): string => {
    return String(
        pick(
            project,
            [
                'client',
                'client_name',
                'customer',
            ],
            'Sin cliente',
        ),
    );
};

const status = (
    project: CctvProject,
): string => {
    return String(
        pick(
            project,
            ['status'],
            'pending',
        ),
    );
};

const statusLabel = (
    value: string,
): string => {
    const labels:
        Record<string, string> = {
        pending:
            'Pendiente',

        scheduled:
            'Programado',

        installation:
            'Instalando',

        in_progress:
            'En proceso',

        active:
            'Activo',

        completed:
            'Completado',

        maintenance:
            'Mantenimiento',

        cancelled:
            'Cancelado',
    };

    return labels[value]
        ?? value;
};

const statusTone = (
    value: string,
):
    | 'success'
    | 'danger'
    | 'warning'
    | 'info'
    | 'neutral' => {
    if (
        [
            'active',
            'completed',
        ].includes(
            value,
        )
    ) {
        return 'success';
    }

    if (
        value ===
        'cancelled'
    ) {
        return 'danger';
    }

    if (
        [
            'pending',
            'maintenance',
        ].includes(
            value,
        )
    ) {
        return 'warning';
    }

    if (
        [
            'scheduled',
            'installation',
            'in_progress',
        ].includes(
            value,
        )
    ) {
        return 'info';
    }

    return 'neutral';
};

const devicesCount = (
    project: CctvProject,
): number => {
    const direct =
        pick(
            project,
            [
                'devices_count',
                'device_count',
                'cameras_count',
                'camera_count',
            ],
            null,
        );

    if (
        direct !== null
    ) {
        return Number(
            direct,
        );
    }

    if (
        Array.isArray(
            project.devices,
        )
    ) {
        return project.devices.length;
    }

    return 0;
};

const activeCount =
    computed(() => {
        return projects.value
            .filter(
                (project) =>
                    ![
                        'completed',
                        'cancelled',
                    ].includes(
                        status(
                            project,
                        ),
                    ),
            )
            .length;
    });

const totalDevices =
    computed(() => {
        return projects.value
            .reduce(
                (
                    total,
                    project,
                ) =>
                    total
                    +
                    devicesCount(
                        project,
                    ),
                0,
            );
    });

const fieldsFor = (
    project: CctvProject,
): MobileRecordField[] => {
    return [
        {
            label:
                'Cliente',

            value:
                clientName(
                    project,
                ),

            wide:
                true,
        },

        {
            label:
                'Tipo',

            value:
                pick(
                    project,
                    [
                        'project_type',
                        'service_type',
                        'type',
                    ],
                    'CCTV',
                ),
        },

        {
            label:
                'Dispositivos',

            value:
                devicesCount(
                    project,
                ),
        },

        {
            label:
                'Orden',

            value:
                pick(
                    project,
                    [
                        'work_order_number',
                        'order_number',
                        'work_order',
                    ],
                    '—',
                ),
        },

        {
            label:
                'Responsable',

            value:
                pick(
                    project,
                    [
                        'employee',
                        'employee_name',
                        'technician',
                        'assigned_employee',
                    ],
                    '—',
                ),
        },

        {
            label:
                'Ubicación',

            value:
                pick(
                    project,
                    [
                        'address',
                        'installation_address',
                        'location',
                    ],
                    '—',
                ),

            wide:
                true,

            copyable:
                Boolean(
                    pick(
                        project,
                        [
                            'address',
                            'installation_address',
                            'location',
                        ],
                        null,
                    ),
                ),
        },

        {
            label:
                'Fecha',

            value:
                pick(
                    project,
                    [
                        'scheduled_date',
                        'installation_date',
                        'created_at',
                    ],
                    '—',
                ),

            wide:
                true,
        },

        {
            label:
                'Notas',

            value:
                pick(
                    project,
                    [
                        'notes',
                        'description',
                    ],
                    '—',
                ),

            wide:
                true,
        },
    ];
};
</script>

<template>
    <Head
        title="CCTV"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <section
                class="adn-enter relative overflow-hidden rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-20 h-52 w-52 rounded-full bg-violet-500/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-500/10 text-violet-400"
                        >
                            <Camera
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-violet-400"
                            >
                                Seguridad
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                CCTV
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Instalaciones, equipos y mantenimiento de videovigilancia.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/cctv/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Nuevo proyecto
                    </Link>
                </div>
            </section>

            <!-- MÉTRICAS -->

            <div
                class="grid grid-cols-3 gap-3 md:max-w-xl"
            >
                <div
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase text-white/30"
                    >
                        Proyectos
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{
                            projects.length
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-emerald-500/15 bg-emerald-500/[0.05] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase text-emerald-400"
                    >
                        Activos
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{
                            activeCount
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-violet-500/15 bg-violet-500/[0.05] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase text-violet-400"
                    >
                        Equipos
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{
                            totalDevices
                        }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        project in projects
                    "
                    :key="
                        project.id
                    "
                    :title="
                        clientName(
                            project,
                        )
                    "
                    :code="
                        projectCode(
                            project,
                        )
                    "
                    :subtitle="
                        pick(
                            project,
                            [
                                'project_type',
                                'service_type',
                            ],
                            'Proyecto CCTV',
                        )
                    "
                    :status="
                        statusLabel(
                            status(
                                project,
                            ),
                        )
                    "
                    :status-tone="
                        statusTone(
                            status(
                                project,
                            ),
                        )
                    "
                    :fields="
                        fieldsFor(
                            project,
                        )
                    "
                    :expanded="
                        isExpanded(
                            project.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            project.id,
                        )
                    "
                >
                    <template
                        #badges
                    >
                        <span
                            class="inline-flex items-center gap-1 rounded-full border border-violet-500/20 bg-violet-500/10 px-2 py-1 text-[8px] font-black text-violet-400"
                        >
                            <HardDrive
                                class="h-3 w-3"
                            />

                            {{
                                devicesCount(
                                    project,
                                )
                            }}
                        </span>
                    </template>

                    <template
                        #actions
                    >
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/cctv/${project.id}`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white"
                            >
                                <Eye
                                    class="h-4 w-4"
                                />

                                Ver proyecto
                            </Link>

                            <Link
                                :href="
                                    `/cctv/${project.id}/edit`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <Pencil
                                    class="h-4 w-4"
                                />

                                Editar
                            </Link>

                            <Link
                                :href="
                                    `/cctv/${project.id}`
                                "
                                class="col-span-2 inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-violet-500/20 bg-violet-500/[0.05] text-xs font-bold text-violet-400"
                            >
                                <ShieldCheck
                                    class="h-4 w-4"
                                />

                                Equipos, credenciales y mantenimiento
                            </Link>
                        </div>
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
                                <th class="px-5 py-4 text-left">
                                    Proyecto
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Cliente
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Tipo
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Equipos
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Responsable
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Estado
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    project in projects
                                "
                                :key="
                                    project.id
                                "
                            >
                                <td
                                    class="px-5 py-4 font-black text-violet-400"
                                >
                                    {{
                                        projectCode(
                                            project,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{
                                        clientName(
                                            project,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        pick(
                                            project,
                                            [
                                                'project_type',
                                                'service_type',
                                            ],
                                            'CCTV',
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-black"
                                >
                                    {{
                                        devicesCount(
                                            project,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        pick(
                                            project,
                                            [
                                                'employee',
                                                'employee_name',
                                                'technician',
                                            ],
                                            '—',
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        statusLabel(
                                            status(
                                                project,
                                            ),
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="
                                                `/cctv/${project.id}`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Eye
                                                class="h-4 w-4"
                                            />
                                        </Link>

                                        <Link
                                            :href="
                                                `/cctv/${project.id}/edit`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Pencil
                                                class="h-4 w-4"
                                            />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>