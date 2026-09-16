<script setup lang="ts">
import {
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';

import {
    router,
} from '@inertiajs/vue3';

import {
    LogOut,
    Settings,
} from '@lucide/vue';

import {
    ref,
} from 'vue';

defineProps<{
    user: any;
}>();

const loggingOut =
    ref(false);

const goToSettings =
    (): void => {
        router.visit(
            '/settings/system',
        );
    };

const logout =
    (): void => {
        if (
            loggingOut.value
        ) {
            return;
        }

        loggingOut.value =
            true;

        router.post(
            '/logout',
            {},
            {
                preserveState:
                    false,

                preserveScroll:
                    false,

                onSuccess: () => {
                    /*
                    |--------------------------------------------------------------------------
                    | REEMPLAZAR LA URL ACTUAL
                    |--------------------------------------------------------------------------
                    |
                    | Además de la redirección del backend, hacemos replace para
                    | que el estado actual del navegador sea /login.
                    |
                    */

                    window.location.replace(
                        '/login',
                    );
                },

                onError: () => {
                    loggingOut.value =
                        false;
                },

                onFinish: () => {
                    /*
                    |--------------------------------------------------------------------------
                    | Si onSuccess ejecuta location.replace esta línea deja de
                    | importar; queda como protección si Laravel responde error.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        window.location.pathname !==
                        '/login'
                    ) {
                        loggingOut.value =
                            false;
                    }
                },
            },
        );
    };
</script>

<template>
    <DropdownMenuLabel
        class="p-0 font-normal"
    >
        <div
            class="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
        >
            <div
                class="grid flex-1 text-left text-sm leading-tight"
            >
                <span
                    class="truncate font-black"
                >
                    {{
                        user?.name
                        ?? 'Usuario'
                    }}
                </span>

                <span
                    class="truncate text-xs text-muted-foreground"
                >
                    {{
                        user?.email
                        ?? ''
                    }}
                </span>
            </div>
        </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator />

    <DropdownMenuItem
        class="cursor-pointer gap-2"
        @select="
            goToSettings
        "
    >
        <Settings
            class="h-4 w-4"
        />

        Configuración
    </DropdownMenuItem>

    <DropdownMenuSeparator />

    <DropdownMenuItem
        class="cursor-pointer gap-2 text-[#e84657] focus:text-[#e84657]"
        :disabled="
            loggingOut
        "
        @select="
            logout
        "
    >
        <LogOut
            class="h-4 w-4"
        />

        {{
            loggingOut
                ? 'Cerrando sesión...'
                : 'Cerrar sesión'
        }}
    </DropdownMenuItem>
</template>