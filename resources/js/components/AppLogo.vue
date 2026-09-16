<script setup lang="ts">
import {
    useSidebar,
} from '@/components/ui/sidebar';

import '../../css/adn-brand.css';

const {
    state,
} = useSidebar();
</script>

<template>
    <div
        class="flex w-full items-center"
        :class="
            state === 'collapsed'
                ? 'justify-center'
                : 'gap-3'
        "
    >
        <div
            class="adn-shine flex shrink-0 items-center justify-center overflow-hidden rounded-xl border border-[#0fa7b4]/20 bg-white shadow-[0_6px_20px_rgba(15,167,180,0.12)]"
            :class="
                state === 'collapsed'
                    ? 'h-9 w-9 p-1.5'
                    : 'h-10 w-10 p-1.5'
            "
        >
            <img
                src="/icons/logo_adn.svg"
                alt="ADN Publicidad"
                class="h-full w-full object-contain"
            />
        </div>

        <div
            v-if="
                state !==
                'collapsed'
            "
            class="min-w-0 flex-1"
        >
            <div
                class="truncate text-sm font-black tracking-tight text-white"
            >
                ADN
                <span
                    class="adn-gradient-text"
                >
                    Publicidad
                </span>
            </div>

            <div
                class="mt-0.5 flex items-center gap-1.5"
            >
                <span
                    class="adn-pulse-dot h-1.5 w-1.5 rounded-full bg-emerald-500"
                />

                <span
                    class="truncate text-[9px] font-bold uppercase tracking-[0.14em] text-white/40"
                >
                    Sistema de gestión
                </span>
            </div>
        </div>
    </div>
</template>