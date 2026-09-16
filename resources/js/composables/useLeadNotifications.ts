import { usePwa } from '@/composables/usePwa';
import {
    computed,
    ref,
} from 'vue';

type NotificationPermissionState =
    | NotificationPermission
    | 'unsupported';

/*
|--------------------------------------------------------------------------
| ESTADO GLOBAL
|--------------------------------------------------------------------------
|
| Se mantiene fuera del composable para que todos los componentes
| compartan el mismo estado de Web Push.
|
*/

const initialized =
    ref(false);

const supported =
    ref(false);

const permission =
    ref<NotificationPermissionState>(
        'unsupported',
    );

const subscribed =
    ref(false);

const loading =
    ref(false);

const lastError =
    ref<string | null>(
        null,
    );

const subscription =
    ref<PushSubscription | null>(
        null,
    );

/*
|--------------------------------------------------------------------------
| XSRF
|--------------------------------------------------------------------------
*/

const getXsrfToken =
    (): string | null => {
        if (
            typeof document ===
            'undefined'
        ) {
            return null;
        }

        const item =
            document.cookie
                .split('; ')
                .find(
                    (row) =>
                        row.startsWith(
                            'XSRF-TOKEN=',
                        ),
                );

        if (!item) {
            return null;
        }

        return decodeURIComponent(
            item.substring(
                'XSRF-TOKEN='.length,
            ),
        );
    };

const requestHeaders =
    (): HeadersInit => {
        const headers:
            Record<string, string> = {
            Accept:
                'application/json',

            'Content-Type':
                'application/json',

            'X-Requested-With':
                'XMLHttpRequest',
        };

        const xsrf =
            getXsrfToken();

        if (xsrf) {
            headers[
                'X-XSRF-TOKEN'
            ] = xsrf;
        }

        return headers;
    };

/*
|--------------------------------------------------------------------------
| VAPID BASE64 URL -> ARRAYBUFFER
|--------------------------------------------------------------------------
|
| IMPORTANTE:
|
| Antes retornábamos Uint8Array.
|
| TypeScript moderno diferencia:
|
| Uint8Array<ArrayBufferLike>
| ArrayBuffer
| SharedArrayBuffer
|
| PushManager.subscribe() acepta BufferSource.
|
| Para eliminar la incompatibilidad de tipos retornamos directamente
| un ArrayBuffer real.
|
*/

const urlBase64ToArrayBuffer = (
    base64String: string,
): ArrayBuffer => {
    const padding =
        '='.repeat(
            (
                4
                -
                (
                    base64String.length
                    % 4
                )
            )
            % 4,
        );

    const base64 =
        (
            base64String
            +
            padding
        )
            .replace(
                /-/g,
                '+',
            )
            .replace(
                /_/g,
                '/',
            );

    const rawData =
        window.atob(
            base64,
        );

    /*
    |--------------------------------------------------------------------------
    | ARRAYBUFFER REAL
    |--------------------------------------------------------------------------
    */

    const buffer =
        new ArrayBuffer(
            rawData.length,
        );

    const outputArray =
        new Uint8Array(
            buffer,
        );

    for (
        let index = 0;
        index <
        rawData.length;
        index++
    ) {
        outputArray[
            index
        ] =
            rawData.charCodeAt(
                index,
            );
    }

    return buffer;
};

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN PUSH
|--------------------------------------------------------------------------
*/

const getPushConfiguration =
    async (): Promise<string> => {
        const response =
            await fetch(
                '/notifications/push/config',
                {
                    method:
                        'GET',

                    credentials:
                        'same-origin',

                    headers: {
                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                },
            );

        if (
            !response.ok
        ) {
            throw new Error(
                'No se pudo obtener la configuración de notificaciones.',
            );
        }

        const payload =
            await response.json();

        const publicKey =
            payload.public_key
            ??
            payload.vapid_public_key
            ??
            payload.vapidPublicKey
            ??
            payload.key;

        if (!publicKey) {
            throw new Error(
                'No existe una clave pública VAPID configurada.',
            );
        }

        return String(
            publicKey,
        );
    };

/*
|--------------------------------------------------------------------------
| GUARDAR SUSCRIPCIÓN EN LARAVEL
|--------------------------------------------------------------------------
*/

const saveSubscription =
    async (
        pushSubscription:
            PushSubscription,
    ): Promise<void> => {
        const json =
            pushSubscription
                .toJSON();

        const response =
            await fetch(
                '/notifications/push/subscribe',
                {
                    method:
                        'POST',

                    credentials:
                        'same-origin',

                    headers:
                        requestHeaders(),

                    body:
                        JSON.stringify({
                            endpoint:
                                pushSubscription.endpoint,

                            expirationTime:
                                json.expirationTime
                                ?? null,

                            keys:
                                json.keys
                                ?? {},

                            /*
                            |--------------------------------------------------------------------------
                            | COMPATIBILIDAD BACKEND
                            |--------------------------------------------------------------------------
                            */

                            public_key:
                                json.keys
                                    ?.p256dh
                                ?? null,

                            p256dh:
                                json.keys
                                    ?.p256dh
                                ?? null,

                            auth_token:
                                json.keys
                                    ?.auth
                                ?? null,

                            auth:
                                json.keys
                                    ?.auth
                                ?? null,

                            content_encoding:
                                'aes128gcm',
                        }),
                },
            );

        if (
            !response.ok
        ) {
            const message =
                await response
                    .text();

            throw new Error(
                message
                ||
                'No se pudo guardar la suscripción.',
            );
        }
    };

/*
|--------------------------------------------------------------------------
| INICIALIZAR NOTIFICACIONES
|--------------------------------------------------------------------------
*/

const initializeNotifications =
    async (): Promise<void> => {
        if (
            typeof window ===
            'undefined'
        ) {
            return;
        }

        supported.value =
            (
                'Notification'
                in window
            )
            &&
            (
                'serviceWorker'
                in navigator
            )
            &&
            (
                'PushManager'
                in window
            );

        if (
            !supported.value
        ) {
            permission.value =
                'unsupported';

            initialized.value =
                true;

            return;
        }

        permission.value =
            Notification.permission;

        try {
            const registration =
                await navigator
                    .serviceWorker
                    .ready;

            subscription.value =
                await registration
                    .pushManager
                    .getSubscription();

            subscribed.value =
                subscription.value !==
                null;
        } catch (
            error
        ) {
            console.error(
                '[ADN PUSH] No se pudo comprobar la suscripción:',
                error,
            );
        }

        initialized.value =
            true;
    };

/*
|--------------------------------------------------------------------------
| ACTIVAR NOTIFICACIONES
|--------------------------------------------------------------------------
*/

const subscribeToNotifications =
    async (): Promise<boolean> => {
        if (
            loading.value
        ) {
            return false;
        }

        lastError.value =
            null;

        const {
            isIos,
            installed,
        } = usePwa();

        if (
            !supported.value
        ) {
            lastError.value =
                'Este navegador no admite notificaciones push.';

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | iPHONE / iPAD
        |--------------------------------------------------------------------------
        |
        | En iOS primero necesitamos que la PWA esté instalada.
        |
        */

        if (
            isIos.value
            &&
            !installed.value
        ) {
            lastError.value =
                'Primero instala ADN Publicidad en la pantalla de inicio.';

            return false;
        }

        loading.value =
            true;

        try {
            let currentPermission =
                Notification.permission;

            /*
            |--------------------------------------------------------------------------
            | SOLICITAR PERMISO
            |--------------------------------------------------------------------------
            |
            | Solamente ocurre como consecuencia de una acción del usuario.
            |
            */

            if (
                currentPermission ===
                'default'
            ) {
                currentPermission =
                    await Notification
                        .requestPermission();
            }

            permission.value =
                currentPermission;

            if (
                currentPermission !==
                'granted'
            ) {
                lastError.value =
                    currentPermission ===
                    'denied'
                        ? 'Las notificaciones están bloqueadas en el navegador.'
                        : 'No se concedió permiso para notificaciones.';

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | SERVICE WORKER
            |--------------------------------------------------------------------------
            */

            const registration =
                await navigator
                    .serviceWorker
                    .ready;

            let currentSubscription =
                await registration
                    .pushManager
                    .getSubscription();

            /*
            |--------------------------------------------------------------------------
            | CREAR SUSCRIPCIÓN
            |--------------------------------------------------------------------------
            */

            if (
                !currentSubscription
            ) {
                const publicKey =
                    await getPushConfiguration();

                const applicationServerKey =
                    urlBase64ToArrayBuffer(
                        publicKey,
                    );

                currentSubscription =
                    await registration
                        .pushManager
                        .subscribe({
                            userVisibleOnly:
                                true,

                            applicationServerKey,
                        });
            }

            /*
            |--------------------------------------------------------------------------
            | GUARDAR EN EL BACKEND
            |--------------------------------------------------------------------------
            */

            await saveSubscription(
                currentSubscription,
            );

            subscription.value =
                currentSubscription;

            subscribed.value =
                true;

            return true;
        } catch (
            error
        ) {
            console.error(
                '[ADN PUSH] Error al activar notificaciones:',
                error,
            );

            lastError.value =
                error instanceof Error
                    ? error.message
                    : 'No se pudieron activar las notificaciones.';

            return false;
        } finally {
            loading.value =
                false;
        }
    };

/*
|--------------------------------------------------------------------------
| DESACTIVAR NOTIFICACIONES
|--------------------------------------------------------------------------
*/

const unsubscribeFromNotifications =
    async (): Promise<boolean> => {
        if (
            loading.value
        ) {
            return false;
        }

        loading.value =
            true;

        lastError.value =
            null;

        try {
            const registration =
                await navigator
                    .serviceWorker
                    .ready;

            const currentSubscription =
                await registration
                    .pushManager
                    .getSubscription();

            if (
                currentSubscription
            ) {
                /*
                |--------------------------------------------------------------------------
                | ELIMINAR EN BACKEND
                |--------------------------------------------------------------------------
                */

                const response =
                    await fetch(
                        '/notifications/push/unsubscribe',
                        {
                            method:
                                'DELETE',

                            credentials:
                                'same-origin',

                            headers:
                                requestHeaders(),

                            body:
                                JSON.stringify({
                                    endpoint:
                                        currentSubscription.endpoint,
                                }),
                        },
                    );

                if (
                    !response.ok
                ) {
                    console.warn(
                        '[ADN PUSH] El backend no pudo eliminar la suscripción.',
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | ELIMINAR EN EL NAVEGADOR
                |--------------------------------------------------------------------------
                */

                await currentSubscription
                    .unsubscribe();
            }

            subscription.value =
                null;

            subscribed.value =
                false;

            return true;
        } catch (
            error
        ) {
            console.error(
                '[ADN PUSH] Error al desactivar notificaciones:',
                error,
            );

            lastError.value =
                error instanceof Error
                    ? error.message
                    : 'No se pudieron desactivar las notificaciones.';

            return false;
        } finally {
            loading.value =
                false;
        }
    };

/*
|--------------------------------------------------------------------------
| ¿SE PUEDE SOLICITAR PERMISO?
|--------------------------------------------------------------------------
*/

const canRequestPermission =
    computed(
        () => {
            const {
                isIos,
                installed,
            } = usePwa();

            if (
                !supported.value
            ) {
                return false;
            }

            if (
                permission.value ===
                'denied'
            ) {
                return false;
            }

            if (
                isIos.value
                &&
                !installed.value
            ) {
                return false;
            }

            return true;
        },
    );

/*
|--------------------------------------------------------------------------
| ESTADO DE NOTIFICACIONES
|--------------------------------------------------------------------------
*/

const notificationStatus =
    computed<
        | 'unsupported'
        | 'blocked'
        | 'subscribed'
        | 'available'
    >(
        () => {
            if (
                !supported.value
            ) {
                return 'unsupported';
            }

            if (
                permission.value ===
                'denied'
            ) {
                return 'blocked';
            }

            if (
                subscribed.value
            ) {
                return 'subscribed';
            }

            return 'available';
        },
    );

/*
|--------------------------------------------------------------------------
| COMPOSABLE
|--------------------------------------------------------------------------
*/

export function useLeadNotifications() {
    return {
        /*
        |--------------------------------------------------------------------------
        | ESTADO
        |--------------------------------------------------------------------------
        */

        initialized,

        supported,

        permission,

        subscribed,

        subscription,

        loading,

        lastError,

        /*
        |--------------------------------------------------------------------------
        | COMPUTED
        |--------------------------------------------------------------------------
        */

        canRequestPermission,

        notificationStatus,

        /*
        |--------------------------------------------------------------------------
        | MÉTODOS
        |--------------------------------------------------------------------------
        */

        initializeNotifications,

        subscribeToNotifications,

        unsubscribeFromNotifications,
    };
}