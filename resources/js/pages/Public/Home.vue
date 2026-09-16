<script setup lang="ts">
import PublicWebsiteLayout from '@/layouts/PublicWebsiteLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    ArrowRight,
    Camera,
    CheckCircle2,
    MonitorUp,
    Palette,
    Printer,
    Sparkles,
} from '@lucide/vue';

defineProps<{
    site: any;
    services: any[];
    portfolio: any[];
}>();

const serviceIcon = (
    index: number,
) => {
    const icons = [
        Palette,
        Printer,
        MonitorUp,
        Camera,
        Sparkles,
        CheckCircle2,
    ];

    return icons[
        index %
        icons.length
    ];
};
</script>

<template>
    <Head
        :title="
            site.business_name
        "
    />

    <PublicWebsiteLayout
        :site="
            site
        "
    >
        <section
            class="relative overflow-hidden"
        >
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(15,167,180,0.22),transparent_35%),radial-gradient(circle_at_80%_30%,rgba(232,70,87,0.18),transparent_32%)]"
            />

            <div
                class="relative mx-auto grid min-h-[78vh] max-w-7xl items-center gap-12 px-5 py-20 lg:grid-cols-2 lg:px-8"
            >
                <div>
                    <p
                        class="mb-5 text-sm font-bold uppercase tracking-[0.28em] text-[#0fa7b4]"
                    >
                        ADN Publicidad
                    </p>

                    <h1
                        class="max-w-3xl text-5xl font-black leading-[1.05] sm:text-6xl xl:text-7xl"
                    >
                        {{
                            site.hero_title
                        }}
                    </h1>

                    <p
                        class="mt-7 max-w-2xl text-lg leading-8 text-white/60"
                    >
                        {{
                            site.hero_subtitle
                        }}
                    </p>

                    <div
                        class="mt-9 flex flex-wrap gap-3"
                    >
                        <Link
                            href="/contacto"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#e84657] px-6 py-3.5 font-bold transition hover:-translate-y-1"
                        >
                            {{
                                site.hero_button_text
                            }}

                            <ArrowRight
                                class="h-4 w-4"
                            />
                        </Link>

                        <Link
                            href="/portafolio"
                            class="rounded-xl border border-white/15 px-6 py-3.5 font-bold transition hover:bg-white/5"
                        >
                            Ver trabajos
                        </Link>
                    </div>
                </div>

                <div
                    class="relative hidden min-h-[480px] lg:block"
                >
                    <div
                        class="absolute left-10 top-10 h-80 w-80 rounded-full bg-[#0fa7b4]/20 blur-3xl"
                    />

                    <div
                        class="absolute bottom-10 right-10 h-72 w-72 rounded-full bg-[#e84657]/20 blur-3xl"
                    />

                    <div
                        class="absolute inset-10 rotate-3 rounded-[2.5rem] border border-white/10 bg-white/[0.04] backdrop-blur"
                    />

                    <div
                        class="absolute inset-20 -rotate-3 rounded-[2.5rem] border border-white/10 bg-black/30 p-12"
                    >
                        <img
                            v-if="
                                site.logo
                            "
                            :src="
                                site.logo
                            "
                            :alt="
                                site.business_name
                            "
                            class="mx-auto mt-20 w-full max-w-sm"
                        />
                    </div>
                </div>
            </div>
        </section>

        <section
            class="border-y border-white/10 bg-white/[0.025]"
        >
            <div
                class="mx-auto max-w-7xl px-5 py-20 lg:px-8"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-bold uppercase tracking-widest text-[#0fa7b4]"
                        >
                            Lo que hacemos
                        </p>

                        <h2
                            class="mt-3 text-4xl font-black"
                        >
                            Soluciones para tu marca
                        </h2>
                    </div>

                    <Link
                        href="/servicios"
                        class="inline-flex items-center gap-2 font-bold text-[#0fa7b4]"
                    >
                        Todos los servicios

                        <ArrowRight
                            class="h-4 w-4"
                        />
                    </Link>
                </div>

                <div
                    class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="(service, index) in services"
                        :key="service.id"
                        class="group rounded-3xl border border-white/10 bg-white/[0.035] p-7 transition duration-300 hover:-translate-y-1 hover:border-[#0fa7b4]/40"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                        >
                            <component
                                :is="
                                    serviceIcon(
                                        index,
                                    )
                                "
                                class="h-5 w-5"
                            />
                        </div>

                        <p
                            v-if="
                                service.subtitle
                            "
                            class="mt-6 text-xs font-bold uppercase tracking-wider text-[#e84657]"
                        >
                            {{
                                service.subtitle
                            }}
                        </p>

                        <h3
                            class="mt-2 text-xl font-bold"
                        >
                            {{
                                service.title
                            }}
                        </h3>

                        <p
                            class="mt-4 text-sm leading-7 text-white/55"
                        >
                            {{
                                service.description
                            }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section>
            <div
                class="mx-auto grid max-w-7xl gap-12 px-5 py-24 lg:grid-cols-2 lg:items-center lg:px-8"
            >
                <div>
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-[#e84657]"
                    >
                        ADN Publicidad
                    </p>

                    <h2
                        class="mt-4 text-4xl font-black sm:text-5xl"
                    >
                        {{
                            site.about_title
                        }}
                    </h2>

                    <p
                        class="mt-6 text-lg leading-8 text-white/55"
                    >
                        {{
                            site.about_text
                        }}
                    </p>
                </div>

                <div
                    class="grid gap-4 sm:grid-cols-2"
                >
                    <div
                        class="rounded-3xl border border-white/10 p-7"
                    >
                        <p
                            class="text-4xl font-black text-[#0fa7b4]"
                        >
                            Diseño
                        </p>

                        <p
                            class="mt-3 text-sm text-white/50"
                        >
                            Soluciones visuales pensadas para comunicar y vender.
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 p-7"
                    >
                        <p
                            class="text-4xl font-black text-[#e84657]"
                        >
                            Producción
                        </p>

                        <p
                            class="mt-3 text-sm text-white/50"
                        >
                            Convertimos el diseño en productos y publicidad real.
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 p-7 sm:col-span-2"
                    >
                        <p
                            class="text-4xl font-black"
                        >
                            Tecnología
                        </p>

                        <p
                            class="mt-3 text-sm text-white/50"
                        >
                            También integramos soluciones tecnológicas y sistemas de seguridad.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-if="
                portfolio.length
            "
            class="bg-white/[0.025]"
        >
            <div
                class="mx-auto max-w-7xl px-5 py-24 lg:px-8"
            >
                <div
                    class="flex items-end justify-between gap-6"
                >
                    <div>
                        <p
                            class="text-sm font-bold uppercase tracking-widest text-[#0fa7b4]"
                        >
                            Portafolio
                        </p>

                        <h2
                            class="mt-3 text-4xl font-black"
                        >
                            Proyectos recientes
                        </h2>
                    </div>

                    <Link
                        href="/portafolio"
                        class="hidden font-bold text-[#0fa7b4] sm:inline-flex"
                    >
                        Ver portafolio
                    </Link>
                </div>

                <div
                    class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="item in portfolio"
                        :key="item.id"
                        class="overflow-hidden rounded-3xl border border-white/10 bg-white/[0.03]"
                    >
                        <div
                            v-if="
                                item.image_url
                            "
                            class="aspect-[4/3] overflow-hidden"
                        >
                            <img
                                :src="
                                    item.image_url
                                "
                                :alt="
                                    item.title
                                "
                                class="h-full w-full object-cover transition duration-500 hover:scale-105"
                            />
                        </div>

                        <div
                            class="p-6"
                        >
                            <h3
                                class="text-xl font-bold"
                            >
                                {{
                                    item.title
                                }}
                            </h3>

                            <p
                                v-if="
                                    item.description
                                "
                                class="mt-3 text-sm leading-6 text-white/50"
                            >
                                {{
                                    item.description
                                }}
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </PublicWebsiteLayout>
</template>