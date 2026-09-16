<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    KeyRound,
    Save,
} from '@lucide/vue';

const props = defineProps<{
    user: any;
    employees: any[];
    roles: any[];
}>();

const breadcrumbs = [
    {
        title: 'Usuarios',
        href: '/users',
    },
    {
        title: props.user.name,
        href: `/users/${props.user.id}/edit`,
    },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    employee_id:
        props.user.employee_id ?? '',
    role_ids:
        props.user.role_ids ?? [],
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.patch(
        `/users/${props.user.id}`,
        {
            preserveScroll:
                true,
        },
    );
};

const resetPassword = () => {
    passwordForm.post(
        `/users/${props.user.id}/reset-password`,
        {
            preserveScroll:
                true,

            onSuccess: () => {
                passwordForm.reset();
            },
        },
    );
};
</script>

<template>
    <Head :title="`Editar ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-6"
        >
            <div
                class="flex items-center justify-between gap-4"
            >
                <div>
                    <p
                        class="text-sm font-semibold text-[#0fa7b4]"
                    >
                        Usuario
                    </p>

                    <h1 class="text-3xl font-bold">
                        {{ user.name }}
                    </h1>
                </div>

                <Link
                    href="/users"
                    class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 font-semibold"
                >
                    <ArrowLeft class="h-4 w-4" />

                    Volver
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6"
            >
                <section
                    class="rounded-2xl border bg-background p-6"
                >
                    <h2 class="font-bold">
                        Información del usuario
                    </h2>

                    <div
                        class="mt-6 grid gap-5 md:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Nombre
                            </label>

                            <input
                                v-model="form.name"
                                class="w-full rounded-xl border bg-background px-4 py-3"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Correo
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-xl border bg-background px-4 py-3"
                            />

                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Empleado asociado
                            </label>

                            <select
                                v-model="form.employee_id"
                                class="w-full rounded-xl border bg-background px-4 py-3"
                            >
                                <option value="">
                                    Sin empleado asociado
                                </option>

                                <option
                                    v-for="employee in employees"
                                    :key="employee.id"
                                    :value="employee.id"
                                >
                                    {{ employee.code }}
                                    ·
                                    {{ employee.name }}
                                    {{
                                        employee.position
                                            ? `· ${employee.position}`
                                            : ''
                                    }}
                                </option>
                            </select>
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-2xl border bg-background p-6"
                >
                    <h2 class="font-bold">
                        Roles
                    </h2>

                    <div
                        class="mt-5 grid gap-3 md:grid-cols-2"
                    >
                        <label
                            v-for="role in roles"
                            :key="role.id"
                            class="flex cursor-pointer gap-3 rounded-xl border p-4"
                        >
                            <input
                                v-model="form.role_ids"
                                type="checkbox"
                                :value="role.id"
                                class="mt-1 h-4 w-4"
                            />

                            <div>
                                <p class="font-semibold">
                                    {{ role.name }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    {{ role.description }}
                                </p>
                            </div>
                        </label>
                    </div>

                    <p
                        v-if="form.errors.role_ids"
                        class="mt-3 text-xs text-red-500"
                    >
                        {{ form.errors.role_ids }}
                    </p>
                </section>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#0fa7b4] px-6 py-3 font-semibold text-white"
                    >
                        <Save class="h-4 w-4" />
                        Guardar cambios
                    </button>
                </div>
            </form>

            <section
                class="rounded-2xl border border-amber-200 bg-amber-50/40 p-6 dark:border-amber-900 dark:bg-amber-950/10"
            >
                <div
                    class="flex items-center gap-3"
                >
                    <KeyRound
                        class="h-5 w-5 text-amber-600"
                    />

                    <div>
                        <h2 class="font-bold">
                            Cambiar contraseña
                        </h2>

                        <p
                            class="text-sm text-muted-foreground"
                        >
                            Define una nueva contraseña de acceso.
                        </p>
                    </div>
                </div>

                <form
                    @submit.prevent="resetPassword"
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <input
                        v-model="passwordForm.password"
                        type="password"
                        placeholder="Nueva contraseña"
                        class="rounded-xl border bg-background px-4 py-3"
                    />

                    <input
                        v-model="
                            passwordForm.password_confirmation
                        "
                        type="password"
                        placeholder="Confirmar contraseña"
                        class="rounded-xl border bg-background px-4 py-3"
                    />

                    <p
                        v-if="passwordForm.errors.password"
                        class="text-xs text-red-500 md:col-span-2"
                    >
                        {{ passwordForm.errors.password }}
                    </p>

                    <div
                        class="md:col-span-2 md:text-right"
                    >
                        <button
                            type="submit"
                            :disabled="
                                passwordForm.processing
                            "
                            class="rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white"
                        >
                            Actualizar contraseña
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AppLayout>
</template>