<?php

namespace App\Services;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class WorkOrderExecutionService
{
    public function __construct(
        private InventoryReservationService $reservationService
    ) {
    }

    public function start(
        WorkOrder $workOrder,
        ?int $userId = null
    ): WorkOrder {
        return DB::transaction(
            function () use (
                $workOrder,
                $userId
            ) {
                $lockedWorkOrder =
                    WorkOrder::query()
                        ->whereKey(
                            $workOrder->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Primer inicio de producción
                |--------------------------------------------------------------------------
                */

                if (
                    !$lockedWorkOrder
                        ->started_at
                ) {
                    $this
                        ->reservationService
                        ->consumeForWorkOrder(
                            $lockedWorkOrder,
                            $userId
                        );

                    $lockedWorkOrder
                        ->started_at =
                        now();
                }

                $lockedWorkOrder
                    ->status =
                    'in_progress';

                $lockedWorkOrder
                    ->save();

                return $lockedWorkOrder;
            }
        );
    }
}