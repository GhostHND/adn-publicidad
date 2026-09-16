<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    Banknote,
    CreditCard,
    Landmark,
} from '@lucide/vue';

const props = defineProps<{
    form: any;
    movementType: 'income' | 'expense';
    dateField: 'income_date' | 'expense_date';
    cancelHref: string;
    submitLabel: string;
    financialAccounts: any[];
}>();

const emit = defineEmits<{
    submit: [];
}>();

const isIncome = computed(
    () => props.movementType === 'income',
);

const title = computed(() =>
    isIncome.value
        ? 'Información del ingreso'
        : 'Información del gasto',
);

const requiresFinancialAccount =
    computed(
        () =>
            props.form.payment_method
            !== 'cash',
    );

const categoryOptions = computed(() =>
    isIncome.value
        ? [
              'Aporte de capital',
              'Reembolso',
              'Venta de activo',
              'Comisión',
              'Otros ingresos',
          ]
        : [
              'Materiales',
              'Insumos',
              'Transporte',
              'Combustible',
              'Servicios públicos',
              'Alquiler',
              'Mantenimiento',
              'Publicidad',
              'Papelería',
              'Herramientas',
              'Alimentación',
              'Otros gastos',
          ],
);

const categoryListId = computed(() =>
    isIncome.value
        ? 'income-categories'
        : 'expense-categories',
);
</script>

<template>
    <form
        @submit.prevent="emit('submit')"
        class="space-y-6"
    >
        <div
            class="rounded-xl border bg-background p-6 shadow-sm"
        >
            <h2
                class="mb-5 text-lg font-semibold"
            >
                {{ title }}
            </h2>

            <div
                class="grid gap-6 md:grid-cols-2"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Fecha *
                    </label>

                    <input
                        v-model="
                            form[dateField]
                        "
                        type="date"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Categoría *
                    </label>

                    <input
                        v-model="
                            form.category
                        "
                        type="text"
                        :list="
                            categoryListId
                        "
                        placeholder="Seleccionar o escribir..."
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />

                    <datalist
                        :id="categoryListId"
                    >
                        <option
                            v-for="
                                category in categoryOptions
                            "
                            :key="category"
                            :value="category"
                        />
                    </datalist>
                </div>

                <div
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Descripción *
                    </label>

                    <input
                        v-model="
                            form.description
                        "
                        type="text"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div
                    v-if="!isIncome"
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Proveedor /
                        beneficiario
                    </label>

                    <input
                        v-model="
                            form.supplier
                        "
                        type="text"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Monto *
                    </label>

                    <input
                        v-model="
                            form.amount
                        "
                        type="number"
                        min="0.01"
                        step="0.01"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />

                    <p
                        v-if="
                            form.errors
                                .amount
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors
                                .amount
                        }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Método *
                    </label>

                    <select
                        v-model="
                            form.payment_method
                        "
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    >
                        <option value="cash">
                            Efectivo
                        </option>

                        <option
                            value="transfer"
                        >
                            Transferencia
                        </option>

                        <option value="card">
                            Tarjeta
                        </option>

                        <option value="other">
                            Otro
                        </option>
                    </select>
                </div>

                <div
                    v-if="
                        requiresFinancialAccount
                    "
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Cuenta / banco *
                    </label>

                    <select
                        v-model="
                            form.financial_account_id
                        "
                        class="w-full rounded-lg border bg-background px-4 py-2.5 outline-none transition focus:border-[#0fa7b4] focus:ring-2 focus:ring-[#0fa7b4]/20"
                    >
                        <option value="">
                            Seleccionar cuenta
                        </option>

                        <option
                            v-for="
                                account in financialAccounts
                            "
                            :key="
                                account.id
                            "
                            :value="
                                account.id
                            "
                        >
                            {{
                                account.name
                            }}
                        </option>
                    </select>

                    <p
                        v-if="
                            form.errors
                                .financial_account_id
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors
                                .financial_account_id
                        }}
                    </p>
                </div>

                <div
                    class="md:col-span-2"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Referencia
                    </label>

                    <input
                        v-model="
                            form.reference
                        "
                        type="text"
                        placeholder="Transferencia, comprobante, autorización..."
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>
            </div>

            <div
                v-if="
                    form.payment_method ===
                    'cash'
                "
                class="mt-6 flex gap-3 rounded-xl border border-[#0fa7b4]/30 bg-[#0fa7b4]/5 p-4"
            >
                <Banknote
                    class="h-5 w-5 shrink-0 text-[#0fa7b4]"
                />

                <p class="text-sm">
                    Este movimiento
                    afectará la caja
                    física abierta.
                </p>
            </div>

            <div
                v-else-if="
                    form.payment_method ===
                    'transfer'
                "
                class="mt-6 flex gap-3 rounded-xl border p-4"
            >
                <Landmark
                    class="h-5 w-5 shrink-0"
                />

                <p class="text-sm">
                    Este movimiento
                    afectará la cuenta
                    bancaria seleccionada,
                    no la caja física.
                </p>
            </div>

            <div
                v-else-if="
                    form.payment_method ===
                    'card'
                "
                class="mt-6 flex gap-3 rounded-xl border p-4"
            >
                <CreditCard
                    class="h-5 w-5 shrink-0"
                />

                <p class="text-sm">
                    El movimiento con
                    tarjeta se asociará a
                    la cuenta financiera
                    seleccionada.
                </p>
            </div>
        </div>

        <div
            class="rounded-xl border bg-background p-6 shadow-sm"
        >
            <label
                class="mb-2 block text-sm font-medium"
            >
                Notas
            </label>

            <textarea
                v-model="form.notes"
                rows="4"
                class="w-full rounded-lg border bg-background px-4 py-3"
            />
        </div>

        <div
            class="flex justify-end gap-3"
        >
            <Link
                :href="cancelHref"
                class="rounded-lg border px-5 py-2.5 text-sm font-semibold"
            >
                Cancelar
            </Link>

            <button
                type="submit"
                :disabled="
                    form.processing
                "
                class="rounded-lg px-6 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
                :class="
                    isIncome
                        ? 'bg-[#0fa7b4]'
                        : 'bg-[#e84657]'
                "
            >
                {{
                    form.processing
                        ? 'Guardando...'
                        : submitLabel
                }}
            </button>
        </div>
    </form>
</template>