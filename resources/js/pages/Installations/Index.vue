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
    CalendarDays,
    Eye,
    MapPin,
    Pencil,
    Plus,
    Wrench,
} from '@lucide/vue';
import { computed } from 'vue';

type Installation = Record<string, any>;

type PaginatedData = {
    data?: Installation[];
};

const props = defineProps<{
    installations?:
        Installation[]
        | PaginatedData;

    items?:
        Installation[]
        | PaginatedData;
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Instalaciones',
        href: '/installations',
    },
];

const installations =
    computed<Installation[]>(() => {
        const source =
            props.installations
            ??
            props.items
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
    installation: Installation,
    keys: string[],
    fallback: any = null,
): any => {
    for (const key of keys) {
        const result =
            installation?.[key];

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

const installationCode = (
    installation: Installation,
): string => {
    return String(
        pick(
            installation,
            [
                'installation_number',
                'installation_code',
                'code',
            ],
            `INS-${String(
                installation.id,
            ).padStart(
                4,
                '0',
            )}`,
        ),
    );
};

const clientName = (
    installation: Installation,
): string => {
    return String(
        pick(
            installation,
            [
                'client',
                'client_name',
                'customer',
            ],
            'Sin cliente',
        ),
    );
};

const workOrder = (
    installation: Installation,
): string => {
    return String(
        pick(
            installation,
            [
                'work_order_number',
                'order_number',
                'work_order',
            ],
            '—',
        ),
    );
};

const status = (
    installation: Installation,
): string => {
    return String(
        pick(
            installation,
            ['status'],
            'pending_schedule',
        ),
    );
};

const statusLabel = (
    value: string,
): string => {
    const labels:
        Record<string, string> = {
        pending_schedule:
            'Pendiente de programar',

        scheduled:
            'Programada',

        on_route:
            'En ruta',

        in_progress:
            'En instalación',

        completed:
            'Completada',

        cancelled:
            'Cancelada',
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
        value === 'completed'
    ) {
        return 'success';
    }

    if (
        value === 'cancelled'
    ) {
        return 'danger';
    }

    if (
        value ===
        'pending_schedule'
    ) {
        return 'warning';
    }

    if (
        [
            'scheduled',
            'on_route',
            'in_progress',
        ].includes(
            value,
        )
    ) {
        return 'info';
    }

    return 'neutral';
};

const activeCount =
    computed(() => {
        return installations.value
            .filter(
                (installation) =>
                    ![
                        'completed',
                        'cancelled',
                    ].includes(
                        status(
                            installation,
                        ),
                    ),
            )
            .length;
    });

const scheduledCount =
    computed(() => {
        return installations.value
            .filter(
                (installation) =>
                    status(
                        installation,
                    ) ===
                    'scheduled',
            )
            .length;
    });

const fieldsFor = (
    installation: Installation,
): MobileRecordField[] => {
    return [
        {
            label:
                'Cliente',

            value:
                clientName(
                    installation,
                ),

            wide:
                true,
        },

        {
            label:
                'Orden',

            value:
                workOrder(
                    installation,
                ),
        },

        {
            label:
                'Estado',

            value:
                statusLabel(
                    status(
                        installation,
                    ),
                ),
        },

        {
            label:
                'Fecha programada',

            value:
                pick(
                    installation,
                    [
                        'scheduled_date',
                        'installation_date',
                        'scheduled_at',
                        'date',
                    ],
                    '—',
                ),

            wide:
                true,
        },

        {
            label:
                'Responsable',

            value:
                pick(
                    installation,
                    [
                        'employee',
                        'employee_name',
                        'assigned_employee',
                        'technician',
                        'responsible',
                    ],
                    '—',
                ),

            wide:
                true,
        },

        {
            label:
                'Dirección',

            value:
                pick(
                    installation,
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
                        installation,
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
                'Notas',

            value:
                pick(
                    installation,
                    [
                        'notes',
                        'description',
                        'instructions',
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
        title="Instalaciones"
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
                    class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-[#0fa7b4]/10 blur-3xl"
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
                            <Wrench
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Trabajo en campo
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Instalaciones
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Programación y seguimiento de instalaciones.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/installations/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Nueva instalación
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
                        class="text-[9px] font-black uppercase tracking-wider text-white/30"
                    >
                        Total
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{
                            installations.length
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-[#0fa7b4]/15 bg-[#0fa7b4]/[0.05] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase tracking-wider text-[#22c6d2]"
                    >
                        Activas
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
                        class="text-[9px] font-black uppercase tracking-wider text-violet-400"
                    >
                        Programadas
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{
                            scheduledCount
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
                        installation in installations
                    "
                    :key="
                        installation.id
                    "
                    :title="
                        clientName(
                            installation,
                        )
                    "
                    :code="
                        installationCode(
                            installation,
                        )
                    "
                    :subtitle="
                        workOrder(
                            installation,
                        )
                    "
                    :status="
                        statusLabel(
                            status(
                                installation,
                            ),
                        )
                    "
                    :status-tone="
                        statusTone(
                            status(
                                installation,
                            ),
                        )
                    "
                    :fields="
                        fieldsFor(
                            installation,
                        )
                    "
                    :expanded="
                        isExpanded(
                            installation.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            installation.id,
                        )
                    "
                >
                    <template
                        #actions
                    >
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/installations/${installation.id}`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white"
                            >
                                <Eye
                                    class="h-4 w-4"
                                />

                                Ver
                            </Link>

                            <Link
                                :href="
                                    `/installations/${installation.id}/edit`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <Pencil
                                    class="h-4 w-4"
                                />

                                Editar
                            </Link>

                            <a
                                v-if="
                                    pick(
                                        installation,
                                        [
                                            'address',
                                            'installation_address',
                                            'location',
                                        ],
                                        null,
                                    )
                                "
                                :href="
                                    `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                                        pick(
                                            installation,
                                            [
                                                'address',
                                                'installation_address',
                                                'location',
                                            ],
                                            '',
                                        ),
                                    )}`
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                                class="col-span-2 inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 text-xs font-bold text-[#22c6d2]"
                            >
                                <MapPin
                                    class="h-4 w-4"
                                />

                                Abrir ubicación
                            </a>
                        </div>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ESCRITORIO -->

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
                                    Instalación
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Cliente
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Orden
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Programada
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Responsable
                                </th>

                                <th
                                    class="px-5 py-4 text-left"
                                >
                                    Estado
                                </th>

                                <th
                                    class="px-5 py-4 text-right"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    installation in installations
                                "
                                :key="
                                    installation.id
                                "
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        installationCode(
                                            installation,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{
                                        clientName(
                                            installation,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        workOrder(
                                            installation,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        pick(
                                            installation,
                                            [
                                                'scheduled_date',
                                                'installation_date',
                                                'scheduled_at',
                                            ],
                                            '—',
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        pick(
                                            installation,
                                            [
                                                'employee',
                                                'employee_name',
                                                'assigned_employee',
                                                'technician',
                                            ],
                                            '—',
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black"
                                    >
                                        {{
                                            statusLabel(
                                                status(
                                                    installation,
                                                ),
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="
                                                `/installations/${installation.id}`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Eye
                                                class="h-4 w-4"
                                            />
                                        </Link>

                                        <Link
                                            :href="
                                                `/installations/${installation.id}/edit`
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