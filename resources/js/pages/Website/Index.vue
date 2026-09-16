<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';
import { useMobileAccordion } from '@/composables/useMobileAccordion';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    router,
    usePage,
} from '@inertiajs/vue3';
import {
    ExternalLink,
    FileImage,
    Globe2,
    Image,
    LayoutTemplate,
    Plus,
    Save,
    Settings2,
    Trash2,
    Upload,
} from '@lucide/vue';
import {
    computed,
    reactive,
    ref,
    watch,
} from 'vue';

type WebsiteSetting = {
    key: string;
    label: string;
    value: string | null;
    type: 'text' | 'textarea' | string;
    description: string;
    group: 'business' | 'home' | 'social' | string;
};

type WebsiteContent = {
    id: number;
    content_type: 'service' | 'portfolio';
    title: string;
    subtitle: string | null;
    description: string | null;
    image_path: string | null;
    image_url: string | null;
    link_url: string | null;
    sort_order: number;
    active: boolean;
};

type WebsiteStats = {
    total?: number;
    active?: number;
    services?: number;
    portfolio?: number;
};

type ContentDraft = {
    content_type: 'service' | 'portfolio';
    title: string;
    subtitle: string;
    description: string;
    link_url: string;
    sort_order: number;
    active: boolean;
    remove_image: boolean;
};

const props = defineProps<{
    settings: WebsiteSetting[];
    contents: WebsiteContent[];
    stats?: WebsiteStats;
    public_url?: string | null;
}>();

const page =
    usePage<any>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title:
            'Sitio web',

        href:
            '/website',
    },
];

const settingsValues =
    reactive<
        Record<
            string,
            string
        >
    >({});

const drafts =
    reactive<
        Record<
            number,
            ContentDraft
        >
    >({});

const contentFiles =
    reactive<
        Record<
            number,
            File | null
        >
    >({});

const newContent =
    reactive<ContentDraft>({
        content_type:
            'service',

        title:
            '',

        subtitle:
            '',

        description:
            '',

        link_url:
            '',

        sort_order:
            0,

        active:
            true,

        remove_image:
            false,
    });

const newContentFile =
    ref<File | null>(
        null,
    );

const showNewContent =
    ref(false);

const savingSettings =
    ref(false);

const creatingContent =
    ref(false);

const savingContentId =
    ref<number | null>(
        null,
    );

const errors =
    computed<
        Record<
            string,
            string
        >
    >(
        () =>
            page.props
                ?.errors
            ?? {},
    );

watch(
    () =>
        props.settings,
    (
        settings,
    ) => {
        Object.keys(
            settingsValues,
        ).forEach(
            (
                key,
            ) => {
                delete settingsValues[
                    key
                ];
            },
        );

        settings.forEach(
            (
                setting,
            ) => {
                settingsValues[
                    setting.key
                ] =
                    String(
                        setting.value
                        ?? '',
                    );
            },
        );
    },
    {
        immediate:
            true,

        deep:
            true,
    },
);

watch(
    () =>
        props.contents,
    (
        contents,
    ) => {
        contents.forEach(
            (
                content,
            ) => {
                drafts[
                    content.id
                ] = {
                    content_type:
                        content.content_type,

                    title:
                        content.title
                        ?? '',

                    subtitle:
                        content.subtitle
                        ?? '',

                    description:
                        content.description
                        ?? '',

                    link_url:
                        content.link_url
                        ?? '',

                    sort_order:
                        Number(
                            content.sort_order
                            ?? 0,
                        ),

                    active:
                        Boolean(
                            content.active,
                        ),

                    remove_image:
                        false,
                };

                if (
                    contentFiles[
                        content.id
                    ] ===
                    undefined
                ) {
                    contentFiles[
                        content.id
                    ] =
                        null;
                }
            },
        );
    },
    {
        immediate:
            true,

        deep:
            true,
    },
);

const businessSettings =
    computed(
        () =>
            props.settings
                .filter(
                    (
                        setting,
                    ) =>
                        setting.group ===
                        'business',
                ),
    );

const homeSettings =
    computed(
        () =>
            props.settings
                .filter(
                    (
                        setting,
                    ) =>
                        setting.group ===
                        'home',
                ),
    );

const socialSettings =
    computed(
        () =>
            props.settings
                .filter(
                    (
                        setting,
                    ) =>
                        setting.group ===
                        'social',
                ),
    );

const totalContent =
    computed(
        () =>
            Number(
                props.stats
                    ?.total
                ?? props.contents.length,
            ),
    );

const activeContent =
    computed(
        () =>
            Number(
                props.stats
                    ?.active
                ?? props.contents
                    .filter(
                        (
                            content,
                        ) =>
                            content.active,
                    )
                    .length,
            ),
    );

const serviceCount =
    computed(
        () =>
            Number(
                props.stats
                    ?.services
                ?? props.contents
                    .filter(
                        (
                            content,
                        ) =>
                            content.content_type ===
                            'service',
                    )
                    .length,
            ),
    );

const portfolioCount =
    computed(
        () =>
            Number(
                props.stats
                    ?.portfolio
                ?? props.contents
                    .filter(
                        (
                            content,
                        ) =>
                            content.content_type ===
                            'portfolio',
                    )
                    .length,
            ),
    );

const publicUrl =
    computed(
        () => {
            let value =
                String(
                    props.public_url
                    ?? settingsValues.website
                    ?? 'https://www.adnpublicidad.site',
                )
                    .trim();

            if (
                value ===
                ''
            ) {
                value =
                    'https://www.adnpublicidad.site';
            }

            if (
                !/^https?:\/\//i.test(
                    value,
                )
            ) {
                value =
                    `https://${value}`;
            }

            return value;
        },
    );

const contentTypeLabel = (
    type:
        WebsiteContent[
            'content_type'
        ],
): string => {
    return type ===
        'service'
        ? 'Servicio'
        : 'Portafolio';
};

const contentFieldsFor = (
    content:
        WebsiteContent,
): MobileRecordField[] => {
    return [
        {
            label:
                'Tipo',

            value:
                contentTypeLabel(
                    content.content_type,
                ),
        },

        {
            label:
                'Estado',

            value:
                content.active
                    ? 'Visible'
                    : 'Oculto',
        },

        {
            label:
                'Orden',

            value:
                content.sort_order,
        },

        {
            label:
                'Enlace',

            value:
                content.link_url
                || '—',
        },
    ];
};

const saveSettings =
    (): void => {
        savingSettings.value =
            true;

        router.patch(
            '/website/settings',
            {
                settings: {
                    ...settingsValues,
                },
            },
            {
                preserveScroll:
                    true,

                onFinish: () => {
                    savingSettings.value =
                        false;
                },
            },
        );
    };

const createContent =
    (): void => {
        creatingContent.value =
            true;

        const payload:
            Record<
                string,
                any
            > = {
            content_type:
                newContent.content_type,

            title:
                newContent.title,

            subtitle:
                newContent.subtitle,

            description:
                newContent.description,

            link_url:
                newContent.link_url,

            sort_order:
                Number(
                    newContent.sort_order
                    ?? 0,
                ),

            active:
                newContent.active,

            remove_image:
                false,
        };

        if (
            newContentFile.value
        ) {
            payload.image =
                newContentFile.value;
        }

        router.post(
            '/website/content',
            payload,
            {
                preserveScroll:
                    true,

                forceFormData:
                    true,

                onSuccess: () => {
                    resetNewContent();

                    showNewContent.value =
                        false;
                },

                onFinish: () => {
                    creatingContent.value =
                        false;
                },
            },
        );
    };

const saveContent = (
    content:
        WebsiteContent,
): void => {
    const draft =
        drafts[
            content.id
        ];

    if (!draft) {
        return;
    }

    savingContentId.value =
        content.id;

    const payload:
        Record<
            string,
            any
        > = {
        _method:
            'patch',

        content_type:
            draft.content_type,

        title:
            draft.title,

        subtitle:
            draft.subtitle,

        description:
            draft.description,

        link_url:
            draft.link_url,

        sort_order:
            Number(
                draft.sort_order
                ?? 0,
            ),

        active:
            draft.active,

        remove_image:
            draft.remove_image,
    };

    const file =
        contentFiles[
            content.id
        ];

    if (file) {
        payload.image =
            file;
    }

    router.post(
        `/website/content/${content.id}`,
        payload,
        {
            preserveScroll:
                true,

            forceFormData:
                true,

            onSuccess: () => {
                contentFiles[
                    content.id
                ] =
                    null;

                if (
                    drafts[
                        content.id
                    ]
                ) {
                    drafts[
                        content.id
                    ].remove_image =
                        false;
                }
            },

            onFinish: () => {
                savingContentId.value =
                    null;
            },
        },
    );
};

const destroyContent = (
    content:
        WebsiteContent,
): void => {
    const accepted =
        window.confirm(
            `¿Eliminar "${content.title}" del sitio web?`,
        );

    if (!accepted) {
        return;
    }

    router.delete(
        `/website/content/${content.id}`,
        {
            preserveScroll:
                true,
        },
    );
};

const handleFile = (
    contentId:
        number,
    event:
        Event,
): void => {
    const input =
        event.target as HTMLInputElement;

    contentFiles[
        contentId
    ] =
        input.files
            ?.[0]
        ?? null;
};

const handleNewFile = (
    event:
        Event,
): void => {
    const input =
        event.target as HTMLInputElement;

    newContentFile.value =
        input.files
            ?.[0]
        ?? null;
};

const resetNewContent =
    (): void => {
        newContent.content_type =
            'service';

        newContent.title =
            '';

        newContent.subtitle =
            '';

        newContent.description =
            '';

        newContent.link_url =
            '';

        newContent.sort_order =
            0;

        newContent.active =
            true;

        newContent.remove_image =
            false;

        newContentFile.value =
            null;
    };
</script>

<template>
    <Head
        title="Sitio web"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <!-- ========================================================= -->
            <!-- CABECERA -->
            <!-- ========================================================= -->

            <section
                class="adn-enter relative overflow-hidden rounded-[1.8rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-7"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#0fa7b4]/12 blur-3xl"
                />

                <div
                    class="pointer-events-none absolute -bottom-20 right-[25%] h-48 w-48 rounded-full bg-[#e84657]/8 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <Globe2
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Gestor de contenido
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Sitio web
                            </h1>

                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground"
                            >
                                La información se guarda en las mismas claves que utiliza
                                el sitio público. Cambias un dato una vez y el cambio se
                                refleja en la web.
                            </p>
                        </div>
                    </div>

                    <a
                        :href="
                            publicUrl
                        "
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 px-5 text-sm font-black text-[#22c6d2] transition hover:bg-[#0fa7b4]/10"
                    >
                        <ExternalLink
                            class="h-4 w-4"
                        />

                        Abrir sitio público
                    </a>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- RESUMEN -->
            <!-- ========================================================= -->

            <section
                class="grid grid-cols-2 gap-3 xl:grid-cols-4"
            >
                <article
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <LayoutTemplate
                        class="h-4 w-4 text-[#0fa7b4]"
                    />

                    <p
                        class="mt-3 text-[9px] font-black uppercase tracking-wider text-white/30"
                    >
                        Contenidos
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{
                            totalContent
                        }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-emerald-500/10 bg-emerald-500/[0.04] p-4"
                >
                    <Globe2
                        class="h-4 w-4 text-emerald-400"
                    />

                    <p
                        class="mt-3 text-[9px] font-black uppercase tracking-wider text-emerald-400"
                    >
                        Publicados
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{
                            activeContent
                        }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-cyan-500/10 bg-cyan-500/[0.04] p-4"
                >
                    <Settings2
                        class="h-4 w-4 text-cyan-400"
                    />

                    <p
                        class="mt-3 text-[9px] font-black uppercase tracking-wider text-cyan-400"
                    >
                        Servicios
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{
                            serviceCount
                        }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-violet-500/10 bg-violet-500/[0.04] p-4"
                >
                    <FileImage
                        class="h-4 w-4 text-violet-400"
                    />

                    <p
                        class="mt-3 text-[9px] font-black uppercase tracking-wider text-violet-400"
                    >
                        Portafolio
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{
                            portfolioCount
                        }}
                    </p>
                </article>
            </section>

            <!-- ========================================================= -->
            <!-- ERRORES -->
            <!-- ========================================================= -->

            <section
                v-if="
                    Object.keys(
                        errors,
                    ).length >
                    0
                "
                class="rounded-2xl border border-[#e84657]/20 bg-[#e84657]/5 p-4"
            >
                <p
                    class="text-sm font-black text-[#f06472]"
                >
                    Revisa los siguientes datos:
                </p>

                <ul
                    class="mt-2 space-y-1 text-xs text-white/55"
                >
                    <li
                        v-for="(
                            message,
                            key
                        ) in errors"
                        :key="
                            key
                        "
                    >
                        • {{
                            message
                        }}
                    </li>
                </ul>
            </section>

            <!-- ========================================================= -->
            <!-- INFORMACIÓN GENERAL -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div
                            class="flex items-center gap-2 text-[#22c6d2]"
                        >
                            <Settings2
                                class="h-4 w-4"
                            />

                            <p
                                class="text-[10px] font-black uppercase tracking-[0.16em]"
                            >
                                Configuración pública
                            </p>
                        </div>

                        <h2
                            class="mt-2 text-xl font-black"
                        >
                            Información del negocio
                        </h2>

                        <p
                            class="mt-1 text-xs leading-5 text-white/35"
                        >
                            Estos datos comparten la misma fuente con Configuración del
                            sistema; no se crea una segunda copia.
                        </p>
                    </div>

                    <button
                        type="button"
                        :disabled="
                            savingSettings
                        "
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 text-sm font-black text-white disabled:opacity-50"
                        @click="
                            saveSettings
                        "
                    >
                        <Save
                            class="h-4 w-4"
                        />

                        {{
                            savingSettings
                                ? 'Guardando...'
                                : 'Guardar sitio'
                        }}
                    </button>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <div
                        v-for="
                            setting in businessSettings
                        "
                        :key="
                            setting.key
                        "
                    >
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            {{
                                setting.label
                            }}
                        </label>

                        <textarea
                            v-if="
                                setting.type ===
                                'textarea'
                            "
                            v-model="
                                settingsValues[
                                    setting.key
                                ]
                            "
                            rows="4"
                            class="w-full rounded-xl border border-white/10 bg-black/15 px-4 py-3 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />

                        <input
                            v-else
                            v-model="
                                settingsValues[
                                    setting.key
                                ]
                            "
                            type="text"
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />

                        <p
                            class="mt-1.5 text-[10px] leading-4 text-white/25"
                        >
                            {{
                                setting.description
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- PORTADA -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex items-center gap-2 text-[#22c6d2]"
                >
                    <LayoutTemplate
                        class="h-4 w-4"
                    />

                    <p
                        class="text-[10px] font-black uppercase tracking-[0.16em]"
                    >
                        Portada y nosotros
                    </p>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <div
                        v-for="
                            setting in homeSettings
                        "
                        :key="
                            setting.key
                        "
                        :class="
                            setting.type ===
                            'textarea'
                                ? 'md:col-span-2'
                                : ''
                        "
                    >
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            {{
                                setting.label
                            }}
                        </label>

                        <textarea
                            v-if="
                                setting.type ===
                                'textarea'
                            "
                            v-model="
                                settingsValues[
                                    setting.key
                                ]
                            "
                            rows="4"
                            class="w-full rounded-xl border border-white/10 bg-black/15 px-4 py-3 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />

                        <input
                            v-else
                            v-model="
                                settingsValues[
                                    setting.key
                                ]
                            "
                            type="text"
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />

                        <p
                            class="mt-1.5 text-[10px] leading-4 text-white/25"
                        >
                            {{
                                setting.description
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- REDES -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex items-center gap-2 text-[#22c6d2]"
                >
                    <Globe2
                        class="h-4 w-4"
                    />

                    <p
                        class="text-[10px] font-black uppercase tracking-[0.16em]"
                    >
                        Redes sociales
                    </p>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-3"
                >
                    <div
                        v-for="
                            setting in socialSettings
                        "
                        :key="
                            setting.key
                        "
                    >
                        <label
                            class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                        >
                            {{
                                setting.label
                            }}
                        </label>

                        <input
                            v-model="
                                settingsValues[
                                    setting.key
                                ]
                            "
                            type="text"
                            class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm outline-none transition focus:border-[#0fa7b4]/50"
                        />

                        <p
                            class="mt-1.5 text-[10px] leading-4 text-white/25"
                        >
                            {{
                                setting.description
                            }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 flex justify-end"
                >
                    <button
                        type="button"
                        :disabled="
                            savingSettings
                        "
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 px-5 text-sm font-black text-[#22c6d2] transition hover:bg-[#0fa7b4]/10 disabled:opacity-50"
                        @click="
                            saveSettings
                        "
                    >
                        <Save
                            class="h-4 w-4"
                        />

                        Guardar configuración
                    </button>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- CONTENIDOS -->
            <!-- ========================================================= -->

            <section
                class="rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div
                            class="flex items-center gap-2 text-[#22c6d2]"
                        >
                            <Image
                                class="h-4 w-4"
                            />

                            <p
                                class="text-[10px] font-black uppercase tracking-[0.16em]"
                            >
                                Contenido público
                            </p>
                        </div>

                        <h2
                            class="mt-2 text-xl font-black"
                        >
                            Servicios y portafolio
                        </h2>

                        <p
                            class="mt-1 text-xs leading-5 text-white/35"
                        >
                            Solo los contenidos marcados como visibles aparecen en el
                            sitio público.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 text-sm font-black text-white"
                        @click="
                            showNewContent =
                                !showNewContent
                        "
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        {{
                            showNewContent
                                ? 'Cerrar'
                                : 'Nuevo contenido'
                        }}
                    </button>
                </div>

                <Transition
                    enter-active-class="transition duration-300"
                    enter-from-class="-translate-y-2 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-200"
                    leave-to-class="-translate-y-2 opacity-0"
                >
                    <form
                        v-if="
                            showNewContent
                        "
                        class="mt-5 grid gap-4 rounded-2xl border border-[#0fa7b4]/15 bg-[#0fa7b4]/[0.035] p-4 md:grid-cols-2"
                        @submit.prevent="
                            createContent
                        "
                    >
                        <div>
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                            >
                                Tipo
                            </label>

                            <select
                                v-model="
                                    newContent.content_type
                                "
                                class="h-12 w-full rounded-xl border border-white/10 bg-[#071014] px-4 text-sm"
                            >
                                <option
                                    value="service"
                                >
                                    Servicio
                                </option>

                                <option
                                    value="portfolio"
                                >
                                    Portafolio
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                            >
                                Orden
                            </label>

                            <input
                                v-model.number="
                                    newContent.sort_order
                                "
                                type="number"
                                min="0"
                                max="9999"
                                class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm"
                            />
                        </div>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                            >
                                Título
                            </label>

                            <input
                                v-model="
                                    newContent.title
                                "
                                type="text"
                                maxlength="200"
                                required
                                class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm"
                            />
                        </div>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                            >
                                Subtítulo
                            </label>

                            <input
                                v-model="
                                    newContent.subtitle
                                "
                                type="text"
                                maxlength="255"
                                class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm"
                            />
                        </div>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                            >
                                Descripción
                            </label>

                            <textarea
                                v-model="
                                    newContent.description
                                "
                                rows="5"
                                maxlength="5000"
                                class="w-full rounded-xl border border-white/10 bg-black/15 px-4 py-3 text-sm"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                            >
                                Enlace
                            </label>

                            <input
                                v-model="
                                    newContent.link_url
                                "
                                type="text"
                                maxlength="500"
                                placeholder="https://..."
                                class="h-12 w-full rounded-xl border border-white/10 bg-black/15 px-4 text-sm"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wide text-white/35"
                            >
                                Imagen
                            </label>

                            <input
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="block min-h-12 w-full rounded-xl border border-white/10 bg-black/15 px-3 py-2 text-sm"
                                @change="
                                    handleNewFile
                                "
                            />
                        </div>

                        <label
                            class="flex min-h-12 items-center gap-3 rounded-xl border border-white/10 bg-black/15 px-4 md:col-span-2"
                        >
                            <input
                                v-model="
                                    newContent.active
                                "
                                type="checkbox"
                                class="h-5 w-5 accent-[#0fa7b4]"
                            />

                            <span
                                class="text-sm font-bold"
                            >
                                Publicar inmediatamente
                            </span>
                        </label>

                        <button
                            type="submit"
                            :disabled="
                                creatingContent
                            "
                            class="adn-shine inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] font-black text-white md:col-span-2 disabled:opacity-50"
                        >
                            <Upload
                                class="h-4 w-4"
                            />

                            {{
                                creatingContent
                                    ? 'Creando...'
                                    : 'Crear contenido'
                            }}
                        </button>
                    </form>
                </Transition>
            </section>

            <!-- ========================================================= -->
            <!-- CONTENIDO MÓVIL -->
            <!-- ========================================================= -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <MobileRecordCard
                    v-for="
                        item in contents
                    "
                    :key="
                        item.id
                    "
                    :title="
                        item.title
                    "
                    :subtitle="
                        contentTypeLabel(
                            item.content_type,
                        )
                    "
                    :status="
                        item.active
                            ? 'Visible'
                            : 'Oculto'
                    "
                    :status-tone="
                        item.active
                            ? 'success'
                            : 'neutral'
                    "
                    :fields="
                        contentFieldsFor(
                            item,
                        )
                    "
                    :expanded="
                        isExpanded(
                            item.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            item.id,
                        )
                    "
                >
                    <template
                        #badges
                    >
                        <Image
                            v-if="
                                item.image_url
                            "
                            class="h-4 w-4 text-violet-400"
                        />
                    </template>

                    <template
                        #actions
                    >
                        <div
                            class="space-y-4"
                        >
                            <div
                                v-if="
                                    item.image_url
                                "
                                class="overflow-hidden rounded-xl border border-white/[0.07] bg-black/20"
                            >
                                <img
                                    :src="
                                        item.image_url
                                        ?? undefined
                                    "
                                    :alt="
                                        item.title
                                    "
                                    class="max-h-48 w-full object-contain"
                                />
                            </div>

                            <div
                                class="space-y-3"
                            >
                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Tipo
                                    </label>

                                    <select
                                        v-model="
                                            drafts[
                                                item.id
                                            ].content_type
                                        "
                                        class="h-11 w-full rounded-xl border border-white/10 bg-[#071014] px-3 text-sm"
                                    >
                                        <option
                                            value="service"
                                        >
                                            Servicio
                                        </option>

                                        <option
                                            value="portfolio"
                                        >
                                            Portafolio
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Título
                                    </label>

                                    <input
                                        v-model="
                                            drafts[
                                                item.id
                                            ].title
                                        "
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Subtítulo
                                    </label>

                                    <input
                                        v-model="
                                            drafts[
                                                item.id
                                            ].subtitle
                                        "
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Descripción
                                    </label>

                                    <textarea
                                        v-model="
                                            drafts[
                                                item.id
                                            ].description
                                        "
                                        rows="4"
                                        class="w-full rounded-xl border border-white/10 bg-black/15 px-3 py-2 text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Enlace
                                    </label>

                                    <input
                                        v-model="
                                            drafts[
                                                item.id
                                            ].link_url
                                        "
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                    >
                                        Orden
                                    </label>

                                    <input
                                        v-model.number="
                                            drafts[
                                                item.id
                                            ].sort_order
                                        "
                                        type="number"
                                        min="0"
                                        max="9999"
                                        class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                                    />
                                </div>

                                <label
                                    class="flex min-h-11 items-center justify-between rounded-xl border border-white/10 bg-black/15 px-3"
                                >
                                    <span
                                        class="text-xs font-bold"
                                    >
                                        {{
                                            drafts[
                                                item.id
                                            ].active
                                                ? 'Visible'
                                                : 'Oculto'
                                        }}
                                    </span>

                                    <input
                                        v-model="
                                            drafts[
                                                item.id
                                            ].active
                                        "
                                        type="checkbox"
                                        class="h-5 w-5 accent-[#0fa7b4]"
                                    />
                                </label>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                                >
                                    Reemplazar imagen
                                </label>

                                <input
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    class="block w-full rounded-xl border border-white/10 bg-black/15 px-3 py-2 text-xs"
                                    @change="
                                        handleFile(
                                            item.id,
                                            $event,
                                        )
                                    "
                                />
                            </div>

                            <label
                                v-if="
                                    item.image_url
                                "
                                class="flex min-h-11 items-center justify-between rounded-xl border border-[#e84657]/15 bg-[#e84657]/5 px-3"
                            >
                                <span
                                    class="text-xs font-bold text-[#f06472]"
                                >
                                    Eliminar imagen actual
                                </span>

                                <input
                                    v-model="
                                        drafts[
                                            item.id
                                        ].remove_image
                                    "
                                    type="checkbox"
                                    class="h-5 w-5 accent-[#e84657]"
                                />
                            </label>

                            <button
                                type="button"
                                :disabled="
                                    savingContentId ===
                                    item.id
                                "
                                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white disabled:opacity-50"
                                @click="
                                    saveContent(
                                        item,
                                    )
                                "
                            >
                                <Save
                                    class="h-4 w-4"
                                />

                                {{
                                    savingContentId ===
                                    item.id
                                        ? 'Guardando...'
                                        : 'Guardar cambios'
                                }}
                            </button>

                            <button
                                type="button"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-[#e84657]/20 bg-[#e84657]/5 text-xs font-bold text-[#f06472]"
                                @click="
                                    destroyContent(
                                        item,
                                    )
                                "
                            >
                                <Trash2
                                    class="h-4 w-4"
                                />

                                Eliminar contenido
                            </button>
                        </div>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ========================================================= -->
            <!-- CONTENIDO ESCRITORIO -->
            <!-- ========================================================= -->

            <section
                class="hidden grid-cols-1 gap-4 md:grid xl:grid-cols-2"
            >
                <article
                    v-for="
                        item in contents
                    "
                    :key="
                        item.id
                    "
                    class="adn-card p-5"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[9px] font-black uppercase tracking-[0.14em] text-[#0fa7b4]"
                            >
                                {{
                                    contentTypeLabel(
                                        item.content_type,
                                    )
                                }}
                            </p>

                            <h3
                                class="mt-1 text-lg font-black"
                            >
                                {{
                                    item.title
                                }}
                            </h3>

                            <p
                                class="mt-1 text-xs text-white/30"
                            >
                                WEB-{{
                                    String(
                                        item.id,
                                    )
                                        .padStart(
                                            4,
                                            '0',
                                        )
                                }}
                            </p>
                        </div>

                        <span
                            class="rounded-full border px-3 py-1 text-[9px] font-black"
                            :class="
                                item.active
                                    ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                    : 'border-white/10 bg-white/5 text-white/40'
                            "
                        >
                            {{
                                item.active
                                    ? 'Visible'
                                    : 'Oculto'
                            }}
                        </span>
                    </div>

                    <div
                        v-if="
                            item.image_url
                        "
                        class="mt-4 flex h-44 items-center justify-center overflow-hidden rounded-xl border border-white/[0.06] bg-black/20"
                    >
                        <img
                            :src="
                                item.image_url
                                ?? undefined
                            "
                            :alt="
                                item.title
                            "
                            class="max-h-full max-w-full object-contain"
                        />
                    </div>

                    <div
                        class="mt-5 grid gap-3 md:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Tipo
                            </label>

                            <select
                                v-model="
                                    drafts[
                                        item.id
                                    ].content_type
                                "
                                class="h-11 w-full rounded-xl border border-white/10 bg-[#071014] px-3 text-sm"
                            >
                                <option
                                    value="service"
                                >
                                    Servicio
                                </option>

                                <option
                                    value="portfolio"
                                >
                                    Portafolio
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Orden
                            </label>

                            <input
                                v-model.number="
                                    drafts[
                                        item.id
                                    ].sort_order
                                "
                                type="number"
                                min="0"
                                max="9999"
                                class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                            />
                        </div>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Título
                            </label>

                            <input
                                v-model="
                                    drafts[
                                        item.id
                                    ].title
                                "
                                type="text"
                                maxlength="200"
                                class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                            />
                        </div>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Subtítulo
                            </label>

                            <input
                                v-model="
                                    drafts[
                                        item.id
                                    ].subtitle
                                "
                                type="text"
                                maxlength="255"
                                class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                            />
                        </div>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Descripción
                            </label>

                            <textarea
                                v-model="
                                    drafts[
                                        item.id
                                    ].description
                                "
                                rows="4"
                                maxlength="5000"
                                class="w-full rounded-xl border border-white/10 bg-black/15 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Enlace
                            </label>

                            <input
                                v-model="
                                    drafts[
                                        item.id
                                    ].link_url
                                "
                                type="text"
                                maxlength="500"
                                class="h-11 w-full rounded-xl border border-white/10 bg-black/15 px-3 text-sm"
                            />
                        </div>

                        <label
                            class="flex h-11 items-center justify-between rounded-xl border border-white/10 bg-black/15 px-3"
                        >
                            <span
                                class="text-xs font-bold"
                            >
                                {{
                                    drafts[
                                        item.id
                                    ].active
                                        ? 'Visible'
                                        : 'Oculto'
                                }}
                            </span>

                            <input
                                v-model="
                                    drafts[
                                        item.id
                                    ].active
                                "
                                type="checkbox"
                                class="h-5 w-5 accent-[#0fa7b4]"
                            />
                        </label>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-[9px] font-black uppercase tracking-wide text-white/30"
                            >
                                Reemplazar imagen
                            </label>

                            <input
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="block w-full rounded-xl border border-white/10 bg-black/15 px-3 py-2 text-xs"
                                @change="
                                    handleFile(
                                        item.id,
                                        $event,
                                    )
                                "
                            />
                        </div>

                        <label
                            v-if="
                                item.image_url
                            "
                            class="flex h-11 items-center justify-between rounded-xl border border-[#e84657]/15 bg-[#e84657]/5 px-3 md:col-span-2"
                        >
                            <span
                                class="text-xs font-bold text-[#f06472]"
                            >
                                Eliminar imagen actual
                            </span>

                            <input
                                v-model="
                                    drafts[
                                        item.id
                                    ].remove_image
                                "
                                type="checkbox"
                                class="h-5 w-5 accent-[#e84657]"
                            />
                        </label>
                    </div>

                    <div
                        class="mt-5 grid grid-cols-[1fr_auto] gap-2"
                    >
                        <button
                            type="button"
                            :disabled="
                                savingContentId ===
                                item.id
                            "
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white disabled:opacity-50"
                            @click="
                                saveContent(
                                    item,
                                )
                            "
                        >
                            <Save
                                class="h-4 w-4"
                            />

                            {{
                                savingContentId ===
                                item.id
                                    ? 'Guardando...'
                                    : 'Guardar'
                            }}
                        </button>

                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#e84657]/20 text-[#f06472]"
                            @click="
                                destroyContent(
                                    item,
                                )
                            "
                        >
                            <Trash2
                                class="h-4 w-4"
                            />
                        </button>
                    </div>
                </article>
            </section>

            <!-- ========================================================= -->
            <!-- VACÍO -->
            <!-- ========================================================= -->

            <div
                v-if="
                    contents.length ===
                    0
                "
                class="rounded-2xl border border-dashed border-white/10 p-12 text-center"
            >
                <LayoutTemplate
                    class="mx-auto h-9 w-9 text-white/20"
                />

                <p
                    class="mt-4 font-black"
                >
                    No hay contenido publicado
                </p>

                <p
                    class="mt-1 text-xs text-muted-foreground"
                >
                    Crea el primer servicio o elemento de portafolio para el sitio.
                </p>
            </div>

            <!-- ========================================================= -->
            <!-- NOTA -->
            <!-- ========================================================= -->

            <section
                class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4"
            >
                <div
                    class="flex items-start gap-3"
                >
                    <FileImage
                        class="mt-0.5 h-4 w-4 shrink-0 text-[#0fa7b4]"
                    />

                    <p
                        class="text-xs leading-5 text-white/35"
                    >
                        Las imágenes se almacenan en el disco público de Laravel.
                        En producción debe existir el enlace
                        <strong
                            class="text-white/55"
                        >
                            public/storage
                        </strong>
                        para que servicios y portafolio se muestren correctamente.
                    </p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
