<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

/*
|--------------------------------------------------------------------------
| ENTRADA DEL SISTEMA
|--------------------------------------------------------------------------
|
| ADN Publicidad es un sistema administrativo privado.
|
| La página pública vive separada del sistema, por lo que cualquier acceso
| a esta vista debe dirigirse al Dashboard.
|
| Si el usuario no está autenticado, Laravel se encargará de enviarlo al
| Login cuando intente acceder a /dashboard.
|
*/

onMounted(() => {
    window.location.replace('/dashboard');
});
</script>

<template>
    <Head title="ADN Publicidad" />

    <main
        class="fixed inset-0 flex items-center justify-center bg-[#05090b]"
    >
        <div
            class="flex flex-col items-center gap-4 text-center"
        >
            <div
                class="flex h-16 w-16 items-center justify-center rounded-2xl border border-[#0fa7b4]/15 bg-[#0fa7b4]/10 p-2"
            >
                <img
                    src="/icons/logo_adn.svg"
                    alt="ADN Publicidad"
                    class="h-full w-full object-contain"
                />
            </div>

            <div>
                <p
                    class="text-sm font-black text-white"
                >
                    ADN Publicidad
                </p>

                <p
                    class="mt-1 text-xs text-white/35"
                >
                    Cargando sistema...
                </p>
            </div>
        </div>
    </main>
</template>