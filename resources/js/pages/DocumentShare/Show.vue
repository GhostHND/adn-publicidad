<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    Clipboard,
    Download,
    ExternalLink,
    FileText,
    MessageCircle,
    Share2,
    Smartphone,
} from '@lucide/vue';
import {
    computed,
    onMounted,
    ref,
} from 'vue';

const props = defineProps<{
    share: {
        type:
            | 'quotation'
            | 'receipt';

        title: string;

        document_number: string;

        client_name: string;

        phone:
            | string
            | null;

        normalized_phone:
            | string
            | null;

        message: string;

        whatsapp_url: string;

        pdf_url: string;

        download_url: string;

        filename: string;
    };
}>();

const sharing =
    ref(false);

const copied =
    ref(false);

const supportsNativeShare =
    ref(false);

const breadcrumbs =
    computed(() => [
        {
            title:
                props.share.type ===
                'quotation'
                    ? 'Cotizaciones'
                    : 'Recibos',

            href:
                props.share.type ===
                'quotation'
                    ? '/quotations'
                    : '/receipts',
        },

        {
            title:
                'Compartir',

            href:
                '#',
        },
    ]);

const backUrl =
    computed(() => {
        if (
            props.share.type ===
            'quotation'
        ) {
            const match =
                props.share.pdf_url.match(
                    /\/quotations\/(\d+)\/pdf/
                );

            if (match) {
                return `/quotations/${match[1]}`;
            }

            return '/quotations';
        }

        return '/receipts';
    });

onMounted(() => {
    supportsNativeShare.value =
        typeof navigator !==
            'undefined'
        &&
        typeof navigator.share ===
            'function';
});

const obtainPdfFile =
    async (): Promise<File> => {
        const response =
            await fetch(
                props.share.pdf_url,
                {
                    method:
                        'GET',

                    credentials:
                        'same-origin',

                    headers: {
                        Accept:
                            'application/pdf',
                    },
                },
            );

        if (!response.ok) {
            throw new Error(
                'No fue posible obtener el PDF.'
            );
        }

        const blob =
            await response.blob();

        return new File(
            [
                blob,
            ],
            props.share.filename,
            {
                type:
                    'application/pdf',
            },
        );
    };

const shareNative =
    async () => {
        if (
            sharing.value
        ) {
            return;
        }

        sharing.value =
            true;

        try {
            if (
                typeof navigator.share !==
                'function'
            ) {
                throw new Error(
                    'Este navegador no admite compartir archivos directamente.'
                );
            }

            const file =
                await obtainPdfFile();

            const canShareFile =
                typeof navigator.canShare !==
                    'function'
                ||
                navigator.canShare({
                    files: [
                        file,
                    ],
                });

            /*
            |--------------------------------------------------------------------------
            | Primera opción:
            | PDF + texto
            |--------------------------------------------------------------------------
            */

            if (canShareFile) {
                try {
                    await navigator.share({
                        title:
                            props.share.title,

                        text:
                            props.share.message,

                        files: [
                            file,
                        ],
                    });

                    return;
                } catch (
                    error: any
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Si el usuario canceló, no hacemos fallback.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        error?.name ===
                        'AbortError'
                    ) {
                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Algunos sistemas permiten archivo pero no archivo + texto.
                    | Intentamos entonces solamente el PDF.
                    |--------------------------------------------------------------------------
                    */

                    try {
                        await navigator.share({
                            title:
                                props.share.title,

                            files: [
                                file,
                            ],
                        });

                        return;
                    } catch (
                        secondError: any
                    ) {
                        if (
                            secondError?.name ===
                            'AbortError'
                        ) {
                            return;
                        }
                    }
                }
            }

            fallbackShare();
        } catch (
            error: any
        ) {
            console.error(
                error
            );

            fallbackShare();
        } finally {
            sharing.value =
                false;
        }
    };

const fallbackShare =
    () => {
        downloadPdf();

        window.open(
            props.share.whatsapp_url,
            '_blank',
            'noopener,noreferrer',
        );
    };

const openWhatsapp =
    () => {
        window.open(
            props.share.whatsapp_url,
            '_blank',
            'noopener,noreferrer',
        );
    };

const downloadPdf =
    () => {
        const link =
            document.createElement(
                'a'
            );

        link.href =
            props.share.download_url;

        link.target =
            '_blank';

        link.rel =
            'noopener';

        document.body
            .appendChild(
                link
            );

        link.click();

        link.remove();
    };

const copyMessage =
    async () => {
        try {
            await navigator.clipboard
                .writeText(
                    props.share.message
                );

            copied.value =
                true;

            window.setTimeout(
                () => {
                    copied.value =
                        false;
                },
                2000
            );
        } catch {
            const textarea =
                document.createElement(
                    'textarea'
                );

            textarea.value =
                props.share.message;

            textarea.style.position =
                'fixed';

            textarea.style.opacity =
                '0';

            document.body
                .appendChild(
                    textarea
                );

            textarea.select();

            document.execCommand(
                'copy'
            );

            textarea.remove();

            copied.value =
                true;
        }
    };
</script>

<template>
    <Head
        :title="
            `Compartir ${share.document_number}`
        "
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-4 sm:p-6"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        Documento listo
                    </p>

                    <h1
                        class="text-3xl font-bold"
                    >
                        Compartir por WhatsApp
                    </h1>

                    <p
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{
                            share.document_number
                        }}
                        ·
                        {{
                            share.client_name
                        }}
                    </p>
                </div>

                <Link
                    :href="
                        backUrl
                    "
                    class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-3 text-sm font-semibold"
                >
                    <ArrowLeft
                        class="h-4 w-4"
                    />

                    Volver
                </Link>
            </div>

            <div
                class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]"
            >
                <!-- PDF -->

                <section
                    class="overflow-hidden rounded-3xl border bg-background shadow-sm"
                >
                    <div
                        class="flex flex-col gap-4 border-b p-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                            >
                                <FileText
                                    class="h-5 w-5"
                                />
                            </div>

                            <div>
                                <p
                                    class="font-bold"
                                >
                                    {{
                                        share.filename
                                    }}
                                </p>

                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    PDF generado por
                                    ADN Publicidad
                                </p>
                            </div>
                        </div>

                        <a
                            :href="
                                share.pdf_url
                            "
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold"
                        >
                            <ExternalLink
                                class="h-4 w-4"
                            />

                            Abrir PDF
                        </a>
                    </div>

                    <div
                        class="h-[600px] bg-muted/30"
                    >
                        <iframe
                            :src="
                                share.pdf_url
                            "
                            class="h-full w-full border-0"
                            title="Vista previa PDF"
                        />
                    </div>
                </section>

                <!-- ACCIONES -->

                <div
                    class="space-y-6"
                >
                    <section
                        class="overflow-hidden rounded-3xl border bg-gradient-to-br from-emerald-500/10 via-background to-[#0fa7b4]/10 p-6"
                    >
                        <div
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white"
                            >
                                <MessageCircle
                                    class="h-6 w-6"
                                />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-emerald-600"
                                >
                                    WhatsApp
                                </p>

                                <h2
                                    class="font-bold"
                                >
                                    {{
                                        share.client_name
                                    }}
                                </h2>
                            </div>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border bg-background/80 p-4"
                        >
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Número registrado
                            </p>

                            <p
                                class="mt-1 font-bold"
                            >
                                {{
                                    share.phone
                                    ||
                                    'Sin número registrado'
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="
                                shareNative
                            "
                            :disabled="
                                sharing
                            "
                            class="mt-5 flex w-full items-center justify-center gap-3 rounded-2xl bg-emerald-500 px-5 py-4 font-bold text-white shadow-sm transition hover:scale-[1.01] disabled:opacity-50"
                        >
                            <Share2
                                class="h-5 w-5"
                            />

                            {{
                                sharing
                                    ? 'Preparando PDF...'
                                    : 'Compartir PDF'
                            }}
                        </button>

                        <div
                            class="mt-3 flex items-start gap-3 rounded-xl bg-background/70 p-4 text-xs text-muted-foreground"
                        >
                            <Smartphone
                                class="mt-0.5 h-4 w-4 shrink-0"
                            />

                            <p>
                                En el teléfono se
                                abrirá el menú nativo
                                para que puedas elegir
                                WhatsApp y el contacto.
                            </p>
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border bg-background p-6"
                    >
                        <h2
                            class="font-bold"
                        >
                            Mensaje preparado
                        </h2>

                        <div
                            class="mt-4 whitespace-pre-line rounded-2xl bg-muted/50 p-4 text-sm leading-6"
                        >
                            {{
                                share.message
                            }}
                        </div>

                        <button
                            type="button"
                            @click="
                                copyMessage
                            "
                            class="mt-4 inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold"
                        >
                            <Check
                                v-if="
                                    copied
                                "
                                class="h-4 w-4 text-emerald-500"
                            />

                            <Clipboard
                                v-else
                                class="h-4 w-4"
                            />

                            {{
                                copied
                                    ? 'Copiado'
                                    : 'Copiar mensaje'
                            }}
                        </button>
                    </section>

                    <section
                        class="rounded-3xl border bg-background p-6"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            Computadora
                        </p>

                        <h2
                            class="mt-1 font-bold"
                        >
                            WhatsApp Web
                        </h2>

                        <p
                            class="mt-2 text-sm text-muted-foreground"
                        >
                            Descarga el PDF y abre
                            directamente el chat del
                            cliente con el mensaje
                            preparado.
                        </p>

                        <div
                            class="mt-5 grid gap-3"
                        >
                            <button
                                type="button"
                                @click="
                                    downloadPdf
                                "
                                class="flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold"
                            >
                                <Download
                                    class="h-4 w-4"
                                />

                                Descargar PDF
                            </button>

                            <button
                                type="button"
                                @click="
                                    openWhatsapp
                                "
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#1d1d1b] px-4 py-3 text-sm font-semibold text-white"
                            >
                                <MessageCircle
                                    class="h-4 w-4"
                                />

                                Abrir WhatsApp
                            </button>
                        </div>
                    </section>
                </div>
            </div>

            <div
                class="rounded-2xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 p-5 text-sm"
            >
                <strong>
                    Sin API externa.
                </strong>

                El PDF permanece dentro de ADN Publicidad.
                El envío final se realiza desde tu propio
                WhatsApp o WhatsApp Web.
            </div>
        </div>
    </AppLayout>
</template>