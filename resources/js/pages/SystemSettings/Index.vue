<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    useForm,
} from '@inertiajs/vue3';
import {
    Building2,
    FileText,
    Globe2,
    Mail,
    MessageCircle,
    Save,
    Settings,
} from '@lucide/vue';
import {
    computed,
} from 'vue';

type Setting = {
    id: number;
    key: string;
    group_name: string;
    label: string;
    value: string;
    type: string;
    description: string | null;
    sort_order: number;
};

const props = defineProps<{
    settings: Setting[];
}>();

const breadcrumbs = [
    {
        title: 'Configuración',
        href: '/settings/system',
    },
];

const form = useForm({
    items:
        props.settings.map(
            (setting) => ({
                key:
                    setting.key,

                group_name:
                    setting.group_name,

                label:
                    setting.label,

                value:
                    setting.value ?? '',

                type:
                    setting.type,

                description:
                    setting.description,

                sort_order:
                    setting.sort_order,
            }),
        ),
});

const groups =
    computed(() => {
        return Array.from(
            new Set(
                form.items.map(
                    (setting) =>
                        setting.group_name,
                ),
            ),
        );
    });

const settingsForGroup = (
    group: string,
) => {
    return form.items.filter(
        (setting) =>
            setting.group_name ===
            group,
    );
};

const groupIcon = (
    group: string,
) => {
    if (
        group ===
        'Empresa'
    ) {
        return Building2;
    }

    if (
        group ===
        'Contacto'
    ) {
        return Globe2;
    }

    if (
        group ===
        'Documentos'
    ) {
        return FileText;
    }

    if (
        group ===
        'Compartir documentos'
    ) {
        return MessageCircle;
    }

    return Settings;
};

const inputType = (
    setting: Setting,
): string => {
    /*
    |--------------------------------------------------------------------------
    | WEB
    |--------------------------------------------------------------------------
    |
    | No usamos type="url", porque queremos permitir:
    |
    | www.adnpublicidad.site
    | adnpublicidad.site
    |
    | El backend agregará https:// automáticamente al guardar.
    |
    */

    if (
        setting.key ===
        'website'
    ) {
        return 'text';
    }

    if (
        setting.type ===
        'email'
    ) {
        return 'email';
    }

    return 'text';
};

const inputPlaceholder = (
    setting: Setting,
): string => {
    if (
        setting.key ===
        'website'
    ) {
        return 'www.adnpublicidad.site';
    }

    if (
        setting.key ===
        'email'
    ) {
        return 'correo@adnpublicidad.site';
    }

    if (
        setting.key ===
        'phone'
    ) {
        return '+504 0000-0000';
    }

    return '';
};

const normalizeWebsiteForDisplay = (): void => {
    const website =
        form.items.find(
            (setting) =>
                setting.key ===
                'website',
        );

    if (
        !website
        ||
        !website.value
    ) {
        return;
    }

    website.value =
        website.value.trim();
};

const submit = (): void => {
    normalizeWebsiteForDisplay();

    form
        .transform(
            (data) => ({
                items:
                    data.items.map(
                        (setting) => ({
                            key:
                                setting.key,

                            value:
                                setting.value,
                        }),
                    ),
            }),
        )
        .patch(
            '/settings/system',
            {
                preserveScroll:
                    true,
            },
        );
};
</script>

<template>
    <Head
        title="Configuración"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-6"
        >
            <!-- ENCABEZADO -->

            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        ADN Publicidad
                    </p>

                    <h1
                        class="text-3xl font-bold"
                    >
                        Configuración general
                    </h1>

                    <p
                        class="mt-1 max-w-2xl text-sm text-muted-foreground"
                    >
                        Administra los datos utilizados por el sistema,
                        documentos PDF y herramientas de comunicación.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="
                        form.processing
                    "
                    @click="
                        submit
                    "
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-6 py-3 font-semibold text-white shadow-sm transition hover:opacity-90 disabled:opacity-50"
                >
                    <Save
                        class="h-4 w-4"
                    />

                    {{
                        form.processing
                            ? 'Guardando...'
                            : 'Guardar configuración'
                    }}
                </button>
            </div>

            <!-- INFORMACIÓN -->

            <div
                class="rounded-2xl border border-[#0fa7b4]/20 bg-[#0fa7b4]/5 p-5"
            >
                <div
                    class="flex gap-3"
                >
                    <Settings
                        class="mt-0.5 h-5 w-5 shrink-0 text-[#0fa7b4]"
                    />

                    <div>
                        <p
                            class="font-semibold"
                        >
                            Configuración centralizada
                        </p>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Los cambios realizados aquí se utilizarán
                            automáticamente por los módulos compatibles del
                            sistema.
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO -->

            <form
                @submit.prevent="
                    submit
                "
                class="space-y-6"
            >
                <section
                    v-for="
                        group in groups
                    "
                    :key="
                        group
                    "
                    class="overflow-hidden rounded-2xl border bg-background shadow-sm"
                >
                    <!-- ENCABEZADO DE GRUPO -->

                    <div
                        class="flex items-center gap-3 border-b bg-muted/20 px-6 py-5"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                        >
                            <component
                                :is="
                                    groupIcon(
                                        group,
                                    )
                                "
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <h2
                                class="font-bold"
                            >
                                {{ group }}
                            </h2>

                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Configuración de
                                {{
                                    group.toLowerCase()
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- CAMPOS -->

                    <div
                        class="grid gap-6 p-6 md:grid-cols-2"
                    >
                        <div
                            v-for="
                                setting in settingsForGroup(
                                    group,
                                )
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
                                class="mb-2 block text-sm font-semibold"
                            >
                                {{
                                    setting.label
                                }}
                            </label>

                            <!-- TEXTAREA -->

                            <textarea
                                v-if="
                                    setting.type ===
                                    'textarea'
                                "
                                v-model="
                                    setting.value
                                "
                                rows="5"
                                class="w-full resize-y rounded-xl border bg-background px-4 py-3 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />

                            <!-- SITIO WEB -->

                            <div
                                v-else-if="
                                    setting.key ===
                                    'website'
                                "
                                class="relative"
                            >
                                <Globe2
                                    class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />

                                <input
                                    v-model="
                                        setting.value
                                    "
                                    type="text"
                                    :placeholder="
                                        inputPlaceholder(
                                            setting,
                                        )
                                    "
                                    autocomplete="url"
                                    class="w-full rounded-xl border bg-background py-3 pl-11 pr-4 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                                />
                            </div>

                            <!-- EMAIL -->

                            <div
                                v-else-if="
                                    setting.key ===
                                    'email'
                                "
                                class="relative"
                            >
                                <Mail
                                    class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />

                                <input
                                    v-model="
                                        setting.value
                                    "
                                    type="email"
                                    :placeholder="
                                        inputPlaceholder(
                                            setting,
                                        )
                                    "
                                    autocomplete="email"
                                    class="w-full rounded-xl border bg-background py-3 pl-11 pr-4 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                                />
                            </div>

                            <!-- OTROS -->

                            <input
                                v-else
                                v-model="
                                    setting.value
                                "
                                :type="
                                    inputType(
                                        setting,
                                    )
                                "
                                :placeholder="
                                    inputPlaceholder(
                                        setting,
                                    )
                                "
                                class="w-full rounded-xl border bg-background px-4 py-3 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />

                            <!-- DESCRIPCIÓN -->

                            <p
                                v-if="
                                    setting.description
                                "
                                class="mt-2 text-xs leading-relaxed text-muted-foreground"
                            >
                                {{
                                    setting.description
                                }}
                            </p>

                            <!-- AYUDA ESPECIAL WEB -->

                            <p
                                v-if="
                                    setting.key ===
                                    'website'
                                "
                                class="mt-2 text-xs text-[#0fa7b4]"
                            >
                                Puedes escribir www.adnpublicidad.site o la
                                dirección completa con https://
                            </p>
                        </div>
                    </div>
                </section>

                <!-- ERRORES -->

                <div
                    v-if="
                        form.hasErrors
                    "
                    class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/20 dark:text-red-300"
                >
                    No se pudo guardar alguna configuración. Revisa los datos
                    ingresados.
                </div>

                <!-- GUARDAR -->

                <div
                    class="flex justify-end pb-8"
                >
                    <button
                        type="submit"
                        :disabled="
                            form.processing
                        "
                        class="inline-flex items-center gap-2 rounded-xl bg-[#0fa7b4] px-7 py-3 font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                    >
                        <Save
                            class="h-4 w-4"
                        />

                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>