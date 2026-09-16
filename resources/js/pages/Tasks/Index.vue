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
    Eye,
    ListTodo,
    Pencil,
    Plus,
} from '@lucide/vue';
import { computed } from 'vue';

type Task = Record<string, any>;

const props = defineProps<{
    tasks?: Task[];
    workOrderTasks?: Task[];
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Tareas',
        href: '/tasks',
    },
];

const tasks =
    computed<Task[]>(() => {
        return (
            props.tasks
            ??
            props.workOrderTasks
            ??
            []
        );
    });

const pick = (
    task: Task,
    keys: string[],
    fallback: any = null,
): any => {
    for (const key of keys) {
        const result =
            task?.[key];

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

const taskTitle = (
    task: Task,
): string => {
    return String(
        pick(
            task,
            [
                'title',
                'name',
                'task_name',
                'step_name',
            ],
            `Tarea #${task.id}`,
        ),
    );
};

const taskCode = (
    task: Task,
): string => {
    return String(
        pick(
            task,
            [
                'task_code',
                'code',
            ],
            `TAR-${String(task.id).padStart(4, '0')}`,
        ),
    );
};

const status = (
    task: Task,
): string => {
    return String(
        pick(
            task,
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
        pending: 'Pendiente',
        ready: 'Lista para iniciar',
        in_progress: 'En proceso',
        paused: 'Pausada',
        review: 'En revisión',
        pending_review: 'En revisión',
        completed: 'Completada',
        blocked: 'Bloqueada',
        issue: 'Con incidencia',
        cancelled: 'Cancelada',
    };

    return (
        labels[value]
        ??
        value
    );
};

const tone = (
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
        [
            'blocked',
            'issue',
            'cancelled',
        ].includes(value)
    ) {
        return 'danger';
    }

    if (
        [
            'paused',
            'pending',
        ].includes(value)
    ) {
        return 'warning';
    }

    if (
        [
            'in_progress',
            'review',
            'pending_review',
        ].includes(value)
    ) {
        return 'info';
    }

    return 'neutral';
};

const activeCount =
    computed(() => {
        return tasks.value.filter(
            (task) =>
                ![
                    'completed',
                    'cancelled',
                ].includes(
                    status(task),
                ),
        ).length;
    });

const fieldsFor = (
    task: Task,
): MobileRecordField[] => {
    return [
        {
            label: 'Orden',
            value:
                pick(
                    task,
                    [
                        'work_order_number',
                        'order_number',
                        'work_order',
                    ],
                    '—',
                ),
        },

        {
            label: 'Prioridad',
            value:
                pick(
                    task,
                    ['priority'],
                    'Normal',
                ),
        },

        {
            label: 'Cliente',
            value:
                pick(
                    task,
                    [
                        'client',
                        'client_name',
                    ],
                    '—',
                ),
            wide: true,
        },

        {
            label: 'Responsable',
            value:
                pick(
                    task,
                    [
                        'assigned_employee',
                        'employee',
                        'responsible',
                        'assigned_to',
                    ],
                    '—',
                ),
            wide: true,
        },

        {
            label: 'Inicio',
            value:
                pick(
                    task,
                    [
                        'started_at',
                        'start_date',
                    ],
                    '—',
                ),
        },

        {
            label: 'Entrega',
            value:
                pick(
                    task,
                    [
                        'due_date',
                        'deadline',
                    ],
                    '—',
                ),
        },
    ];
};
</script>

<template>
    <Head title="Tareas" />

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
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <ListTodo
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Flujo de producción
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Tareas
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Actividades pendientes, activas y completadas.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/tasks/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Nueva tarea
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
                        Total
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ tasks.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-[#0fa7b4]/15 bg-[#0fa7b4]/[0.05] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase text-[#22c6d2]"
                    >
                        Activas
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ activeCount }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="task in tasks"
                    :key="task.id"
                    :title="taskTitle(task)"
                    :code="taskCode(task)"
                    :subtitle="
                        pick(
                            task,
                            [
                                'work_order_number',
                                'order_number',
                                'work_order',
                            ],
                            'Tarea de producción',
                        )
                    "
                    :status="
                        statusLabel(
                            status(task),
                        )
                    "
                    :status-tone="
                        tone(
                            status(task),
                        )
                    "
                    :fields="
                        fieldsFor(task)
                    "
                    :expanded="
                        isExpanded(task.id)
                    "
                    @toggle="
                        toggleCard(task.id)
                    "
                >
                    <template #actions>
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/tasks/${task.id}`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white"
                            >
                                <Eye
                                    class="h-4 w-4"
                                />

                                Ver tarea
                            </Link>

                            <Link
                                :href="
                                    `/tasks/${task.id}/edit`
                                "
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold"
                            >
                                <Pencil
                                    class="h-4 w-4"
                                />

                                Editar
                            </Link>
                        </div>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ESCRITORIO -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-5 py-4 text-left">
                                    Tarea
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Orden
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Responsable
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Prioridad
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
                                v-for="task in tasks"
                                :key="task.id"
                            >
                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{ taskTitle(task) }}
                                </td>

                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        pick(
                                            task,
                                            [
                                                'work_order_number',
                                                'order_number',
                                                'work_order',
                                            ],
                                            '—',
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        pick(
                                            task,
                                            [
                                                'assigned_employee',
                                                'employee',
                                                'responsible',
                                            ],
                                            '—',
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        pick(
                                            task,
                                            ['priority'],
                                            'Normal',
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black"
                                    >
                                        {{
                                            statusLabel(
                                                status(task),
                                            )
                                        }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="
                                                `/tasks/${task.id}`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                        >
                                            <Eye
                                                class="h-4 w-4"
                                            />
                                        </Link>

                                        <Link
                                            :href="
                                                `/tasks/${task.id}/edit`
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