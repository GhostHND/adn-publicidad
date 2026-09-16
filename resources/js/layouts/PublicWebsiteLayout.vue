<script setup lang="ts">
import {
    Link,
    usePage,
} from '@inertiajs/vue3';
import {
    Mail,
    Menu,
    Phone,
    X,
} from '@lucide/vue';
import {
    computed,
    ref,
} from 'vue';

const props = defineProps<{
    site: any;
}>();

const page = usePage();

const mobileOpen =
    ref(false);

const phoneDigits =
    computed(() => {
        const digits =
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
            return `504${digits}`;
        }

        return digits;
    });

const whatsappUrl =
    computed(() => {
        if (
            !phoneDigits.value
        ) {
            return '/contacto';
        }

        const message =
            encodeURIComponent(
                'Hola ADN Publicidad, quiero solicitar información sobre sus servicios.',
            );

        return `https://wa.me/${phoneDigits.value}?text=${message}`;
    });

const isActive = (
    path: string,
): boolean => {
    if (
        path === '/'
    ) {
        return page.url === '/';
    }

    return page.url.startsWith(
        path,
    );
};

const closeMenu = (): void => {
    mobileOpen.value =
        false;
};
</script>

<template>
    <div
        class="min-h-screen bg-[#080b0d] text-white"
    >
        <header
            class="sticky top-0 z-50 border-b border-white/10 bg-[#080b0d]/90 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8"
            >
                <Link
                    href="/"
                    class="flex items-center gap-3"
                    @click="
                        closeMenu
                    "
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
                        class="h-11 w-auto"
                    />

                    <div
                        v-else
                        class="text-xl font-black"
                    >
                        ADN
                        <span
                            class="text-[#0fa7b4]"
                        >
                            Publicidad
                        </span>
                    </div>
                </Link>

                <nav
                    class="hidden items-center gap-8 lg:flex"
                >
                    <Link
                        href="/"
                        class="text-sm font-semibold transition hover:text-[#0fa7b4]"
                        :class="
                            isActive('/')
                                ? 'text-[#0fa7b4]'
                                : 'text-white/80'
                        "
                    >
                        Inicio
                    </Link>

                    <Link
                        href="/servicios"
                        class="text-sm font-semibold transition hover:text-[#0fa7b4]"
                        :class="
                            isActive(
                                '/servicios',
                            )
                                ? 'text-[#0fa7b4]'
                                : 'text-white/80'
                        "
                    >
                        Servicios
                    </Link>

                    <Link
                        href="/portafolio"
                        class="text-sm font-semibold transition hover:text-[#0fa7b4]"
                        :class="
                            isActive(
                                '/portafolio',
                            )
                                ? 'text-[#0fa7b4]'
                                : 'text-white/80'
                        "
                    >
                        Portafolio
                    </Link>

                    <Link
                        href="/contacto"
                        class="text-sm font-semibold transition hover:text-[#0fa7b4]"
                        :class="
                            isActive(
                                '/contacto',
                            )
                                ? 'text-[#0fa7b4]'
                                : 'text-white/80'
                        "
                    >
                        Contacto
                    </Link>
                </nav>

                <div
                    class="hidden lg:block"
                >
                    <a
                        :href="
                            whatsappUrl
                        "
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center rounded-xl bg-[#e84657] px-5 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        Cotizar ahora
                    </a>
                </div>

                <button
                    type="button"
                    class="rounded-lg border border-white/10 p-2 lg:hidden"
                    @click="
                        mobileOpen =
                            !mobileOpen
                    "
                >
                    <X
                        v-if="
                            mobileOpen
                        "
                        class="h-5 w-5"
                    />

                    <Menu
                        v-else
                        class="h-5 w-5"
                    />
                </button>
            </div>

            <div
                v-if="
                    mobileOpen
                "
                class="border-t border-white/10 px-5 py-5 lg:hidden"
            >
                <nav
                    class="flex flex-col gap-2"
                >
                    <Link
                        href="/"
                        class="rounded-xl px-4 py-3 font-semibold hover:bg-white/5"
                        @click="
                            closeMenu
                        "
                    >
                        Inicio
                    </Link>

                    <Link
                        href="/servicios"
                        class="rounded-xl px-4 py-3 font-semibold hover:bg-white/5"
                        @click="
                            closeMenu
                        "
                    >
                        Servicios
                    </Link>

                    <Link
                        href="/portafolio"
                        class="rounded-xl px-4 py-3 font-semibold hover:bg-white/5"
                        @click="
                            closeMenu
                        "
                    >
                        Portafolio
                    </Link>

                    <Link
                        href="/contacto"
                        class="rounded-xl px-4 py-3 font-semibold hover:bg-white/5"
                        @click="
                            closeMenu
                        "
                    >
                        Contacto
                    </Link>

                    <a
                        :href="
                            whatsappUrl
                        "
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-2 rounded-xl bg-[#e84657] px-4 py-3 text-center font-bold"
                    >
                        Cotizar ahora
                    </a>
                </nav>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <footer
            class="border-t border-white/10 bg-black/30"
        >
            <div
                class="mx-auto grid max-w-7xl gap-10 px-5 py-12 md:grid-cols-3 lg:px-8"
            >
                <div>
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
                        class="h-12 w-auto"
                    />

                    <p
                        class="mt-5 max-w-sm text-sm leading-6 text-white/55"
                    >
                        {{
                            site.footer_text
                        }}
                    </p>
                </div>

                <div>
                    <p
                        class="font-bold"
                    >
                        Navegación
                    </p>

                    <div
                        class="mt-4 flex flex-col gap-3 text-sm text-white/60"
                    >
                        <Link
                            href="/servicios"
                            class="hover:text-white"
                        >
                            Servicios
                        </Link>

                        <Link
                            href="/portafolio"
                            class="hover:text-white"
                        >
                            Portafolio
                        </Link>

                        <Link
                            href="/contacto"
                            class="hover:text-white"
                        >
                            Contacto
                        </Link>
                    </div>
                </div>

                <div>
                    <p
                        class="font-bold"
                    >
                        Contacto
                    </p>

                    <div
                        class="mt-4 space-y-3 text-sm text-white/60"
                    >
                        <p
                            v-if="
                                site.phone
                            "
                            class="flex items-center gap-2"
                        >
                            <Phone
                                class="h-4 w-4 text-[#0fa7b4]"
                            />

                            {{
                                site.phone
                            }}
                        </p>

                        <p
                            v-if="
                                site.email
                            "
                            class="flex items-center gap-2"
                        >
                            <Mail
                                class="h-4 w-4 text-[#0fa7b4]"
                            />

                            {{
                                site.email
                            }}
                        </p>

                        <p
                            v-if="
                                site.business_location
                            "
                        >
                            {{
                                site.business_location
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="border-t border-white/10 px-5 py-5 text-center text-xs text-white/40"
            >
                ©
                {{ new Date().getFullYear() }}
                {{
                    site.business_name
                }}.
                Todos los derechos reservados.
            </div>
        </footer>
    </div>
</template>