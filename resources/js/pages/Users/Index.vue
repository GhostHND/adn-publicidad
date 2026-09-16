<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';
import { useMobileAccordion } from '@/composables/useMobileAccordion';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3';
import {
    KeyRound,
    Pencil,
    Plus,
    ShieldCheck,
    UserRoundCog,
    UsersRound,
} from '@lucide/vue';
import {
    computed,
} from 'vue';

type Role = {
    id?: number;
    name?: string;
    slug?: string;
};

type User = {
    id: number;

    name: string;
    username?: string | null;
    email: string;

    active?: boolean | number | null;

    employee?: {
        id?: number;
        full_name?: string | null;
        name?: string | null;
        position?: string | null;
    } | null;

    roles?: Role[];

    last_login_at?: string | null;
    last_login_ip?: string | null;
    email_verified_at?: string | null;
};

type PaginatedUsers = {
    data?: User[];
    total?: number;
};

const props = defineProps<{
    users:
        User[]
        | PaginatedUsers;

    roles?: Role[];

    stats?: Record<string, any>;
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title:
            'Usuarios y permisos',

        href:
            '/users',
    },
];

const users =
    computed<User[]>(() => {
        if (
            Array.isArray(
                props.users,
            )
        ) {
            return props.users;
        }

        return props.users.data
            ?? [];
    });

const total =
    computed(() => {
        if (
            Array.isArray(
                props.users,
            )
        ) {
            return props.users.length;
        }

        return Number(
            props.users.total
            ?? users.value.length,
        );
    });

const activeCount =
    computed(() => {
        return users.value.filter(
            (user) =>
                Boolean(
                    user.active,
                ),
        ).length;
    });

const administratorCount =
    computed(() => {
        return users.value.filter(
            (user) =>
                user.roles?.some(
                    (role) =>
                        [
                            'admin',
                            'administrator',
                        ].includes(
                            String(
                                role.slug
                                ?? '',
                            ),
                        ),
                ),
        ).length;
    });

const roleNames = (
    user: User,
): string => {
    const names =
        user.roles
            ?.map(
                (role) =>
                    role.name
                    ??
                    role.slug,
            )
            .filter(Boolean)
            ?? [];

    return names.length > 0
        ? names.join(', ')
        : 'Sin rol';
};

const employeeName = (
    user: User,
): string => {
    return (
        user.employee?.full_name
        ||
        user.employee?.name
        ||
        'Sin empleado vinculado'
    );
};

const fieldsFor = (
    user: User,
): MobileRecordField[] => {
    return [
        {
            label:
                'Usuario',

            value:
                user.username
                ?? '—',
        },

        {
            label:
                'Estado',

            value:
                user.active
                    ? 'Activo'
                    : 'Inactivo',
        },

        {
            label:
                'Correo',

            value:
                user.email,

            wide:
                true,

            copyable:
                true,
        },

        {
            label:
                'Empleado',

            value:
                employeeName(
                    user,
                ),

            wide:
                true,
        },

        {
            label:
                'Roles',

            value:
                roleNames(
                    user,
                ),

            wide:
                true,
        },

        {
            label:
                'Último acceso',

            value:
                user.last_login_at
                ?? 'Sin registro',

            wide:
                true,
        },

        {
            label:
                'Última IP',

            value:
                user.last_login_ip
                ?? '—',

            wide:
                true,

            copyable:
                Boolean(
                    user.last_login_ip,
                ),
        },
    ];
};

const toggleStatus = (
    user: User,
): void => {
    const action =
        user.active
            ? 'desactivar'
            : 'activar';

    const accepted =
        window.confirm(
            `¿Deseas ${action} el usuario ${user.name}?`,
        );

    if (!accepted) {
        return;
    }

    router.patch(
        `/users/${user.id}/status`,
        {
            active:
                !Boolean(
                    user.active,
                ),
        },
        {
            preserveScroll:
                true,
        },
    );
};

const resetPassword = (
    user: User,
): void => {
    const accepted =
        window.confirm(
            `¿Generar una nueva contraseña para ${user.name}?`,
        );

    if (!accepted) {
        return;
    }

    router.post(
        `/users/${user.id}/reset-password`,
        {},
        {
            preserveScroll:
                true,
        },
    );
};
</script>

<template>
    <Head
        title="Usuarios y permisos"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <!-- CABECERA -->

            <section
                class="adn-enter relative overflow-hidden rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-20 h-52 w-52 rounded-full bg-violet-500/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-500/10 text-violet-400"
                        >
                            <UserRoundCog
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-violet-400"
                            >
                                Seguridad
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Usuarios y permisos
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Controla las cuentas que pueden ingresar al sistema.
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid gap-2 sm:flex"
                    >
                        <Link
                            href="/users/permissions"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-500/20 bg-violet-500/[0.05] px-5 text-sm font-black text-violet-400"
                        >
                            <ShieldCheck
                                class="h-4 w-4"
                            />

                            Roles y permisos
                        </Link>

                        <Link
                            href="/users/create"
                            class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                        >
                            <Plus
                                class="h-4 w-4"
                            />

                            Nuevo usuario
                        </Link>
                    </div>
                </div>
            </section>

            <!-- MÉTRICAS -->

            <section
                class="grid grid-cols-3 gap-3 md:max-w-xl"
            >
                <article
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase tracking-wide text-white/30"
                    >
                        Usuarios
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{ total }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-emerald-500/15 bg-emerald-500/[0.05] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase tracking-wide text-emerald-400"
                    >
                        Activos
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{ activeCount }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-violet-500/15 bg-violet-500/[0.05] p-4"
                >
                    <p
                        class="text-[9px] font-black uppercase tracking-wide text-violet-400"
                    >
                        Admin
                    </p>

                    <p
                        class="mt-1 text-xl font-black"
                    >
                        {{
                            administratorCount
                        }}
                    </p>
                </article>
            </section>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <div
                    v-if="
                        users.length === 0
                    "
                    class="rounded-2xl border border-dashed border-white/10 p-10 text-center"
                >
                    <UsersRound
                        class="mx-auto h-8 w-8 text-white/20"
                    />

                    <p
                        class="mt-3 font-black"
                    >
                        No hay usuarios
                    </p>
                </div>

                <MobileRecordCard
                    v-for="
                        user in users
                    "
                    :key="
                        user.id
                    "
                    :title="
                        user.name
                    "
                    :code="
                        user.username
                        ?? `USR-${String(
                            user.id,
                        ).padStart(
                            4,
                            '0',
                        )}`
                    "
                    :subtitle="
                        roleNames(
                            user,
                        )
                    "
                    :status="
                        user.active
                            ? 'Activo'
                            : 'Inactivo'
                    "
                    :status-tone="
                        user.active
                            ? 'success'
                            : 'danger'
                    "
                    :fields="
                        fieldsFor(
                            user,
                        )
                    "
                    :expanded="
                        isExpanded(
                            user.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            user.id,
                        )
                    "
                >
                    <template
                        #badges
                    >
                        <ShieldCheck
                            v-if="
                                user.roles?.some(
                                    (
                                        role,
                                    ) =>
                                        [
                                            'admin',
                                            'administrator',
                                        ].includes(
                                            String(
                                                role.slug
                                                ?? '',
                                            ),
                                        ),
                                )
                            "
                            class="h-4 w-4 text-violet-400"
                        />
                    </template>

                    <template
                        #actions
                    >
                        <div
                            class="space-y-2"
                        >
                            <Link
                                :href="
                                    `/users/${user.id}/edit`
                                "
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white"
                            >
                                <Pencil
                                    class="h-4 w-4"
                                />

                                Editar usuario
                            </Link>

                            <div
                                class="grid grid-cols-2 gap-2"
                            >
                                <button
                                    type="button"
                                    class="inline-flex h-10 items-center justify-center rounded-xl border text-xs font-bold"
                                    :class="
                                        user.active
                                            ? 'border-[#e84657]/20 bg-[#e84657]/5 text-[#f06472]'
                                            : 'border-emerald-500/20 bg-emerald-500/5 text-emerald-400'
                                    "
                                    @click="
                                        toggleStatus(
                                            user,
                                        )
                                    "
                                >
                                    {{
                                        user.active
                                            ? 'Desactivar'
                                            : 'Activar'
                                    }}
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-violet-500/20 bg-violet-500/[0.05] text-xs font-bold text-violet-400"
                                    @click="
                                        resetPassword(
                                            user,
                                        )
                                    "
                                >
                                    <KeyRound
                                        class="h-3.5 w-3.5"
                                    />

                                    Contraseña
                                </button>
                            </div>
                        </div>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ESCRITORIO -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full text-sm"
                    >
                        <thead>
                            <tr>
                                <th class="px-5 py-4 text-left">
                                    Usuario
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Correo
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Empleado
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Roles
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Último acceso
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Estado
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    user in users
                                "
                                :key="
                                    user.id
                                "
                            >
                                <td
                                    class="px-5 py-4"
                                >
                                    <p
                                        class="font-black"
                                    >
                                        {{
                                            user.name
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-[10px] text-muted-foreground"
                                    >
                                        {{
                                            user.username
                                            ?? 'Sin nombre de usuario'
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        user.email
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        employeeName(
                                            user,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <span
                                        class="rounded-full border border-violet-500/15 bg-violet-500/[0.05] px-3 py-1 text-[10px] font-black text-violet-400"
                                    >
                                        {{
                                            roleNames(
                                                user,
                                            )
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    {{
                                        user.last_login_at
                                        ?? 'Nunca'
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black"
                                        :class="
                                            user.active
                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                                : 'border-[#e84657]/20 bg-[#e84657]/10 text-[#f06472]'
                                        "
                                    >
                                        {{
                                            user.active
                                                ? 'Activo'
                                                : 'Inactivo'
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4"
                                >
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <Link
                                            :href="
                                                `/users/${user.id}/edit`
                                            "
                                            class="rounded-xl border border-white/10 p-2.5"
                                            title="Editar"
                                        >
                                            <Pencil
                                                class="h-4 w-4"
                                            />
                                        </Link>

                                        <button
                                            type="button"
                                            class="rounded-xl border border-violet-500/20 p-2.5 text-violet-400"
                                            title="Restablecer contraseña"
                                            @click="
                                                resetPassword(
                                                    user,
                                                )
                                            "
                                        >
                                            <KeyRound
                                                class="h-4 w-4"
                                            />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl border px-3 py-2 text-xs font-black"
                                            :class="
                                                user.active
                                                    ? 'border-[#e84657]/20 text-[#f06472]'
                                                    : 'border-emerald-500/20 text-emerald-400'
                                            "
                                            @click="
                                                toggleStatus(
                                                    user,
                                                )
                                            "
                                        >
                                            {{
                                                user.active
                                                    ? 'Desactivar'
                                                    : 'Activar'
                                            }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>