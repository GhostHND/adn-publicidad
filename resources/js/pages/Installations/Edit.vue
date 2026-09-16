<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

const props = defineProps<{
    installation: any;
    employees: any[];
}>();

const form = useForm({
    responsible_employee_id:
        props.installation
            .responsible_employee_id
        ?? '',

    scheduled_at:
        props.installation
            .scheduled_at
        ?? '',

    contact_name:
        props.installation
            .contact_name
        ?? '',

    contact_phone:
        props.installation
            .contact_phone
        ?? '',

    address:
        props.installation
            .address
        ?? '',

    city:
        props.installation
            .city
        ?? '',

    reference:
        props.installation
            .reference
        ?? '',

    estimated_duration_minutes:
        props.installation
            .estimated_duration_minutes
        ?? '',

    notes:
        props.installation
            .notes
        ?? '',
});

const breadcrumbs = [
    {
        title: 'Instalaciones',
        href: '/installations',
    },
    {
        title:
            props.installation
                .installation_number,
        href:
            `/installations/${props.installation.id}`,
    },
    {
        title: 'Editar',
        href:
            `/installations/${props.installation.id}/edit`,
    },
];

const submit = () => {
    form.patch(
        `/installations/${props.installation.id}`,
    );
};
</script>

<template>
    <Head title="Editar instalación" />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="mx-auto w-full max-w-5xl p-6"
        >
            <h1
                class="text-3xl font-bold"
            >
                Programar instalación
            </h1>

            <p
                class="mt-1 text-sm text-muted-foreground"
            >
                {{
                    installation.installation_number
                }}
                ·
                {{
                    installation.client_name
                }}
            </p>

            <form
                @submit.prevent="submit"
                class="mt-6 space-y-6"
            >
                <div
                    class="grid gap-5 rounded-2xl border bg-background p-6 md:grid-cols-2"
                >
                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Fecha y hora
                        </label>

                        <input
                            v-model="
                                form.scheduled_at
                            "
                            type="datetime-local"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Responsable
                        </label>

                        <select
                            v-model="
                                form.responsible_employee_id
                            "
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        >
                            <option value="">
                                Operador principal
                            </option>

                            <option
                                v-for="
                                    employee in employees
                                "
                                :key="employee.id"
                                :value="employee.id"
                            >
                                {{
                                    employee.name
                                }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Contacto
                        </label>

                        <input
                            v-model="
                                form.contact_name
                            "
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Teléfono
                        </label>

                        <input
                            v-model="
                                form.contact_phone
                            "
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div
                        class="md:col-span-2"
                    >
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Dirección
                        </label>

                        <textarea
                            v-model="
                                form.address
                            "
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Ciudad
                        </label>

                        <input
                            v-model="
                                form.city
                            "
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Referencia
                        </label>

                        <input
                            v-model="
                                form.reference
                            "
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Duración estimada
                            (minutos)
                        </label>

                        <input
                            v-model="
                                form.estimated_duration_minutes
                            "
                            type="number"
                            min="1"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div
                        class="md:col-span-2"
                    >
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Notas
                        </label>

                        <textarea
                            v-model="
                                form.notes
                            "
                            rows="4"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3"
                >
                    <Link
                        :href="`/installations/${installation.id}`"
                        class="rounded-lg border px-5 py-3 text-sm font-semibold"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="
                            form.processing
                        "
                        class="rounded-lg bg-[#0fa7b4] px-6 py-3 text-sm font-semibold text-white"
                    >
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>