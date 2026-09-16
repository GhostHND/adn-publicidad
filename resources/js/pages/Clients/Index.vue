<script setup lang="ts">
import MobileRecordCard, {
    type MobileRecordField,
} from '@/components/mobile/MobileRecordCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Head,
    Link,
} from '@inertiajs/vue3';
import {
    Mail,
    Pencil,
    Phone,
    Plus,
    UserRound,
    UsersRound,
} from '@lucide/vue';
import {
    computed,
    ref,
} from 'vue';

type Client = {
    id: number;

    client_code?: string | null;
    code?: string | null;

    display_name?: string | null;
    name?: string | null;

    first_name?: string | null;
    last_name?: string | null;
    business_name?: string | null;

    client_type?: string | null;
    type?: string | null;

    phone?: string | null;
    whatsapp?: string | null;
    email?: string | null;

    city?: string | null;
    department_region?: string | null;

    active?: boolean | number | null;
};

type CollectionProp = {
    data?: Client[];
};

const props = defineProps<{
    clients:
        Client[]
        | CollectionProp;
}>();

const breadcrumbs = [
    {
        title:
            'Clientes',

        href:
            '/clients',
    },
];

/*
|--------------------------------------------------------------------------
| ACORDEÓN MÓVIL
|--------------------------------------------------------------------------
|
| null = todas cerradas.
|
*/

const expandedId =
    ref<number | null>(
        null,
    );

const toggleCard = (
    id: number,
): void => {
    expandedId.value =
        expandedId.value ===
        id
            ? null
            : id;
};

const clients =
    computed<Client[]>(() => {
        if (
            Array.isArray(
                props.clients,
            )
        ) {
            return props.clients;
        }

        return props.clients
            ?.data
            ?? [];
    });

const activeCount =
    computed(() => {
        return clients.value.filter(
            (client) =>
                Boolean(
                    client.active,
                ),
        ).length;
    });

const clientName = (
    client: Client,
): string => {
    if (
        client.display_name
    ) {
        return client.display_name;
    }

    if (
        client.name
    ) {
        return client.name;
    }

    if (
        client.business_name
    ) {
        return client.business_name;
    }

    const personName = [
        client.first_name,
        client.last_name,
    ]
        .filter(Boolean)
        .join(' ')
        .trim();

    return personName
        || 'Cliente sin nombre';
};

const clientCode = (
    client: Client,
): string => {
    return (
        client.client_code
        ||
        client.code
        ||
        `CLI-${String(
            client.id,
        ).padStart(
            4,
            '0',
        )}`
    );
};

const typeLabel = (
    value?:
        string
        | null,
): string => {
    if (!value) {
        return 'Cliente';
    }

    const labels:
        Record<string, string> = {
        PERSON:
            'Persona natural',

        NATURAL:
            'Persona natural',

        natural:
            'Persona natural',

        person:
            'Persona natural',

        COMPANY:
            'Empresa',

        BUSINESS:
            'Empresa',

        business:
            'Empresa',

        company:
            'Empresa',

        INSTITUTION:
            'Institución',

        institution:
            'Institución',

        OCCASIONAL:
            'Ocasional',

        occasional:
            'Ocasional',

        FINAL_CONSUMER:
            'Consumidor final',
    };

    return (
        labels[value]
        ??
        value
    );
};

const fieldsFor = (
    client: Client,
): MobileRecordField[] => {
    return [
        {
            label:
                'Tipo',

            value:
                typeLabel(
                    client.client_type
                    ??
                    client.type,
                ),
        },

        {
            label:
                'Estado',

            value:
                client.active
                    ? 'Activo'
                    : 'Inactivo',
        },

        {
            label:
                'Teléfono',

            value:
                client.phone
                ?? '—',

            copyable:
                Boolean(
                    client.phone,
                ),
        },

        {
            label:
                'WhatsApp',

            value:
                client.whatsapp
                ??
                client.phone
                ??
                '—',

            copyable:
                Boolean(
                    client.whatsapp
                    ??
                    client.phone,
                ),
        },

        {
            label:
                'Correo',

            value:
                client.email
                ?? '—',

            wide:
                true,

            copyable:
                Boolean(
                    client.email,
                ),
        },

        {
            label:
                'Ubicación',

            value: [
                client.city,
                client.department_region,
            ]
                .filter(Boolean)
                .join(', ')
                ||
                '—',

            wide:
                true,
        },
    ];
};

const whatsappUrl = (
    client: Client,
): string | null => {
    const number =
        String(
            client.whatsapp
            ??
            client.phone
            ??
            '',
        ).replace(
            /\D/g,
            '',
        );

    if (!number) {
        return null;
    }

    const normalized =
        number.length ===
        8
            ? `504${number}`
            : number;

    return `https://wa.me/${normalized}`;
};
</script>

<template>
    <Head
        title="Clientes"
    />

    <AppLayout
        :breadcrumbs="
            breadcrumbs
        "
    >
        <div
            class="w-full space-y-5 p-4 sm:p-6 lg:p-8"
        >
            <section
                class="adn-enter relative overflow-hidden rounded-[1.7rem] border border-white/[0.07] bg-[#091317] p-5 sm:p-6"
            >
                <div
                    class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-[#0fa7b4]/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0fa7b4]/10 text-[#22c6d2]"
                        >
                            <UsersRound
                                class="h-5 w-5"
                            />
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-[#0fa7b4]"
                            >
                                Base comercial
                            </p>

                            <h1
                                class="mt-1 text-2xl font-black sm:text-3xl"
                            >
                                Clientes
                            </h1>

                            <p
                                class="mt-1 text-sm text-muted-foreground"
                            >
                                Administra los clientes de ADN Publicidad.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/clients/create"
                        class="adn-shine inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-5 py-3 text-sm font-black text-white shadow-[0_10px_28px_rgba(15,167,180,.2)]"
                    >
                        <Plus
                            class="h-4 w-4"
                        />

                        Nuevo cliente
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
                        Total
                    </p>

                    <p
                        class="mt-1 text-2xl font-black"
                    >
                        {{
                            clients.length
                        }}
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
                        {{
                            activeCount
                        }}
                    </p>
                </div>
            </div>

            <!-- MÓVIL -->

            <section
                class="grid gap-2.5 md:hidden"
            >
                <div
                    v-if="
                        clients.length ===
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
                        No hay clientes
                    </p>
                </div>

                <MobileRecordCard
                    v-for="
                        client in clients
                    "
                    :key="
                        client.id
                    "
                    :title="
                        clientName(
                            client,
                        )
                    "
                    :code="
                        clientCode(
                            client,
                        )
                    "
                    :subtitle="
                        typeLabel(
                            client.client_type
                            ??
                            client.type,
                        )
                    "
                    :status="
                        client.active
                            ? 'Activo'
                            : 'Inactivo'
                    "
                    :status-tone="
                        client.active
                            ? 'success'
                            : 'neutral'
                    "
                    :fields="
                        fieldsFor(
                            client,
                        )
                    "
                    :expanded="
                        expandedId ===
                        client.id
                    "
                    @toggle="
                        toggleCard(
                            client.id,
                        )
                    "
                >
                    <template
                        #actions
                    >
                        <div
                            class="grid grid-cols-2 gap-2"
                        >
                            <Link
                                :href="
                                    `/clients/${client.id}/edit`
                                "
                                class="col-span-2 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] text-sm font-black text-white"
                            >
                                <Pencil
                                    class="h-4 w-4"
                                />

                                Editar cliente
                            </Link>

                            <a
                                v-if="
                                    client.phone
                                "
                                :href="
                                    `tel:${client.phone}`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.03] text-xs font-bold text-white/70"
                            >
                                <Phone
                                    class="h-3.5 w-3.5 text-[#0fa7b4]"
                                />

                                Llamar
                            </a>

                            <a
                                v-if="
                                    client.email
                                "
                                :href="
                                    `mailto:${client.email}`
                                "
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/[0.03] text-xs font-bold text-white/70"
                            >
                                <Mail
                                    class="h-3.5 w-3.5 text-[#e84657]"
                                />

                                Correo
                            </a>

                            <a
                                v-if="
                                    whatsappUrl(
                                        client,
                                    )
                                "
                                :href="
                                    whatsappUrl(
                                        client,
                                    )
                                    ?? undefined
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                                class="col-span-2 inline-flex h-10 items-center justify-center rounded-xl border border-emerald-500/15 bg-emerald-500/[0.06] text-xs font-bold text-emerald-400"
                            >
                                WhatsApp
                            </a>
                        </div>
                    </template>
                </MobileRecordCard>
            </section>

            <!-- ESCRITORIO -->

            <section
                class="hidden overflow-hidden rounded-2xl border border-white/[0.07] bg-[#081115] md:block"
            >
                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full text-sm"
                    >
                        <thead>
                            <tr>
                                <th class="px-5 py-4 text-left">
                                    Código
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Cliente
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Tipo
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Teléfono
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Correo
                                </th>

                                <th class="px-5 py-4 text-left">
                                    Ciudad
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
                                    client in clients
                                "
                                :key="
                                    client.id
                                "
                            >
                                <td
                                    class="px-5 py-4 font-black text-[#22c6d2]"
                                >
                                    {{
                                        clientCode(
                                            client,
                                        )
                                    }}
                                </td>

                                <td
                                    class="px-5 py-4 font-bold"
                                >
                                    {{
                                        clientName(
                                            client,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        typeLabel(
                                            client.client_type
                                            ??
                                            client.type,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        client.phone
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        client.email
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    {{
                                        client.city
                                        ?? '—'
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full border px-3 py-1 text-[10px] font-black"
                                        :class="
                                            client.active
                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                                : 'border-white/10 bg-white/5 text-white/40'
                                        "
                                    >
                                        {{
                                            client.active
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
                                            `/clients/${client.id}/edit`
                                        "
                                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-4 py-2 font-bold hover:border-[#0fa7b4]/30 hover:bg-[#0fa7b4]/10"
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