<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
    router,
    useForm,
} from '@inertiajs/vue3';
import {
    Camera,
    KeyRound,
    MapPin,
    Pencil,
    Plus,
    ShieldCheck,
    Trash2,
    Wrench,
} from '@lucide/vue';

const props = defineProps<{
    project: any;
    employees: any[];
    allowedStatuses: string[];
}>();

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
];

const deviceForm = useForm({
    operation: 'device',
    device_id: '',
    device_type: 'camera',
    brand: '',
    model: '',
    serial_number: '',
    mac_address: '',
    ip_address: '',
    channel: '',
    location: '',
    status: 'active',
    notes: '',
});

const credentialForm = useForm({
    operation: 'credential',
    cctv_device_id: '',
    credential_type: 'device_admin',
    label: '',
    username: '',
    secret_value: '',
    host: '',
    port: '',
    notes: '',
});

const maintenanceForm = useForm({
    operation: 'maintenance',
    cctv_device_id: '',
    performed_by_employee_id:
        props.project.responsible_employee_id
        ?? '',
    maintenance_type: 'preventive',
    maintenance_date:
        new Date()
            .toISOString()
            .slice(0, 10),
    description: '',
    findings: '',
    actions_taken: '',
    cost: '0',
    next_due_date: '',
});

const statusLabel = (
    status: string,
) => {
    const labels: Record<string, string> = {
        planning: 'Planificación',
        survey_pending: 'Visita técnica',
        equipment_pending: 'Equipos pendientes',
        ready_installation: 'Listo para instalar',
        installation_scheduled: 'Instalación programada',
        installation_in_progress: 'Instalando',
        installed: 'Instalado',
        maintenance: 'Mantenimiento',
        cancelled: 'Cancelado',
    };

    return labels[status] ?? status;
};

const deviceTypeLabel = (
    type: string,
) => {
    const labels: Record<string, string> = {
        camera: 'Cámara',
        nvr: 'NVR',
        dvr: 'DVR',
        xvr: 'XVR',
        poe_switch: 'Switch PoE',
        router: 'Router',
        ups: 'UPS',
        hdd: 'Disco duro',
        monitor: 'Monitor',
        other: 'Otro',
    };

    return labels[type] ?? type;
};

const maintenanceTypeLabel = (
    type: string,
) => {
    const labels: Record<string, string> = {
        preventive: 'Preventivo',
        corrective: 'Correctivo',
        inspection: 'Inspección',
        configuration: 'Configuración',
    };

    return labels[type] ?? type;
};

const money = (
    value: number | string,
) =>
    `L ${Number(value || 0).toLocaleString(
        'es-HN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;

const changeStatus = (
    status: string,
) => {
    if (
        !window.confirm(
            `¿Cambiar el proyecto a "${statusLabel(
                status,
            )}"?`,
        )
    ) {
        return;
    }

    router.patch(
        `/cctv/${props.project.id}`,
        {
            operation: 'status',
            status,
        },
        {
            preserveScroll: true,
        },
    );
};

const saveDevice = () => {
    deviceForm.patch(
        `/cctv/${props.project.id}`,
        {
            preserveScroll: true,
            onSuccess: () =>
                deviceForm.reset(
                    'device_id',
                    'brand',
                    'model',
                    'serial_number',
                    'mac_address',
                    'ip_address',
                    'channel',
                    'location',
                    'notes',
                ),
        },
    );
};

const deleteDevice = (
    id: number,
) => {
    if (
        !window.confirm(
            '¿Retirar este equipo del proyecto?',
        )
    ) {
        return;
    }

    router.patch(
        `/cctv/${props.project.id}`,
        {
            operation: 'delete_device',
            device_id: id,
        },
        {
            preserveScroll: true,
        },
    );
};

const saveCredential = () => {
    credentialForm.patch(
        `/cctv/${props.project.id}`,
        {
            preserveScroll: true,
            onSuccess: () =>
                credentialForm.reset(
                    'cctv_device_id',
                    'label',
                    'username',
                    'secret_value',
                    'host',
                    'port',
                    'notes',
                ),
        },
    );
};

const deleteCredential = (
    id: number,
) => {
    if (
        !window.confirm(
            '¿Eliminar esta credencial técnica?',
        )
    ) {
        return;
    }

    router.patch(
        `/cctv/${props.project.id}`,
        {
            operation: 'delete_credential',
            credential_id: id,
        },
        {
            preserveScroll: true,
        },
    );
};

const saveMaintenance = () => {
    maintenanceForm.patch(
        `/cctv/${props.project.id}`,
        {
            preserveScroll: true,
            onSuccess: () =>
                maintenanceForm.reset(
                    'cctv_device_id',
                    'description',
                    'findings',
                    'actions_taken',
                    'next_due_date',
                ),
        },
    );
};
</script>

<template>
    <Head :title="project.project_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold text-[#0fa7b4]">
                        {{ project.project_number }}
                    </p>

                    <h1 class="text-3xl font-bold">
                        {{
                            project.site_name
                            || project.client_name
                        }}
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ project.client_name }}
                        ·
                        {{ statusLabel(project.status) }}
                    </p>
                </div>

                <Link
                    :href="`/cctv/${project.id}/edit`"
                    class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
                >
                    <Pencil class="h-4 w-4" />
                    Editar proyecto
                </Link>
            </div>

            <div
                v-if="allowedStatuses.length"
                class="rounded-2xl border p-5"
            >
                <p class="mb-3 text-sm font-semibold">
                    Cambiar estado
                </p>

                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="status in allowedStatuses"
                        :key="status"
                        type="button"
                        @click="changeStatus(status)"
                        class="rounded-lg border px-4 py-2 text-sm font-semibold transition hover:border-[#0fa7b4] hover:text-[#0fa7b4]"
                    >
                        {{ statusLabel(status) }}
                    </button>
                </div>
            </div>

            <div
                class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
            >
                <div class="rounded-2xl border p-5">
                    <p class="text-xs text-muted-foreground">
                        Cliente
                    </p>
                    <p class="mt-2 font-bold">
                        {{ project.client_name }}
                    </p>
                </div>

                <div class="rounded-2xl border p-5">
                    <p class="text-xs text-muted-foreground">
                        Responsable
                    </p>
                    <p class="mt-2 font-bold">
                        {{
                            project.responsible
                            || 'Operador principal'
                        }}
                    </p>
                </div>

                <div class="rounded-2xl border p-5">
                    <p class="text-xs text-muted-foreground">
                        Equipos registrados
                    </p>
                    <p class="mt-2 text-2xl font-bold">
                        {{ project.devices.length }}
                    </p>
                </div>

                <div class="rounded-2xl border p-5">
                    <p class="text-xs text-muted-foreground">
                        Próximo mantenimiento
                    </p>
                    <p class="mt-2 font-bold">
                        {{
                            project.maintenance_due_at
                            || 'Sin programar'
                        }}
                    </p>
                </div>
            </div>

            <div
                class="grid gap-6 lg:grid-cols-2"
            >
                <div class="rounded-2xl border p-6">
                    <div class="flex items-center gap-3">
                        <MapPin
                            class="h-5 w-5 text-[#0fa7b4]"
                        />
                        <h2 class="font-bold">
                            Ubicación
                        </h2>
                    </div>

                    <p class="mt-4 font-semibold">
                        {{
                            project.address
                            || 'Sin dirección'
                        }}
                    </p>

                    <p class="text-sm text-muted-foreground">
                        {{ project.city }}
                    </p>

                    <p class="mt-4 text-sm">
                        Contacto:
                        <strong>
                            {{
                                project.contact_name
                                || project.client_name
                            }}
                        </strong>
                    </p>

                    <p class="text-sm">
                        Teléfono:
                        {{
                            project.contact_phone
                            || 'No registrado'
                        }}
                    </p>
                </div>

                <div class="rounded-2xl border p-6">
                    <h2 class="font-bold">
                        Documentos relacionados
                    </h2>

                    <div class="mt-4 space-y-3 text-sm">
                        <Link
                            v-if="project.quotation_id"
                            :href="`/quotations/${project.quotation_id}`"
                            class="block font-semibold text-[#0fa7b4]"
                        >
                            Cotización:
                            {{ project.quotation_number }}
                        </Link>

                        <Link
                            v-if="project.sale_id"
                            :href="`/sales/${project.sale_id}`"
                            class="block font-semibold text-[#0fa7b4]"
                        >
                            Venta:
                            {{ project.sale_number }}
                        </Link>

                        <Link
                            v-if="project.work_order_id"
                            :href="`/work-orders/${project.work_order_id}`"
                            class="block font-semibold text-[#0fa7b4]"
                        >
                            OT:
                            {{ project.work_order_number }}
                        </Link>

                        <Link
                            v-if="project.installation_id"
                            :href="`/installations/${project.installation_id}`"
                            class="block font-semibold text-[#0fa7b4]"
                        >
                            Instalación:
                            {{ project.installation_number }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- EQUIPOS -->

            <section
                class="overflow-hidden rounded-2xl border bg-background"
            >
                <div class="border-b p-6">
                    <div class="flex items-center gap-3">
                        <Camera
                            class="h-5 w-5 text-[#0fa7b4]"
                        />

                        <div>
                            <h2 class="font-bold">
                                Equipos CCTV
                            </h2>

                            <p class="text-sm text-muted-foreground">
                                Cámaras, grabadores, red,
                                almacenamiento y respaldo.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="project.devices.length"
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full min-w-[950px] text-sm"
                    >
                        <thead class="border-b bg-muted/40">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    Equipo
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Marca / modelo
                                </th>
                                <th class="px-4 py-3 text-left">
                                    IP
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Ubicación
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Serie
                                </th>
                                <th class="px-4 py-3 text-right">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="device in project.devices"
                                :key="device.id"
                                class="border-b last:border-b-0"
                            >
                                <td class="px-4 py-4">
                                    <strong>
                                        {{
                                            deviceTypeLabel(
                                                device.device_type,
                                            )
                                        }}
                                    </strong>

                                    <p
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ device.device_code }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    {{ device.brand || '—' }}
                                    {{ device.model || '' }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ device.ip_address || '—' }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ device.location || '—' }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ device.serial_number || '—' }}
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <button
                                        type="button"
                                        @click="
                                            deleteDevice(
                                                device.id,
                                            )
                                        "
                                        class="text-red-500"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form
                    @submit.prevent="saveDevice"
                    class="grid gap-4 border-t p-6 md:grid-cols-3 xl:grid-cols-4"
                >
                    <select
                        v-model="deviceForm.device_type"
                        class="rounded-lg border bg-background px-3 py-2"
                    >
                        <option value="camera">Cámara</option>
                        <option value="nvr">NVR</option>
                        <option value="dvr">DVR</option>
                        <option value="xvr">XVR</option>
                        <option value="poe_switch">Switch PoE</option>
                        <option value="router">Router</option>
                        <option value="ups">UPS</option>
                        <option value="hdd">Disco duro</option>
                        <option value="monitor">Monitor</option>
                        <option value="other">Otro</option>
                    </select>

                    <input
                        v-model="deviceForm.brand"
                        placeholder="Marca"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="deviceForm.model"
                        placeholder="Modelo"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="deviceForm.serial_number"
                        placeholder="Número de serie"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="deviceForm.ip_address"
                        placeholder="IP"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="deviceForm.mac_address"
                        placeholder="MAC"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="deviceForm.location"
                        placeholder="Ubicación"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="deviceForm.channel"
                        type="number"
                        placeholder="Canal"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0fa7b4] px-4 py-2 font-semibold text-white"
                    >
                        <Plus class="h-4 w-4" />
                        Agregar equipo
                    </button>
                </form>
            </section>

            <!-- CREDENCIALES -->

            <section class="rounded-2xl border p-6">
                <div class="flex items-center gap-3">
                    <KeyRound
                        class="h-5 w-5 text-[#e84657]"
                    />

                    <div>
                        <h2 class="font-bold">
                            Credenciales técnicas
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            Los secretos se almacenan cifrados
                            y no se envían nuevamente al navegador.
                        </p>
                    </div>
                </div>

                <div
                    v-if="project.credentials.length"
                    class="mt-5 space-y-3"
                >
                    <div
                        v-for="credential in project.credentials"
                        :key="credential.id"
                        class="flex items-center justify-between gap-4 rounded-xl border p-4"
                    >
                        <div>
                            <p class="font-semibold">
                                {{ credential.label }}
                            </p>

                            <p class="text-xs text-muted-foreground">
                                Usuario:
                                {{ credential.username || '—' }}
                                ·
                                Contraseña:
                                {{
                                    credential.has_secret
                                    ? '••••••••'
                                    : '—'
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="
                                deleteCredential(
                                    credential.id,
                                )
                            "
                            class="text-red-500"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <form
                    @submit.prevent="saveCredential"
                    class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <select
                        v-model="
                            credentialForm.cctv_device_id
                        "
                        class="rounded-lg border bg-background px-3 py-2"
                    >
                        <option value="">
                            Proyecto general
                        </option>

                        <option
                            v-for="device in project.devices"
                            :key="device.id"
                            :value="device.id"
                        >
                            {{ device.device_code }}
                            —
                            {{
                                deviceTypeLabel(
                                    device.device_type,
                                )
                            }}
                        </option>
                    </select>

                    <select
                        v-model="
                            credentialForm.credential_type
                        "
                        class="rounded-lg border bg-background px-3 py-2"
                    >
                        <option value="device_admin">
                            Administrador equipo
                        </option>
                        <option value="router">
                            Router
                        </option>
                        <option value="wifi">
                            WiFi
                        </option>
                        <option value="cloud">
                            Nube
                        </option>
                        <option value="ddns">
                            DDNS
                        </option>
                        <option value="email">
                            Correo
                        </option>
                        <option value="app">
                            Aplicación
                        </option>
                        <option value="other">
                            Otro
                        </option>
                    </select>

                    <input
                        v-model="credentialForm.label"
                        placeholder="Descripción"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="credentialForm.username"
                        placeholder="Usuario"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="credentialForm.secret_value"
                        type="password"
                        placeholder="Contraseña / secreto"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="credentialForm.host"
                        placeholder="Host / dominio"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="credentialForm.port"
                        type="number"
                        placeholder="Puerto"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <button
                        type="submit"
                        class="rounded-lg bg-[#1d1d1b] px-4 py-2 font-semibold text-white"
                    >
                        Guardar credencial
                    </button>
                </form>
            </section>

            <!-- MANTENIMIENTO -->

            <section class="rounded-2xl border p-6">
                <div class="flex items-center gap-3">
                    <Wrench
                        class="h-5 w-5 text-[#0fa7b4]"
                    />
                    <h2 class="font-bold">
                        Mantenimiento
                    </h2>
                </div>

                <div
                    v-if="project.maintenance.length"
                    class="mt-5 space-y-4"
                >
                    <div
                        v-for="record in project.maintenance"
                        :key="record.id"
                        class="rounded-xl border p-5"
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="font-semibold">
                                    {{
                                        maintenanceTypeLabel(
                                            record.maintenance_type,
                                        )
                                    }}
                                </p>

                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    {{
                                        record.maintenance_date
                                    }}
                                    ·
                                    {{
                                        record.performed_by
                                        || 'Operador principal'
                                    }}
                                </p>
                            </div>

                            <strong>
                                {{ money(record.cost) }}
                            </strong>
                        </div>

                        <p class="mt-3 text-sm">
                            {{ record.description }}
                        </p>

                        <p
                            v-if="record.findings"
                            class="mt-2 text-sm text-muted-foreground"
                        >
                            Hallazgos:
                            {{ record.findings }}
                        </p>

                        <p
                            v-if="record.next_due_date"
                            class="mt-2 text-xs font-semibold text-[#0fa7b4]"
                        >
                            Próximo:
                            {{ record.next_due_date }}
                        </p>
                    </div>
                </div>

                <form
                    @submit.prevent="saveMaintenance"
                    class="mt-6 grid gap-4 md:grid-cols-2"
                >
                    <select
                        v-model="
                            maintenanceForm.maintenance_type
                        "
                        class="rounded-lg border bg-background px-3 py-2"
                    >
                        <option value="preventive">
                            Preventivo
                        </option>
                        <option value="corrective">
                            Correctivo
                        </option>
                        <option value="inspection">
                            Inspección
                        </option>
                        <option value="configuration">
                            Configuración
                        </option>
                    </select>

                    <input
                        v-model="
                            maintenanceForm.maintenance_date
                        "
                        type="date"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <select
                        v-model="
                            maintenanceForm.cctv_device_id
                        "
                        class="rounded-lg border bg-background px-3 py-2"
                    >
                        <option value="">
                            Sistema completo
                        </option>

                        <option
                            v-for="device in project.devices"
                            :key="device.id"
                            :value="device.id"
                        >
                            {{ device.device_code }}
                        </option>
                    </select>

                    <select
                        v-model="
                            maintenanceForm.performed_by_employee_id
                        "
                        class="rounded-lg border bg-background px-3 py-2"
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

                    <textarea
                        v-model="
                            maintenanceForm.description
                        "
                        rows="3"
                        placeholder="Trabajo realizado *"
                        class="rounded-lg border bg-background px-3 py-2 md:col-span-2"
                    />

                    <textarea
                        v-model="
                            maintenanceForm.findings
                        "
                        rows="3"
                        placeholder="Hallazgos"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <textarea
                        v-model="
                            maintenanceForm.actions_taken
                        "
                        rows="3"
                        placeholder="Acciones realizadas"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="maintenanceForm.cost"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="Costo"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <input
                        v-model="
                            maintenanceForm.next_due_date
                        "
                        type="date"
                        class="rounded-lg border bg-background px-3 py-2"
                    />

                    <button
                        type="submit"
                        class="rounded-lg bg-[#0fa7b4] px-5 py-3 font-semibold text-white md:col-span-2"
                    >
                        Registrar mantenimiento
                    </button>
                </form>
            </section>

            <div
                v-if="
                    project.network_notes
                    || project.notes
                "
                class="grid gap-6 lg:grid-cols-2"
            >
                <div
                    v-if="project.network_notes"
                    class="rounded-2xl border p-6"
                >
                    <h2 class="font-bold">
                        Información de red
                    </h2>

                    <p
                        class="mt-3 whitespace-pre-line text-sm"
                    >
                        {{ project.network_notes }}
                    </p>
                </div>

                <div
                    v-if="project.notes"
                    class="rounded-2xl border p-6"
                >
                    <h2 class="font-bold">
                        Notas
                    </h2>

                    <p
                        class="mt-3 whitespace-pre-line text-sm"
                    >
                        {{ project.notes }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>