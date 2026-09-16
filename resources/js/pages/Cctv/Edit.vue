<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';

const props = defineProps<{
    project: any;
    clients: any[];
    employees: any[];
}>();

const form = useForm({
    client_id:
        props.project.client_id,

    responsible_employee_id:
        props.project.responsible_employee_id
        ?? '',

    system_type:
        props.project.system_type,

    site_name:
        props.project.site_name
        ?? '',

    address:
        props.project.address
        ?? '',

    city:
        props.project.city
        ?? '',

    contact_name:
        props.project.contact_name
        ?? '',

    contact_phone:
        props.project.contact_phone
        ?? '',

    internet_provider:
        props.project.internet_provider
        ?? '',

    network_notes:
        props.project.network_notes
        ?? '',

    site_survey_scheduled_at:
        props.project.site_survey_scheduled_at
        ?? '',

    site_survey_completed_at:
        props.project.site_survey_completed_at
        ?? '',

    site_survey_notes:
        props.project.site_survey_notes
        ?? '',

    maintenance_due_at:
        props.project.maintenance_due_at
        ?? '',

    notes:
        props.project.notes
        ?? '',
});

const breadcrumbs = [
    {
        title: 'CCTV',
        href: '/cctv',
    },
    {
        title:
            props.project.project_number,
        href:
            `/cctv/${props.project.id}`,
    },
    {
        title: 'Editar',
        href:
            `/cctv/${props.project.id}/edit`,
    },
];

const submit = () => {
    form.patch(
        `/cctv/${props.project.id}`,
    );
};
</script>

<template>
    <Head title="Editar CCTV" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto w-full max-w-5xl p-6"
        >
            <h1 class="text-3xl font-bold">
                Editar proyecto CCTV
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                {{ project.project_number }}
            </p>

            <form
                @submit.prevent="submit"
                class="mt-6 space-y-6"
            >
                <div
                    class="grid gap-5 rounded-2xl border p-6 md:grid-cols-2"
                >
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Cliente
                        </label>

                        <select
                            v-model="form.client_id"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        >
                            <option
                                v-for="client in clients"
                                :key="client.id"
                                :value="client.id"
                            >
                                {{ client.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
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
                                v-for="employee in employees"
                                :key="employee.id"
                                :value="employee.id"
                            >
                                {{ employee.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Sistema
                        </label>

                        <select
                            v-model="form.system_type"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        >
                            <option value="unspecified">
                                Por definir
                            </option>
                            <option value="analog">
                                Analógico
                            </option>
                            <option value="ip">
                                IP
                            </option>
                            <option value="hybrid">
                                Híbrido
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Nombre del sitio
                        </label>

                        <input
                            v-model="form.site_name"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Dirección
                        </label>

                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Ciudad
                        </label>

                        <input
                            v-model="form.city"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Contacto
                        </label>

                        <input
                            v-model="form.contact_name"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Teléfono
                        </label>

                        <input
                            v-model="form.contact_phone"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Proveedor de Internet
                        </label>

                        <input
                            v-model="form.internet_provider"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Próximo mantenimiento
                        </label>

                        <input
                            v-model="form.maintenance_due_at"
                            type="date"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Visita programada
                        </label>

                        <input
                            v-model="
                                form.site_survey_scheduled_at
                            "
                            type="datetime-local"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Visita realizada
                        </label>

                        <input
                            v-model="
                                form.site_survey_completed_at
                            "
                            type="datetime-local"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Resultado de visita
                        </label>

                        <textarea
                            v-model="form.site_survey_notes"
                            rows="4"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Información de red
                        </label>

                        <textarea
                            v-model="form.network_notes"
                            rows="4"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Notas
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="4"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link
                        :href="`/cctv/${project.id}`"
                        class="rounded-lg border px-5 py-3 text-sm font-semibold"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-[#0fa7b4] px-6 py-3 text-sm font-semibold text-white"
                    >
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>