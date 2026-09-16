<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <SidebarProvider
        storage-key="adn-publicidad-sidebar"
        class="min-h-svh w-full overflow-hidden bg-[#05090b]"
    >
        <AppSidebar />

        <SidebarInset
            class="min-w-0 flex-1 bg-[#070b0d]"
        >
            <!-- ÚNICO CONTROL DEL SIDEBAR -->

            <header
                class="sticky top-0 z-40 flex h-16 shrink-0 items-center border-b border-[#0fa7b4]/10 bg-[#070b0d]/95 px-4 backdrop-blur-xl sm:px-5"
            >
                <div
                    class="flex min-w-0 items-center gap-3"
                >
                    <SidebarTrigger
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/[0.025] text-white/70 transition hover:border-[#0fa7b4]/30 hover:bg-[#0fa7b4]/10 hover:text-[#22c6d2]"
                    />

                    <div
                        v-if="
                            breadcrumbs &&
                            breadcrumbs.length > 0
                        "
                        class="min-w-0"
                    >
                        <Breadcrumbs
                            :breadcrumbs="
                                breadcrumbs
                            "
                        />
                    </div>
                </div>

                <div
                    class="ml-auto hidden items-center gap-2 sm:flex"
                >
                    <span
                        class="adn-pulse-dot h-2 w-2 rounded-full bg-emerald-500"
                    />

                    <span
                        class="text-[10px] font-black uppercase tracking-[0.16em] text-white/35"
                    >
                        ADN System
                    </span>
                </div>
            </header>

            <!--
            Aquí vive TODO el contenido.
            No existe otro SidebarInset,
            AppContent ni segundo header.
            -->

            <main
                class="adn-page-host min-w-0 flex-1"
            >
                <slot />
            </main>
        </SidebarInset>

        <Toaster />
    </SidebarProvider>
</template>