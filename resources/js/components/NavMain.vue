<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import {
    Link,
    usePage,
} from '@inertiajs/vue3';
import type { Component } from 'vue';

type NavigationItem = {
    title: string;
    href: string;
    icon?: Component;
    badge?: number;
};

defineProps<{
    items: NavigationItem[];
}>();

const page = usePage();

const { state } =
    useSidebar();

const isActive = (
    href: string,
): boolean => {
    if (
        href ===
        '/dashboard'
    ) {
        return page.url.startsWith(
            '/dashboard',
        );
    }

    return page.url.startsWith(
        href,
    );
};
</script>

<template>
    <SidebarGroup
        class="px-2"
        :class="
            state === 'collapsed'
                ? '!px-1'
                : ''
        "
    >
        <SidebarGroupContent>
            <SidebarMenu
                class="gap-1"
            >
                <SidebarMenuItem
                    v-for="item in items"
                    :key="item.href"
                    class="flex justify-center"
                >
                    <SidebarMenuButton
                        as-child
                        :is-active="
                            isActive(
                                item.href,
                            )
                        "
                        :tooltip="
                            item.title
                        "
                        class="relative h-10 rounded-xl"
                        :class="
                            state === 'collapsed'
                                ? '!h-10 !w-10 !justify-center !p-0'
                                : 'px-3'
                        "
                    >
                        <Link
                            :href="
                                item.href
                            "
                            class="relative flex h-full w-full items-center"
                            :class="
                                state === 'collapsed'
                                    ? 'justify-center'
                                    : 'gap-3'
                            "
                        >
                            <component
                                v-if="
                                    item.icon
                                "
                                :is="
                                    item.icon
                                "
                                class="h-[18px] w-[18px] shrink-0"
                            />

                            <span
                                v-if="
                                    state !==
                                    'collapsed'
                                "
                                class="min-w-0 flex-1 truncate font-semibold"
                            >
                                {{
                                    item.title
                                }}
                            </span>

                            <span
                                v-if="
                                    item.badge &&
                                    item.badge > 0
                                "
                                class="inline-flex items-center justify-center rounded-full bg-[#e84657] font-black leading-none text-white shadow-[0_4px_14px_rgba(232,70,87,0.45)]"
                                :class="
                                    state ===
                                    'collapsed'
                                        ? 'absolute -right-1 -top-1 h-4 min-w-4 px-1 text-[8px]'
                                        : 'ml-auto min-w-5 px-1.5 py-1 text-[10px]'
                                "
                            >
                                {{
                                    item.badge > 99
                                        ? '99+'
                                        : item.badge
                                }}
                            </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>