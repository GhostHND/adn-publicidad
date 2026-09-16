<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';
import { useMobileAccordion } from '@/composables/useMobileAccordion';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    BriefcaseBusiness,
    Mail,
    Pencil,
    Phone,
    Plus,
    ShieldCheck,
    UserRound,
} from '@lucide/vue';
import { computed } from 'vue';

type Employee = {
    id: number;
    employee_code?: string | null;
    code?: string | null;
    full_name?: string | null;
    name?: string | null;
    first_name?: string | null;
    middle_name?: string | null;
    last_name?: string | null;
    second_last_name?: string | null;
    position?: string | null;
    phone?: string | null;
    alternate_phone?: string | null;
    email?: string | null;
    hire_date?: string | null;
    active?: boolean | number | null;
    is_default_operator?: boolean | number | null;
};

type CollectionProp = {
    data?: Employee[];
};

const props = defineProps<{
    employees:
        Employee[]
        | CollectionProp;
}>();

const {
    toggleCard,
    isExpanded,
} = useMobileAccordion();

const breadcrumbs = [
    {
        title: 'Empleados',
        href: '/employees',
    },
];

const employees =
    computed<Employee[]>(() => {
        if (
            Array.isArray(
                props.employees,
            )
        ) {
            return props.employees;
        }

        return props.employees?.data
            ?? [];
    });

const activeCount =
    computed(() => {
        return employees.value.filter(
            (employee) =>
                Boolean(
                    employee.active,
                ),
        ).length;
    });

const employeeName = (
    employee: Employee,
): string => {
    if (employee.full_name) {
        return employee.full_name;
    }

    if (employee.name) {
        return employee.name;
    }

    return [
        employee.first_name,
        employee.middle_name,
        employee.last_name,
        employee.second_last_name,
    ]
        .filter(Boolean)
        .join(' ')
        .trim()
        ||
        'Empleado sin nombre';
};

const employeeCode = (
    employee: Employee,
): string => {
    return (
        employee.employee_code
        ||
        employee.code
        ||
        `EMP-${String(
            employee.id,
        ).padStart(
            4,
            '0',
        )}`
    );
};

const fieldsFor = (
    employee: Employee,
): MobileRecordField[] => {
    return [
        {
            label: 'Cargo',
            value:
                employee.position
                ?? 'Sin cargo',
            wide: true,
        },
        {
            label: 'Teléfono',
            value:
                employee.phone
                ?? '—',
            copyable:
                Boolean(
                    employee.phone,
                ),
        },
        {
            label: 'Alternativo',
            value:
                employee.alternate_phone
                ?? '—',
            copyable:
                Boolean(
                    employee.alternate_phone,
                ),
        },
        {
            label: 'Correo',
            value:
                employee.email
                ?? '—',
            wide: true,
            copyable:
                Boolean(
                    employee.email,
                ),
        },
        {
            label: 'Ingreso',
            value:
                employee.hire_date
                ?? '—',
            wide: true,
        },
    ];
};
</script>

<template>
    <Head title="Empleados" />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <section
                class="adn-enter relative overflow-hidden rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <BriefcaseBusiness
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Equipo ADN
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Empleados
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Administra responsables y personal del sistema.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/employees/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white"
                    >
                        <Plus class="h-4 w-4" />
                        Nuevo empleado
                    </Link>
                </div>
            </section>

            <div
                class="grid grid-cols-2 gap-3 md:max-w-md"
            >
                <div
                    class="rounded-2xl border border-white/[0.07] bg-[#091317] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wider text-white/30"
                    >
                        Registrados
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ employees.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-emerald-500/10 bg-emerald-500/[0.04] p-4"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-wider text-emerald-400"
                    >
                        Activos
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{ activeCount }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <div
                    v-if="
                        employees.length ===
                        0
                    "
                    class="rounded-2xl border border-dashed border-white/10 p-10 text-center"
                >
                    <UserRound
                        class="mx-auto h-8 w-8 text-white/25"
                    />

                    <p
                        class="mt-3 font-bold"
                    >
                        No hay empleados
                    </p>
                </div>

                <MobileRecordCard
                    v-for="
                        employee in employees
                    "
                    :key="employee.id"
                    :title="
                        employeeName(
                            employee,
                        )
                    "
                    :code="
                        employeeCode(
                            employee,
                        )
                    "
                    :subtitle="
                        employee.position
                        ?? 'Personal ADN'
                    "
                    :status="
                        employee.active
                            ? 'Activo'
                            : 'Inactivo'
                    "
                    :status-tone="
                        employee.active
                            ? 'success'
                            : 'neutral'
                    "
                    :fields="
                        fieldsFor(
                            employee,
                        )
                    "
                    :expanded="
                        isExpanded(
                            employee.id,
                        )
                    "
                    @toggle="
                        toggleCard(
                            employee.id,
                        )
                    "
                >
                    <template #badges>
                        <span
                            v-if="
                                employee.is_default_operator
                            "
                            class="inline-flex items-center gap-1 rounded-full border border-[#0fa7b4]/20 bg-[#0fa7b4]/10 px-2 py-1 text-[8px] font-black uppercase text-[#22c6d2]"
                        >
                            <ShieldCheck
                                class="h-3 w-3"
                            />
                            Principal
                        </span>
                    </template>

                    <template #actions>
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/employees/${employee.id}/edit`
                                "
                                class="col-span-2 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white"
                            >
                                <Pencil
                                    class="h-4 w-4"
                                />
                                Editar empleado
                            </Link>

                            <a
                                v-if="employee.phone"
                                :href="
                                    `tel:${employee.phone}`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold text-white/70"
                            >
                                <Phone
                                    class="h-3.5 w-3.5 text-[#0fa7b4]"
                                />
                                Llamar
                            </a>

                            <a
                                v-if="employee.email"
                                :href="
                                    `mailto:${employee.email}`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 text-xs font-bold text-white/70"
                            >
                                <Mail
                                    class="h-3.5 w-3.5 text-[#e84657]"
                                />
                                Correo
                            </a>
                        </div>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ESCRITORIO -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="px-5 py-4 text-left">
                                    Código
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Empleado
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Cargo
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Teléfono
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Correo
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Responsable
                                </th>
                                <th class="px-5 py-4 text-left">
                                    Estado
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-white/[0.06]"
                        >
                            <tr
                                v-for="
                                    employee in employees
                                "
                                :key="employee.id"
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        employeeCode(
                                            employee,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{
                                        employeeName(
                                            employee,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        employee.position
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        employee.phone
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        employee.email
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        v-if="
                                            employee.is_default_operator
                                        "
                                        class="rounded-full bg-[#0fa7b4]/10 px-3 py-1 text-[10px] font-black text-[#22c6d2]"
                                    >
                                        Principal
                                    </span>

                                    <span v-else>
                                        —
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black"
                                        :class="
                                            employee.active
                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                                : 'border-white/10 bg-white/5 text-white/40'
                                        "
                                    >
                                        {{
                                            employee.active
                                                ? 'Activo'
                                                : 'Inactivo'
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4 text-right"
                                >
                                    <Link
                                        :href="
                                            `/employees/${employee.id}/edit`
                                        "
                                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-2 font-bold hover:bg-[#0fa7b4]/10"
                                    >
                                        <Pencil
                                            class="h-4 w-4"
                                        />
                                        Editar
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>