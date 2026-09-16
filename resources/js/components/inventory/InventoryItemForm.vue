<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Boxes,
    Save,
} from '@lucide/vue';

defineProps<{
    form: any;
    creating: boolean;
}>();

const emit = defineEmits<{
    submit: [];
}>();

const units = [
    'unidad',
    'pulgada',
    'pie',
    'metro',
    'centímetro',
    'm²',
    'pie²',
    'pulgada²',
    'litro',
    'mililitro',
    'rollo',
    'hoja',
    'lámina',
    'caja',
    'paquete',
];
</script>

<template>
    <form
        @submit.prevent="emit('submit')"
        class="space-y-6"
    >
        <div
            class="rounded-2xl border bg-background p-6 shadow-sm"
        >
            <div
                class="mb-6 flex items-center gap-3"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0fa7b4]/10 text-[#0fa7b4]"
                >
                    <Boxes
                        class="h-5 w-5"
                    />
                </div>

                <div>
                    <h2
                        class="font-semibold"
                    >
                        Información del material
                    </h2>

                    <p
                        class="text-sm text-muted-foreground"
                    >
                        Datos generales y control
                        de existencias.
                    </p>
                </div>
            </div>

            <div
                class="grid gap-5 md:grid-cols-2"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Nombre *
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Ej. Vinil adhesivo blanco"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />

                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Categoría
                    </label>

                    <input
                        v-model="form.category"
                        type="text"
                        list="inventory-categories"
                        placeholder="Ej. Viniles"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />

                    <datalist
                        id="inventory-categories"
                    >
                        <option value="Viniles" />
                        <option value="Lonas" />
                        <option value="PVC" />
                        <option value="Acrílicos" />
                        <option value="Tintas" />
                        <option value="Papeles" />
                        <option value="Sublimación" />
                        <option value="Electricidad" />
                        <option value="Iluminación" />
                        <option value="CCTV" />
                        <option value="Ferretería" />
                        <option value="Otros" />
                    </datalist>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Unidad de medida *
                    </label>

                    <input
                        v-model="
                            form.measurement_unit
                        "
                        type="text"
                        list="inventory-units"
                        placeholder="Ej. pie²"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />

                    <datalist
                        id="inventory-units"
                    >
                        <option
                            v-for="unit in units"
                            :key="unit"
                            :value="unit"
                        />
                    </datalist>

                    <p
                        v-if="
                            form.errors
                                .measurement_unit
                        "
                        class="mt-1 text-sm text-red-500"
                    >
                        {{
                            form.errors
                                .measurement_unit
                        }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Stock mínimo
                    </label>

                    <input
                        v-model="
                            form.minimum_stock
                        "
                        type="number"
                        min="0"
                        step="0.001"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div
                    v-if="creating"
                >
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Stock inicial
                    </label>

                    <input
                        v-model="
                            form.initial_stock
                        "
                        type="number"
                        min="0"
                        step="0.001"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />

                    <p
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        Generará automáticamente
                        el primer movimiento de
                        inventario.
                    </p>
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Costo unitario
                    </label>

                    <input
                        v-model="
                            form.unit_cost
                        "
                        type="number"
                        min="0"
                        step="0.0001"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div>
                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Proveedor
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
                        Ubicación
                    </label>

                    <input
                        v-model="
                            form.location
                        "
                        type="text"
                        placeholder="Ej. Bodega A · Estante 2"
                        class="w-full rounded-lg border bg-background px-4 py-2.5"
                    />
                </div>

                <div
                    class="flex items-center gap-3"
                >
                    <input
                        id="inventory-active"
                        v-model="form.active"
                        type="checkbox"
                        class="h-4 w-4"
                    />

                    <label
                        for="inventory-active"
                        class="text-sm font-medium"
                    >
                        Material activo
                    </label>
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
                        v-model="form.notes"
                        rows="4"
                        class="w-full rounded-lg border bg-background px-4 py-3"
                    />
                </div>
            </div>
        </div>

        <div
            class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
        >
            <Link
                href="/inventory"
                class="inline-flex items-center justify-center rounded-lg border px-5 py-2.5 text-sm font-semibold"
            >
                Cancelar
            </Link>

            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0fa7b4] px-6 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
            >
                <Save class="h-4 w-4" />

                {{
                    form.processing
                        ? 'Guardando...'
                        : creating
                          ? 'Crear material'
                          : 'Guardar cambios'
                }}
            </button>
        </div>
    </form>
</template>