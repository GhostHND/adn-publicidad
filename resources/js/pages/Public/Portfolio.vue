<script setup lang="ts">
import PublicWebsiteLayout from '@/layouts/PublicWebsiteLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    ArrowRight,
    Images,
} from '@lucide/vue';

defineProps<{
    site: any;
    portfolio: any[];
}>();
</script>

<template>
    <Head title="Portafolio | ADN Publicidad" />

    <PublicWebsiteLayout :site="site">
        <section
            class="border-b border-white/10"
        >
            <div
                class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28"
            >
                <p
                    class="text-sm font-bold uppercase tracking-[0.25em] text-[#e84657]"
                >
                    Portafolio
                </p>

                <h1
                    class="mt-4 max-w-4xl text-5xl font-black sm:text-6xl"
                >
                    Trabajos que hablan por nosotros
                </h1>

                <p
                    class="mt-6 max-w-2xl text-lg leading-8 text-white/55"
                >
                    Una selección de proyectos desarrollados para nuestros clientes.
                </p>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-5 py-20 lg:px-8"
        >
            <div
                v-if="
                    portfolio.length
                "
                class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="item in portfolio"
                    :key="item.id"
                    class="group overflow-hidden rounded-3xl border border-white/10 bg-white/[0.03]"
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
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        />
                    </div>

                    <div
                        v-else
                        class="flex aspect-[4/3] items-center justify-center bg-white/[0.025]"
                    >
                        <Images
                            class="h-12 w-12 text-white/20"
                        />
                    </div>

                    <div
                        class="p-6"
                    >
                        <p
                            v-if="
                                item.subtitle
                            "
                            class="text-xs font-bold uppercase tracking-wider text-[#0fa7b4]"
                        >
                            {{
                                item.subtitle
                            }}
                        </p>

                        <h2
                            class="mt-2 text-xl font-black"
                        >
                            {{
                                item.title
                            }}
                        </h2>

                        <p
                            v-if="
                                item.description
                            "
                            class="mt-3 text-sm leading-7 text-white/50"
                        >
                            {{
                                item.description
                            }}
                        </p>

                        <a
                            v-if="
                                item.link_url
                            "
                            :href="
                                item.link_url
                            "
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[#0fa7b4]"
                        >
                            Ver proyecto

                            <ArrowRight
                                class="h-4 w-4"
                            />
                        </a>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="rounded-3xl border border-white/10 py-24 text-center"
            >
                <Images
                    class="mx-auto h-12 w-12 text-white/20"
                />

                <h2
                    class="mt-5 text-2xl font-bold"
                >
                    Portafolio en preparación
                </h2>

                <p
                    class="mt-2 text-white/45"
                >
                    Próximamente encontrarás nuestros proyectos destacados aquí.
                </p>
            </div>

            <div
                class="mt-16 text-center"
            >
                <Link
                    href="/contacto"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#e84657] px-6 py-3.5 font-bold"
                >
                    Quiero trabajar con ADN

                    <ArrowRight
                        class="h-4 w-4"
                    />
                </Link>
            </div>
        </section>
    </PublicWebsiteLayout>
</template>