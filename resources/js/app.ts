import {
    createInertiaApp,
} from '@inertiajs/vue3';

import {
    initializeTheme,
} from '@/composables/useAppearance';

import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

import {
    initializeFlashToast,
} from '@/lib/flashToast';

import {
    initializeGlobalSelects,
} from '@/lib/globalSelects';

import '../css/adn-mobile.css';

/*
|--------------------------------------------------------------------------
| NOMBRE DE LA APLICACIÓN
|--------------------------------------------------------------------------
*/

const appName =
    import.meta.env.VITE_APP_NAME
    || 'ADN Publicidad';

/*
|--------------------------------------------------------------------------
| INERTIA
|--------------------------------------------------------------------------
*/

const inertiaApp =
    createInertiaApp({
        title: (
            title,
        ) => {
            return title
                ? `${title} - ${appName}`
                : appName;
        },

        layout: (
            name,
        ) => {
            /*
            |--------------------------------------------------------------------------
            | LOGIN
            |--------------------------------------------------------------------------
            */

            if (
                name === 'auth/Login'
            ) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | OTRAS PÁGINAS AUTH
            |--------------------------------------------------------------------------
            */

            if (
                name.startsWith(
                    'auth/',
                )
            ) {
                return AuthLayout;
            }

            /*
            |--------------------------------------------------------------------------
            | CONFIGURACIÓN DE CUENTA
            |--------------------------------------------------------------------------
            */

            if (
                name.startsWith(
                    'settings/',
                )
            ) {
                return [
                    AppLayout,
                    SettingsLayout,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | RESTO DE ADN PUBLICIDAD
            |--------------------------------------------------------------------------
            */

            return null;
        },

        progress: {
            color:
                '#0fa7b4',
        },
    });

/*
|--------------------------------------------------------------------------
| TEMA
|--------------------------------------------------------------------------
*/

initializeTheme();

/*
|--------------------------------------------------------------------------
| FLASH TOAST
|--------------------------------------------------------------------------
*/

initializeFlashToast();

/*
|--------------------------------------------------------------------------
| SELECTORES ADN
|--------------------------------------------------------------------------
|
| Esperamos explícitamente a que Inertia termine su inicialización.
| Así los <select> de la primera página ya existen cuando realizamos
| el primer escaneo.
|
*/

if (
    typeof window !==
        'undefined'
) {
    void Promise
        .resolve(
            inertiaApp,
        )
        .then(
            () => {
                window.requestAnimationFrame(
                    () => {
                        initializeGlobalSelects();
                    },
                );
            },
        );
}

/*
|--------------------------------------------------------------------------
| BFCACHE / SEGURIDAD
|--------------------------------------------------------------------------
*/

if (
    typeof window !==
        'undefined'
) {
    window.addEventListener(
        'pageshow',
        (
            event:
                PageTransitionEvent,
        ) => {
            if (
                event.persisted
            ) {
                window.location.reload();
            }
        },
    );
}