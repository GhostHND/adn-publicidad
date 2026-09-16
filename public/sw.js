/*
|--------------------------------------------------------------------------
| ADN PUBLICIDAD - SERVICE WORKER
|--------------------------------------------------------------------------
|
| Funciones:
|
| - Soporte PWA.
| - Web Push.
| - Notificaciones de nuevas solicitudes.
| - Comunicación con la aplicación abierta.
| - Apertura directa de Solicitudes.
| - Caché únicamente para recursos estáticos seguros.
|
| IMPORTANTE:
|
| NO almacenamos en caché Dashboard, Caja, Ventas, Clientes,
| Cotizaciones ni ningún dato administrativo dinámico.
|
*/

const CACHE_NAME =
    'adn-publicidad-static-v4';

const STATIC_ASSETS = [
    '/manifest.webmanifest',
    '/icons/logo_adn.svg',
    '/favicon.svg',
];

/*
|--------------------------------------------------------------------------
| INSTALL
|--------------------------------------------------------------------------
*/

self.addEventListener(
    'install',
    (event) => {
        event.waitUntil(
            caches
                .open(
                    CACHE_NAME,
                )
                .then(
                    (cache) =>
                        cache.addAll(
                            STATIC_ASSETS,
                        ),
                )
                .catch(
                    (error) => {
                        console.warn(
                            '[ADN SW] No fue posible almacenar todos los recursos estáticos:',
                            error,
                        );
                    },
                ),
        );

        /*
        |--------------------------------------------------------------------------
        | ACTIVAR NUEVA VERSIÓN
        |--------------------------------------------------------------------------
        */

        self.skipWaiting();
    },
);

/*
|--------------------------------------------------------------------------
| ACTIVATE
|--------------------------------------------------------------------------
*/

self.addEventListener(
    'activate',
    (event) => {
        event.waitUntil(
            caches
                .keys()
                .then(
                    (
                        cacheNames,
                    ) => {
                        return Promise.all(
                            cacheNames
                                .filter(
                                    (
                                        cacheName,
                                    ) =>
                                        cacheName !==
                                        CACHE_NAME,
                                )
                                .map(
                                    (
                                        cacheName,
                                    ) =>
                                        caches.delete(
                                            cacheName,
                                        ),
                                ),
                        );
                    },
                )
                .then(
                    () =>
                        self.clients
                            .claim(),
                ),
        );
    },
);

/*
|--------------------------------------------------------------------------
| FETCH
|--------------------------------------------------------------------------
|
| Únicamente utilizamos caché para archivos estáticos explícitos.
|
| Todo dato administrativo continúa consultándose directamente
| desde Laravel.
|
*/

self.addEventListener(
    'fetch',
    (event) => {
        if (
            event.request.method !==
            'GET'
        ) {
            return;
        }

        const url =
            new URL(
                event.request.url,
            );

        /*
        |--------------------------------------------------------------------------
        | SOLAMENTE MISMO ORIGEN
        |--------------------------------------------------------------------------
        */

        if (
            url.origin !==
            self.location.origin
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | SOLAMENTE RECURSOS ESTÁTICOS
        |--------------------------------------------------------------------------
        */

        if (
            !STATIC_ASSETS.includes(
                url.pathname,
            )
        ) {
            return;
        }

        event.respondWith(
            caches
                .match(
                    event.request,
                )
                .then(
                    (
                        cachedResponse,
                    ) => {
                        if (
                            cachedResponse
                        ) {
                            return cachedResponse;
                        }

                        return fetch(
                            event.request,
                        );
                    },
                ),
        );
    },
);

/*
|--------------------------------------------------------------------------
| NORMALIZAR PAYLOAD PUSH
|--------------------------------------------------------------------------
*/

const normalizePushPayload = (
    event,
) => {
    let payload = {};

    try {
        if (
            event.data
        ) {
            payload =
                event.data.json();
        }
    } catch (
        error
    ) {
        try {
            payload = {
                body:
                    event.data
                        ?.text()
                    ?? '',
            };
        } catch (
            secondError
        ) {
            payload = {};
        }
    }

    const nestedData =
        payload.data
        &&
        typeof payload.data ===
            'object'
            ? payload.data
            : {};

    const leadId =
        payload.lead_id
        ??
        nestedData.lead_id
        ??
        nestedData.id
        ??
        null;

    const targetUrl =
        payload.url
        ??
        nestedData.url
        ??
        (
            leadId
                ? `/website/leads?lead=${leadId}`
                : '/website/leads'
        );

    return {
        title:
            payload.title
            ??
            'Nueva solicitud · ADN Publicidad',

        body:
            payload.body
            ??
            payload.message
            ??
            nestedData.body
            ??
            'Has recibido una nueva solicitud desde el sitio web.',

        icon:
            payload.icon
            ??
            '/icons/logo_adn.svg',

        badge:
            payload.badge
            ??
            '/icons/logo_adn.svg',

        url:
            targetUrl,

        leadId,
    };
};

/*
|--------------------------------------------------------------------------
| ENVIAR MENSAJE A LA APP ABIERTA
|--------------------------------------------------------------------------
*/

const notifyOpenClients =
    async (
        clients,
        payload,
    ) => {
        for (
            const client
            of clients
        ) {
            try {
                client.postMessage({
                    type:
                        'ADN_PUSH_RECEIVED',

                    leadId:
                        payload.leadId,

                    url:
                        payload.url,
                });
            } catch (
                error
            ) {
                console.warn(
                    '[ADN SW] No se pudo comunicar con una ventana:',
                    error,
                );
            }
        }
    };

/*
|--------------------------------------------------------------------------
| ¿LA APP ESTÁ ABIERTA Y ENFOCADA?
|--------------------------------------------------------------------------
|
| Si el usuario está mirando directamente ADN Publicidad no necesitamos
| duplicar el aviso con una notificación del sistema.
|
*/

const hasFocusedVisibleClient = (
    clients,
) => {
    return clients.some(
        (
            client,
        ) => {
            return (
                client.visibilityState ===
                    'visible'
                &&
                client.focused ===
                    true
            );
        },
    );
};

/*
|--------------------------------------------------------------------------
| MOSTRAR NOTIFICACIÓN DEL SISTEMA
|--------------------------------------------------------------------------
*/

const showSystemNotification =
    async (
        payload,
    ) => {
        const tag =
            payload.leadId
                ? `adn-lead-${payload.leadId}`
                : 'adn-new-lead';

        const options = {
            body:
                payload.body,

            icon:
                payload.icon,

            badge:
                payload.badge,

            tag,

            renotify:
                true,

            requireInteraction:
                false,

            data: {
                url:
                    payload.url,

                leadId:
                    payload.leadId,
            },

            actions: [
                {
                    action:
                        'open',

                    title:
                        'Ver solicitud',
                },
            ],
        };

        await self.registration
            .showNotification(
                payload.title,
                options,
            );
    };

/*
|--------------------------------------------------------------------------
| PUSH
|--------------------------------------------------------------------------
|
| COMPORTAMIENTO:
|
| 1. Recibimos el Push.
| 2. Buscamos ventanas abiertas de ADN Publicidad.
| 3. Avisamos al frontend para que actualice Solicitudes/popup.
| 4. Si la app está enfocada:
|       NO mostramos notificación del sistema.
| 5. Si está cerrada, minimizada o en segundo plano:
|       SÍ mostramos notificación del sistema.
|
*/

self.addEventListener(
    'push',
    (event) => {
        event.waitUntil(
            (
                async () => {
                    const payload =
                        normalizePushPayload(
                            event,
                        );

                    const clientList =
                        await self.clients
                            .matchAll({
                                type:
                                    'window',

                                includeUncontrolled:
                                    true,
                            });

                    /*
                    |--------------------------------------------------------------------------
                    | AVISAR A LA APLICACIÓN
                    |--------------------------------------------------------------------------
                    */

                    await notifyOpenClients(
                        clientList,
                        payload,
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | APP ACTUALMENTE EN PANTALLA
                    |--------------------------------------------------------------------------
                    |
                    | LeadNotificationCenter mostrará el popup interno.
                    |
                    */

                    if (
                        hasFocusedVisibleClient(
                            clientList,
                        )
                    ) {
                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | APP NO ESTÁ EN PRIMER PLANO
                    |--------------------------------------------------------------------------
                    */

                    await showSystemNotification(
                        payload,
                    );
                }
            )(),
        );
    },
);

/*
|--------------------------------------------------------------------------
| CLICK EN NOTIFICACIÓN
|--------------------------------------------------------------------------
*/

self.addEventListener(
    'notificationclick',
    (event) => {
        event.notification
            .close();

        const targetUrl =
            event.notification
                ?.data
                ?.url
            ??
            '/website/leads';

        event.waitUntil(
            (
                async () => {
                    const clientList =
                        await self.clients
                            .matchAll({
                                type:
                                    'window',

                                includeUncontrolled:
                                    true,
                            });

                    /*
                    |--------------------------------------------------------------------------
                    | BUSCAR UNA VENTANA EXISTENTE DE ADN PUBLICIDAD
                    |--------------------------------------------------------------------------
                    */

                    for (
                        const client
                        of clientList
                    ) {
                        try {
                            const clientUrl =
                                new URL(
                                    client.url,
                                );

                            if (
                                clientUrl.origin !==
                                self.location.origin
                            ) {
                                continue;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | NAVEGAR A SOLICITUDES
                            |--------------------------------------------------------------------------
                            */

                            if (
                                'navigate'
                                in client
                            ) {
                                await client
                                    .navigate(
                                        targetUrl,
                                    );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | TRAER LA APP AL FRENTE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                'focus'
                                in client
                            ) {
                                await client
                                    .focus();
                            }

                            return;
                        } catch (
                            error
                        ) {
                            console.warn(
                                '[ADN SW] No se pudo reutilizar una ventana existente:',
                                error,
                            );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NO HAY VENTANA ABIERTA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        self.clients
                            .openWindow
                    ) {
                        await self.clients
                            .openWindow(
                                targetUrl,
                            );
                    }
                }
            )(),
        );
    },
);