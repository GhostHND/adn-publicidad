<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import LeadNotificationCenter from '@/components/LeadNotificationCenter.vue';
import LeadRequestPanel from '@/components/LeadRequestPanel.vue';
import NavUser from '@/components/NavUser.vue';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import {
    Link,
    usePage,
} from '@inertiajs/vue3';

import {
    Activity,
    BellRing,
    Boxes,
    BriefcaseBusiness,
    Camera,
    ChartNoAxesCombined,
    CircleDollarSign,
    ClipboardList,
    FileText,
    Globe2,
    HandCoins,
    LayoutDashboard,
    ListTodo,
    PackageOpen,
    ReceiptText,
    Settings2,
    ShoppingCart,
    UserRoundCog,
    UsersRound,
    WalletCards,
    Wrench,
} from '@lucide/vue';

import {
    computed,
} from 'vue';

/*
|--------------------------------------------------------------------------
| TIPOS
|--------------------------------------------------------------------------
*/

interface NavigationItem {
    title: string;

    href: string;

    icon: any;

    roles?: string[];

    badge?: boolean;

    exact?: boolean;

    external?: boolean;
}

/*
|--------------------------------------------------------------------------
| INERTIA
|--------------------------------------------------------------------------
*/

const page =
    usePage<any>();

/*
|--------------------------------------------------------------------------
| EDITOR ADN WEB
|--------------------------------------------------------------------------
*/

const adnWebEditorUrl =
    computed<string>(
        () => {
            return String(
                page.props
                    ?.adnWebEditorUrl
                ?? ''
            )
                .trim();
        },
    );

/*
|--------------------------------------------------------------------------
| SOLICITUDES NUEVAS
|--------------------------------------------------------------------------
*/

const newLeadCount =
    computed<number>(
        () => {
            return Number(
                page.props
                    ?.leadState
                    ?.new_count
                ?? 0,
            );
        },
    );

/*
|--------------------------------------------------------------------------
| DATOS DE AUTORIZACIÓN
|--------------------------------------------------------------------------
*/

const userRoles =
    computed<string[]>(
        () => {
            const roles =
                page.props
                    ?.authz
                    ?.roles;

            if (
                !Array.isArray(
                    roles,
                )
            ) {
                return [];
            }

            return roles
                .map(
                    (
                        role: unknown,
                    ) =>
                        String(
                            role,
                        )
                            .trim()
                            .toLowerCase(),
                )
                .filter(
                    Boolean,
                );
        },
    );

const isAdmin =
    computed<boolean>(
        () => {
            return (
                Boolean(
                    page.props
                        ?.authz
                        ?.is_admin,
                )
                ||
                userRoles.value
                    .includes(
                        'admin',
                    )
            );
        },
    );

/*
|--------------------------------------------------------------------------
| NAVEGACIÓN
|--------------------------------------------------------------------------
|
| Los roles aquí controlan únicamente VISIBILIDAD.
|
| La autorización real continúa en Laravel.
|
*/

const navigation =
    computed<
        NavigationItem[]
    >(
        () => [
            /*
            |--------------------------------------------------------------------------
            | GENERAL
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Dashboard',

                href:
                    '/dashboard',

                icon:
                    LayoutDashboard,

                roles: [
                    'admin',
                    'ventas',
                    'diseno',
                    'produccion',
                    'instalador',
                ],
            },

            /*
            |--------------------------------------------------------------------------
            | ADMINISTRACIÓN
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Empleados',

                href:
                    '/employees',

                icon:
                    BriefcaseBusiness,

                roles: [
                    'admin',
                ],
            },

            {
                title:
                    'Clientes',

                href:
                    '/clients',

                icon:
                    UsersRound,

                roles: [
                    'admin',
                    'ventas',
                ],
            },

            /*
            |--------------------------------------------------------------------------
            | PRODUCTOS / PRODUCCIÓN
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Catálogo',

                href:
                    '/catalog',

                icon:
                    Boxes,

                roles: [
                    'admin',
                    'ventas',
                    'diseno',
                    'produccion',
                ],
            },

            {
                title:
                    'Inventario',

                href:
                    '/inventory',

                icon:
                    PackageOpen,

                roles: [
                    'admin',
                    'ventas',
                    'produccion',
                ],
            },

            /*
            |--------------------------------------------------------------------------
            | COMERCIAL
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Cotizaciones',

                href:
                    '/quotations',

                icon:
                    FileText,

                roles: [
                    'admin',
                    'ventas',
                ],
            },

            {
                title:
                    'Ventas',

                href:
                    '/sales',

                icon:
                    ShoppingCart,

                roles: [
                    'admin',
                    'ventas',
                ],
            },

            {
                title:
                    'Recibos',

                href:
                    '/receipts',

                icon:
                    ReceiptText,

                roles: [
                    'admin',
                    'ventas',
                ],
            },

            {
                title:
                    'Cuentas por cobrar',

                href:
                    '/accounts-receivable',

                icon:
                    HandCoins,

                roles: [
                    'admin',
                    'ventas',
                ],
            },

            /*
            |--------------------------------------------------------------------------
            | OPERACIÓN
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Órdenes de trabajo',

                href:
                    '/work-orders',

                icon:
                    ClipboardList,

                roles: [
                    'admin',
                    'ventas',
                    'diseno',
                    'produccion',
                    'instalador',
                ],
            },

            {
                title:
                    'Tareas',

                href:
                    '/tasks',

                icon:
                    ListTodo,

                roles: [
                    'admin',
                    'diseno',
                    'produccion',
                    'instalador',
                ],
            },

            {
                title:
                    'Instalaciones',

                href:
                    '/installations',

                icon:
                    Wrench,

                roles: [
                    'admin',
                    'produccion',
                    'instalador',
                ],
            },

            {
                title:
                    'CCTV',

                href:
                    '/cctv',

                icon:
                    Camera,

                roles: [
                    'admin',
                    'instalador',
                ],
            },

            /*
            |--------------------------------------------------------------------------
            | SOLICITUDES
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Solicitudes',

                href:
                    '/website/leads',

                icon:
                    BellRing,

                badge:
                    true,

                roles: [
                    'admin',
                    'ventas',
                ],
            },

            /*
            |--------------------------------------------------------------------------
            | FINANZAS
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Caja',

                href:
                    '/cash',

                icon:
                    WalletCards,

                roles: [
                    'admin',
                ],
            },

            {
                title:
                    'Ingresos',

                href:
                    '/income',

                icon:
                    CircleDollarSign,

                roles: [
                    'admin',
                ],
            },

            {
                title:
                    'Gastos',

                href:
                    '/expenses',

                icon:
                    CircleDollarSign,

                roles: [
                    'admin',
                ],
            },

            {
                title:
                    'Reportes',

                href:
                    '/reports',

                icon:
                    ChartNoAxesCombined,

                roles: [
                    'admin',
                ],
            },

            /*
            |--------------------------------------------------------------------------
            | SISTEMA
            |--------------------------------------------------------------------------
            */

            {
                title:
                    'Sitio web',

                href:
                    adnWebEditorUrl.value
                    ||
                    '/website',

                icon:
                    Globe2,

                exact:
                    true,

                external:
                    adnWebEditorUrl.value
                    !==
                    '',

                roles: [
                    'admin',
                ],
            },

            {
                title:
                    'Usuarios y permisos',

                href:
                    '/users',

                icon:
                    UserRoundCog,

                roles: [
                    'admin',
                ],
            },

            {
                title:
                    'Auditoría',

                href:
                    '/audit',

                icon:
                    Activity,

                roles: [
                    'admin',
                ],
            },

            {
                title:
                    'Configuración',

                href:
                    '/settings/system',

                icon:
                    Settings2,

                roles: [
                    'admin',
                ],
            },
        ],
    );

/*
|--------------------------------------------------------------------------
| VISIBILIDAD
|--------------------------------------------------------------------------
*/

const canSeeItem = (
    item: NavigationItem,
): boolean => {
    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    if (
        isAdmin.value
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD
    |--------------------------------------------------------------------------
    |
    | Si todavía no recibimos metadata de roles, mantenemos el sidebar
    | visible en vez de dejarlo completamente vacío.
    |
    | El middleware del servidor sigue protegiendo las rutas.
    |
    */

    if (
        userRoles.value.length ===
        0
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | SIN RESTRICCIÓN
    |--------------------------------------------------------------------------
    */

    if (
        !item.roles
        ||
        item.roles.length ===
            0
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | COINCIDENCIA DE ROL
    |--------------------------------------------------------------------------
    */

    return item.roles
        .some(
            (
                role,
            ) =>
                userRoles.value
                    .includes(
                        role,
                    ),
        );
};

const visibleNavigation =
    computed<
        NavigationItem[]
    >(
        () => {
            return navigation
                .value
                .filter(
                    canSeeItem,
                );
        },
    );

/*
|--------------------------------------------------------------------------
| RUTA ACTUAL
|--------------------------------------------------------------------------
*/

const currentPath =
    computed<string>(
        () => {
            const url =
                String(
                    page.url
                    ?? '/',
                );

            return url
                .split('?')[0]
                .split('#')[0];
        },
    );

/*
|--------------------------------------------------------------------------
| ELEMENTO ACTIVO
|--------------------------------------------------------------------------
*/

const isActive = (
    item:
        NavigationItem,
): boolean => {
    if (
        item.external
    ) {
        return false;
    }

    const path =
        currentPath.value;

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    if (
        item.href ===
        '/dashboard'
    ) {
        return path ===
            '/dashboard';
    }

    /*
    |--------------------------------------------------------------------------
    | RUTAS EXACTAS
    |--------------------------------------------------------------------------
    */

    if (
        item.exact
    ) {
        return path ===
            item.href;
    }

    return (
        path ===
        item.href
        ||
        path.startsWith(
            `${item.href}/`,
        )
    );
};
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="sidebar"
        class="border-r border-[#0fa7b4]/10 bg-[#061115]"
    >
        <!-- ============================================================= -->
        <!-- LOGO -->
        <!-- ============================================================= -->

        <SidebarHeader
            class="border-b border-[#0fa7b4]/10 p-2"
        >
            <AppLogo />
        </SidebarHeader>

        <!-- ============================================================= -->
        <!-- NAVEGACIÓN -->
        <!-- ============================================================= -->

        <SidebarContent
            class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden px-2 py-3"
        >
            <SidebarMenu
                class="gap-1"
            >
                <SidebarMenuItem
                    v-for="
                        item
                        in visibleNavigation
                    "
                    :key="
                        item.href
                    "
                    class="relative"
                >
                    <SidebarMenuButton
                        as-child
                        :is-active="
                            isActive(
                                item,
                            )
                        "
                        :tooltip="
                            item.title
                        "
                        class="
                            relative
                            min-h-10
                            rounded-xl
                            px-3
                            font-bold
                            text-white/72
                            transition-all
                            duration-200

                            hover:bg-white/[0.045]
                            hover:text-white

                            data-[active=true]:bg-gradient-to-r
                            data-[active=true]:from-[#0fa7b4]
                            data-[active=true]:to-[#18bdca]
                            data-[active=true]:text-white
                            data-[active=true]:shadow-[0_8px_24px_rgba(15,167,180,.18)]

                            group-data-[collapsible=icon]:mx-auto
                            group-data-[collapsible=icon]:h-10
                            group-data-[collapsible=icon]:w-10
                            group-data-[collapsible=icon]:justify-center
                            group-data-[collapsible=icon]:p-0
                        "
                    >
                        <component
                            :is="
                                item.external
                                    ? 'a'
                                    : Link
                            "
                            :href="
                                item.href
                            "
                            class="relative flex w-full items-center gap-3"
                        >
                            <component
                                :is="
                                    item.icon
                                "
                                class="h-[17px] w-[17px] shrink-0"
                            />

                            <span
                                class="min-w-0 flex-1 truncate text-[13px] group-data-[collapsible=icon]:hidden"
                            >
                                {{
                                    item.title
                                }}
                            </span>

                            <!-- BADGE SOLICITUDES -->

                            <span
                                v-if="
                                    item.badge
                                    &&
                                    newLeadCount >
                                        0
                                "
                                class="
                                    ml-auto
                                    flex
                                    min-w-5
                                    shrink-0
                                    items-center
                                    justify-center
                                    rounded-full
                                    bg-[#e84657]
                                    px-1.5
                                    py-0.5
                                    text-[9px]
                                    font-black
                                    leading-none
                                    text-white

                                    group-data-[collapsible=icon]:hidden
                                "
                            >
                                {{
                                    newLeadCount >
                                    99
                                        ? '99+'
                                        : newLeadCount
                                }}
                            </span>

                            <!-- BADGE SIDEBAR COLAPSADO -->

                            <span
                                v-if="
                                    item.badge
                                    &&
                                    newLeadCount >
                                        0
                                "
                                class="
                                    absolute
                                    -right-1
                                    -top-1
                                    hidden
                                    h-2.5
                                    w-2.5
                                    rounded-full
                                    bg-[#e84657]
                                    ring-2
                                    ring-[#061115]

                                    group-data-[collapsible=icon]:block
                                "
                            />
                        </component>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarContent>

        <!-- ============================================================= -->
        <!-- USUARIO -->
        <!-- ============================================================= -->

        <SidebarFooter
            class="mt-auto border-t border-[#0fa7b4]/10 bg-[#061115] p-2"
        >
            <NavUser />
        </SidebarFooter>
    </Sidebar>

    <!-- ============================================================= -->
    <!-- COMPONENTES GLOBALES -->
    <!-- ============================================================= -->

    <LeadNotificationCenter />

    <LeadRequestPanel />
</template>