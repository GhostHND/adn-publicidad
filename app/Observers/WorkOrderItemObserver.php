<?php

namespace App\Observers;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Services\CctvAutomationService;
use App\Services\InstallationAutomationService;

class WorkOrderItemObserver
{
    public function saved(
        WorkOrderItem $workOrderItem
    ): void {
        $this->sync(
            $workOrderItem
                ->work_order_id
        );
    }

    public function deleted(
        WorkOrderItem $workOrderItem
    ): void {
        $this->sync(
            $workOrderItem
                ->work_order_id
        );
    }

    private function sync(
        int $workOrderId
    ): void {
        $workOrder =
            WorkOrder::query()
                ->find(
                    $workOrderId
                );

        if (!$workOrder) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | INSTALACIONES
        |--------------------------------------------------------------------------
        */

        app(
            InstallationAutomationService::class
        )->syncForWorkOrder(
            $workOrder
        );

        /*
        |--------------------------------------------------------------------------
        | CCTV
        |--------------------------------------------------------------------------
        */

        app(
            CctvAutomationService::class
        )->syncForWorkOrder(
            $workOrder
        );
    }
}