<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps<{
    clients: any[];
    employees: any[];
    workOrders: any[];
}>();

const form = useForm({
    client_id: '',
    work_order_id: '',
    responsible_employee_id: '',
    system_type: 'unspecified',
    status: 'planning',
    site_name: '',
    address: '',
    city: '',
    contact_name: '',
    contact_phone: '',
    internet_provider: '',
    network_notes: '',
    site_survey_scheduled_at: '',
    notes: '',
});

const breadcrumbs = [
    {
        title: 'CCTV',
        href: '/cctv',
    },
    {
        title: 'Nuevo proyecto',
        href: '/cctv/create',
    },
];

const selectedClient =
    computed(() =>
        props.clients.find(
            (client) =>
                Number(client.id)
                ===
                Number(form.client_id),
        ),
    );

watch(
    selectedClient,
    (client) => {
        if (!client) {
            return;
        }

        if (!form.site_name) {
            form.site_name =
                client.name ?? '';
        }

        if (!form.contact_name) {
            form.contact_name =
                client.name ?? '';
        }

        if (!form.contact_phone) {
            form.contact_phone =
                client.phone ?? '';
        }

        if (!form.address) {
            form.address =
                client.address ?? '';
        }

        if (!form.city) {
            form.city =
                client.city ?? '';
        }
    },
);

const submit = () => {
    form.post('/cctv');
};
</script>

<template>
    <Head title="Nuevo proyecto CCTV" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto w-full max-w-5xl p-6"
        >
            <h1 class="text-3xl font-bold">
                Nuevo proyecto CCTV
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                Para proyectos que comienzan antes de una
                cotización o como registro manual.
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
                            Cliente *
                        </label>

                        <select
                            v-model="form.client_id"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        >
                            <option value="">
                                Seleccionar
                            </option>

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
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            OT relacionada
                        </label>

                        <select
                            v-model="form.work_order_id"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        >
                            <option value="">
                                Ninguna
                            </option>

                            <option
                                v-for="order in workOrders"
                                :key="order.id"
                                :value="order.id"
                            >
                                {{ order.number }}
                                —
                                {{ order.title }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Tipo de sistema
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
                                v-for="employee in employees"
                                :key="employee.id"
                                :value="employee.id"
                            >
                                {{ employee.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Nombre del sitio
                        </label>

                        <input
                            v-model="form.site_name"
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
                            v-model="form.city"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Dirección
                        </label>

                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Contacto
                        </label>

                        <input
                            v-model="form.contact_name"
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
                            v-model="form.contact_phone"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Proveedor de Internet
                        </label>

                        <input
                            v-model="form.internet_provider"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Visita técnica
                        </label>

                        <input
                            v-model="
                                form.site_survey_scheduled_at
                            "
                            type="datetime-local"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Notas de red
                        </label>

                        <textarea
                            v-model="form.network_notes"
                            rows="3"
                            class="w-full rounded-lg border bg-background px-4 py-3"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Notas generales
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
                        href="/cctv"
                        class="rounded-lg border px-5 py-3 text-sm font-semibold"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-[#0fa7b4] px-6 py-3 text-sm font-semibold text-white"
                    >
                        Crear proyecto
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>