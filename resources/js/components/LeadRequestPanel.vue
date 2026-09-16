<script setup lang="ts">
import {
    Check,
    Clipboard,
    FileText,
    Minimize2,
    UserRound,
    X,
} from '@lucide/vue';
import {
    onMounted,
    ref,
} from 'vue';

type LeadContext = {
    id: number;
    lead_number: string;
    name: string;
    business_name: string | null;
    phone: string;
    email: string | null;
    service_interest: string | null;
    message: string;
    client_id: number | null;
    quotation_id: number | null;
    created_at: string | null;
};

const lead =
    ref<LeadContext | null>(
        null,
    );

const visible =
    ref(false);

const minimized =
    ref(false);

const copied =
    ref(false);

const clientAutoLoaded =
    ref(false);

const getLeadId = (): number => {
    const params =
        new URLSearchParams(
            window.location.search,
        );

    return Number(
        params.get(
            'lead_id',
        )
        ?? 0,
    );
};

const trySelectClient =
    (): boolean => {
        if (
            !lead.value
            ||
            !lead.value.client_id
        ) {
            return false;
        }

        const clientId =
            String(
                lead.value.client_id,
            );

        const direct =
            document.querySelector(
                'select[name="client_id"], select#client_id, select[data-field="client_id"]',
            ) as HTMLSelectElement
            | null;

        let select =
            direct;

        /*
        |--------------------------------------------------------------------------
        | RESPALDO
        |--------------------------------------------------------------------------
        |
        | Si el formulario no usa name/id, buscamos un select que contenga una
        | opción con el ID exacto del cliente.
        |
        */

        if (!select) {
            const selects =
                Array.from(
                    document.querySelectorAll(
                        'select',
                    ),
                ) as HTMLSelectElement[];

            select =
                selects.find(
                    (candidate) =>
                        Array.from(
                            candidate.options,
                        ).some(
                            (option) =>
                                option.value
                                === clientId,
                        ),
                )
                ?? null;
        }

        if (!select) {
            return false;
        }

        const optionExists =
            Array.from(
                select.options,
            ).some(
                (option) =>
                    option.value
                    === clientId,
            );

        if (!optionExists) {
            return false;
        }

        select.value =
            clientId;

        select.dispatchEvent(
            new Event(
                'input',
                {
                    bubbles: true,
                },
            ),
        );

        select.dispatchEvent(
            new Event(
                'change',
                {
                    bubbles: true,
                },
            ),
        );

        clientAutoLoaded.value =
            true;

        return true;
};

const attemptClientPrefill =
    (): void => {
        let attempts = 0;

        const interval =
            window.setInterval(
                () => {
                    attempts++;

                    if (
                        trySelectClient()
                        ||
                        attempts >= 15
                    ) {
                        window.clearInterval(
                            interval,
                        );
                    }
                },
                250,
            );
    };

const loadLead =
    async (): Promise<void> => {
        if (
            !window.location.pathname.startsWith(
                '/quotations/create',
            )
        ) {
            return;
        }

        const leadId =
            getLeadId();

        if (!leadId) {
            return;
        }

        try {
            const response =
                await fetch(
                    `/notifications/leads/${leadId}`,
                    {
                        headers: {
                            Accept:
                                'application/json',
                        },

                        credentials:
                            'same-origin',
                    },
                );

            if (!response.ok) {
                return;
            }

            lead.value =
                await response.json();

            visible.value =
                true;

            attemptClientPrefill();
        } catch {
            //
        }
    };

const copy = async (
    text: string,
): Promise<void> => {
    await navigator
        .clipboard
        .writeText(
            text,
        );

    copied.value =
        true;

    window.setTimeout(
        () => {
            copied.value =
                false;
        },
        1500,
    );
};

const copyAll =
    async (): Promise<void> => {
        if (!lead.value) {
            return;
        }

        const content = [
            `Solicitud: ${lead.value.lead_number}`,
            `Cliente: ${lead.value.name}`,
            lead.value.business_name
                ? `Empresa: ${lead.value.business_name}`
                : '',
            `Teléfono: ${lead.value.phone}`,
            lead.value.email
                ? `Correo: ${lead.value.email}`
                : '',
            lead.value.service_interest
                ? `Servicio: ${lead.value.service_interest}`
                : '',
            '',
            'Descripción:',
            lead.value.message,
        ]
            .filter(
                (line) =>
                    line !== '',
            )
            .join(
                '\n',
            );

        await copy(
            content,
        );
    };

onMounted(
    loadLead,
);
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-5 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
    >
        <aside
            v-if="
                visible
                &&
                lead
            "
            class="fixed bottom-5 right-5 z-[80] w-[calc(100%-2.5rem)] max-w-md overflow-hidden rounded-2xl border bg-background shadow-2xl"
        >
            <div
                class="h-1 bg-gradient-to-r from-[#0fa7b4] to-[#e84657]"
            />

            <div
                class="flex items-center justify-between border-b px-5 py-4"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-wider text-[#0fa7b4]"
                    >
                        Solicitud original
                    </p>

                    <p
                        class="font-bold"
                    >
                        {{
                            lead.lead_number
                        }}
                    </p>
                </div>

                <div
                    class="flex gap-1"
                >
                    <button
                        type="button"
                        class="rounded-lg p-2 hover:bg-muted"
                        @click="
                            minimized =
                                !minimized
                        "
                    >
                        <Minimize2
                            class="h-4 w-4"
                        />
                    </button>

                    <button
                        type="button"
                        class="rounded-lg p-2 hover:bg-muted"
                        @click="
                            visible =
                                false
                        "
                    >
                        <X
                            class="h-4 w-4"
                        />
                    </button>
                </div>
            </div>

            <div
                v-if="
                    !minimized
                "
                class="max-h-[65vh] overflow-y-auto p-5"
            >
                <div
                    class="flex items-start gap-3"
                >
                    <UserRound
                        class="mt-1 h-5 w-5 shrink-0 text-[#0fa7b4]"
                    />

                    <div>
                        <p
                            class="font-bold"
                        >
                            {{
                                lead.name
                            }}
                        </p>

                        <p
                            v-if="
                                lead.business_name
                            "
                            class="text-sm text-muted-foreground"
                        >
                            {{
                                lead.business_name
                            }}
                        </p>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            {{
                                lead.phone
                            }}
                        </p>

                        <p
                            v-if="
                                lead.email
                            "
                            class="text-sm text-muted-foreground"
                        >
                            {{
                                lead.email
                            }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="
                        lead.service_interest
                    "
                    class="mt-5 rounded-xl bg-[#0fa7b4]/10 p-4"
                >
                    <p
                        class="text-xs font-bold uppercase text-[#0fa7b4]"
                    >
                        Servicio solicitado
                    </p>

                    <p
                        class="mt-1 font-semibold"
                    >
                        {{
                            lead.service_interest
                        }}
                    </p>
                </div>

                <div
                    class="mt-5"
                >
                    <div
                        class="mb-2 flex items-center justify-between"
                    >
                        <p
                            class="flex items-center gap-2 text-sm font-bold"
                        >
                            <FileText
                                class="h-4 w-4"
                            />

                            Detalles del cliente
                        </p>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-[#0fa7b4]"
                            @click="
                                copy(
                                    lead.message,
                                )
                            "
                        >
                            <Clipboard
                                class="h-3.5 w-3.5"
                            />

                            Copiar
                        </button>
                    </div>

                    <div
                        class="whitespace-pre-line rounded-xl bg-muted/40 p-4 text-sm leading-6"
                    >
                        {{
                            lead.message
                        }}
                    </div>
                </div>

                <div
                    v-if="
                        lead.client_id
                    "
                    class="mt-5 rounded-xl border p-3 text-sm"
                >
                    <div
                        class="flex items-center justify-between gap-3"
                    >
                        <span>
                            Cliente registrado:
                            <strong>
                                #{{ lead.client_id }}
                            </strong>
                        </span>

                        <span
                            v-if="
                                clientAutoLoaded
                            "
                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600"
                        >
                            <Check
                                class="h-3.5 w-3.5"
                            />

                            Cargado
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0fa7b4] px-4 py-3 font-semibold text-white"
                    @click="
                        copyAll
                    "
                >
                    <Check
                        v-if="
                            copied
                        "
                        class="h-4 w-4"
                    />

                    <Clipboard
                        v-else
                        class="h-4 w-4"
                    />

                    {{
                        copied
                            ? 'Datos copiados'
                            : 'Copiar todos los datos'
                    }}
                </button>
            </div>
        </aside>
    </transition>
</template>