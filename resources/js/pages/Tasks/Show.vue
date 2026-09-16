<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    CirclePause,
    ClipboardCheck,
    Play,
    RotateCcw,
    SearchCheck,
} from '@lucide/vue';

const props = defineProps<{
    task: any;
}>();

const breadcrumbs = [
    {
        title: 'Tareas',
        href: '/tasks',
    },
    {
        title: props.task.task_number,
        href: `/tasks/${props.task.id}`,
    },
];

const statusLabel = (
    status: string,
) => {
    const labels: Record<
        string,
        string
    > = {
        blocked: 'En espera',
        pending: 'Pendiente',
        in_progress: 'En progreso',
        paused: 'Pausada',
        review: 'En revisión',
        issue: 'Con inconveniente',
        completed: 'Finalizada',
    };

    return labels[status]
        ?? status;
};

const executeAction = (
    action: string,
) => {
    router.patch(
        `/tasks/${props.task.id}/${action}`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const reportIssue = () => {
    const description =
        window.prompt(
            'Describe el inconveniente:',
        );

    if (
        !description
        || !description.trim()
    ) {
        return;
    }

    router.patch(
        `/tasks/${props.task.id}/report-issue`,
        {
            issue_description:
                description,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head :title="task.task_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        {{ task.task_number }}
                    </p>

                    <h1
                        class="text-2xl font-bold"
                    >
                        {{ task.title }}
                    </h1>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        {{
                            task.work_order.client
                        }}
                        ·
                        {{
                            task.work_order.number
                        }}
                    </p>
                </div>

                <Link
                    href="/tasks"
                    class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                >
                    <ArrowLeft
                        class="h-4 w-4"
                    />
                    Volver
                </Link>
            </div>

            <div
                v-if="
                    task.status ===
                    'blocked'
                "
                class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300"
            >
                Esta tarea se habilitará
                automáticamente cuando termines
                la tarea anterior.
            </div>

            <div
                class="rounded-xl border bg-background p-6 shadow-sm"
            >
                <div
                    class="flex flex-wrap gap-3"
                >
                    <button
                        v-if="
                            task.status ===
                            'pending'
                        "
                        type="button"
                        @click="
                            executeAction(
                                'start',
                            )
                        "
                        class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-5 py-3 font-semibold text-white"
                    >
                        <Play
                            class="h-4 w-4"
                        />
                        Iniciar
                    </button>

                    <button
                        v-if="
                            task.status ===
                            'in_progress'
                        "
                        type="button"
                        @click="
                            executeAction(
                                'pause',
                            )
                        "
                        class="inline-flex items-center gap-2 rounded-lg border px-5 py-3 font-semibold"
                    >
                        <CirclePause
                            class="h-4 w-4"
                        />
                        Pausar
                    </button>

                    <button
                        v-if="
                            [
                                'paused',
                                'issue',
                                'review',
                            ].includes(
                                task.status,
                            )
                        "
                        type="button"
                        @click="
                            executeAction(
                                'resume',
                            )
                        "
                        class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-5 py-3 font-semibold text-white"
                    >
                        <RotateCcw
                            class="h-4 w-4"
                        />
                        Reanudar
                    </button>

                    <button
                        v-if="
                            task.status ===
                            'in_progress'
                        "
                        type="button"
                        @click="
                            executeAction(
                                'send-for-review',
                            )
                        "
                        class="inline-flex items-center gap-2 rounded-lg border px-5 py-3 font-semibold"
                    >
                        <SearchCheck
                            class="h-4 w-4"
                        />
                        Revisar
                    </button>

                    <button
                        v-if="
                            [
                                'in_progress',
                                'review',
                            ].includes(
                                task.status,
                            )
                        "
                        type="button"
                        @click="
                            executeAction(
                                'finish',
                            )
                        "
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-3 font-semibold text-white"
                    >
                        <CheckCircle2
                            class="h-4 w-4"
                        />
                        Finalizar
                    </button>

                    <button
                        v-if="
                            [
                                'in_progress',
                                'paused',
                            ].includes(
                                task.status,
                            )
                        "
                        type="button"
                        @click="
                            reportIssue
                        "
                        class="inline-flex items-center gap-2 rounded-lg border border-[#e84657]/30 px-5 py-3 font-semibold text-[#e84657]"
                    >
                        <AlertTriangle
                            class="h-4 w-4"
                        />
                        Reportar inconveniente
                    </button>
                </div>
            </div>

            <div
                class="grid gap-6 lg:grid-cols-2"
            >
                <div
                    class="rounded-xl border bg-background p-6"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        Detalles
                    </h2>

                    <div
                        class="mt-5 space-y-4"
                    >
                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Estado
                            </p>

                            <p
                                class="font-bold"
                            >
                                {{
                                    statusLabel(
                                        task.status,
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Responsable
                            </p>

                            <p
                                class="font-bold"
                            >
                                {{
                                    task.responsible
                                        ?.name
                                    || 'Operador principal'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Orden de trabajo
                            </p>

                            <Link
                                :href="`/work-orders/${task.work_order.id}`"
                                class="font-bold text-[#0fa7b4]"
                            >
                                {{
                                    task.work_order
                                        .number
                                }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border bg-background p-6"
                >
                    <h2
                        class="text-lg font-semibold"
                    >
                        Tiempos
                    </h2>

                    <div
                        class="mt-5 space-y-3 text-sm"
                    >
                        <div
                            class="flex justify-between gap-4"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Inicio
                            </span>

                            <strong>
                                {{
                                    task.started_at
                                    || '—'
                                }}
                            </strong>
                        </div>

                        <div
                            class="flex justify-between gap-4"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Pausa
                            </span>

                            <strong>
                                {{
                                    task.paused_at
                                    || '—'
                                }}
                            </strong>
                        </div>

                        <div
                            class="flex justify-between gap-4"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Finalización
                            </span>

                            <strong>
                                {{
                                    task.completed_at
                                    || '—'
                                }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="task.description"
                class="rounded-xl border bg-background p-6"
            >
                <h2 class="font-semibold">
                    Instrucciones
                </h2>

                <p
                    class="mt-3 whitespace-pre-line text-sm"
                >
                    {{ task.description }}
                </p>
            </div>

            <div
                v-if="
                    task.issue_description
                "
                class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900 dark:bg-red-950/30"
            >
                <h2
                    class="font-semibold text-red-700 dark:text-red-300"
                >
                    Inconveniente reportado
                </h2>

                <p class="mt-3 text-sm">
                    {{
                        task.issue_description
                    }}
                </p>
            </div>

            <div
                class="rounded-xl border bg-background p-6"
            >
                <div
                    class="mb-5 flex items-center gap-3"
                >
                    <ClipboardCheck
                        class="h-5 w-5 text-[#0fa7b4]"
                    />

                    <h2
                        class="font-semibold"
                    >
                        Historial
                    </h2>
                </div>

                <div
                    class="space-y-4"
                >
                    <div
                        v-for="
                            event in task.events
                        "
                        :key="event.id"
                        class="border-l-2 border-[#0fa7b4]/30 pl-4"
                    >
                        <p
                            class="font-semibold"
                        >
                            {{
                                event.event_type
                            }}
                        </p>

                        <p
                            class="text-xs text-muted-foreground"
                        >
                            {{
                                event.occurred_at
                            }}
                            {{
                                event.user
                                    ? `· ${event.user}`
                                    : ''
                            }}
                        </p>

                        <p
                            v-if="event.notes"
                            class="mt-1 text-sm"
                        >
                            {{ event.notes }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>