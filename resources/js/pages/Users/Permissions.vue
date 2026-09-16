<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Plus,
    Save,
    ShieldCheck,
    Trash2,
} from '@lucide/vue';
import { reactive } from 'vue';

type RoleForm = {
    name: string;
    description: string;
    permission_ids: number[];
};

const props = defineProps<{
    roles: any[];
    permissionGroups: any[];
}>();

const breadcrumbs = [
    {
        title: 'Usuarios',
        href: '/users',
    },
    {
        title: 'Roles y permisos',
        href: '/users/permissions',
    },
];

const createForm = useForm({
    name: '',
    description: '',
    permission_ids: [] as number[],
});

const roleForms =
    reactive<Record<number, RoleForm>>(
        Object.fromEntries(
            props.roles.map(
                (role) => [
                    role.id,
                    {
                        name:
                            role.name,

                        description:
                            role.description ?? '',

                        permission_ids:
                            [
                                ...role.permission_ids,
                            ],
                    },
                ],
            ),
        ),
    );

const createRole = () => {
    createForm.post(
        '/users/roles',
        {
            preserveScroll:
                true,

            onSuccess: () => {
                createForm.reset();
            },
        },
    );
};

const updateRole = (
    role: any,
) => {
    router.patch(
        `/users/roles/${role.id}`,
        roleForms[role.id],
        {
            preserveScroll:
                true,
        },
    );
};

const deleteRole = (
    role: any,
) => {
    if (
        !window.confirm(
            `¿Eliminar el rol ${role.name}?`,
        )
    ) {
        return;
    }

    router.delete(
        `/users/roles/${role.id}`,
        {
            preserveScroll:
                true,
        },
    );
};
</script>

<template>
    <Head title="Roles y permisos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        Control de acceso
                    </p>

                    <h1 class="text-3xl font-bold">
                        Roles y permisos
                    </h1>

                    <p
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        Define exactamente qué puede hacer cada tipo de usuario.
                    </p>
                </div>

                <Link
                    href="/users"
                    class="inline-flex items-center gap-2 rounded-xl border px-5 py-3 font-semibold"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Usuarios
                </Link>
            </div>

            <section
                class="rounded-2xl border bg-background p-6"
            >
                <div
                    class="flex items-center gap-3"
                >
                    <Plus
                        class="h-5 w-5 text-[#0fa7b4]"
                    />

                    <h2 class="font-bold">
                        Crear rol personalizado
                    </h2>
                </div>

                <form
                    @submit.prevent="createRole"
                    class="mt-5 space-y-5"
                >
                    <div
                        class="grid gap-4 md:grid-cols-2"
                    >
                        <input
                            v-model="createForm.name"
                            placeholder="Nombre del rol"
                            class="rounded-xl border bg-background px-4 py-3"
                        />

                        <input
                            v-model="
                                createForm.description
                            "
                            placeholder="Descripción"
                            class="rounded-xl border bg-background px-4 py-3"
                        />
                    </div>

                    <div
                        class="grid gap-4 lg:grid-cols-3"
                    >
                        <div
                            v-for="group in permissionGroups"
                            :key="group.name"
                            class="rounded-xl border p-4"
                        >
                            <p class="font-bold">
                                {{ group.name }}
                            </p>

                            <div
                                class="mt-3 space-y-2"
                            >
                                <label
                                    v-for="permission in group.permissions"
                                    :key="permission.id"
                                    class="flex gap-2 text-sm"
                                >
                                    <input
                                        v-model="
                                            createForm.permission_ids
                                        "
                                        type="checkbox"
                                        :value="
                                            permission.id
                                        "
                                    />

                                    <span>
                                        {{ permission.name }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 font-semibold text-white"
                        >
                            <Plus class="h-4 w-4" />
                            Crear rol
                        </button>
                    </div>
                </form>
            </section>

            <section
                v-for="role in roles"
                :key="role.id"
                class="rounded-2xl border bg-background p-6"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                        >
                            <ShieldCheck
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p class="font-bold">
                                {{ role.name }}
                            </p>

                            <p
                                class="text-xs text-muted-foreground"
                            >
                                {{ role.user_count }}
                                usuario(s)
                                ·
                                {{ role.slug }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            v-if="!role.protected"
                            type="button"
                            @click="
                                updateRole(
                                    role,
                                )
                            "
                            class="inline-flex items-center gap-2 rounded-lg bg-[#0fa7b4] px-4 py-2 text-sm font-semibold text-white"
                        >
                            <Save class="h-4 w-4" />
                            Guardar
                        </button>

                        <button
                            v-if="!role.is_system"
                            type="button"
                            @click="
                                deleteRole(
                                    role,
                                )
                            "
                            class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600"
                        >
                            <Trash2 class="h-4 w-4" />
                            Eliminar
                        </button>
                    </div>
                </div>

                <div
                    v-if="role.protected"
                    class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700"
                >
                    El Administrador tiene acceso completo y está protegido contra modificaciones que puedan bloquear el sistema.
                </div>

                <div
                    v-else
                    class="mt-5"
                >
                    <div
                        class="grid gap-4 md:grid-cols-2"
                    >
                        <input
                            v-model="
                                roleForms[role.id].name
                            "
                            class="rounded-xl border bg-background px-4 py-3"
                        />

                        <input
                            v-model="
                                roleForms[role.id].description
                            "
                            class="rounded-xl border bg-background px-4 py-3"
                            placeholder="Descripción"
                        />
                    </div>

                    <div
                        class="mt-5 grid gap-4 lg:grid-cols-3"
                    >
                        <div
                            v-for="group in permissionGroups"
                            :key="
                                `${role.id}-${group.name}`
                            "
                            class="rounded-xl border p-4"
                        >
                            <p class="font-bold">
                                {{ group.name }}
                            </p>

                            <div
                                class="mt-3 space-y-2"
                            >
                                <label
                                    v-for="permission in group.permissions"
                                    :key="permission.id"
                                    class="flex gap-2 text-sm"
                                >
                                    <input
                                        v-model="
                                            roleForms[role.id]
                                                .permission_ids
                                        "
                                        type="checkbox"
                                        :value="
                                            permission.id
                                        "
                                    />

                                    <span>
                                        {{ permission.name }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>