<script setup lang="ts">
import PublicWebsiteLayout from '@/layouts/PublicWebsiteLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
} from '@lucide/vue';

defineProps<{
    site: any;
    services: any[];
}>();
</script>

<template>
    <Head title="Servicios | ADN Publicidad" />

    <PublicWebsiteLayout :site="site">
        <section
            class="border-b border-white/10"
        >
            <div
                class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28"
            >
                <p
                    class="text-sm font-bold uppercase tracking-[0.25em] text-[#0fa7b4]"
                >
                    Servicios
                </p>

                <h1
                    class="mt-4 max-w-4xl text-5xl font-black sm:text-6xl"
                >
                    Todo lo que necesitas para hacer visible tu negocio
                </h1>

                <p
                    class="mt-6 max-w-2xl text-lg leading-8 text-white/55"
                >
                    Diseño, producción gráfica, publicidad, personalización y tecnología desarrollados desde una misma solución.
                </p>
            </div>
        </section>

        <section>
            <div
                class="mx-auto grid max-w-7xl gap-5 px-5 py-20 md:grid-cols-2 lg:px-8 xl:grid-cols-3"
            >
                <article
                    v-for="service in services"
                    :key="service.id"
                    class="rounded-3xl border border-white/10 bg-white/[0.035] p-7"
                >
                    <CheckCircle2
                        class="h-7 w-7 text-[#0fa7b4]"
                    />

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

                    <h2
                        class="mt-2 text-2xl font-black"
                    >
                        {{
                            service.title
                        }}
                    </h2>

                    <p
                        class="mt-4 leading-7 text-white/55"
                    >
                        {{
                            service.description
                        }}
                    </p>
                </article>
            </div>
        </section>

        <section
            class="border-t border-white/10 bg-[#0fa7b4]/5"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-16 md:flex-row md:items-center md:justify-between lg:px-8"
            >
                <div>
                    <h2
                        class="text-3xl font-black"
                    >
                        ¿Tienes un proyecto especial?
                    </h2>

                    <p
                        class="mt-2 text-white/55"
                    >
                        Cuéntanos qué necesitas y te ayudamos a convertirlo en realidad.
                    </p>
                </div>

                <Link
                    href="/contacto"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#e84657] px-6 py-3.5 font-bold"
                >
                    Solicitar información

                    <ArrowRight
                        class="h-4 w-4"
                    />
                </Link>
            </div>
        </section>
    </PublicWebsiteLayout>
</template>