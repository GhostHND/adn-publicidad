<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskEvent;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;

class TaskAutomationService
{
    public function generateForWorkOrder(
        WorkOrder $workOrder
    ): void {
        $workOrder->loadMissing([
            'items.catalogItem.productionSteps',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Si las tareas automáticas todavía no han empezado podemos regenerarlas.
        |
        | Esto permite cambiar la configuración del catálogo y reconstruir una OT
        | pendiente sin duplicar tareas.
        |--------------------------------------------------------------------------
        */

        $automaticTasks =
            Task::query()
                ->withTrashed()
                ->where(
                    'work_order_id',
                    $workOrder->id
                )
                ->whereNotNull(
                    'automation_key'
                )
                ->get();

        $hasStarted =
            Task::query()
                ->where(
                    'work_order_id',
                    $workOrder->id
                )
                ->whereNotNull(
                    'automation_key'
                )
                ->where(
                    function ($query) {
                        $query
                            ->whereNotNull(
                                'started_at'
                            )
                            ->orWhereNotIn(
                                'status',
                                [
                                    'pending',
                                    'blocked',
                                ]
                            );
                    }
                )
                ->exists();

        if (
            !$hasStarted
            &&
            $automaticTasks->isNotEmpty()
        ) {
            foreach (
                $automaticTasks
                as $task
            ) {
                $task->forceDelete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Si alguna tarea ya empezó no reconstruimos el flujo.
        |--------------------------------------------------------------------------
        */

        if ($hasStarted) {
            return;
        }

        $responsibleEmployeeId =
            $workOrder
                ->responsible_employee_id;

        $createdBy =
            $workOrder
                ->created_by
            ?? auth()->id();

        $sortOrder = 10;

        /*
        |--------------------------------------------------------------------------
        | Preparación general
        |--------------------------------------------------------------------------
        */

        $this->createTask(
            workOrder:
                $workOrder,

            workOrderItem:
                null,

            automationKey:
                'automatic-preparation',

            title:
                'Preparar trabajo',

            description:
                'Revisar la orden, archivos, medidas, materiales y requerimientos antes de iniciar la producción.',

            responsibleEmployeeId:
                $responsibleEmployeeId,

            createdBy:
                $createdBy,

            sortOrder:
                $sortOrder,

            status:
                'pending',

            isReviewTask:
                false
        );

        $sortOrder += 10;

        /*
        |--------------------------------------------------------------------------
        | Flujo específico de cada producto
        |--------------------------------------------------------------------------
        */

        foreach (
            $workOrder->items
            as $workOrderItem
        ) {
            $steps =
                $workOrderItem
                    ->catalogItem
                    ?->productionSteps
                    ?->where(
                        'active',
                        true
                    )
                ?? collect();

            /*
            |--------------------------------------------------------------------------
            | Si el producto todavía no tiene flujo configurado usamos fallback.
            |--------------------------------------------------------------------------
            */

            if ($steps->isEmpty()) {
                $this->createTask(
                    workOrder:
                        $workOrder,

                    workOrderItem:
                        $workOrderItem,

                    automationKey:
                        'automatic-item-' .
                        $workOrderItem
                            ->id .
                        '-fallback',

                    title:
                        'Ejecutar: ' .
                        $workOrderItem
                            ->item_name,

                    description:
                        $this
                            ->buildItemDescription(
                                $workOrderItem
                            ),

                    responsibleEmployeeId:
                        $responsibleEmployeeId,

                    createdBy:
                        $createdBy,

                    sortOrder:
                        $sortOrder,

                    status:
                        'blocked',

                    isReviewTask:
                        false
                );

                $sortOrder += 10;

                continue;
            }

            foreach (
                $steps
                as $productionStep
            ) {
                $this->createTask(
                    workOrder:
                        $workOrder,

                    workOrderItem:
                        $workOrderItem,

                    automationKey:
                        'automatic-item-' .
                        $workOrderItem->id .
                        '-step-' .
                        $productionStep->id,

                    title:
                        $workOrderItem
                            ->item_name .
                        ' · ' .
                        $productionStep
                            ->title,

                    description:
                        $this
                            ->buildStepDescription(
                                $workOrderItem,
                                $productionStep
                            ),

                    responsibleEmployeeId:
                        $responsibleEmployeeId,

                    createdBy:
                        $createdBy,

                    sortOrder:
                        $sortOrder,

                    status:
                        'blocked',

                    isReviewTask:
                        false
                );

                $sortOrder += 10;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Revisión final de la orden completa
        |--------------------------------------------------------------------------
        */

        $this->createTask(
            workOrder:
                $workOrder,

            workOrderItem:
                null,

            automationKey:
                'automatic-final-review',

            title:
                'Revisión final',

            description:
                'Verificar calidad, acabado, medidas, cantidad y cumplimiento de los requerimientos antes de entregar el trabajo.',

            responsibleEmployeeId:
                $responsibleEmployeeId,

            createdBy:
                $createdBy,

            sortOrder:
                $sortOrder,

            status:
                'blocked',

            isReviewTask:
                true
        );
    }

    private function createTask(
        WorkOrder $workOrder,
        ?WorkOrderItem $workOrderItem,
        string $automationKey,
        string $title,
        ?string $description,
        ?int $responsibleEmployeeId,
        ?int $createdBy,
        int $sortOrder,
        string $status,
        bool $isReviewTask
    ): Task {
        $task = Task::create([
            'task_number' =>
                $this
                    ->nextTaskNumber(),

            'work_order_id' =>
                $workOrder->id,

            'work_order_item_id' =>
                $workOrderItem?->id,

            'responsible_employee_id' =>
                $responsibleEmployeeId,

            'created_by' =>
                $createdBy,

            'automation_key' =>
                $automationKey,

            'title' =>
                $title,

            'description' =>
                $description,

            'status' =>
                $status,

            'priority' =>
                $workOrder
                    ->priority,

            'sort_order' =>
                $sortOrder,

            'is_review_task' =>
                $isReviewTask,
        ]);

        TaskEvent::create([
            'task_id' =>
                $task->id,

            'user_id' =>
                $createdBy,

            'event_type' =>
                'created_automatically',

            'notes' =>
                'Tarea generada automáticamente desde la configuración del catálogo.',

            'occurred_at' =>
                now(),
        ]);

        return $task;
    }

    private function buildItemDescription(
        WorkOrderItem $item
    ): string {
        $lines = [
            'Ejecutar el trabajo correspondiente a "' .
            $item->item_name .
            '".',

            'Cantidad: ' .
            number_format(
                (float)
                $item->quantity,
                2
            ),
        ];

        if (
            $item->width !== null
            &&
            $item->height !== null
        ) {
            $lines[] =
                'Medidas: ' .
                $item->width .
                ' × ' .
                $item->height .
                (
                    $item
                        ->measurement_unit
                        ? ' ' .
                            $item
                                ->measurement_unit
                        : ''
                );
        }

        if ($item->description) {
            $lines[] =
                'Detalles: ' .
                $item->description;
        }

        return implode(
            PHP_EOL . PHP_EOL,
            $lines
        );
    }

    private function buildStepDescription(
        WorkOrderItem $item,
        object $productionStep
    ): string {
        $lines = [];

        if (
            $productionStep
                ->description
        ) {
            $lines[] =
                $productionStep
                    ->description;
        }

        $lines[] =
            'Producto: ' .
            $item->item_name;

        $lines[] =
            'Cantidad: ' .
            number_format(
                (float)
                $item->quantity,
                2
            );

        if (
            $item->width !== null
            &&
            $item->height !== null
        ) {
            $lines[] =
                'Medidas: ' .
                $item->width .
                ' × ' .
                $item->height .
                (
                    $item
                        ->measurement_unit
                        ? ' ' .
                            $item
                                ->measurement_unit
                        : ''
                );
        }

        if ($item->description) {
            $lines[] =
                'Detalles del trabajo: ' .
                $item->description;
        }

        return implode(
            PHP_EOL . PHP_EOL,
            $lines
        );
    }

    private function nextTaskNumber(): string
    {
        $next =
            (
                Task::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'TAR-' .
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