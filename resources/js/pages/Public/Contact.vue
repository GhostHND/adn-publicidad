<script setup lang="ts">
import PublicWebsiteLayout from '@/layouts/PublicWebsiteLayout.vue';
import {
    Head,
    useForm,
} from '@inertiajs/vue3';
import {
    CheckCircle2,
    Globe2,
    Mail,
    MapPin,
    MessageCircle,
    Phone,
    Send,
} from '@lucide/vue';
import {
    computed,
    ref,
} from 'vue';

const props = defineProps<{
    site: any;
    services: {
        id: number;
        title: string;
    }[];
}>();

const sent =
    ref(false);

const form = useForm({
    name: '',
    business_name: '',
    phone: '',
    email: '',
    service_interest: '',
    message: '',
    website: '',
});

const whatsappUrl =
    computed(() => {
        let digits =
            String(
                props.site.phone
                ?? '',
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

        const message =
            encodeURIComponent(
                'Hola ADN Publicidad, quiero solicitar una cotización.',
            );

        return digits
            ? `https://wa.me/${digits}?text=${message}`
            : '#';
    });

const submit = (): void => {
    sent.value =
        false;

    form.post(
        '/contacto/solicitud',
        {
            preserveScroll:
                true,

            onSuccess: () => {
                sent.value =
                    true;

                form.reset();
            },
        },
    );
};
</script>

<template>
    <Head
        title="Contacto | ADN Publicidad"
    />

    <PublicWebsiteLayout
        :site="
            site
        "
    >
        <section
            class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28"
        >
            <div
                class="max-w-3xl"
            >
                <p
                    class="text-sm font-bold uppercase tracking-[0.25em] text-[#0fa7b4]"
                >
                    Contacto
                </p>

                <h1
                    class="mt-4 text-5xl font-black sm:text-6xl"
                >
                    Hablemos de tu próximo proyecto
                </h1>

                <p
                    class="mt-6 text-lg leading-8 text-white/55"
                >
                    Cuéntanos qué necesitas. La solicitud llegará directamente
                    a nuestro sistema para darle seguimiento.
                </p>
            </div>

            <div
                class="mt-14 grid gap-8 lg:grid-cols-[1.15fr_0.85fr]"
            >
                <!-- FORMULARIO -->

                <section
                    class="rounded-[2rem] border border-white/10 bg-white/[0.035] p-6 sm:p-8"
                >
                    <div
                        v-if="
                            sent
                        "
                        class="mb-6 flex gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-5"
                    >
                        <CheckCircle2
                            class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400"
                        />

                        <div>
                            <p
                                class="font-bold text-emerald-300"
                            >
                                Solicitud enviada
                            </p>

                            <p
                                class="mt-1 text-sm text-white/60"
                            >
                                Recibimos tu información. ADN Publicidad se pondrá
                                en contacto contigo.
                            </p>
                        </div>
                    </div>

                    <h2
                        class="text-2xl font-black"
                    >
                        Solicitar información
                    </h2>

                    <form
                        @submit.prevent="
                            submit
                        "
                        class="mt-7 grid gap-5 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold"
                            >
                                Nombre *
                            </label>

                            <input
                                v-model="
                                    form.name
                                "
                                type="text"
                                autocomplete="name"
                                class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 outline-none transition focus:border-[#0fa7b4]"
                            />

                            <p
                                v-if="
                                    form.errors.name
                                "
                                class="mt-1 text-xs text-red-400"
                            >
                                {{
                                    form.errors.name
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold"
                            >
                                Empresa
                            </label>

                            <input
                                v-model="
                                    form.business_name
                                "
                                type="text"
                                class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 outline-none transition focus:border-[#0fa7b4]"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold"
                            >
                                Teléfono / WhatsApp *
                            </label>

                            <input
                                v-model="
                                    form.phone
                                "
                                type="tel"
                                autocomplete="tel"
                                class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 outline-none transition focus:border-[#0fa7b4]"
                            />

                            <p
                                v-if="
                                    form.errors.phone
                                "
                                class="mt-1 text-xs text-red-400"
                            >
                                {{
                                    form.errors.phone
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold"
                            >
                                Correo
                            </label>

                            <input
                                v-model="
                                    form.email
                                "
                                type="email"
                                autocomplete="email"
                                class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 outline-none transition focus:border-[#0fa7b4]"
                            />

                            <p
                                v-if="
                                    form.errors.email
                                "
                                class="mt-1 text-xs text-red-400"
                            >
                                {{
                                    form.errors.email
                                }}
                            </p>
                        </div>

                        <div
                            class="sm:col-span-2"
                        >
                            <label
                                class="mb-2 block text-sm font-semibold"
                            >
                                Servicio de interés
                            </label>

                            <select
                                v-model="
                                    form.service_interest
                                "
                                class="w-full rounded-xl border border-white/10 bg-[#0c1012] px-4 py-3 outline-none transition focus:border-[#0fa7b4]"
                            >
                                <option value="">
                                    Seleccionar servicio
                                </option>

                                <option
                                    v-for="
                                        service in services
                                    "
                                    :key="
                                        service.id
                                    "
                                    :value="
                                        service.title
                                    "
                                >
                                    {{
                                        service.title
                                    }}
                                </option>

                                <option
                                    value="Otro proyecto personalizado"
                                >
                                    Otro proyecto personalizado
                                </option>
                            </select>
                        </div>

                        <div
                            class="sm:col-span-2"
                        >
                            <label
                                class="mb-2 block text-sm font-semibold"
                            >
                                ¿Qué necesitas? *
                            </label>

                            <textarea
                                v-model="
                                    form.message
                                "
                                rows="6"
                                placeholder="Cuéntanos sobre tu proyecto, medidas aproximadas, cantidades u otra información que consideres importante..."
                                class="w-full resize-y rounded-xl border border-white/10 bg-white/[0.04] px-4 py-3 outline-none transition focus:border-[#0fa7b4]"
                            />

                            <p
                                v-if="
                                    form.errors.message
                                "
                                class="mt-1 text-xs text-red-400"
                            >
                                {{
                                    form.errors.message
                                }}
                            </p>
                        </div>

                        <!-- Honeypot invisible -->

                        <div
                            class="hidden"
                            aria-hidden="true"
                        >
                            <label>
                                Website

                                <input
                                    v-model="
                                        form.website
                                    "
                                    type="text"
                                    tabindex="-1"
                                    autocomplete="off"
                                />
                            </label>
                        </div>

                        <div
                            class="sm:col-span-2"
                        >
                            <button
                                type="submit"
                                :disabled="
                                    form.processing
                                "
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#e84657] px-6 py-4 font-bold text-white transition hover:-translate-y-0.5 disabled:opacity-50 sm:w-auto"
                            >
                                <Send
                                    class="h-4 w-4"
                                />

                                {{
                                    form.processing
                                        ? 'Enviando...'
                                        : 'Enviar solicitud'
                                }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- CONTACTO DIRECTO -->

                <div
                    class="space-y-5"
                >
                    <a
                        :href="
                            whatsappUrl
                        "
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block rounded-3xl border border-white/10 bg-[#0fa7b4]/5 p-7 transition hover:border-[#0fa7b4]/40"
                    >
                        <MessageCircle
                            class="h-7 w-7 text-[#0fa7b4]"
                        />

                        <p
                            class="mt-5 text-sm text-white/45"
                        >
                            WhatsApp
                        </p>

                        <p
                            class="mt-1 text-2xl font-black"
                        >
                            {{
                                site.phone ||
                                'Escríbenos'
                            }}
                        </p>
                    </a>

                    <a
                        v-if="
                            site.email
                        "
                        :href="
                            `mailto:${site.email}`
                        "
                        class="block rounded-3xl border border-white/10 p-7 transition hover:border-[#e84657]/40"
                    >
                        <Mail
                            class="h-7 w-7 text-[#e84657]"
                        />

                        <p
                            class="mt-5 text-sm text-white/45"
                        >
                            Correo
                        </p>

                        <p
                            class="mt-1 break-all text-xl font-black"
                        >
                            {{
                                site.email
                            }}
                        </p>
                    </a>

                    <div
                        v-if="
                            site.business_location
                        "
                        class="rounded-3xl border border-white/10 p-7"
                    >
                        <MapPin
                            class="h-7 w-7 text-[#0fa7b4]"
                        />

                        <p
                            class="mt-5 text-sm text-white/45"
                        >
                            Ubicación
                        </p>

                        <p
                            class="mt-1 text-xl font-black"
                        >
                            {{
                                site.business_location
                            }}
                        </p>
                    </div>

                    <a
                        v-if="
                            site.website
                        "
                        :href="
                            site.website
                        "
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block rounded-3xl border border-white/10 p-7"
                    >
                        <Globe2
                            class="h-7 w-7 text-[#e84657]"
                        />

                        <p
                            class="mt-5 text-sm text-white/45"
                        >
                            Sitio web
                        </p>

                        <p
                            class="mt-1 text-xl font-black"
                        >
                            adnpublicidad.site
                        </p>
                    </a>

                    <div
                        v-if="
                            site.phone
                        "
                        class="flex items-center gap-3 rounded-3xl border border-white/10 p-5"
                    >
                        <Phone
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <span
                            class="font-semibold"
                        >
                            {{
                                site.phone
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </PublicWebsiteLayout>
</template>