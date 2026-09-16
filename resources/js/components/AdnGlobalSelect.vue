<script setup lang="ts">
import {
    Check,
    ChevronDown,
    Search,
} from '@lucide/vue';

import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';

import type {
    CSSProperties,
} from 'vue';

type SelectOption = {
    value: string;
    label: string;
    disabled: boolean;
    group: string | null;
};

const props = defineProps<{
    nativeSelect: HTMLSelectElement;
}>();

const triggerRef =
    ref<HTMLButtonElement | null>(
        null,
    );

const menuRef =
    ref<HTMLDivElement | null>(
        null,
    );

const searchRef =
    ref<HTMLInputElement | null>(
        null,
    );

const isOpen =
    ref(false);

const query =
    ref('');

const options =
    ref<SelectOption[]>([]);

const currentValue =
    ref('');

const isDisabled =
    ref(false);

const highlightedIndex =
    ref(-1);

const menuStyle =
    ref<CSSProperties>({
        position: 'fixed',
        zIndex: 2147483000,
    });

const menuId =
    `adn-select-${Math.random()
        .toString(36)
        .slice(2)}`;

/*
|--------------------------------------------------------------------------
| NORMALIZAR TEXTO
|--------------------------------------------------------------------------
*/

const normalizeText = (
    value: string,
): string => {
    return value
        .normalize('NFD')
        .replace(
            /[\u0300-\u036f]/g,
            '',
        )
        .toLowerCase()
        .trim();
};

/*
|--------------------------------------------------------------------------
| LEER SELECT NATIVO
|--------------------------------------------------------------------------
*/

const refreshNativeState =
    (): void => {
        options.value =
            Array.from(
                props.nativeSelect.options,
            ).map(
                (
                    option,
                ): SelectOption => {
                    const parent =
                        option.parentElement;

                    return {
                        value:
                            option.value,

                        label:
                            option.textContent
                                ?.trim()
                            || option.label
                            || option.value,

                        disabled:
                            option.disabled
                            ||
                            (
                                parent instanceof
                                HTMLOptGroupElement
                                &&
                                parent.disabled
                            ),

                        group:
                            parent instanceof
                            HTMLOptGroupElement
                                ? parent.label
                                : null,
                    };
                },
            );

        currentValue.value =
            props.nativeSelect.value;

        isDisabled.value =
            props.nativeSelect.disabled;
    };

/*
|--------------------------------------------------------------------------
| OPCIÓN ACTUAL
|--------------------------------------------------------------------------
*/

const selectedOption =
    computed<SelectOption | null>(
        () => {
            return options.value.find(
                (
                    option,
                ) =>
                    option.value ===
                    currentValue.value,
            )
            ?? null;
        },
    );

const selectedLabel =
    computed<string>(
        () => {
            return selectedOption.value
                ?.label
                ?? 'Seleccionar';
        },
    );

/*
|--------------------------------------------------------------------------
| BUSCADOR
|--------------------------------------------------------------------------
*/

const searchable =
    computed<boolean>(
        () =>
            options.value.length >= 8,
    );

const filteredOptions =
    computed<SelectOption[]>(
        () => {
            const search =
                normalizeText(
                    query.value,
                );

            if (
                search === ''
            ) {
                return options.value;
            }

            return options.value.filter(
                (
                    option,
                ) =>
                    normalizeText(
                        option.label,
                    ).includes(
                        search,
                    ),
            );
        },
    );

/*
|--------------------------------------------------------------------------
| ETIQUETA ACCESIBLE
|--------------------------------------------------------------------------
*/

const ariaLabel =
    computed<string>(
        () => {
            const explicit =
                props.nativeSelect
                    .getAttribute(
                        'aria-label',
                    );

            if (
                explicit
            ) {
                return explicit;
            }

            const label =
                props.nativeSelect
                    .labels
                    ?.item(0)
                    ?.textContent
                    ?.trim();

            return label
                || 'Seleccionar opción';
        },
    );

/*
|--------------------------------------------------------------------------
| POSICIONAMIENTO
|--------------------------------------------------------------------------
*/

const updateMenuPosition =
    (): void => {
        if (
            !isOpen.value
            ||
            !triggerRef.value
        ) {
            return;
        }

        const rect =
            triggerRef.value
                .getBoundingClientRect();

        const viewportWidth =
            window.innerWidth;

        const viewportHeight =
            window.innerHeight;

        const padding =
            8;

        const gap =
            7;

        const width =
            Math.max(
                rect.width,
                220,
            );

        let left =
            rect.left;

        if (
            left + width >
            viewportWidth - padding
        ) {
            left =
                viewportWidth
                -
                width
                -
                padding;
        }

        left =
            Math.max(
                padding,
                left,
            );

        const estimatedHeight =
            menuRef.value
                ?.getBoundingClientRect()
                .height
            ?? 320;

        const spaceBelow =
            viewportHeight
            -
            rect.bottom;

        const spaceAbove =
            rect.top;

        const openAbove =
            spaceBelow < 300
            &&
            spaceAbove > spaceBelow;

        let top =
            openAbove
                ? rect.top
                    -
                    estimatedHeight
                    -
                    gap
                : rect.bottom
                    +
                    gap;

        top =
            Math.max(
                padding,
                Math.min(
                    top,
                    viewportHeight
                    -
                    estimatedHeight
                    -
                    padding,
                ),
            );

        menuStyle.value = {
            position:
                'fixed',

            zIndex:
                2147483000,

            width:
                `${width}px`,

            maxWidth:
                'calc(100vw - 16px)',

            left:
                `${left}px`,

            top:
                `${top}px`,
        };
    };

/*
|--------------------------------------------------------------------------
| OPCIÓN RESALTADA
|--------------------------------------------------------------------------
*/

const syncHighlight =
    (): void => {
        const selectedIndex =
            filteredOptions.value
                .findIndex(
                    (
                        option,
                    ) =>
                        option.value ===
                        currentValue.value,
                );

        if (
            selectedIndex >= 0
        ) {
            highlightedIndex.value =
                selectedIndex;

            return;
        }

        highlightedIndex.value =
            filteredOptions.value
                .findIndex(
                    (
                        option,
                    ) =>
                        !option.disabled,
                );
    };

/*
|--------------------------------------------------------------------------
| ABRIR / CERRAR
|--------------------------------------------------------------------------
*/

const openMenu =
    async (): Promise<void> => {
        if (
            isDisabled.value
        ) {
            return;
        }

        refreshNativeState();

        query.value =
            '';

        isOpen.value =
            true;

        syncHighlight();

        await nextTick();

        updateMenuPosition();

        await nextTick();

        updateMenuPosition();

        if (
            searchable.value
        ) {
            searchRef.value
                ?.focus();
        }
    };

const closeMenu =
    (): void => {
        isOpen.value =
            false;

        query.value =
            '';

        highlightedIndex.value =
            -1;
    };

const toggleMenu =
    (): void => {
        if (
            isOpen.value
        ) {
            closeMenu();

            return;
        }

        void openMenu();
    };

/*
|--------------------------------------------------------------------------
| SELECCIONAR
|--------------------------------------------------------------------------
*/

const chooseOption = (
    option: SelectOption,
): void => {
    if (
        option.disabled
        ||
        isDisabled.value
    ) {
        return;
    }

    props.nativeSelect.value =
        option.value;

    currentValue.value =
        option.value;

    /*
    |--------------------------------------------------------------------------
    | SINCRONIZAR V-MODEL
    |--------------------------------------------------------------------------
    */

    props.nativeSelect
        .dispatchEvent(
            new Event(
                'input',
                {
                    bubbles: true,
                },
            ),
        );

    props.nativeSelect
        .dispatchEvent(
            new Event(
                'change',
                {
                    bubbles: true,
                },
            ),
        );

    refreshNativeState();

    closeMenu();

    void nextTick(
        () => {
            triggerRef.value
                ?.focus();
        },
    );
};

/*
|--------------------------------------------------------------------------
| TECLADO
|--------------------------------------------------------------------------
*/

const moveHighlight = (
    direction: number,
): void => {
    const list =
        filteredOptions.value;

    if (
        list.length === 0
    ) {
        return;
    }

    let index =
        highlightedIndex.value;

    for (
        let attempt = 0;
        attempt < list.length;
        attempt++
    ) {
        index =
            (
                index
                +
                direction
                +
                list.length
            )
            %
            list.length;

        if (
            !list[index].disabled
        ) {
            highlightedIndex.value =
                index;

            void nextTick(
                () => {
                    document
                        .querySelector(
                            `[data-adn-option-index="${index}"]`,
                        )
                        ?.scrollIntoView({
                            block:
                                'nearest',
                        });
                },
            );

            return;
        }
    }
};

const chooseHighlighted =
    (): void => {
        const option =
            filteredOptions.value[
                highlightedIndex.value
            ];

        if (
            option
            &&
            !option.disabled
        ) {
            chooseOption(
                option,
            );
        }
    };

const handleKeydown = (
    event: KeyboardEvent,
): void => {
    switch (
        event.key
    ) {
        case 'ArrowDown':
            event.preventDefault();

            if (
                !isOpen.value
            ) {
                void openMenu();
            } else {
                moveHighlight(1);
            }

            break;

        case 'ArrowUp':
            event.preventDefault();

            if (
                !isOpen.value
            ) {
                void openMenu();
            } else {
                moveHighlight(-1);
            }

            break;

        case 'Enter':
            event.preventDefault();

            if (
                !isOpen.value
            ) {
                void openMenu();
            } else {
                chooseHighlighted();
            }

            break;

        case ' ':
            if (
                !isOpen.value
            ) {
                event.preventDefault();

                void openMenu();
            }

            break;

        case 'Escape':
            event.preventDefault();

            closeMenu();

            void nextTick(
                () => {
                    triggerRef.value
                        ?.focus();
                },
            );

            break;
    }
};

/*
|--------------------------------------------------------------------------
| CLICK FUERA
|--------------------------------------------------------------------------
*/

const handleOutsidePointer = (
    event: PointerEvent,
): void => {
    if (
        !isOpen.value
    ) {
        return;
    }

    const target =
        event.target as Node;

    if (
        triggerRef.value
            ?.contains(target)
    ) {
        return;
    }

    if (
        menuRef.value
            ?.contains(target)
    ) {
        return;
    }

    closeMenu();
};

/*
|--------------------------------------------------------------------------
| CAMBIOS DEL SELECT NATIVO
|--------------------------------------------------------------------------
*/

const handleNativeChange =
    (): void => {
        refreshNativeState();
    };

/*
|--------------------------------------------------------------------------
| VALIDACIÓN REQUIRED
|--------------------------------------------------------------------------
*/

const handleInvalid = (
    event: Event,
): void => {
    event.preventDefault();

    void openMenu();
};

/*
|--------------------------------------------------------------------------
| OBSERVADORES
|--------------------------------------------------------------------------
*/

let mutationObserver:
    MutationObserver
    | null =
    null;

let synchronizationTimer:
    ReturnType<typeof setInterval>
    | null =
    null;

const synchronizeNative =
    (): void => {
        if (
            props.nativeSelect.value !==
                currentValue.value
            ||
            props.nativeSelect.disabled !==
                isDisabled.value
        ) {
            refreshNativeState();
        }
    };

const handleViewportChange =
    (): void => {
        if (
            isOpen.value
        ) {
            updateMenuPosition();
        }
    };

/*
|--------------------------------------------------------------------------
| MONTAJE
|--------------------------------------------------------------------------
*/

onMounted(
    () => {
        refreshNativeState();

        props.nativeSelect
            .addEventListener(
                'change',
                handleNativeChange,
            );

        props.nativeSelect
            .addEventListener(
                'input',
                handleNativeChange,
            );

        props.nativeSelect
            .addEventListener(
                'invalid',
                handleInvalid,
            );

        document.addEventListener(
            'pointerdown',
            handleOutsidePointer,
        );

        window.addEventListener(
            'resize',
            handleViewportChange,
        );

        window.addEventListener(
            'scroll',
            handleViewportChange,
            true,
        );

        mutationObserver =
            new MutationObserver(
                () => {
                    refreshNativeState();
                },
            );

        mutationObserver.observe(
            props.nativeSelect,
            {
                attributes:
                    true,

                childList:
                    true,

                subtree:
                    true,
            },
        );

        synchronizationTimer =
            setInterval(
                synchronizeNative,
                200,
            );
    },
);

/*
|--------------------------------------------------------------------------
| DESMONTAJE
|--------------------------------------------------------------------------
*/

onBeforeUnmount(
    () => {
        props.nativeSelect
            .removeEventListener(
                'change',
                handleNativeChange,
            );

        props.nativeSelect
            .removeEventListener(
                'input',
                handleNativeChange,
            );

        props.nativeSelect
            .removeEventListener(
                'invalid',
                handleInvalid,
            );

        document.removeEventListener(
            'pointerdown',
            handleOutsidePointer,
        );

        window.removeEventListener(
            'resize',
            handleViewportChange,
        );

        window.removeEventListener(
            'scroll',
            handleViewportChange,
            true,
        );

        mutationObserver
            ?.disconnect();

        mutationObserver =
            null;

        if (
            synchronizationTimer
        ) {
            clearInterval(
                synchronizationTimer,
            );

            synchronizationTimer =
                null;
        }
    },
);
</script>

<template>
    <div class="w-full">
        <!-- ========================================================= -->
        <!-- BOTÓN PRINCIPAL -->
        <!-- ========================================================= -->

        <button
            ref="triggerRef"
            type="button"
            data-adn-select-trigger
            class="group flex min-h-11 w-full items-center justify-between gap-3 rounded-xl border border-white/10 bg-[#071014] px-4 py-2.5 text-left text-sm font-medium text-slate-100 shadow-sm outline-none transition-all duration-200 hover:border-[#0fa7b4]/45 hover:bg-[#09171b] focus:border-[#0fa7b4]/70 focus:ring-2 focus:ring-[#0fa7b4]/15 disabled:cursor-not-allowed disabled:opacity-50"
            :class="{
                'border-[#0fa7b4]/60 bg-[#09171b] ring-2 ring-[#0fa7b4]/10':
                    isOpen,
            }"
            :disabled="isDisabled"
            :aria-label="ariaLabel"
            aria-haspopup="listbox"
            :aria-expanded="isOpen"
            :aria-controls="menuId"
            @click="toggleMenu"
            @keydown="handleKeydown"
        >
            <span class="min-w-0 flex-1 truncate">
                {{ selectedLabel }}
            </span>

            <ChevronDown
                class="h-4 w-4 shrink-0 text-white/45 transition-transform duration-200 group-hover:text-[#22c6d2]"
                :class="{
                    'rotate-180 text-[#22c6d2]':
                        isOpen,
                }"
            />
        </button>

        <!-- ========================================================= -->
        <!-- MENÚ -->
        <!-- ========================================================= -->

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="-translate-y-1 scale-[0.985] opacity-0"
                enter-to-class="translate-y-0 scale-100 opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="translate-y-0 scale-100 opacity-100"
                leave-to-class="-translate-y-1 scale-[0.985] opacity-0"
            >
                <div
                    v-if="isOpen"
                    :id="menuId"
                    ref="menuRef"
                    :style="menuStyle"
                    class="overflow-hidden rounded-2xl border border-[#0fa7b4]/25 bg-[#071014] shadow-2xl shadow-black/70"
                    role="listbox"
                    :aria-label="ariaLabel"
                    @keydown="handleKeydown"
                >
                    <!-- ================================================= -->
                    <!-- BUSCADOR -->
                    <!-- ================================================= -->

                    <div
                        v-if="searchable"
                        class="border-b border-white/[0.07] bg-[#081217] p-2.5"
                    >
                        <div
                            class="flex items-center gap-2 rounded-xl border border-white/[0.08] bg-black/20 px-3"
                        >
                            <Search
                                class="h-4 w-4 shrink-0 text-[#22c6d2]"
                            />

                            <input
                                ref="searchRef"
                                v-model="query"
                                type="text"
                                autocomplete="off"
                                placeholder="Buscar..."
                                class="h-10 min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-white outline-none placeholder:text-white/25 focus:ring-0"
                                @keydown="handleKeydown"
                            />
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- OPCIONES -->
                    <!-- ================================================= -->

                    <div
                        class="adn-select-options max-h-[min(22rem,60vh)] overflow-y-auto overscroll-contain p-1.5"
                    >
                        <div
                            v-if="filteredOptions.length === 0"
                            class="px-4 py-8 text-center text-sm text-white/35"
                        >
                            No se encontraron opciones.
                        </div>

                        <template
                            v-for="(option, index) in filteredOptions"
                            :key="`${option.value}-${index}`"
                        >
                            <div
                                v-if="
                                    option.group
                                    &&
                                    (
                                        index === 0
                                        ||
                                        filteredOptions[index - 1]?.group !==
                                            option.group
                                    )
                                "
                                class="px-3 pb-1 pt-3 text-[9px] font-black uppercase tracking-[0.14em] text-[#0fa7b4]"
                            >
                                {{ option.group }}
                            </div>

                            <button
                                type="button"
                                :data-adn-option-index="index"
                                role="option"
                                :aria-selected="
                                    option.value ===
                                    currentValue
                                "
                                :disabled="option.disabled"
                                class="flex min-h-10 w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm outline-none transition duration-100 disabled:cursor-not-allowed disabled:opacity-30"
                                :class="[
                                    option.value ===
                                    currentValue
                                        ? 'bg-[#0fa7b4]/15 text-white'
                                        : 'text-slate-200 hover:bg-white/[0.055]',

                                    highlightedIndex ===
                                    index
                                        ? 'bg-white/[0.055] ring-1 ring-inset ring-[#0fa7b4]/30'
                                        : '',
                                ]"
                                @mouseenter="
                                    highlightedIndex =
                                        index
                                "
                                @click="
                                    chooseOption(option)
                                "
                            >
                                <span
                                    class="min-w-0 flex-1 truncate"
                                >
                                    {{ option.label }}
                                </span>

                                <Check
                                    v-if="
                                        option.value ===
                                        currentValue
                                    "
                                    class="h-4 w-4 shrink-0 text-[#22c6d2]"
                                />
                            </button>
                        </template>
                    </div>

                    <!-- ================================================= -->
                    <!-- PIE -->
                    <!-- ================================================= -->

                    <div
                        v-if="searchable"
                        class="border-t border-white/[0.06] bg-[#081217] px-3 py-2 text-[9px] text-white/25"
                    >
                        {{ filteredOptions.length }}
                        de
                        {{ options.length }}
                        opciones
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.adn-select-options {
    scrollbar-width: thin;
    scrollbar-color:
        rgba(15, 167, 180, 0.5)
        rgba(255, 255, 255, 0.04);
}

.adn-select-options::-webkit-scrollbar {
    width: 7px;
}

.adn-select-options::-webkit-scrollbar-track {
    background: rgba(
        255,
        255,
        255,
        0.025
    );
}

.adn-select-options::-webkit-scrollbar-thumb {
    border-radius: 9999px;
    background: rgba(
        15,
        167,
        180,
        0.45
    );
}

.adn-select-options::-webkit-scrollbar-thumb:hover {
    background: rgba(
        15,
        167,
        180,
        0.7
    );
}
</style>