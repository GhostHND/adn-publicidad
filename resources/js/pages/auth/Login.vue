<script setup lang="ts">
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowRight,
    Eye,
    EyeOff,
    LockKeyhole,
    Mail,
    ShieldCheck,
    Sparkles,
} from '@lucide/vue';
import {
    ref,
} from 'vue';

import '../../../css/adn-brand.css';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const showPassword =
    ref(false);

const form =
    useForm({
        email: '',
        password: '',
        remember: false,
    });

const submit =
    (): void => {
        form.post(
            '/login',
            {
                preserveScroll:
                    true,

                onFinish: () => {
                    form.reset(
                        'password',
                    );
                },
            },
        );
    };
</script>

<template>
    <Head
        title="Iniciar sesión"
    />

    <!--
    fixed + inset-0 hace que el login ocupe SIEMPRE
    el viewport completo aunque exista un layout padre.
    -->

    <main
        class="fixed inset-0 z-[999] overflow-y-auto bg-[#040809] text-white"
    >
        <!-- FONDO GENERAL -->

        <div
            class="pointer-events-none fixed inset-0"
        >
            <div
                class="absolute -left-52 -top-52 h-[42rem] w-[42rem] rounded-full bg-[#0fa7b4]/20 blur-[150px]"
            />

            <div
                class="absolute -bottom-64 -right-52 h-[46rem] w-[46rem] rounded-full bg-[#e84657]/15 blur-[170px]"
            />

            <div
                class="absolute left-[45%] top-[20%] h-[30rem] w-[30rem] rounded-full bg-[#0fa7b4]/7 blur-[140px]"
            />

            <div
                class="absolute inset-0 opacity-[0.025]"
                style="
                    background-image:
                        linear-gradient(
                            rgba(255,255,255,.9) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(255,255,255,.9) 1px,
                            transparent 1px
                        );
                    background-size:
                        48px 48px;
                "
            />
        </div>

        <!-- CONTENEDOR -->

        <div
            class="relative flex min-h-dvh items-center justify-center px-4 py-8 sm:px-8 lg:px-12"
        >
            <section
                class="adn-enter grid w-full max-w-[1180px] overflow-hidden rounded-[2.5rem] border border-white/10 bg-[#071014]/90 shadow-[0_45px_140px_rgba(0,0,0,.58)] backdrop-blur-3xl lg:grid-cols-[1.08fr_.92fr]"
            >
                <!-- PANEL IZQUIERDO DESKTOP -->

                <div
                    class="relative hidden min-h-[680px] overflow-hidden border-r border-white/10 p-12 lg:flex lg:flex-col lg:justify-between"
                >
                    <div
                        class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(15,167,180,.22),transparent_38%),radial-gradient(circle_at_80%_80%,rgba(232,70,87,.13),transparent_42%)]"
                    />

                    <div
                        class="absolute -left-32 bottom-4 h-96 w-96 rounded-full border-[55px] border-[#0fa7b4]/10"
                    />

                    <div
                        class="relative"
                    >
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-[#0fa7b4]/20 bg-[#0fa7b4]/8 px-3 py-1.5 text-xs font-bold text-[#22c6d2]"
                        >
                            <Sparkles
                                class="h-3.5 w-3.5"
                            />

                            Centro de operaciones
                        </div>

                        <h1
                            class="mt-8 max-w-xl text-5xl font-black leading-[1.05] tracking-tight xl:text-6xl"
                        >
                            Todo tu negocio.

                            <span
                                class="adn-gradient-text"
                            >
                                En un solo lugar.
                            </span>
                        </h1>

                        <p
                            class="mt-6 max-w-lg text-base leading-8 text-white/50"
                        >
                            Solicitudes, cotizaciones, clientes, producción,
                            inventario, ventas y finanzas conectados dentro del
                            sistema de ADN Publicidad.
                        </p>
                    </div>

                    <div
                        class="relative"
                    >
                        <div
                            class="flex items-center gap-5"
                        >
                            <div
                                class="adn-shine flex h-24 w-24 items-center justify-center rounded-[1.8rem] border border-white/10 bg-white p-4 shadow-[0_18px_60px_rgba(15,167,180,.18)]"
                            >
                                <img
                                    src="/icons/logo_adn.svg"
                                    alt="ADN Publicidad"
                                    class="h-full w-full object-contain"
                                />
                            </div>

                            <div>
                                <p
                                    class="text-2xl font-black"
                                >
                                    ADN
                                    <span
                                        class="adn-gradient-text"
                                    >
                                        Publicidad
                                    </span>
                                </p>

                                <div
                                    class="mt-2 flex items-center gap-2"
                                >
                                    <span
                                        class="adn-pulse-dot h-2 w-2 rounded-full bg-emerald-500"
                                    />

                                    <span
                                        class="text-xs font-bold uppercase tracking-[0.15em] text-white/35"
                                    >
                                        Sistema de gestión
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORMULARIO -->

                <div
                    class="relative flex min-h-[620px] items-center p-6 sm:p-10 lg:min-h-[680px] lg:p-12"
                >
                    <div
                        class="mx-auto w-full max-w-[430px]"
                    >
                        <!-- LOGO MOBILE -->

                        <div
                            class="mb-8 flex justify-center lg:hidden"
                        >
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white p-3 shadow-xl"
                            >
                                <img
                                    src="/icons/logo_adn.svg"
                                    alt="ADN Publicidad"
                                    class="h-full w-full object-contain"
                                />
                            </div>
                        </div>

                        <p
                            class="text-xs font-black uppercase tracking-[0.2em] text-[#0fa7b4]"
                        >
                            Acceso privado
                        </p>

                        <h2
                            class="mt-3 text-3xl font-black tracking-tight sm:text-4xl"
                        >
                            Bienvenido de nuevo
                        </h2>

                        <p
                            class="mt-3 text-sm leading-6 text-white/40"
                        >
                            Ingresa tus credenciales para continuar al centro
                            de operaciones.
                        </p>

                        <div
                            v-if="
                                status
                            "
                            class="mt-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300"
                        >
                            {{
                                status
                            }}
                        </div>

                        <form
                            class="mt-8 space-y-5"
                            @submit.prevent="
                                submit
                            "
                        >
                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-[11px] font-black uppercase tracking-[0.13em] text-white/45"
                                >
                                    Correo electrónico
                                </label>

                                <div
                                    class="relative"
                                >
                                    <Mail
                                        class="absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-white/25"
                                    />

                                    <input
                                        id="email"
                                        v-model="
                                            form.email
                                        "
                                        type="email"
                                        autocomplete="email"
                                        autofocus
                                        placeholder="admin@adnpublicidad.site"
                                        class="h-14 w-full rounded-2xl border border-white/10 bg-white/[0.035] pl-12 pr-4 text-sm text-white outline-none placeholder:text-white/20 focus:border-[#0fa7b4]/70 focus:bg-[#0fa7b4]/5 focus:ring-4 focus:ring-[#0fa7b4]/8"
                                    />
                                </div>

                                <p
                                    v-if="
                                        form.errors.email
                                    "
                                    class="mt-2 text-xs text-[#f06472]"
                                >
                                    {{
                                        form.errors.email
                                    }}
                                </p>
                            </div>

                            <div>
                                <div
                                    class="mb-2 flex items-center justify-between gap-3"
                                >
                                    <label
                                        for="password"
                                        class="text-[11px] font-black uppercase tracking-[0.13em] text-white/45"
                                    >
                                        Contraseña
                                    </label>

                                    <Link
                                        v-if="
                                            canResetPassword
                                        "
                                        href="/forgot-password"
                                        class="text-xs font-bold text-[#22c6d2] transition hover:text-white"
                                    >
                                        ¿Olvidaste tu contraseña?
                                    </Link>
                                </div>

                                <div
                                    class="relative"
                                >
                                    <LockKeyhole
                                        class="absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-white/25"
                                    />

                                    <input
                                        id="password"
                                        v-model="
                                            form.password
                                        "
                                        :type="
                                            showPassword
                                                ? 'text'
                                                : 'password'
                                        "
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="h-14 w-full rounded-2xl border border-white/10 bg-white/[0.035] pl-12 pr-12 text-sm text-white outline-none placeholder:text-white/20 focus:border-[#0fa7b4]/70 focus:bg-[#0fa7b4]/5 focus:ring-4 focus:ring-[#0fa7b4]/8"
                                    />

                                    <button
                                        type="button"
                                        class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-white/25 transition hover:bg-white/5 hover:text-white"
                                        @click="
                                            showPassword =
                                                !showPassword
                                        "
                                    >
                                        <EyeOff
                                            v-if="
                                                showPassword
                                            "
                                            class="h-4 w-4"
                                        />

                                        <Eye
                                            v-else
                                            class="h-4 w-4"
                                        />
                                    </button>
                                </div>

                                <p
                                    v-if="
                                        form.errors.password
                                    "
                                    class="mt-2 text-xs text-[#f06472]"
                                >
                                    {{
                                        form.errors.password
                                    }}
                                </p>
                            </div>

                            <label
                                class="flex cursor-pointer items-center gap-3 text-sm text-white/45"
                            >
                                <input
                                    v-model="
                                        form.remember
                                    "
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-white/20 bg-transparent accent-[#0fa7b4]"
                                />

                                Mantener sesión iniciada
                            </label>

                            <button
                                type="submit"
                                :disabled="
                                    form.processing
                                "
                                class="adn-shine group flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#14bfcc] via-[#0fa7b4] to-[#e84657] px-5 font-black text-white shadow-[0_15px_40px_rgba(15,167,180,.22)] transition hover:-translate-y-1 hover:shadow-[0_22px_55px_rgba(15,167,180,.3)] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? 'Ingresando...'
                                        : 'Iniciar sesión'
                                }}

                                <ArrowRight
                                    v-if="
                                        !form.processing
                                    "
                                    class="h-4 w-4 transition-transform group-hover:translate-x-1"
                                />
                            </button>
                        </form>

                        <div
                            class="mt-8 flex items-center justify-center gap-2 text-[11px] text-white/25"
                        >
                            <ShieldCheck
                                class="h-4 w-4 text-[#0fa7b4]"
                            />

                            Acceso seguro · ADN Publicidad
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>