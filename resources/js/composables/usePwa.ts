import {
    computed,
    ref,
} from 'vue';

interface BeforeInstallPromptEvent extends Event {
    prompt: () => Promise<void>;

    userChoice: Promise<{
        outcome:
            | 'accepted'
            | 'dismissed';

        platform: string;
    }>;
}

interface NavigatorStandalone extends Navigator {
    standalone?: boolean;
}

/*
|--------------------------------------------------------------------------
| ESTADO GLOBAL
|--------------------------------------------------------------------------
|
| Este estado queda fuera del composable para que todas las vistas que
| utilicen usePwa() compartan la misma información.
|
*/

const initialized =
    ref(false);

const installPrompt =
    ref<BeforeInstallPromptEvent | null>(
        null,
    );

const serviceWorkerRegistration =
    ref<ServiceWorkerRegistration | null>(
        null,
    );

const serviceWorkerReady =
    ref(false);

const installed =
    ref(false);

const ios =
    ref(false);

const android =
    ref(false);

const mobile =
    ref(false);

const installDismissed =
    ref(false);

const INSTALL_DISMISS_KEY =
    'adn-pwa-install-dismissed-until';

const DISMISS_DAYS =
    7;

/*
|--------------------------------------------------------------------------
| DETECCIÓN DEL DISPOSITIVO
|--------------------------------------------------------------------------
*/

const detectDevice =
    (): void => {
        if (
            typeof window ===
            'undefined'
        ) {
            return;
        }

        const userAgent =
            navigator.userAgent
                .toLowerCase();

        const platform =
            navigator.platform
                ?.toLowerCase()
            ?? '';

        /*
        |--------------------------------------------------------------------------
        | iPHONE / iPAD
        |--------------------------------------------------------------------------
        |
        | También detectamos iPads modernos que pueden reportarse como Mac.
        |
        */

        ios.value =
            /iphone|ipad|ipod/.test(
                userAgent,
            )
            ||
            (
                platform.includes(
                    'mac',
                )
                &&
                navigator.maxTouchPoints >
                    1
            );

        android.value =
            /android/.test(
                userAgent,
            );

        mobile.value =
            ios.value
            ||
            android.value
            ||
            /mobile/.test(
                userAgent,
            );
    };

/*
|--------------------------------------------------------------------------
| DETECTAR SI YA ESTÁ INSTALADA
|--------------------------------------------------------------------------
*/

const detectInstalled =
    (): boolean => {
        if (
            typeof window ===
            'undefined'
        ) {
            return false;
        }

        const standaloneMode =
            window
                .matchMedia(
                    '(display-mode: standalone)',
                )
                .matches;

        const fullscreenMode =
            window
                .matchMedia(
                    '(display-mode: fullscreen)',
                )
                .matches;

        /*
        |--------------------------------------------------------------------------
        | IMPORTANTE
        |--------------------------------------------------------------------------
        |
        | El cast debe permanecer EN UNA SOLA EXPRESIÓN.
        | Vite 8 / Rolldown puede fallar si "as NavigatorStandalone"
        | queda partido entre varias líneas.
        |
        */

        const navigatorStandalone =
            Boolean(
                (navigator as NavigatorStandalone).standalone,
            );

        installed.value =
            standaloneMode
            ||
            fullscreenMode
            ||
            navigatorStandalone;

        return installed.value;
    };

/*
|--------------------------------------------------------------------------
| ESTADO DEL AVISO DESCARTADO
|--------------------------------------------------------------------------
*/

const readDismissState =
    (): void => {
        if (
            typeof window ===
            'undefined'
        ) {
            return;
        }

        const raw =
            window.localStorage
                .getItem(
                    INSTALL_DISMISS_KEY,
                );

        if (!raw) {
            installDismissed.value =
                false;

            return;
        }

        const until =
            Number(
                raw,
            );

        if (
            Number.isNaN(
                until,
            )
            ||
            Date.now() >
                until
        ) {
            window.localStorage
                .removeItem(
                    INSTALL_DISMISS_KEY,
                );

            installDismissed.value =
                false;

            return;
        }

        installDismissed.value =
            true;
    };

/*
|--------------------------------------------------------------------------
| SERVICE WORKER
|--------------------------------------------------------------------------
*/

const registerServiceWorker =
    async (): Promise<ServiceWorkerRegistration | null> => {
        if (
            typeof window ===
            'undefined'
            ||
            !(
                'serviceWorker'
                in navigator
            )
        ) {
            return null;
        }

        try {
            const registration =
                await navigator
                    .serviceWorker
                    .register(
                        '/sw.js',
                        {
                            scope:
                                '/',
                        },
                    );

            serviceWorkerRegistration.value =
                registration;

            await navigator
                .serviceWorker
                .ready;

            serviceWorkerReady.value =
                true;

            return registration;
        } catch (
            error
        ) {
            console.error(
                '[ADN PWA] No se pudo registrar el Service Worker:',
                error,
            );

            serviceWorkerReady.value =
                false;

            return null;
        }
    };

/*
|--------------------------------------------------------------------------
| EVENTO DE INSTALACIÓN
|--------------------------------------------------------------------------
*/

const handleBeforeInstallPrompt = (
    event: Event,
): void => {
    event.preventDefault();

    installPrompt.value =
        event as BeforeInstallPromptEvent;
};

/*
|--------------------------------------------------------------------------
| APP INSTALADA
|--------------------------------------------------------------------------
*/

const handleInstalled =
    (): void => {
        installed.value =
            true;

        installPrompt.value =
            null;

        installDismissed.value =
            false;

        if (
            typeof window !==
            'undefined'
        ) {
            window.localStorage
                .removeItem(
                    INSTALL_DISMISS_KEY,
                );
        }
    };

/*
|--------------------------------------------------------------------------
| INICIALIZAR PWA
|--------------------------------------------------------------------------
*/

const initializePwa =
    async (): Promise<void> => {
        if (
            typeof window ===
            'undefined'
        ) {
            return;
        }

        detectDevice();

        detectInstalled();

        readDismissState();

        if (
            !initialized.value
        ) {
            window.addEventListener(
                'beforeinstallprompt',
                handleBeforeInstallPrompt,
            );

            window.addEventListener(
                'appinstalled',
                handleInstalled,
            );

            const displayMode =
                window.matchMedia(
                    '(display-mode: standalone)',
                );

            displayMode.addEventListener(
                'change',
                () => {
                    detectInstalled();
                },
            );

            initialized.value =
                true;
        }

        await registerServiceWorker();
    };

/*
|--------------------------------------------------------------------------
| ¿PUEDE MOSTRAR EL PROMPT NATIVO?
|--------------------------------------------------------------------------
*/

const canPromptInstall =
    computed(
        () => {
            return (
                !installed.value
                &&
                installPrompt.value !==
                    null
            );
        },
    );

/*
|--------------------------------------------------------------------------
| iOS NECESITA INSTRUCCIONES MANUALES
|--------------------------------------------------------------------------
*/

const needsIosInstructions =
    computed(
        () => {
            return (
                ios.value
                &&
                !installed.value
            );
        },
    );

/*
|--------------------------------------------------------------------------
| ¿MOSTRAMOS ALGUNA OPCIÓN DE INSTALACIÓN?
|--------------------------------------------------------------------------
*/

const shouldOfferInstall =
    computed(
        () => {
            if (
                installed.value
                ||
                installDismissed.value
            ) {
                return false;
            }

            return (
                canPromptInstall.value
                ||
                needsIosInstructions.value
            );
        },
    );

/*
|--------------------------------------------------------------------------
| INSTALAR APLICACIÓN
|--------------------------------------------------------------------------
*/

const installApp =
    async (): Promise<
        | 'accepted'
        | 'dismissed'
        | 'ios'
        | 'unavailable'
    > => {
        if (
            installed.value
        ) {
            return 'accepted';
        }

        /*
        |--------------------------------------------------------------------------
        | iPHONE / iPAD
        |--------------------------------------------------------------------------
        |
        | iOS no expone beforeinstallprompt.
        | La interfaz mostrará las instrucciones manuales.
        |
        */

        if (
            ios.value
            &&
            !installPrompt.value
        ) {
            return 'ios';
        }

        if (
            !installPrompt.value
        ) {
            return 'unavailable';
        }

        const prompt =
            installPrompt.value;

        await prompt.prompt();

        const result =
            await prompt.userChoice;

        if (
            result.outcome ===
            'accepted'
        ) {
            installed.value =
                true;

            installPrompt.value =
                null;

            return 'accepted';
        }

        return 'dismissed';
    };

/*
|--------------------------------------------------------------------------
| OCULTAR AVISO DE INSTALACIÓN
|--------------------------------------------------------------------------
*/

const dismissInstall =
    (): void => {
        installDismissed.value =
            true;

        if (
            typeof window ===
            'undefined'
        ) {
            return;
        }

        const until =
            Date.now()
            +
            (
                DISMISS_DAYS
                *
                24
                *
                60
                *
                60
                *
                1000
            );

        window.localStorage
            .setItem(
                INSTALL_DISMISS_KEY,
                String(
                    until,
                ),
            );
    };

/*
|--------------------------------------------------------------------------
| COMPOSABLE
|--------------------------------------------------------------------------
*/

export function usePwa() {
    return {
        initialized,

        installed,

        isIos:
            ios,

        isAndroid:
            android,

        isMobile:
            mobile,

        serviceWorkerReady,

        serviceWorkerRegistration,

        canPromptInstall,

        needsIosInstructions,

        shouldOfferInstall,

        initializePwa,

        installApp,

        dismissInstall,

        detectInstalled,
    };
}