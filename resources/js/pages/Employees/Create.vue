<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const breadcrumbs = [
    {
        title: 'Empleados',
        href: '/employees',
    },
    {
        title: 'Nuevo empleado',
        href: '/employees/create',
    },
];

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    second_last_name: '',
    identity_number: '',
    gender: '',
    birth_date: '',
    email: '',
    phone: '',
    alternate_phone: '',
    address: '',
    position: '',
    hire_date: '',
    notes: '',
    active: true,
});

const submit = () => {
    form.post('/employees');
};
</script>

<template>
    <Head title="Nuevo empleado" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold">
                        Nuevo empleado
                    </h1>

                    <p class="text-sm text-muted-foreground">
                        Registra un nuevo miembro del equipo de ADN Publicidad.
                    </p>
                </div>

                <Link
                    href="/employees"
                    class="inline-flex items-center justify-center rounded-lg border px-5 py-2.5 text-sm font-semibold transition hover:bg-muted"
                >
                    Volver a empleados
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="rounded-xl border bg-background p-6 shadow-sm"
            >
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Primer nombre *
                        </label>

                        <input
                            v-model="form.first_name"
                            type="text"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.first_name"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.first_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Segundo nombre
                        </label>

                        <input
                            v-model="form.middle_name"
                            type="text"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Primer apellido *
                        </label>

                        <input
                            v-model="form.last_name"
                            type="text"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.last_name"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.last_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Segundo apellido
                        </label>

                        <input
                            v-model="form.second_last_name"
                            type="text"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Identidad
                        </label>

                        <input
                            v-model="form.identity_number"
                            type="text"
                            placeholder="0801-1990-00000"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.identity_number"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.identity_number }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Sexo
                        </label>

                        <select
                            v-model="form.gender"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        >
                            <option value="">
                                Seleccionar
                            </option>

                            <option value="Masculino">
                                Masculino
                            </option>

                            <option value="Femenino">
                                Femenino
                            </option>

                            <option value="Otro">
                                Otro
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Fecha de nacimiento
                        </label>

                        <input
                            v-model="form.birth_date"
                            type="date"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Cargo
                        </label>

                        <input
                            v-model="form.position"
                            type="text"
                            placeholder="Ej. Diseñador gráfico"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Teléfono *
                        </label>

                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="9999-9999"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Teléfono alternativo
                        </label>

                        <input
                            v-model="form.alternate_phone"
                            type="text"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Correo electrónico
                        </label>

                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />

                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Fecha de ingreso
                        </label>

                        <input
                            v-model="form.hire_date"
                            type="date"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Dirección
                        </label>

                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Notas
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.active"
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                            />

                            <span class="text-sm font-medium">
                                Empleado activo
                            </span>
                        </label>
                    </div>
                </div>

                <div
                    class="mt-8 flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/employees"
                        class="inline-flex items-center justify-center rounded-lg border px-5 py-2.5 text-sm font-semibold transition hover:bg-muted"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-lg bg-[#0fa7b4] px-6 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Guardando...'
                                : 'Guardar empleado'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>