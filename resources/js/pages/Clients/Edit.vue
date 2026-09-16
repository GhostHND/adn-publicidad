<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

type Client = {
    id: number;
    client_code: string;
    client_type: string;
    first_name: string | null;
    middle_name: string | null;
    last_name: string | null;
    second_last_name: string | null;
    business_name: string | null;
    identity_number: string | null;
    rtn: string | null;
    contact_person: string | null;
    email: string | null;
    phone: string | null;
    alternate_phone: string | null;
    address: string | null;
    city: string | null;
    notes: string | null;
    active: boolean;
};

const props = defineProps<{
    client: Client;
}>();

const breadcrumbs = [
    {
        title: 'Clientes',
        href: '/clients',
    },
    {
        title: 'Editar cliente',
        href: `/clients/${props.client.id}/edit`,
    },
];

const form = useForm({
    client_type: props.client.client_type,
    first_name: props.client.first_name ?? '',
    middle_name: props.client.middle_name ?? '',
    last_name: props.client.last_name ?? '',
    second_last_name: props.client.second_last_name ?? '',
    business_name: props.client.business_name ?? '',
    identity_number: props.client.identity_number ?? '',
    rtn: props.client.rtn ?? '',
    contact_person: props.client.contact_person ?? '',
    email: props.client.email ?? '',
    phone: props.client.phone ?? '',
    alternate_phone: props.client.alternate_phone ?? '',
    address: props.client.address ?? '',
    city: props.client.city ?? '',
    notes: props.client.notes ?? '',
    active: props.client.active,
});

const isPerson = () => {
    return ['natural', 'occasional'].includes(form.client_type);
};

const isOrganization = () => {
    return ['business', 'institution'].includes(form.client_type);
};

const submit = () => {
    form.patch(`/clients/${props.client.id}`);
};
</script>

<template>
    <Head title="Editar cliente" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold">
                        Editar cliente
                    </h1>

                    <p class="text-sm text-muted-foreground">
                        Actualiza la información de
                        {{ client.client_code }}.
                    </p>
                </div>

                <Link
                    href="/clients"
                    class="inline-flex items-center justify-center rounded-lg border px-5 py-2.5 text-sm font-semibold transition hover:bg-muted"
                >
                    Volver a clientes
                </Link>
            </div>

            <form
                @submit.prevent="submit"
                class="rounded-xl border bg-background p-6 shadow-sm"
            >
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Tipo de cliente *
                        </label>

                        <select
                            v-model="form.client_type"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        >
                            <option value="natural">
                                Persona natural
                            </option>

                            <option value="business">
                                Empresa
                            </option>

                            <option value="institution">
                                Institución
                            </option>

                            <option value="occasional">
                                Cliente ocasional
                            </option>

                            <option value="final_consumer">
                                Consumidor final
                            </option>
                        </select>

                        <p
                            v-if="form.errors.client_type"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ form.errors.client_type }}
                        </p>
                    </div>

                    <template v-if="isPerson()">
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
                                Número de identidad
                            </label>

                            <input
                                v-model="form.identity_number"
                                type="text"
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
                                RTN
                            </label>

                            <input
                                v-model="form.rtn"
                                type="text"
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />

                            <p
                                v-if="form.errors.rtn"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.rtn }}
                            </p>
                        </div>
                    </template>

                    <template v-if="isOrganization()">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium">
                                Nombre de la
                                {{
                                    form.client_type === 'business'
                                        ? 'empresa'
                                        : 'institución'
                                }}
                                *
                            </label>

                            <input
                                v-model="form.business_name"
                                type="text"
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />

                            <p
                                v-if="form.errors.business_name"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.business_name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                RTN
                            </label>

                            <input
                                v-model="form.rtn"
                                type="text"
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />

                            <p
                                v-if="form.errors.rtn"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ form.errors.rtn }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">
                                Persona de contacto
                            </label>

                            <input
                                v-model="form.contact_person"
                                type="text"
                                class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                            />
                        </div>
                    </template>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Teléfono
                        </label>

                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                        />
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
                            Ciudad
                        </label>

                        <input
                            v-model="form.city"
                            type="text"
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
                                Cliente activo
                            </span>
                        </label>
                    </div>
                </div>

                <div
                    class="mt-8 flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/clients"
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
                                : 'Guardar cambios'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>