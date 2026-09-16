<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarClock,
    CheckCircle2,
    Clock3,
    MapPin,
    Navigation,
    Pencil,
    Phone,
    Wrench,
    XCircle,
} from '@lucide/vue';

const props = defineProps<{
    installation: any;
    allowedStatuses: string[];
}>();

const breadcrumbs = [
    {
        title: 'Instalaciones',
        href: '/installations',
    },
    {
        title:
            props.installation
                .installation_number,
        href:
            `/installations/${props.installation.id}`,
    },
];

const completionForm = useForm({
    status: 'completed',
    completion_notes:
        props.installation
            .completion_notes
        ?? '',
});

const statusLabel = (
    status: string,
) => {
    const labels: Record<
        string,
        string
    > = {
        pending_schedule:
            'Pendiente de programar',

        scheduled:
            'Programada',

        on_route:
            'En camino',

        in_progress:
            'Instalando',

        completed:
            'Finalizada',

        cancelled:
            'Cancelada',
    };

    return labels[status] ?? status;
};

const statusIcon = (
    status: string,
) => {
    if (
        status ===
        'scheduled'
    ) {
        return CalendarClock;
    }

    if (
        status ===
        'on_route'
    ) {
        return Navigation;
    }

    if (
        status ===
        'in_progress'
    ) {
        return Wrench;
    }

    if (
        status ===
        'completed'
    ) {
        return CheckCircle2;
    }

    if (
        status ===
        'cancelled'
    ) {
        return XCircle;
    }

    return Clock3;
};

const changeStatus = (
    status: string,
) => {
    if (
        !window.confirm(
            `¿Cambiar la instalación a "${statusLabel(
                status,
            )}"?`,
        )
    ) {
        return;
    }

    router.patch(
        `/installations/${props.installation.id}`,
        {
            status,
        },
        {
            preserveScroll: true,
        },
    );
};

const complete = () => {
    if (
        !window.confirm(
            '¿Confirmar que la instalación fue finalizada?',
        )
    ) {
        return;
    }

    completionForm.patch(
        `/installations/${props.installation.id}`,
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head
        :title="
            installation.installation_number
        "
    />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="flex flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        {{
                            installation.installation_number
                        }}
                    </p>

                    <h1
                        class="text-3xl font-bold"
                    >
                        {{
                            installation.work_order_title
                        }}
                    </h1>

                    <p
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{
                            installation.client_name
                        }}
                        ·
                        {{
                            statusLabel(
                                installation.status,
                            )
                        }}
                    </p>
                </div>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <Link
                        href="/installations"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <ArrowLeft
                            class="h-4 w-4"
                        />
                        Volver
                    </Link>

                    <Link
                        v-if="
                            installation.status !==
                            'completed'
                        "
                        :href="`/installations/${installation.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                    >
                        <Pencil
                            class="h-4 w-4"
                        />
                        Programar / editar
                    </Link>
                </div>
            </div>

            <div
                v-if="
                    allowedStatuses.length > 0
                "
                class="rounded-2xl border bg-background p-5"
            >
                <p
                    class="mb-3 text-sm font-semibold"
                >
                    Siguiente acción
                </p>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <button
                        v-for="
                            status in allowedStatuses.filter(
                                (item) =>
                                    item !==
                                    'completed',
                            )
                        "
                        :key="status"
                        type="button"
                        @click="
                            changeStatus(
                                status,
                            )
                        "
                        class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4]"
                    >
                        <component
                            :is="
                                statusIcon(
                                    status,
                                )
                            "
                            class="h-4 w-4"
                        />

                        {{
                            statusLabel(
                                status,
                            )
                        }}
                    </button>
                </div>
            </div>

            <div
                class="grid gap-6 lg:grid-cols-2"
            >
                <div
                    class="rounded-2xl border bg-background p-6"
                >
                    <h2
                        class="font-bold"
                    >
                        Programación
                    </h2>

                    <div
                        class="mt-5 space-y-5"
                    >
                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Fecha y hora
                            </p>

                            <p
                                class="mt-1 font-semibold"
                            >
                                {{
                                    installation.scheduled_display
                                    || 'Pendiente de programar'
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
                                class="mt-1 font-semibold"
                            >
                                {{
                                    installation.responsible
                                    || 'Operador principal'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Duración estimada
                            </p>

                            <p
                                class="mt-1 font-semibold"
                            >
                                {{
                                    installation.estimated_duration_minutes
                                        ? `${installation.estimated_duration_minutes} minutos`
                                        : 'Sin estimar'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border bg-background p-6"
                >
                    <h2
                        class="font-bold"
                    >
                        Ubicación y contacto
                    </h2>

                    <div
                        class="mt-5 space-y-5"
                    >
                        <div
                            class="flex gap-3"
                        >
                            <MapPin
                                class="mt-1 h-5 w-5 shrink-0 text-[#0fa7b4]"
                            />

                            <div>
                                <p
                                    class="font-semibold"
                                >
                                    {{
                                        installation.address
                                        || 'Sin dirección'
                                    }}
                                </p>

                                <p
                                    v-if="
                                        installation.city
                                    "
                                    class="text-sm text-muted-foreground"
                                >
                                    {{
                                        installation.city
                                    }}
                                </p>

                                <p
                                    v-if="
                                        installation.reference
                                    "
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    Referencia:
                                    {{
                                        installation.reference
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex gap-3"
                        >
                            <Phone
                                class="mt-1 h-5 w-5 shrink-0 text-[#0fa7b4]"
                            />

                            <div>
                                <p
                                    class="font-semibold"
                                >
                                    {{
                                        installation.contact_name
                                        || installation.client_name
                                    }}
                                </p>

                                <p
                                    class="text-sm text-muted-foreground"
                                >
                                    {{
                                        installation.contact_phone
                                        || 'Sin teléfono'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border bg-background p-6"
            >
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <div>
                        <h2
                            class="font-bold"
                        >
                            Orden de trabajo
                        </h2>

                        <p
                            class="text-sm text-muted-foreground"
                        >
                            {{
                                installation.work_order_number
                            }}
                        </p>
                    </div>

                    <Link
                        :href="`/work-orders/${installation.work_order_id}`"
                        class="font-semibold text-[#0fa7b4]"
                    >
                        Abrir OT →
                    </Link>
                </div>

                <div
                    v-if="
                        installation.items?.length
                    "
                    class="mt-5 space-y-3"
                >
                    <div
                        v-for="
                            item in installation.items
                        "
                        :key="item.id"
                        class="rounded-xl border p-4"
                    >
                        <div
                            class="flex justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="font-semibold"
                                >
                                    {{ item.name }}
                                </p>

                                <p
                                    v-if="
                                        item.description
                                    "
                                    class="mt-1 text-sm text-muted-foreground"
                                >
                                    {{
                                        item.description
                                    }}
                                </p>
                            </div>

                            <strong>
                                x{{ item.quantity }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="
                    installation.notes
                "
                class="rounded-2xl border bg-background p-6"
            >
                <h2
                    class="font-bold"
                >
                    Notas de instalación
                </h2>

                <p
                    class="mt-3 whitespace-pre-line text-sm"
                >
                    {{
                        installation.notes
                    }}
                </p>
            </div>

            <div
                v-if="
                    installation.status ===
                    'in_progress'
                "
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-900 dark:bg-emerald-950/30"
            >
                <h2
                    class="font-bold"
                >
                    Finalizar instalación
                </h2>

                <p
                    class="mt-1 text-sm text-muted-foreground"
                >
                    Registra cualquier observación
                    final antes de cerrar el
                    trabajo.
                </p>

                <textarea
                    v-model="
                        completionForm.completion_notes
                    "
                    rows="4"
                    placeholder="Trabajo instalado correctamente, observaciones, pendientes..."
                    class="mt-4 w-full rounded-lg border bg-background px-4 py-3"
                />

                <button
                    type="button"
                    @click="complete"
                    :disabled="
                        completionForm.processing
                    "
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white"
                >
                    <CheckCircle2
                        class="h-5 w-5"
                    />

                    Confirmar instalación
                </button>
            </div>

            <div
                v-if="
                    installation.status ===
                    'completed'
                "
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-900 dark:bg-emerald-950/30"
            >
                <div
                    class="flex gap-4"
                >
                    <CheckCircle2
                        class="h-7 w-7 shrink-0 text-emerald-600"
                    />

                    <div>
                        <h2
                            class="font-bold text-emerald-700 dark:text-emerald-300"
                        >
                            Instalación finalizada
                        </h2>

                        <p
                            class="mt-1 text-sm"
                        >
                            {{
                                installation.completed_at
                            }}
                        </p>

                        <p
                            v-if="
                                installation.completion_notes
                            "
                            class="mt-3 whitespace-pre-line text-sm"
                        >
                            {{
                                installation.completion_notes
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="grid gap-4 md:grid-cols-3"
            >
                <div
                    class="rounded-xl border bg-background p-5"
                >
                    <p
                        class="text-xs text-muted-foreground"
                    >
                        Salida
                    </p>

                    <p
                        class="mt-2 font-semibold"
                    >
                        {{
                            installation.departed_at
                            || '—'
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5"
                >
                    <p
                        class="text-xs text-muted-foreground"
                    >
                        Inicio
                    </p>

                    <p
                        class="mt-2 font-semibold"
                    >
                        {{
                            installation.started_at
                            || '—'
                        }}
                    </p>
                </div>

                <div
                    class="rounded-xl border bg-background p-5"
                >
                    <p
                        class="text-xs text-muted-foreground"
                    >
                        Finalización
                    </p>

                    <p
                        class="mt-2 font-semibold"
                    >
                        {{
                            installation.completed_at
                            || '—'
                        }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>