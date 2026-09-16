import AdnGlobalSelect from '../components/AdnGlobalSelect.vue';

import {
    createApp,
} from 'vue';

import type {
    App,
} from 'vue';

type MountedSelect = {
    app: App;
    host: HTMLDivElement;
    originalStyle: string | null;
    originalAriaHidden: string | null;
    originalTabIndex: string | null;
};

const mounted =
    new Map<
        HTMLSelectElement,
        MountedSelect
    >();

let initialized =
    false;

let observer:
    MutationObserver
    | null =
    null;

let scanPending =
    false;

/*
|--------------------------------------------------------------------------
| SELECTS EXCLUIDOS
|--------------------------------------------------------------------------
|
| Si alguna vez necesitamos conservar un select nativo:
|
| <select data-adn-native-select="true">
|
*/

const shouldIgnore = (
    select: HTMLSelectElement,
): boolean => {
    if (
        select.multiple
    ) {
        return true;
    }

    if (
        select.size >
        1
    ) {
        return true;
    }

    if (
        select.hasAttribute(
            'data-adn-native-select',
        )
    ) {
        return true;
    }

    if (
        select.hasAttribute(
            'data-adn-select-enhanced',
        )
    ) {
        return true;
    }

    if (
        select.closest(
            '[data-adn-global-select-host]',
        )
    ) {
        return true;
    }

    return false;
};

/*
|--------------------------------------------------------------------------
| CREAR HOST VISUAL
|--------------------------------------------------------------------------
*/

const createHost = (
    select: HTMLSelectElement,
): HTMLDivElement => {
    const host =
        document.createElement(
            'div',
        );

    host.setAttribute(
        'data-adn-global-select-host',
        'true',
    );

    host.className =
        'relative w-full min-w-0';

    if (
        select.style.width
    ) {
        host.style.width =
            select.style.width;
    }

    if (
        select.style.minWidth
    ) {
        host.style.minWidth =
            select.style.minWidth;
    }

    if (
        select.style.maxWidth
    ) {
        host.style.maxWidth =
            select.style.maxWidth;
    }

    return host;
};

/*
|--------------------------------------------------------------------------
| OCULTAR SELECT NATIVO
|--------------------------------------------------------------------------
|
| El select original continúa existiendo para:
|
| - v-model
| - required
| - validación
| - change
| - input
| - formularios
| - Inertia
|
*/

const hideNativeSelect = (
    select: HTMLSelectElement,
): void => {
    select.setAttribute(
        'data-adn-select-enhanced',
        'true',
    );

    select.setAttribute(
        'aria-hidden',
        'true',
    );

    select.tabIndex =
        -1;

    select.style.position =
        'absolute';

    select.style.width =
        '1px';

    select.style.height =
        '1px';

    select.style.padding =
        '0';

    select.style.margin =
        '-1px';

    select.style.overflow =
        'hidden';

    select.style.clip =
        'rect(0 0 0 0)';

    select.style.clipPath =
        'inset(50%)';

    select.style.whiteSpace =
        'nowrap';

    select.style.border =
        '0';

    select.style.opacity =
        '0';

    select.style.pointerEvents =
        'none';
};

/*
|--------------------------------------------------------------------------
| RESTAURAR SELECT NATIVO
|--------------------------------------------------------------------------
*/

const restoreNativeSelect = (
    select: HTMLSelectElement,
    record: MountedSelect,
): void => {
    if (
        record.originalStyle ===
        null
    ) {
        select.removeAttribute(
            'style',
        );
    } else {
        select.setAttribute(
            'style',
            record.originalStyle,
        );
    }

    if (
        record.originalAriaHidden ===
        null
    ) {
        select.removeAttribute(
            'aria-hidden',
        );
    } else {
        select.setAttribute(
            'aria-hidden',
            record.originalAriaHidden,
        );
    }

    if (
        record.originalTabIndex ===
        null
    ) {
        select.removeAttribute(
            'tabindex',
        );
    } else {
        select.setAttribute(
            'tabindex',
            record.originalTabIndex,
        );
    }

    select.removeAttribute(
        'data-adn-select-enhanced',
    );
};

/*
|--------------------------------------------------------------------------
| CONVERTIR SELECT
|--------------------------------------------------------------------------
*/

const enhanceSelect = (
    select: HTMLSelectElement,
): void => {
    if (
        mounted.has(
            select,
        )
    ) {
        return;
    }

    if (
        shouldIgnore(
            select,
        )
    ) {
        return;
    }

    const originalStyle =
        select.getAttribute(
            'style',
        );

    const originalAriaHidden =
        select.getAttribute(
            'aria-hidden',
        );

    const originalTabIndex =
        select.getAttribute(
            'tabindex',
        );

    const host =
        createHost(
            select,
        );

    select.insertAdjacentElement(
        'afterend',
        host,
    );

    hideNativeSelect(
        select,
    );

    const app =
        createApp(
            AdnGlobalSelect,
            {
                nativeSelect:
                    select,
            },
        );

    app.mount(
        host,
    );

    mounted.set(
        select,
        {
            app,
            host,
            originalStyle,
            originalAriaHidden,
            originalTabIndex,
        },
    );
};

/*
|--------------------------------------------------------------------------
| DESMONTAR
|--------------------------------------------------------------------------
*/

const unmountSelect = (
    select: HTMLSelectElement,
): void => {
    const record =
        mounted.get(
            select,
        );

    if (
        !record
    ) {
        return;
    }

    try {
        record.app.unmount();
    } catch {
        // No detener la aplicación.
    }

    if (
        record.host.isConnected
    ) {
        record.host.remove();
    }

    if (
        select.isConnected
    ) {
        restoreNativeSelect(
            select,
            record,
        );
    }

    mounted.delete(
        select,
    );
};

/*
|--------------------------------------------------------------------------
| LIMPIAR REGISTROS VIEJOS
|--------------------------------------------------------------------------
*/

const cleanup =
    (): void => {
        for (
            const [
                select,
                record,
            ]
            of mounted.entries()
        ) {
            /*
            |--------------------------------------------------------------------------
            | SELECT ELIMINADO POR VUE / INERTIA
            |--------------------------------------------------------------------------
            */

            if (
                !select.isConnected
            ) {
                try {
                    record.app.unmount();
                } catch {
                    // No detener la aplicación.
                }

                mounted.delete(
                    select,
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | HOST ELIMINADO DURANTE UN RENDER
            |--------------------------------------------------------------------------
            */

            if (
                !record.host.isConnected
            ) {
                try {
                    record.app.unmount();
                } catch {
                    // No detener la aplicación.
                }

                restoreNativeSelect(
                    select,
                    record,
                );

                mounted.delete(
                    select,
                );
            }
        }
    };

/*
|--------------------------------------------------------------------------
| ESCANEAR DOM
|--------------------------------------------------------------------------
*/

const scan = (
    root: ParentNode = document,
): void => {
    cleanup();

    if (
        root
        instanceof
        HTMLSelectElement
    ) {
        enhanceSelect(
            root,
        );

        return;
    }

    root.querySelectorAll(
        'select',
    ).forEach(
        (
            element,
        ) => {
            if (
                element
                instanceof
                HTMLSelectElement
            ) {
                enhanceSelect(
                    element,
                );
            }
        },
    );
};

/*
|--------------------------------------------------------------------------
| PROGRAMAR ESCANEO
|--------------------------------------------------------------------------
*/

const scheduleScan =
    (): void => {
        if (
            scanPending
        ) {
            return;
        }

        scanPending =
            true;

        window.requestAnimationFrame(
            () => {
                scanPending =
                    false;

                scan(
                    document,
                );
            },
        );
    };

/*
|--------------------------------------------------------------------------
| INICIALIZAR
|--------------------------------------------------------------------------
*/

export const initializeGlobalSelects =
    (): void => {
        if (
            typeof window ===
                'undefined'
            ||
            typeof document ===
                'undefined'
        ) {
            return;
        }

        if (
            initialized
        ) {
            scheduleScan();

            return;
        }

        initialized =
            true;

        /*
        |--------------------------------------------------------------------------
        | PRIMER ESCANEO
        |--------------------------------------------------------------------------
        */

        if (
            document.readyState ===
            'loading'
        ) {
            document.addEventListener(
                'DOMContentLoaded',
                scheduleScan,
                {
                    once:
                        true,
                },
            );
        } else {
            scheduleScan();
        }

        /*
        |--------------------------------------------------------------------------
        | CAMBIOS DINÁMICOS
        |--------------------------------------------------------------------------
        |
        | Detecta selects agregados mediante:
        |
        | - Vue
        | - v-if
        | - v-for
        | - Inertia
        | - formularios dinámicos
        |
        */

        observer =
            new MutationObserver(
                (
                    mutations,
                ) => {
                    let needsScan =
                        false;

                    for (
                        const mutation
                        of mutations
                    ) {
                        if (
                            mutation.addedNodes
                                .length >
                            0
                            ||
                            mutation.removedNodes
                                .length >
                            0
                        ) {
                            needsScan =
                                true;

                            break;
                        }
                    }

                    if (
                        needsScan
                    ) {
                        scheduleScan();
                    }
                },
            );

        observer.observe(
            document.body,
            {
                childList:
                    true,

                subtree:
                    true,
            },
        );

        /*
        |--------------------------------------------------------------------------
        | NAVEGACIÓN INERTIA
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'inertia:navigate',
            scheduleScan,
        );

        document.addEventListener(
            'inertia:finish',
            scheduleScan,
        );

        /*
        |--------------------------------------------------------------------------
        | BFCACHE
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'pageshow',
            scheduleScan,
        );
    };

/*
|--------------------------------------------------------------------------
| DESTRUIR
|--------------------------------------------------------------------------
*/

export const destroyGlobalSelects =
    (): void => {
        observer
            ?.disconnect();

        observer =
            null;

        document.removeEventListener(
            'inertia:navigate',
            scheduleScan,
        );

        document.removeEventListener(
            'inertia:finish',
            scheduleScan,
        );

        window.removeEventListener(
            'pageshow',
            scheduleScan,
        );

        Array.from(
            mounted.keys(),
        ).forEach(
            (
                select,
            ) => {
                unmountSelect(
                    select,
                );
            },
        );

        initialized =
            false;
    };