<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Installation;
use App\Models\WorkOrder;
use Illuminate\Support\Str;

class InstallationAutomationService
{
    public function syncForWorkOrder(
        WorkOrder $workOrder
    ): ?Installation {
        $workOrder->load([
            'client',
            'responsibleEmployee',
            'items.catalogItem.productionSteps',
        ]);

        $requiresInstallation =
            $this->requiresInstallation(
                $workOrder
            );

        $existing =
            Installation::withTrashed()
                ->where(
                    'work_order_id',
                    $workOrder->id
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | YA NO REQUIERE INSTALACIÓN
        |--------------------------------------------------------------------------
        */

        if (!$requiresInstallation) {
            if (
                $existing
                &&
                !$existing->trashed()
                &&
                $existing->source === 'automatic'
                &&
                in_array(
                    $existing->status,
                    [
                        'pending_schedule',
                        'scheduled',
                    ],
                    true
                )
                &&
                !$existing->started_at
            ) {
                $existing->update([
                    'status' => 'cancelled',

                    'notes' =>
                        trim(
                            ($existing->notes ?? '') .
                            PHP_EOL .
                            'Cancelada automáticamente porque la orden ya no contiene productos que requieran instalación.'
                        ),
                ]);
            }

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | SI YA EXISTE
        |--------------------------------------------------------------------------
        */

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            if (
                $existing->status === 'cancelled'
                &&
                $existing->source === 'automatic'
                &&
                !$existing->started_at
            ) {
                $existing->update([
                    'status' =>
                        $existing->scheduled_at
                            ? 'scheduled'
                            : 'pending_schedule',
                ]);
            }

            return $existing;
        }

        /*
        |--------------------------------------------------------------------------
        | RESOLVER RESPONSABLE
        |--------------------------------------------------------------------------
        */

        $responsibleEmployeeId =
            $workOrder
                ->responsible_employee_id;

        if (!$responsibleEmployeeId) {
            $defaultOperator =
                Employee::query()
                    ->where(
                        'active',
                        true
                    )
                    ->where(
                        'is_default_operator',
                        true
                    )
                    ->first();

            if (!$defaultOperator) {
                $activeEmployees =
                    Employee::query()
                        ->where(
                            'active',
                            true
                        )
                        ->get();

                if (
                    $activeEmployees->count()
                    === 1
                ) {
                    $defaultOperator =
                        $activeEmployees
                            ->first();
                }
            }

            $responsibleEmployeeId =
                $defaultOperator?->id;
        }

        $client =
            $workOrder
                ->client;

        /*
        |--------------------------------------------------------------------------
        | CREAR INSTALACIÓN
        |--------------------------------------------------------------------------
        */

        return Installation::create([
            'installation_number' =>
                $this
                    ->nextNumber(),

            'work_order_id' =>
                $workOrder->id,

            'client_id' =>
                $workOrder
                    ->client_id,

            'responsible_employee_id' =>
                $responsibleEmployeeId,

            'created_by' =>
                $workOrder
                    ->created_by,

            'source' =>
                'automatic',

            'status' =>
                'pending_schedule',

            'contact_name' =>
                $client
                    ?->display_name,

            'contact_phone' =>
                data_get(
                    $client,
                    'phone'
                )
                ??
                data_get(
                    $client,
                    'mobile'
                )
                ??
                data_get(
                    $client,
                    'phone_secondary'
                ),

            'address' =>
                data_get(
                    $client,
                    'address'
                ),

            'city' =>
                data_get(
                    $client,
                    'city'
                ),

            'notes' =>
                'Instalación creada automáticamente desde ' .
                $workOrder
                    ->work_order_number .
                '.',
        ]);
    }

    private function requiresInstallation(
        WorkOrder $workOrder
    ): bool {
        foreach (
            $workOrder->items
            as $item
        ) {
            $catalogItem =
                $item
                    ->catalogItem;

            if (!$catalogItem) {
                continue;
            }

            foreach (
                $catalogItem
                    ->productionSteps
                as $step
            ) {
                if (!$step->active) {
                    continue;
                }

                if (
                    $this->isInstallationStep(
                        $step->title
                    )
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isInstallationStep(
        ?string $title
    ): bool {
        if (!$title) {
            return false;
        }

        $normalized =
            Str::lower(
                Str::ascii(
                    $title
                )
            );

        return Str::contains(
            $normalized,
            [
                'instalacion',
                'instalar',
                'instalado',
                'montaje',
                'montar',
                'colocacion',
                'colocar',
            ]
        );
    }

    private function nextNumber(): string
    {
        $next =
            (
                Installation::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'INS-' .
            now()->format('Y') .
            '-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}