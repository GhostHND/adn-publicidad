<?php

namespace App\Services;

use App\Models\CctvProject;
use App\Models\Employee;
use App\Models\Installation;
use App\Models\WorkOrder;
use Illuminate\Support\Str;

class CctvAutomationService
{
    public function syncForWorkOrder(
        WorkOrder $workOrder
    ): ?CctvProject {
        $workOrder->load([
            'client',
            'sale',
            'items.catalogItem',
        ]);

        $requiresCctv =
            $this->requiresCctv(
                $workOrder
            );

        $existing =
            CctvProject::withTrashed()
                ->where(
                    'work_order_id',
                    $workOrder->id
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | YA NO ES UN TRABAJO CCTV
        |--------------------------------------------------------------------------
        */

        if (!$requiresCctv) {
            if (
                $existing
                &&
                !$existing->trashed()
                &&
                $existing->source === 'automatic'
                &&
                !in_array(
                    $existing->status,
                    [
                        'installed',
                        'maintenance',
                    ],
                    true
                )
            ) {
                $existing->update([
                    'status' =>
                        'cancelled',

                    'notes' =>
                        trim(
                            ($existing->notes ?? '') .
                            PHP_EOL .
                            'Cancelado automáticamente porque la orden dejó de contener productos CCTV.'
                        ),
                ]);
            }

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | INSTALACIÓN RELACIONADA
        |--------------------------------------------------------------------------
        */

        $installation =
            Installation::query()
                ->where(
                    'work_order_id',
                    $workOrder->id
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | RESPONSABLE
        |--------------------------------------------------------------------------
        */

        $responsibleEmployeeId =
            $workOrder
                ->responsible_employee_id;

        if (!$responsibleEmployeeId) {
            $responsibleEmployeeId =
                $this
                    ->resolveDefaultOperator();
        }

        $client =
            $workOrder
                ->client;

        $sale =
            $workOrder
                ->sale;

        /*
        |--------------------------------------------------------------------------
        | PROYECTO EXISTENTE
        |--------------------------------------------------------------------------
        */

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $data = [
                'client_id' =>
                    $workOrder
                        ->client_id,

                'sale_id' =>
                    $workOrder
                        ->sale_id,

                'quotation_id' =>
                    data_get(
                        $sale,
                        'quotation_id'
                    ),

                'installation_id' =>
                    $installation?->id,

                'responsible_employee_id' =>
                    $responsibleEmployeeId,
            ];

            if (
                $existing->status ===
                'cancelled'
                &&
                $existing->source ===
                'automatic'
            ) {
                $data['status'] =
                    'equipment_pending';
            }

            $existing->update(
                $data
            );

            return $existing
                ->fresh();
        }

        /*
        |--------------------------------------------------------------------------
        | CREAR PROYECTO
        |--------------------------------------------------------------------------
        */

        return CctvProject::create([
            'project_number' =>
                $this
                    ->nextNumber(),

            'client_id' =>
                $workOrder
                    ->client_id,

            'quotation_id' =>
                data_get(
                    $sale,
                    'quotation_id'
                ),

            'sale_id' =>
                $workOrder
                    ->sale_id,

            'work_order_id' =>
                $workOrder
                    ->id,

            'installation_id' =>
                $installation?->id,

            'responsible_employee_id' =>
                $responsibleEmployeeId,

            'created_by' =>
                $workOrder
                    ->created_by,

            'source' =>
                'automatic',

            'system_type' =>
                'unspecified',

            'status' =>
                'equipment_pending',

            'site_name' =>
                $client
                    ?->display_name,

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
                ),

            'notes' =>
                'Proyecto CCTV creado automáticamente desde ' .
                $workOrder
                    ->work_order_number .
                '.',
        ]);
    }

    private function requiresCctv(
        WorkOrder $workOrder
    ): bool {
        foreach (
            $workOrder->items
            as $item
        ) {
            $catalog =
                $item
                    ->catalogItem;

            $text =
                implode(
                    ' ',
                    [
                        $item
                            ->item_name,

                        $item
                            ->description,

                        $catalog
                            ?->name,

                        $catalog
                            ?->description,

                        $catalog
                            ?->category,
                    ]
                );

            $normalized =
                Str::lower(
                    Str::ascii(
                        $text
                    )
                );

            if (
                Str::contains(
                    $normalized,
                    [
                        'cctv',
                        'camara',
                        'camaras',
                        'videovigilancia',
                        'video vigilancia',
                        'nvr',
                        'dvr',
                        'xvr',
                        'vigilancia',
                        'circuito cerrado',
                    ]
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function resolveDefaultOperator(): ?int
    {
        $employee =
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

        if ($employee) {
            return $employee->id;
        }

        $employees =
            Employee::query()
                ->where(
                    'active',
                    true
                )
                ->get();

        if (
            $employees->count()
            === 1
        ) {
            return $employees
                ->first()
                ->id;
        }

        return null;
    }

    private function nextNumber(): string
    {
        $next =
            (
                CctvProject::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'CCTV-' .
            now()
                ->format('Y') .
            '-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}