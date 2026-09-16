<script setup lang="ts">
import {
    Check,
    ChevronDown,
    Copy,
} from '@lucide/vue';
import {
    ref,
} from 'vue';

export type MobileRecordField = {
    label: string;
    value?: string | number | null;
    wide?: boolean;
    copyable?: boolean;
};

type Tone =
    | 'success'
    | 'danger'
    | 'warning'
    | 'info'
    | 'neutral';

const props = withDefaults(
    defineProps<{
        title: string;
        code?: string | null;
        subtitle?: string | null;
        status?: string | null;
        statusTone?: Tone;
        fields?: MobileRecordField[];
        expanded?: boolean;
    }>(),
    {
        code: null,
        subtitle: null,
        status: null,
        statusTone: 'neutral',
        fields: () => [],
        expanded: false,
    },
);

const emit =
    defineEmits<{
        toggle: [];
    }>();

const copiedIndex =
    ref<number | null>(
        null,
    );

const toneClass = (
    tone: Tone,
): string => {
    const classes:
        Record<Tone, string> = {
        success:
            'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',

        danger:
            'border-[#e84657]/25 bg-[#e84657]/10 text-[#f06472]',

        warning:
            'border-amber-500/20 bg-amber-500/10 text-amber-400',

        info:
            'border-[#0fa7b4]/25 bg-[#0fa7b4]/10 text-[#22c6d2]',

        neutral:
            'border-white/10 bg-white/5 text-white/55',
    };

    return classes[tone];
};

const hasValue = (
    value:
        string
        | number
        | null
        | undefined,
): boolean => {
    return (
        value !== null
        &&
        value !== undefined
        &&
        String(
            value,
        ).trim() !== ''
    );
};

const copyField =
    async (
        field:
            MobileRecordField,
        index:
            number,
    ): Promise<void> => {
        if (
            !field.copyable
            ||
            !hasValue(
                field.value,
            )
            ||
            !navigator.clipboard
        ) {
            return;
        }

        await navigator
            .clipboard
            .writeText(
                String(
                    field.value,
                ),
            );

        copiedIndex.value =
            index;

        window.setTimeout(
            () => {
                if (
                    copiedIndex.value ===
                    index
                ) {
                    copiedIndex.value =
                        null;
                }
            },
            1300,
        );
    };

const toggle =
    (): void => {
        emit(
            'toggle',
        );
    };
</script>

<template>
    <article
        class="adn-mobile-record-card relative overflow-hidden rounded-[1.35rem] border border-white/[0.08] bg-[#091317] shadow-[0_12px_38px_rgba(0,0,0,.20)]"
        :class="
            expanded
                ? 'border-[#0fa7b4]/20 shadow-[0_18px_50px_rgba(0,0,0,.28)]'
                : ''
        "
    >
        <!-- LÍNEA SUPERIOR -->

        <div
            class="h-[2px] w-full bg-gradient-to-r from-[#0fa7b4] via-[#22c6d2] to-[#e84657]"
        />

        <!--
        |--------------------------------------------------------------------------
        | CABECERA / ACORDEÓN
        |--------------------------------------------------------------------------
        |
        | Cuando está cerrado únicamente mostramos el nombre.
        |
        -->

        <button
            type="button"
            class="group flex min-h-[62px] w-full items-center justify-between gap-3 px-4 py-3.5 text-left"
            :aria-expanded="
                expanded
            "
            @click="
                toggle
            "
        >
            <h3
                class="min-w-0 flex-1 break-words text-[16px] font-black leading-5 text-white"
            >
                {{ title }}
            </h3>

            <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.025] text-white/35 transition group-active:scale-95"
                :class="
                    expanded
                        ? 'border-[#0fa7b4]/20 bg-[#0fa7b4]/10 text-[#22c6d2]'
                        : ''
                "
            >
                <ChevronDown
                    class="h-4 w-4 transition-transform duration-300"
                    :class="
                        expanded
                            ? 'rotate-180'
                            : ''
                    "
                />
            </div>
        </button>

        <!-- CONTENIDO EXPANDIBLE -->

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="-translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-2 opacity-0"
        >
            <div
                v-if="
                    expanded
                "
                class="border-t border-white/[0.06]"
            >
                <div
                    class="p-4 pt-3"
                >
                    <!-- DATOS PRINCIPALES -->

                    <div
                        class="flex items-start justify-between gap-3"
                    >
                        <div
                            class="min-w-0 flex-1"
                        >
                            <p
                                v-if="
                                    code
                                "
                                class="text-[9px] font-black uppercase tracking-[0.16em] text-[#0fa7b4]"
                            >
                                {{ code }}
                            </p>

                            <p
                                v-if="
                                    subtitle
                                "
                                class="mt-1.5 break-words text-xs leading-5 text-white/40"
                            >
                                {{ subtitle }}
                            </p>
                        </div>

                        <div
                            class="flex shrink-0 flex-col items-end gap-1.5"
                        >
                            <span
                                v-if="
                                    status
                                "
                                class="rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wide"
                                :class="
                                    toneClass(
                                        statusTone,
                                    )
                                "
                            >
                                {{ status }}
                            </span>

                            <slot
                                name="badges"
                            />
                        </div>
                    </div>

                    <!-- CAMPOS -->

                    <div
                        v-if="
                            fields.length >
                            0
                        "
                        class="mt-4 grid grid-cols-2 gap-2"
                    >
                        <div
                            v-for="(
                                field,
                                index
                            ) in fields"
                            :key="
                                `${field.label}-${index}`
                            "
                            class="relative min-w-0 rounded-xl border border-white/[0.055] bg-black/15 px-3 py-2.5"
                            :class="
                                field.wide
                                    ? 'col-span-2'
                                    : ''
                            "
                        >
                            <div
                                class="flex items-start justify-between gap-2"
                            >
                                <div
                                    class="min-w-0 flex-1"
                                >
                                    <p
                                        class="text-[9px] font-black uppercase tracking-[0.12em] text-white/30"
                                    >
                                        {{
                                            field.label
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 break-words text-[12px] font-semibold leading-4 text-white/85"
                                    >
                                        {{
                                            hasValue(
                                                field.value,
                                            )
                                                ? field.value
                                                : '—'
                                        }}
                                    </p>
                                </div>

                                <button
                                    v-if="
                                        field.copyable
                                        &&
                                        hasValue(
                                            field.value,
                                        )
                                    "
                                    type="button"
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-white/30 transition active:scale-95 active:bg-white/5"
                                    @click.stop="
                                        copyField(
                                            field,
                                            index,
                                        )
                                    "
                                >
                                    <Check
                                        v-if="
                                            copiedIndex ===
                                            index
                                        "
                                        class="h-3.5 w-3.5 text-emerald-400"
                                    />

                                    <Copy
                                        v-else
                                        class="h-3.5 w-3.5"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ACCIONES -->

                    <div
                        v-if="
                            $slots.actions
                        "
                        class="mt-4 border-t border-white/[0.07] pt-4"
                    >
                        <slot
                            name="actions"
                        />
                    </div>
                </div>
            </div>
        </Transition>
    </article>
</template>