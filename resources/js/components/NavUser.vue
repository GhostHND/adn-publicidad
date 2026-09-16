<script setup lang="ts">
import UserMenuContent from '@/components/UserMenuContent.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import {
    usePage,
} from '@inertiajs/vue3';
import {
    ChevronsUpDown,
} from '@lucide/vue';
import {
    computed,
} from 'vue';

const page =
    usePage();

const {
    isMobile,
    state,
} = useSidebar();

const user =
    computed(
        () =>
            (page.props as any)
                .auth
                ?.user,
    );

const initials =
    computed(() => {
        const name =
            String(
                user.value
                    ?.name
                ?? 'Usuario',
            ).trim();

        return name
            .split(/\s+/)
            .slice(0, 2)
            .map(
                (word) =>
                    word
                        .charAt(0)
                        .toUpperCase(),
            )
            .join('');
    });
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger
                    as-child
                >
                    <SidebarMenuButton
                        size="lg"
                        class="h-12 rounded-xl data-[state=open]:bg-[#0fa7b4]/10"
                        :class="
                            state ===
                            'collapsed'
                                ? '!w-10 !justify-center !px-0'
                                : ''
                        "
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#0fa7b4]/20 to-[#e84657]/15 text-xs font-black text-white ring-1 ring-white/10"
                        >
                            {{
                                initials
                            }}
                        </div>

                        <div
                            v-if="
                                state !==
                                'collapsed'
                            "
                            class="min-w-0 flex-1 text-left"
                        >
                            <p
                                class="truncate text-sm font-bold"
                            >
                                {{
                                    user?.name
                                }}
                            </p>

                            <p
                                class="truncate text-[10px] text-muted-foreground"
                            >
                                {{
                                    user?.email
                                }}
                            </p>
                        </div>

                        <ChevronsUpDown
                            v-if="
                                state !==
                                'collapsed'
                            "
                            class="ml-auto h-4 w-4 text-muted-foreground"
                        />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    class="min-w-60 rounded-2xl border border-white/10 bg-[#0a1216] p-2 shadow-2xl"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state ===
                                'collapsed'
                                ? 'right'
                                : 'top'
                    "
                    align="end"
                    :side-offset="8"
                >
                    <UserMenuContent
                        :user="
                            user
                        "
                    />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>