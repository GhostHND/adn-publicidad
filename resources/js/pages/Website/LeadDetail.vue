<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3';

import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    Download,
    Eye,
    File,
    FileText,
    Image as ImageIcon,
    Mail,
    MessageCircle,
    Package,
    Phone,
    Ruler,
    UserCheck,
    UserRound,
    X,
} from '@lucide/vue';

import {
    computed,
    ref,
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
    contacted_at: string | null;
    created_at: string | null;
};

type WebValue = {
    id: number;
    field_key: string;
    label: string;
    value: string | null;
    unit: string | null;
    field_type: string | null;
};

type WebItem = {
    id: number;
    product_id: number | null;
    product_code: string | null;
    app_reference_code: string | null;
    product_name: string;
    quantity: string | number;
    quantity_display: string;
    unit: string | null;
    quote_mode: string | null;
    values: WebValue[];
};

type WebFile = {
    id: number;
    quote_request_item_id: number | null;
    original_name: string;
    mime_type: string | null;
    size: number | null;
    is_image: boolean;
};

type WebRequest = {
    id: number;
    request_number: string;
    status: string;
    client_name: string;
    company: string | null;
    phone: string;
    whatsapp: string | null;
    email: string | null;
    notes: string | null;
    source: string;
    privacy_consent: boolean;
    created_at: string | null;
    items: WebItem[];
    files: WebFile[];
};

const props =
    defineProps<{
        lead: Lead;
        webRequest: WebRequest | null;
        integrationError: string | null;
    }>();

const selectedImage =
    ref<WebFile | null>(
        null,
    );

const breadcrumbs =
    computed(
        () => [
            {
                title:
                    'Solicitudes',

                href:
                    '/website/leads',
            },

            {
                title:
                    props.lead
                        .lead_number,

                href:
                    `/website/leads/${props.lead.id}/detail`,
            },
        ],
    );

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
        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300',

    reviewed:
        'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300',

    attending:
        'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-900 dark:bg-violet-950/40 dark:text-violet-300',

    quoted:
        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300',

    won:
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300',

    lost:
        'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300',
};

const whatsappUrl =
    computed(
        (): string => {
            let digits =
                String(
                    props.webRequest
                        ?.whatsapp
                    ||
                    props.lead
                        .phone
                    ||
                    '',
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

            const reference =
                props.webRequest
                    ?.request_number
                ||
                props.lead
                    .lead_number;

            const message =
                encodeURIComponent(
                    `Hola ${props.lead.name}, te contactamos de ADN Publicidad con relación a tu solicitud ${reference}.`,
                );

            return digits
                ? `https://wa.me/${digits}?text=${message}`
                : '#';
        },
    );

const filePreviewUrl =
    (
        file:
            WebFile,
    ): string => {
        return `/website/leads/${props.lead.id}/files/${file.id}/view`;
    };

const fileDownloadUrl =
    (
        file:
            WebFile,
    ): string => {
        return `/website/leads/${props.lead.id}/files/${file.id}/download`;
    };

const formatBytes =
    (
        bytes:
            number | null,
    ): string => {
        if (
            !bytes
            ||
            bytes <=
                0
        ) {
            return 'Tamaño no disponible';
        }

        const units = [
            'B',
            'KB',
            'MB',
            'GB',
        ];

        let value =
            bytes;

        let index =
            0;

        while (
            value >=
                1024
            &&
            index <
                units.length
                -
                1
        ) {
            value /=
                1024;

            index +=
                1;
        }

        return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
    };

const formatDate =
    (
        value:
            string | null,
    ): string => {
        if (
            !value
        ) {
            return '—';
        }

        const date =
            new Date(
                value,
            );

        if (
            Number.isNaN(
                date.getTime(),
            )
        ) {
            return value;
        }

        return new Intl
            .DateTimeFormat(
                'es-HN',
                {
                    dateStyle:
                        'medium',

                    timeStyle:
                        'short',
                },
            )
            .format(
                date,
            );
    };

const attend =
    (): void => {
        router.post(
            `/website/leads/${props.lead.id}/attend`,
            {},
        );
    };

const markReviewed =
    (): void => {
        router.patch(
            `/website/leads/${props.lead.id}/reviewed`,
            {},
            {
                preserveScroll:
                    true,
            },
        );
    };

const changeStatus =
    (
        event:
            Event,
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
            `/website/leads/${props.lead.id}/status`,
            {
                status,
            },
            {
                preserveScroll:
                    true,
            },
        );
    };
</script>

<template>
    <Head
        :title="
            `Solicitud ${lead.lead_number}`
        "
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="mx-auto flex w-full max-w-[1500px] flex-col gap-6 p-4 sm:p-6"
        >
            <!-- Encabezado -->

            <section
                class="overflow-hidden rounded-3xl border bg-background shadow-sm"
            >
                <div
                    class="h-1.5 bg-gradient-to-r from-[#0fa7b4] via-[#ed7e24] to-[#e84657]"
                />

                <div
                    class="p-5 sm:p-7"
                >
                    <div
                        class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between"
                    >
                        <div
                            class="min-w-0"
                        >
                            <Link
                                href="/website/leads"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground transition hover:text-[#0fa7b4]"
                            >
                                <ArrowLeft
                                    class="h-4 w-4"
                                />

                                Volver a solicitudes
                            </Link>

                            <div
                                class="mt-5 flex flex-wrap items-center gap-3"
                            >
                                <p
                                    class="text-sm font-black uppercase tracking-[0.12em] text-[#0fa7b4]"
                                >
                                    {{
                                        lead.lead_number
                                    }}
                                </p>

                                <span
                                    class="rounded-full border px-3 py-1 text-xs font-bold"
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

                            <h1
                                class="mt-3 text-2xl font-black tracking-tight sm:text-3xl"
                            >
                                Solicitud de
                                {{
                                    lead.name
                                }}
                            </h1>

                            <p
                                v-if="
                                    webRequest
                                "
                                class="mt-2 text-sm text-muted-foreground"
                            >
                                Referencia original:
                                <strong
                                    class="font-bold text-foreground"
                                >
                                    {{
                                        webRequest.request_number
                                    }}
                                </strong>
                            </p>
                        </div>

                        <div
                            class="grid gap-2 sm:grid-cols-2 xl:min-w-[430px]"
                        >
                            <button
                                v-if="
                                    !lead.quotation_id
                                "
                                type="button"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#e84657] px-5 py-3 font-bold text-white shadow-sm transition hover:-translate-y-0.5"
                                @click="
                                    attend
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
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 font-bold text-white transition hover:-translate-y-0.5"
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

                            <a
                                :href="
                                    whatsappUrl
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 font-bold text-white transition hover:-translate-y-0.5"
                            >
                                <MessageCircle
                                    class="h-4 w-4"
                                />

                                WhatsApp
                            </a>

                            <button
                                v-if="
                                    lead.status ===
                                    'new'
                                "
                                type="button"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border px-5 py-3 font-bold transition hover:bg-muted"
                                @click="
                                    markReviewed
                                "
                            >
                                <CheckCircle2
                                    class="h-4 w-4"
                                />

                                Marcar revisada
                            </button>

                            <select
                                :value="
                                    lead.status
                                "
                                class="min-h-12 rounded-xl border bg-background px-4 py-3 font-semibold"
                                @change="
                                    changeStatus
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
                        </div>
                    </div>
                </div>
            </section>

            <!-- Error de conexión -->

            <section
                v-if="
                    integrationError
                "
                class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"
            >
                <p
                    class="font-bold"
                >
                    Información temporalmente limitada
                </p>

                <p
                    class="mt-1 text-sm"
                >
                    {{
                        integrationError
                    }}
                </p>
            </section>

            <!-- Cliente -->

            <div
                class="grid gap-6 xl:grid-cols-[1fr_1.25fr]"
            >
                <section
                    class="rounded-3xl border bg-background p-5 shadow-sm sm:p-6"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                        >
                            <UserRound
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.12em] text-muted-foreground"
                            >
                                Cliente
                            </p>

                            <h2
                                class="font-black"
                            >
                                Datos de contacto
                            </h2>
                        </div>
                    </div>

                    <div
                        class="mt-6 space-y-4"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground"
                            >
                                Nombre
                            </p>

                            <p
                                class="mt-1 font-bold"
                            >
                                {{
                                    webRequest?.client_name
                                    ||
                                    lead.name
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                webRequest?.company
                                ||
                                lead.business_name
                            "
                            class="flex gap-3"
                        >
                            <Building2
                                class="mt-0.5 h-4 w-4 shrink-0 text-[#ed7e24]"
                            />

                            <div>
                                <p
                                    class="text-xs font-semibold text-muted-foreground"
                                >
                                    Empresa
                                </p>

                                <p
                                    class="mt-1 font-semibold"
                                >
                                    {{
                                        webRequest?.company
                                        ||
                                        lead.business_name
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex gap-3"
                        >
                            <Phone
                                class="mt-0.5 h-4 w-4 shrink-0 text-[#0fa7b4]"
                            />

                            <div>
                                <p
                                    class="text-xs font-semibold text-muted-foreground"
                                >
                                    Teléfono
                                </p>

                                <p
                                    class="mt-1 font-semibold"
                                >
                                    {{
                                        webRequest?.phone
                                        ||
                                        lead.phone
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="
                                webRequest?.whatsapp
                            "
                            class="flex gap-3"
                        >
                            <MessageCircle
                                class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500"
                            />

                            <div>
                                <p
                                    class="text-xs font-semibold text-muted-foreground"
                                >
                                    WhatsApp
                                </p>

                                <p
                                    class="mt-1 font-semibold"
                                >
                                    {{
                                        webRequest.whatsapp
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="
                                webRequest?.email
                                ||
                                lead.email
                            "
                            class="flex gap-3"
                        >
                            <Mail
                                class="mt-0.5 h-4 w-4 shrink-0 text-[#e84657]"
                            />

                            <div
                                class="min-w-0"
                            >
                                <p
                                    class="text-xs font-semibold text-muted-foreground"
                                >
                                    Correo
                                </p>

                                <p
                                    class="mt-1 break-all font-semibold"
                                >
                                    {{
                                        webRequest?.email
                                        ||
                                        lead.email
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Información -->

                <section
                    class="rounded-3xl border bg-background p-5 shadow-sm sm:p-6"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#ed7e24]/10 text-[#ed7e24]"
                        >
                            <FileText
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.12em] text-muted-foreground"
                            >
                                Solicitud
                            </p>

                            <h2
                                class="font-black"
                            >
                                Información general
                            </h2>
                        </div>
                    </div>

                    <div
                        class="mt-6 grid gap-5 sm:grid-cols-2"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground"
                            >
                                Solicitud APP
                            </p>

                            <p
                                class="mt-1 font-black text-[#0fa7b4]"
                            >
                                {{
                                    lead.lead_number
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground"
                            >
                                Solicitud Web
                            </p>

                            <p
                                class="mt-1 font-black"
                            >
                                {{
                                    webRequest?.request_number
                                    ||
                                    '—'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground"
                            >
                                Recibida
                            </p>

                            <p
                                class="mt-1 font-semibold"
                            >
                                {{
                                    webRequest
                                        ? formatDate(
                                            webRequest.created_at,
                                        )
                                        : lead.created_at
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold text-muted-foreground"
                            >
                                Responsable
                            </p>

                            <p
                                class="mt-1 font-semibold"
                            >
                                {{
                                    lead.assigned_employee
                                    ||
                                    'Sin asignar'
                                }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Productos -->

            <section
                class="rounded-3xl border bg-background p-5 shadow-sm sm:p-6"
            >
                <div
                    class="flex items-center gap-3"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#e84657]/10 text-[#e84657]"
                    >
                        <Package
                            class="h-5 w-5"
                        />
                    </div>

                    <div>
                        <p
                            class="text-xs font-black uppercase tracking-[0.12em] text-muted-foreground"
                        >
                            Cotización solicitada
                        </p>

                        <h2
                            class="font-black"
                        >
                            Productos y configuración
                        </h2>
                    </div>
                </div>

                <div
                    v-if="
                        webRequest
                        &&
                        webRequest.items.length
                    "
                    class="mt-6 space-y-5"
                >
                    <article
                        v-for="
                            item in webRequest.items
                        "
                        :key="
                            item.id
                        "
                        class="overflow-hidden rounded-2xl border"
                    >
                        <div
                            class="flex flex-col gap-4 border-b bg-muted/25 p-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h3
                                    class="text-xl font-black"
                                >
                                    {{
                                        item.product_name
                                    }}
                                </h3>

                                <div
                                    class="mt-2 flex flex-wrap gap-2"
                                >
                                    <span
                                        v-if="
                                            item.product_code
                                        "
                                        class="rounded-lg bg-[#0fa7b4]/10 px-2.5 py-1 text-xs font-bold text-[#0fa7b4]"
                                    >
                                        {{
                                            item.product_code
                                        }}
                                    </span>

                                    <span
                                        v-if="
                                            item.app_reference_code
                                        "
                                        class="rounded-lg bg-[#ed7e24]/10 px-2.5 py-1 text-xs font-bold text-[#ed7e24]"
                                    >
                                        APP:
                                        {{
                                            item.app_reference_code
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border bg-background px-5 py-3 text-center"
                            >
                                <p
                                    class="text-[10px] font-black uppercase tracking-[0.12em] text-muted-foreground"
                                >
                                    Cantidad
                                </p>

                                <p
                                    class="mt-1 text-2xl font-black"
                                >
                                    {{
                                        item.quantity_display
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="
                                    value in item.values
                                "
                                :key="
                                    value.id
                                "
                                class="rounded-2xl bg-muted/35 p-4"
                            >
                                <div
                                    class="flex items-start gap-3"
                                >
                                    <Ruler
                                        class="mt-0.5 h-4 w-4 shrink-0 text-[#0fa7b4]"
                                    />

                                    <div>
                                        <p
                                            class="text-xs font-semibold text-muted-foreground"
                                        >
                                            {{
                                                value.label
                                            }}
                                        </p>

                                        <p
                                            class="mt-1 font-black"
                                        >
                                            {{
                                                value.value
                                                ||
                                                '—'
                                            }}

                                            <span
                                                v-if="
                                                    value.unit
                                                "
                                                class="font-semibold text-muted-foreground"
                                            >
                                                {{
                                                    value.unit
                                                }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="mt-6 rounded-2xl bg-muted/30 p-6 text-sm text-muted-foreground"
                >
                    No se pudieron cargar los productos detallados.
                </div>
            </section>

            <!-- Archivos -->

            <section
                class="rounded-3xl border bg-background p-5 shadow-sm sm:p-6"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f5c000]/10 text-[#c69a00]"
                        >
                            <ImageIcon
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-xs font-black uppercase tracking-[0.12em] text-muted-foreground"
                            >
                                Material recibido
                            </p>

                            <h2
                                class="font-black"
                            >
                                Archivos del cliente
                            </h2>
                        </div>
                    </div>

                    <span
                        v-if="
                            webRequest
                        "
                        class="w-fit rounded-full border px-3 py-1 text-xs font-bold"
                    >
                        {{
                            webRequest.files.length
                        }}
                        archivo(s)
                    </span>
                </div>

                <div
                    v-if="
                        webRequest
                        &&
                        webRequest.files.length
                    "
                    class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <article
                        v-for="
                            file in webRequest.files
                        "
                        :key="
                            file.id
                        "
                        class="overflow-hidden rounded-2xl border bg-muted/15"
                    >
                        <button
                            v-if="
                                file.is_image
                            "
                            type="button"
                            class="group relative block aspect-[4/3] w-full overflow-hidden bg-black/5"
                            @click="
                                selectedImage =
                                    file
                            "
                        >
                            <img
                                :src="
                                    filePreviewUrl(
                                        file,
                                    )
                                "
                                :alt="
                                    file.original_name
                                "
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                            >

                            <span
                                class="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition group-hover:bg-black/35 group-hover:opacity-100"
                            >
                                <span
                                    class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-black shadow-xl"
                                >
                                    <Eye
                                        class="h-5 w-5"
                                    />
                                </span>
                            </span>
                        </button>

                        <div
                            v-else
                            class="flex aspect-[4/3] items-center justify-center bg-muted/40"
                        >
                            <File
                                class="h-12 w-12 text-muted-foreground"
                            />
                        </div>

                        <div
                            class="p-4"
                        >
                            <p
                                class="truncate text-sm font-bold"
                                :title="
                                    file.original_name
                                "
                            >
                                {{
                                    file.original_name
                                }}
                            </p>

                            <p
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                {{
                                    formatBytes(
                                        file.size,
                                    )
                                }}
                            </p>

                            <div
                                class="mt-4 grid grid-cols-2 gap-2"
                            >
                                <a
                                    :href="
                                        filePreviewUrl(
                                            file,
                                        )
                                    "
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2 text-xs font-bold transition hover:bg-muted"
                                >
                                    <Eye
                                        class="h-3.5 w-3.5"
                                    />

                                    Ver
                                </a>

                                <a
                                    :href="
                                        fileDownloadUrl(
                                            file,
                                        )
                                    "
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-3 py-2 text-xs font-bold text-white transition hover:opacity-90"
                                >
                                    <Download
                                        class="h-3.5 w-3.5"
                                    />

                                    Descargar
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="mt-6 rounded-2xl bg-muted/30 p-8 text-center"
                >
                    <File
                        class="mx-auto h-9 w-9 text-muted-foreground"
                    />

                    <p
                        class="mt-3 font-bold"
                    >
                        Sin archivos
                    </p>
                </div>
            </section>

            <!-- Observaciones -->

            <section
                v-if="
                    webRequest?.notes
                "
                class="rounded-3xl border bg-background p-5 shadow-sm sm:p-6"
            >
                <p
                    class="text-xs font-black uppercase tracking-[0.12em] text-[#e84657]"
                >
                    Observaciones
                </p>

                <p
                    class="mt-3 whitespace-pre-line leading-7"
                >
                    {{
                        webRequest.notes
                    }}
                </p>
            </section>

            <!-- Resumen original -->

            <details
                class="rounded-2xl border bg-background"
            >
                <summary
                    class="cursor-pointer px-5 py-4 text-sm font-bold"
                >
                    Ver mensaje técnico recibido
                </summary>

                <div
                    class="border-t p-5"
                >
                    <pre
                        class="whitespace-pre-wrap break-words font-sans text-sm leading-6 text-muted-foreground"
                    >{{ lead.message }}</pre>
                </div>
            </details>
        </div>

        <!-- Preview interno de imagen -->

        <Teleport
            to="body"
        >
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="
                        selectedImage
                    "
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/85 p-4 backdrop-blur-sm"
                    @click.self="
                        selectedImage =
                            null
                    "
                >
                    <div
                        class="relative max-h-[94vh] max-w-6xl overflow-hidden rounded-2xl bg-black shadow-2xl"
                    >
                        <button
                            type="button"
                            class="absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-black/65 text-white backdrop-blur transition hover:bg-black"
                            @click="
                                selectedImage =
                                    null
                            "
                        >
                            <X
                                class="h-5 w-5"
                            />
                        </button>

                        <img
                            :src="
                                filePreviewUrl(
                                    selectedImage,
                                )
                            "
                            :alt="
                                selectedImage.original_name
                            "
                            class="max-h-[94vh] max-w-full object-contain"
                        >
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>