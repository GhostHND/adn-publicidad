<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Save,
    UserPlus,
} from '@lucide/vue';

defineProps<{
    employees: any[];
    roles: any[];
}>();

const breadcrumbs = [
    {
        title: 'Usuarios',
        href: '/users',
    },
    {
        title: 'Nuevo usuario',
        href: '/users/create',
    },
];

const form = useForm({
    name: '',
    email: '',
    employee_id: '',
    password: '',
    password_confirmation: '',
    role_ids: [] as number[],
});

const submit = () => {
    form.post('/users');
};
</script>

<template>
    <Head title="Nuevo usuario" />

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
                        Seguridad
                    </p>

                    <h1 class="text-3xl font-bold">
                        Nuevo usuario
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
                    <div
                        class="flex items-center gap-3"
                    >
                        <UserPlus
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <h2 class="font-bold">
                            Información de acceso
                        </h2>
                    </div>

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

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Contraseña
                            </label>

                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-xl border bg-background px-4 py-3"
                            />

                            <p
                                v-if="form.errors.password"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Confirmar contraseña
                            </label>

                            <input
                                v-model="
                                    form.password_confirmation
                                "
                                type="password"
                                class="w-full rounded-xl border bg-background px-4 py-3"
                            />
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-2xl border bg-background p-6"
                >
                    <h2 class="font-bold">
                        Roles
                    </h2>

                    <p
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        Un usuario puede tener uno o varios roles.
                    </p>

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
                        class="inline-flex items-center gap-2 rounded-xl bg-[#0fa7b4] px-6 py-3 font-semibold text-white disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />

                        Crear usuario
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>