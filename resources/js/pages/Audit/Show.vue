<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Database,
    Globe2,
    Monitor,
    ShieldCheck,
    UserRound,
} from '@lucide/vue';
import {
    computed,
} from 'vue';

const props = defineProps<{
    log: any;
}>();

const breadcrumbs = [
    {
        title: 'Auditoría',
        href: '/audit',
    },
    {
        title:
            `Evento #${props.log.id}`,
        href:
            `/audit/${props.log.id}`,
    },
];

const payload =
    computed(() => {
        if (
            !props.log
                .request_payload
        ) {
            return 'Sin datos adicionales.';
        }

        return JSON.stringify(
            props.log
                .request_payload,
            null,
            2,
        );
    });

const success =
    computed(
        () =>
            Number(
                props.log
                    .status_code,
            ) < 400,
    );
</script>

<template>
    <Head
        :title="
            `Auditoría #${log.id}`
        "
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        Evento de auditoría
                    </p>

                    <h1
                        class="text-3xl font-bold"
                    >
                        {{
                            log.description
                        }}
                    </h1>

                    <p
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{
                            log.created_at
                        }}
                    </p>
                </div>

                <Link
                    href="/audit"
                    class="inline-flex items-center gap-2 rounded-xl border px-5 py-3 font-semibold"
                >
                    <ArrowLeft
                        class="h-4 w-4"
                    />

                    Volver
                </Link>
            </div>

            <div
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
            >
                <div
                    class="rounded-2xl border bg-background p-5"
                >
                    <UserRound
                        class="h-5 w-5 text-[#0fa7b4]"
                    />

                    <p
                        class="mt-4 text-xs uppercase text-muted-foreground"
                    >
                        Usuario
                    </p>

                    <p
                        class="mt-1 font-bold"
                    >
                        {{
                            log.user.name
                            ||
                            'Sistema'
                        }}
                    </p>

                    <p
                        class="text-xs text-muted-foreground"
                    >
                        {{
                            log.user.email
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-background p-5"
                >
                    <ShieldCheck
                        class="h-5 w-5 text-emerald-500"
                    />

                    <p
                        class="mt-4 text-xs uppercase text-muted-foreground"
                    >
                        Acción
                    </p>

                    <p
                        class="mt-1 font-bold"
                    >
                        {{
                            log.action
                        }}
                    </p>

                    <p
                        class="text-xs text-muted-foreground"
                    >
                        {{
                            log.module
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-background p-5"
                >
                    <Globe2
                        class="h-5 w-5 text-violet-500"
                    />

                    <p
                        class="mt-4 text-xs uppercase text-muted-foreground"
                    >
                        IP
                    </p>

                    <p
                        class="mt-1 font-bold"
                    >
                        {{
                            log.ip_address
                            ||
                            '—'
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border bg-background p-5"
                >
                    <Monitor
                        class="h-5 w-5"
                        :class="
                            success
                                ? 'text-emerald-500'
                                : 'text-red-500'
                        "
                    />

                    <p
                        class="mt-4 text-xs uppercase text-muted-foreground"
                    >
                        Resultado HTTP
                    </p>

                    <p
                        class="mt-1 text-2xl font-bold"
                    >
                        {{
                            log.status_code
                        }}
                    </p>
                </div>
            </div>

            <section
                class="rounded-2xl border bg-background p-6"
            >
                <h2
                    class="font-bold"
                >
                    Información técnica
                </h2>

                <div
                    class="mt-5 grid gap-5 md:grid-cols-2"
                >
                    <div>
                        <p
                            class="text-xs uppercase text-muted-foreground"
                        >
                            Ruta
                        </p>

                        <p
                            class="mt-1 font-mono text-sm"
                        >
                            {{
                                log.route_name
                                ||
                                '—'
                            }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs uppercase text-muted-foreground"
                        >
                            Método
                        </p>

                        <p
                            class="mt-1 font-bold"
                        >
                            {{
                                log.method
                            }}
                        </p>
                    </div>

                    <div
                        class="md:col-span-2"
                    >
                        <p
                            class="text-xs uppercase text-muted-foreground"
                        >
                            URL
                        </p>

                        <p
                            class="mt-1 break-all font-mono text-sm"
                        >
                            {{
                                log.url
                            }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs uppercase text-muted-foreground"
                        >
                            Tipo de entidad
                        </p>

                        <p
                            class="mt-1 font-mono text-sm"
                        >
                            {{
                                log.entity_type
                                ||
                                '—'
                            }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs uppercase text-muted-foreground"
                        >
                            ID de entidad
                        </p>

                        <p
                            class="mt-1 font-bold"
                        >
                            {{
                                log.entity_id
                                ||
                                '—'
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                v-if="
                    log.employee
                "
                class="rounded-2xl border bg-background p-6"
            >
                <h2
                    class="font-bold"
                >
                    Empleado asociado
                </h2>

                <p
                    class="mt-3 text-lg font-semibold"
                >
                    {{
                        log.employee.name
                    }}
                </p>

                <p
                    class="text-sm text-muted-foreground"
                >
                    {{
                        log.employee.code
                    }}
                </p>
            </section>

            <section
                class="rounded-2xl border bg-background p-6"
            >
                <div
                    class="flex items-center gap-3"
                >
                    <Database
                        class="h-5 w-5 text-[#0fa7b4]"
                    />

                    <h2
                        class="font-bold"
                    >
                        Datos enviados
                    </h2>
                </div>

                <pre
                    class="mt-5 max-h-[600px] overflow-auto rounded-xl bg-[#111] p-5 text-xs leading-6 text-white"
                >{{ payload }}</pre>
            </section>

            <section
                class="rounded-2xl border bg-background p-6"
            >
                <p
                    class="text-xs uppercase text-muted-foreground"
                >
                    Navegador / dispositivo
                </p>

                <p
                    class="mt-2 break-all text-sm"
                >
                    {{
                        log.user_agent
                        ||
                        'No disponible'
                    }}
                </p>
            </section>
        </div>
    </AppLayout>
</template>