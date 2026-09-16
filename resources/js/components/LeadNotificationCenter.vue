<script setup lang="ts">
import { useLeadNotifications } from '@/composables/useLeadNotifications';
import { usePwa } from '@/composables/usePwa';

import {
    router,
    usePage,
} from '@inertiajs/vue3';

import {
    BellRing,
    CheckCircle2,
    Download,
    ExternalLink,
    Share2,
    Smartphone,
    X,
} from '@lucide/vue';

import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';

type Lead = Record<string, any>;

const page =
    usePage<any>();

/*
|--------------------------------------------------------------------------
| AUTORIZACIÓN
|--------------------------------------------------------------------------
|
| Únicamente Administrador y Ventas necesitan:
|
| - polling de nuevas solicitudes;
| - popup de nuevos leads;
| - Web Push asociado a solicitudes.
|
| La PWA sigue disponible para todos los usuarios.
|
*/

const roles =
    computed<string[]>(
        () => {
            const value =
                page.props
                    ?.authz
                    ?.roles;

            if (
                !Array.isArray(
                    value,
                )
            ) {
                return [];
            }

            return value
                .map(
                    (
                        role: unknown,
                    ) =>
                        String(
                            role,
                        )
                            .trim()
                            .toLowerCase(),
                )
                .filter(
                    Boolean,
                );
        },
    );

const canHandleLeads =
    computed<boolean>(
        () => {
            if (
                Boolean(
                    page.props
                        ?.authz
                        ?.is_admin,
                )
            ) {
                return true;
            }

            return (
                roles.value
                    .includes(
                        'admin',
                    )
                ||
                roles.value
                    .includes(
                        'ventas',
                    )
            );
        },
    );

/*
|--------------------------------------------------------------------------
| PWA
|--------------------------------------------------------------------------
*/

const {
    installed,

    isIos,

    canPromptInstall,

    needsIosInstructions,

    shouldOfferInstall,

    initializePwa,

    installApp,

    dismissInstall,
} = usePwa();

/*
|--------------------------------------------------------------------------
| WEB PUSH
|--------------------------------------------------------------------------
*/

const {
    supported:
        notificationsSupported,

    permission:
        notificationPermission,

    subscribed,

    loading:
        notificationLoading,

    lastError:
        notificationError,

    initializeNotifications,

    subscribeToNotifications,
} = useLeadNotifications();

/*
|--------------------------------------------------------------------------
| SOLICITUDES
|--------------------------------------------------------------------------
*/

const lead =
    ref<Lead | null>(
        null,
    );

const lastLeadId =
    ref<number>(
        Number(
            page.props
                ?.leadState
                ?.latest_id
            ?? 0,
        ),
    );

const polling =
    ref(false);

let pollingTimer:
    number
    | null =
    null;

let leadCloseTimer:
    number
    | null =
    null;

/*
|--------------------------------------------------------------------------
| RECORDAR SI SE OCULTÓ EL AVISO PUSH
|--------------------------------------------------------------------------
*/

const NOTIFICATION_DISMISS_KEY =
    'adn-notifications-prompt-dismissed';

const notificationPromptDismissed =
    ref(false);

const readNotificationDismiss =
    (): void => {
        if (
            typeof window ===
            'undefined'
        ) {
            return;
        }

        notificationPromptDismissed.value =
            window.localStorage
                .getItem(
                    NOTIFICATION_DISMISS_KEY,
                ) ===
                '1';
    };

const dismissNotificationPrompt =
    (): void => {
        notificationPromptDismissed.value =
            true;

        if (
            typeof window ===
            'undefined'
        ) {
            return;
        }

        window.localStorage
            .setItem(
                NOTIFICATION_DISMISS_KEY,
                '1',
            );
    };

/*
|--------------------------------------------------------------------------
| MOSTRAR ACTIVACIÓN DE PUSH
|--------------------------------------------------------------------------
*/

const showNotificationPrompt =
    computed<boolean>(
        () => {
            /*
            |--------------------------------------------------------------------------
            | SOLO ADMINISTRADOR / VENTAS
            |--------------------------------------------------------------------------
            */

            if (
                !canHandleLeads.value
            ) {
                return false;
            }

            if (
                notificationPromptDismissed.value
                ||
                !notificationsSupported.value
                ||
                subscribed.value
                ||
                notificationPermission.value ===
                    'denied'
            ) {
                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | iOS
            |--------------------------------------------------------------------------
            |
            | Primero debe instalarse la PWA.
            |
            */

            if (
                isIos.value
                &&
                !installed.value
            ) {
                return false;
            }

            return (
                notificationPermission.value ===
                    'default'
                ||
                notificationPermission.value ===
                    'granted'
            );
        },
    );

/*
|--------------------------------------------------------------------------
| NORMALIZAR RESPUESTA DE SOLICITUDES
|--------------------------------------------------------------------------
*/

const extractLeads = (
    payload: any,
): Lead[] => {
    if (
        Array.isArray(
            payload,
        )
    ) {
        return payload;
    }

    if (
        Array.isArray(
            payload?.leads,
        )
    ) {
        return payload.leads;
    }

    if (
        Array.isArray(
            payload?.data,
        )
    ) {
        return payload.data;
    }

    if (
        Array.isArray(
            payload?.items,
        )
    ) {
        return payload.items;
    }

    if (
        payload?.lead
    ) {
        return [
            payload.lead,
        ];
    }

    return [];
};

const leadId = (
    item: Lead,
): number => {
    return Number(
        item.id
        ??
        item.lead_id
        ??
        0,
    );
};

const leadName = (
    item: Lead,
): string => {
    return String(
        item.name
        ??
        item.full_name
        ??
        item.client_name
        ??
        'Nuevo cliente',
    );
};

const leadService = (
    item: Lead,
): string => {
    return String(
        item.service
        ??
        item.service_name
        ??
        item.subject
        ??
        item.request_type
        ??
        'Nueva solicitud desde el sitio web',
    );
};

const leadMessage = (
    item: Lead,
): string | null => {
    const value =
        item.message
        ??
        item.description
        ??
        item.details
        ??
        null;

    return value
        ? String(
            value,
        )
        : null;
};

/*
|--------------------------------------------------------------------------
| MOSTRAR SOLICITUD
|--------------------------------------------------------------------------
*/

const showLead = (
    item: Lead,
): void => {
    if (
        !canHandleLeads.value
    ) {
        return;
    }

    lead.value =
        item;

    if (
        leadCloseTimer !==
        null
    ) {
        window.clearTimeout(
            leadCloseTimer,
        );
    }

    leadCloseTimer =
        window.setTimeout(
            () => {
                lead.value =
                    null;
            },
            15000,
        );
};

/*
|--------------------------------------------------------------------------
| CERRAR POPUP
|--------------------------------------------------------------------------
*/

const closeLead =
    (): void => {
        lead.value =
            null;

        if (
            leadCloseTimer !==
            null
        ) {
            window.clearTimeout(
                leadCloseTimer,
            );

            leadCloseTimer =
                null;
        }
    };

/*
|--------------------------------------------------------------------------
| ABRIR SOLICITUD
|--------------------------------------------------------------------------
*/

const openLead =
    (): void => {
        if (
            !canHandleLeads.value
        ) {
            return;
        }

        const id =
            lead.value
                ? leadId(
                    lead.value,
                )
                : null;

        closeLead();

        router.visit(
            id
                ? `/website/leads?lead=${id}`
                : '/website/leads',
        );
    };

/*
|--------------------------------------------------------------------------
| POLLING
|--------------------------------------------------------------------------
*/

const pollLeads =
    async (): Promise<void> => {
        /*
        |--------------------------------------------------------------------------
        | SEGURIDAD / EFICIENCIA
        |--------------------------------------------------------------------------
        */

        if (
            !canHandleLeads.value
        ) {
            return;
        }

        if (
            polling.value
            ||
            document.hidden
        ) {
            return;
        }

        polling.value =
            true;

        try {
            const response =
                await fetch(
                    `/notifications/leads?after_id=${lastLeadId.value}`,
                    {
                        method:
                            'GET',

                        credentials:
                            'same-origin',

                        headers: {
                            Accept:
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },
                    },
                );

            /*
            |--------------------------------------------------------------------------
            | NO AUTORIZADO
            |--------------------------------------------------------------------------
            |
            | No generamos errores visuales si el backend devuelve 403.
            |
            */

            if (
                response.status ===
                403
            ) {
                return;
            }

            if (
                !response.ok
            ) {
                return;
            }

            const payload =
                await response.json();

            const items =
                extractLeads(
                    payload,
                );

            let newestId =
                Number(
                    payload.latest_id
                    ??
                    lastLeadId.value,
                );

            items.forEach(
                (
                    item,
                ) => {
                    newestId =
                        Math.max(
                            newestId,
                            leadId(
                                item,
                            ),
                        );
                },
            );

            if (
                items.length >
                0
            ) {
                const newest =
                    [...items]
                        .sort(
                            (
                                a,
                                b,
                            ) =>
                                leadId(
                                    b,
                                )
                                -
                                leadId(
                                    a,
                                ),
                        )[0];

                if (
                    newest
                ) {
                    showLead(
                        newest,
                    );
                }
            }

            lastLeadId.value =
                Math.max(
                    lastLeadId.value,
                    newestId,
                );
        } catch (
            error
        ) {
            console.error(
                '[ADN Leads] Error consultando solicitudes:',
                error,
            );
        } finally {
            polling.value =
                false;
        }
    };

/*
|--------------------------------------------------------------------------
| INSTALACIÓN PWA
|--------------------------------------------------------------------------
*/

const installing =
    ref(false);

const triggerInstall =
    async (): Promise<void> => {
        installing.value =
            true;

        try {
            await installApp();
        } finally {
            installing.value =
                false;
        }
    };

/*
|--------------------------------------------------------------------------
| ACTIVAR WEB PUSH
|--------------------------------------------------------------------------
*/

const activatingNotifications =
    ref(false);

const activateNotifications =
    async (): Promise<void> => {
        if (
            !canHandleLeads.value
        ) {
            return;
        }

        activatingNotifications.value =
            true;

        try {
            const success =
                await subscribeToNotifications();

            if (
                success
            ) {
                if (
                    typeof window !==
                    'undefined'
                ) {
                    window.localStorage
                        .removeItem(
                            NOTIFICATION_DISMISS_KEY,
                        );
                }

                notificationPromptDismissed.value =
                    false;
            }
        } finally {
            activatingNotifications.value =
                false;
        }
    };

/*
|--------------------------------------------------------------------------
| MENSAJES DEL SERVICE WORKER
|--------------------------------------------------------------------------
*/

const handleServiceWorkerMessage = (
    event: MessageEvent,
): void => {
    if (
        !canHandleLeads.value
    ) {
        return;
    }

    if (
        event.data?.type ===
        'ADN_PUSH_RECEIVED'
    ) {
        void pollLeads();
    }
};

/*
|--------------------------------------------------------------------------
| VISIBILIDAD DE LA APP
|--------------------------------------------------------------------------
*/

const handleVisibility =
    (): void => {
        if (
            !canHandleLeads.value
        ) {
            return;
        }

        if (
            !document.hidden
        ) {
            void pollLeads();
        }
    };

/*
|--------------------------------------------------------------------------
| INICIAR POLLING
|--------------------------------------------------------------------------
*/

const startLeadPolling =
    (): void => {
        if (
            !canHandleLeads.value
        ) {
            return;
        }

        if (
            pollingTimer !==
            null
        ) {
            return;
        }

        pollingTimer =
            window.setInterval(
                () => {
                    void pollLeads();
                },
                8000,
            );
    };

/*
|--------------------------------------------------------------------------
| DETENER POLLING
|--------------------------------------------------------------------------
*/

const stopLeadPolling =
    (): void => {
        if (
            pollingTimer ===
            null
        ) {
            return;
        }

        window.clearInterval(
            pollingTimer,
        );

        pollingTimer =
            null;
    };

/*
|--------------------------------------------------------------------------
| MOUNT
|--------------------------------------------------------------------------
*/

onMounted(
    async () => {
        /*
        |--------------------------------------------------------------------------
        | PWA PARA TODOS
        |--------------------------------------------------------------------------
        */

        await initializePwa();

        /*
        |--------------------------------------------------------------------------
        | LEADS / PUSH SOLO PARA ADMIN Y VENTAS
        |--------------------------------------------------------------------------
        */

        if (
            canHandleLeads.value
        ) {
            readNotificationDismiss();

            await initializeNotifications();

            startLeadPolling();

            document.addEventListener(
                'visibilitychange',
                handleVisibility,
            );

            if (
                'serviceWorker'
                in navigator
            ) {
                navigator
                    .serviceWorker
                    .addEventListener(
                        'message',
                        handleServiceWorkerMessage,
                    );
            }
        }
    },
);

/*
|--------------------------------------------------------------------------
| UNMOUNT
|--------------------------------------------------------------------------
*/

onBeforeUnmount(
    () => {
        stopLeadPolling();

        if (
            leadCloseTimer !==
            null
        ) {
            window.clearTimeout(
                leadCloseTimer,
            );

            leadCloseTimer =
                null;
        }

        document.removeEventListener(
            'visibilitychange',
            handleVisibility,
        );

        if (
            typeof navigator !==
                'undefined'
            &&
            'serviceWorker'
                in navigator
        ) {
            navigator
                .serviceWorker
                .removeEventListener(
                    'message',
                    handleServiceWorkerMessage,
                );
        }
    },
);
</script>

<template>
    <Teleport
        to="body"
    >
        <div
            class="pointer-events-none fixed inset-x-3 bottom-3 z-[100] flex flex-col items-end gap-2 md:left-auto md:right-4 md:w-[370px]"
        >
            <!-- ========================================================= -->
            <!-- NUEVA SOLICITUD -->
            <!-- ========================================================= -->

            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="translate-y-6 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-4 opacity-0"
            >
                <article
                    v-if="
                        canHandleLeads
                        &&
                        lead
                    "
                    class="pointer-events-auto w-full overflow-hidden rounded-[1.3rem] border border-[#e84657]/20 bg-[#081115]/[0.98] shadow-[0_25px_70px_rgba(0,0,0,.55)] backdrop-blur-xl"
                >
                    <div
                        class="h-[2px] bg-gradient-to-r from-[#e84657] via-[#0fa7b4] to-[#22c6d2]"
                    />

                    <div
                        class="p-4"
                    >
                        <div
                            class="flex items-start gap-3"
                        >
                            <div
                                class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e84657]/10 text-[#f06472]"
                            >
                                <BellRing
                                    class="h-4 w-4"
                                />

                                <span
                                    class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-[#e84657] ring-4 ring-[#081115]"
                                />
                            </div>

                            <div
                                class="min-w-0 flex-1"
                            >
                                <p
                                    class="text-[9px] font-black uppercase tracking-[0.14em] text-[#e84657]"
                                >
                                    Nueva solicitud
                                </p>

                                <h3
                                    class="mt-1 truncate text-sm font-black text-white"
                                >
                                    {{
                                        leadName(
                                            lead,
                                        )
                                    }}
                                </h3>

                                <p
                                    class="mt-1 text-xs font-semibold text-[#22c6d2]"
                                >
                                    {{
                                        leadService(
                                            lead,
                                        )
                                    }}
                                </p>

                                <p
                                    v-if="
                                        leadMessage(
                                            lead,
                                        )
                                    "
                                    class="mt-2 line-clamp-2 text-xs leading-5 text-white/45"
                                >
                                    {{
                                        leadMessage(
                                            lead,
                                        )
                                    }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white/30 transition hover:bg-white/5 hover:text-white"
                                @click="
                                    closeLead
                                "
                            >
                                <X
                                    class="h-4 w-4"
                                />
                            </button>
                        </div>

                        <button
                            type="button"
                            class="adn-shine mt-4 flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white"
                            @click="
                                openLead
                            "
                        >
                            Ver solicitud

                            <ExternalLink
                                class="h-3.5 w-3.5"
                            />
                        </button>
                    </div>
                </article>
            </Transition>

            <!-- ========================================================= -->
            <!-- INSTALACIÓN CHROMIUM -->
            <!-- ========================================================= -->

            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-5 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200"
                leave-to-class="translate-y-4 opacity-0"
            >
                <article
                    v-if="
                        shouldOfferInstall
                        &&
                        canPromptInstall
                    "
                    class="pointer-events-auto w-full rounded-[1.3rem] border border-[#0fa7b4]/20 bg-[#081115]/[0.98] p-4 shadow-[0_20px_60px_rgba(0,0,0,.5)] backdrop-blur-xl"
                >
                    <div
                        class="flex items-start gap-3"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <Download
                                class="h-4 w-4"
                            />
                        </div>

                        <div
                            class="min-w-0 flex-1"
                        >
                            <p
                                class="text-sm font-black text-white"
                            >
                                Instalar ADN Publicidad
                            </p>

                            <p
                                class="mt-1 text-[11px] leading-5 text-white/40"
                            >
                                Instala el sistema para abrirlo como una aplicación independiente.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center text-white/30"
                            @click="
                                dismissInstall
                            "
                        >
                            <X
                                class="h-4 w-4"
                            />
                        </button>
                    </div>

                    <button
                        type="button"
                        :disabled="
                            installing
                        "
                        class="mt-3 flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-xs font-black text-white disabled:opacity-50"
                        @click="
                            triggerInstall
                        "
                    >
                        <Download
                            class="h-3.5 w-3.5"
                        />

                        {{
                            installing
                                ? 'Instalando...'
                                : 'Instalar aplicación'
                        }}
                    </button>
                </article>
            </Transition>

            <!-- ========================================================= -->
            <!-- INSTALACIÓN iOS -->
            <!-- ========================================================= -->

            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-5 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200"
                leave-to-class="translate-y-4 opacity-0"
            >
                <article
                    v-if="
                        shouldOfferInstall
                        &&
                        needsIosInstructions
                    "
                    class="pointer-events-auto w-full rounded-[1.3rem] border border-[#0fa7b4]/20 bg-[#081115]/[0.98] p-4 shadow-[0_20px_60px_rgba(0,0,0,.5)] backdrop-blur-xl"
                >
                    <div
                        class="flex items-start gap-3"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <Smartphone
                                class="h-4 w-4"
                            />
                        </div>

                        <div
                            class="min-w-0 flex-1"
                        >
                            <p
                                class="text-sm font-black text-white"
                            >
                                Instalar en iPhone
                            </p>

                            <p
                                class="mt-1 text-[11px] leading-5 text-white/45"
                            >
                                En Safari toca

                                <span
                                    class="inline-flex items-center gap-1 font-black text-[#22c6d2]"
                                >
                                    Compartir

                                    <Share2
                                        class="h-3 w-3"
                                    />
                                </span>

                                y selecciona

                                <strong
                                    class="text-white/75"
                                >
                                    “Añadir a pantalla de inicio”.
                                </strong>
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center text-white/30"
                            @click="
                                dismissInstall
                            "
                        >
                            <X
                                class="h-4 w-4"
                            />
                        </button>
                    </div>
                </article>
            </Transition>

            <!-- ========================================================= -->
            <!-- ACTIVAR NOTIFICACIONES -->
            <!-- ========================================================= -->

            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-5 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200"
                leave-to-class="translate-y-4 opacity-0"
            >
                <article
                    v-if="
                        showNotificationPrompt
                    "
                    class="pointer-events-auto w-full rounded-[1.3rem] border border-violet-500/20 bg-[#081115]/[0.98] p-4 shadow-[0_20px_60px_rgba(0,0,0,.5)] backdrop-blur-xl"
                >
                    <div
                        class="flex items-start gap-3"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400"
                        >
                            <BellRing
                                class="h-4 w-4"
                            />
                        </div>

                        <div
                            class="min-w-0 flex-1"
                        >
                            <p
                                class="text-sm font-black text-white"
                            >
                                Activar notificaciones
                            </p>

                            <p
                                class="mt-1 text-[11px] leading-5 text-white/40"
                            >
                                Recibe un aviso cuando entre una nueva solicitud aunque no estés viendo el sistema.
                            </p>

                            <p
                                v-if="
                                    notificationError
                                "
                                class="mt-2 text-[10px] leading-4 text-[#f06472]"
                            >
                                {{
                                    notificationError
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center text-white/30"
                            @click="
                                dismissNotificationPrompt
                            "
                        >
                            <X
                                class="h-4 w-4"
                            />
                        </button>
                    </div>

                    <button
                        type="button"
                        :disabled="
                            activatingNotifications
                            ||
                            notificationLoading
                        "
                        class="mt-3 flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-violet-500 text-xs font-black text-white disabled:opacity-50"
                        @click="
                            activateNotifications
                        "
                    >
                        <BellRing
                            class="h-3.5 w-3.5"
                        />

                        {{
                            activatingNotifications
                                ? 'Activando...'
                                : 'Activar notificaciones'
                        }}
                    </button>
                </article>
            </Transition>

            <!-- ========================================================= -->
            <!-- ESTADO PUSH -->
            <!-- ========================================================= -->

            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-5 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
            >
                <div
                    v-if="
                        canHandleLeads
                        &&
                        subscribed
                    "
                    class="pointer-events-none hidden items-center gap-2 rounded-full border border-emerald-500/15 bg-[#081115]/90 px-3 py-2 text-[10px] font-bold text-emerald-400 shadow-lg md:flex"
                >
                    <CheckCircle2
                        class="h-3.5 w-3.5"
                    />

                    Notificaciones activas
                </div>
            </Transition>
        </div>
    </Teleport>
</template>