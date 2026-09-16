<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps<{
    workOrders: any[];
    employees: any[];
}>();

const form = useForm({
    work_order_id: '',
    responsible_employee_id: '',
    scheduled_at: '',
    contact_name: '',
    contact_phone: '',
    address: '',
    city: '',
    reference: '',
    estimated_duration_minutes: '',
    notes: '',
});

const breadcrumbs = [
    {
        title: 'Instalaciones',
        href: '/installations',
    },
    {
        title: 'Nueva',
        href: '/installations/create',
    },
];

const selectedOrder =
    computed(() =>
        props.workOrders.find(
            (order) =>
                Number(order.id)
                ===
                Number(
                    form.work_order_id,
                ),
        ),
    );

watch(
    selectedOrder,
    (order) => {
        if (!order) {
            return;
        }

        form.responsible_employee_id =
            order.responsible_employee_id
            ?? '';

        form.contact_name =
            order.client ?? '';

        form.contact_phone =
            order.phone ?? '';

        form.address =
            order.address ?? '';

        form.city =
            order.city ?? '';
    },
);

const submit = () => {
    form.post(
        '/installations',
    );
};
</script>

<template>
    <Head
        title="Nueva instalación"
    />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-6"
        >
            <div>
                <h1
                    class="text-3xl font-bold"
                >
                    Nueva instalación
                </h1>

                <p
                    class="mt-1 text-sm text-muted-foreground"
                >
                    Utiliza esta opción únicamente
                    como respaldo. Normalmente las
                    instalaciones se generan desde
                    la orden de trabajo.
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6"
            >
                <div
                    class="rounded-2xl border bg-background p-6"
                >
                    <h2
                        class="font-semibold"
                    >
                        Trabajo
                    </h2>

                    <div
                        class="mt-5 grid gap-5 md:grid-cols-2"
                    >
                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Orden de trabajo *
                            </label>

                            <select
                                v-model="
                                    form.work_order_id
                                "
                                class="w-full rounded-lg border bg-background px-4 py-3"
                            >
                                <option value="">
                                    Seleccionar
                                </option>

                                <option
                                    v-for="
                                        order in workOrders
                                    "
                                    :key="order.id"
                                    :value="order.id"
                                >
                                    {{
                                        order.number
                                    }}
                                    —
                                    {{
                                        order.title
                                    }}
                                    —
                                    {{
                                        order.client
                                    }}
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
                                    v-for="
                                        employee in employees
                                    "
                                    :key="
                                        employee.id
                                    "
                                    :value="
                                        employee.id
                                    "
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
                    </div>
                </div>

                <div
                    class="rounded-2xl border bg-background p-6"
                >
                    <h2
                        class="font-semibold"
                    >
                        Lugar y contacto
                    </h2>

                    <div
                        class="mt-5 grid gap-5 md:grid-cols-2"
                    >
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
                </div>

                <div
                    class="flex justify-end gap-3"
                >
                    <Link
                        href="/installations"
                        class="rounded-lg border px-5 py-3 text-sm font-semibold"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="
                            form.processing
                        "
                        class="rounded-lg bg-[#0fa7b4] px-6 py-3 text-sm font-semibold text-white disabled:opacity-50"
                    >
                        Guardar instalación
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>