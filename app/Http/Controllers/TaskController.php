<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskEvent;
use App\Models\WorkOrder;
use App\Services\WorkOrderExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private WorkOrderExecutionService $workOrderExecutionService
    ) {
    }

    public function index(): Response
    {
        $tasks = Task::query()
            ->with([
                'workOrder.client',
                'responsibleEmployee',
            ])
            ->orderByRaw("
                CASE status
                    WHEN 'in_progress' THEN 1
                    WHEN 'pending' THEN 2
                    WHEN 'issue' THEN 3
                    WHEN 'paused' THEN 4
                    WHEN 'review' THEN 5
                    WHEN 'blocked' THEN 6
                    WHEN 'completed' THEN 7
                    ELSE 8
                END
            ")
            ->orderBy('sort_order')
            ->latest('id')
            ->get()
            ->map(
                fn (Task $task) =>
                    $this->taskForIndex(
                        $task
                    )
            )
            ->values();

        return Inertia::render(
            'Tasks/Index',
            [
                'tasks' =>
                    $tasks,

                'summary' => [
                    'pending' =>
                        Task::query()
                            ->where(
                                'status',
                                'pending'
                            )
                            ->count(),

                    'in_progress' =>
                        Task::query()
                            ->where(
                                'status',
                                'in_progress'
                            )
                            ->count(),

                    'blocked' =>
                        Task::query()
                            ->where(
                                'status',
                                'blocked'
                            )
                            ->count(),

                    'completed' =>
                        Task::query()
                            ->where(
                                'status',
                                'completed'
                            )
                            ->count(),
                ],
            ]
        );
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('tasks.index')
            ->with(
                'success',
                'Las tareas de producción se generan automáticamente desde las órdenes de trabajo.'
            );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        return redirect()
            ->route('tasks.index');
    }

    public function show(
        Task $task
    ): Response {
        $task->load([
            'workOrder.client',
            'responsibleEmployee',
            'events.user',
        ]);

        return Inertia::render(
            'Tasks/Show',
            [
                'task' =>
                    $this
                        ->taskForFrontend(
                            $task
                        ),
            ]
        );
    }

    public function edit(
        Task $task
    ): RedirectResponse {
        return redirect()
            ->route(
                'tasks.show',
                $task
            );
    }

    public function update(
        Request $request,
        Task $task
    ): RedirectResponse {
        return redirect()
            ->route(
                'tasks.show',
                $task
            );
    }

    public function start(
        Task $task
    ): RedirectResponse {
        if (
            $task->status !==
            'pending'
        ) {
            return back()->withErrors([
                'task' =>
                    'Esta tarea no está disponible para iniciar.',
            ]);
        }

        $previousPending =
            Task::query()
                ->where(
                    'work_order_id',
                    $task->work_order_id
                )
                ->where(
                    'sort_order',
                    '<',
                    $task->sort_order
                )
                ->where(
                    'status',
                    '!=',
                    'completed'
                )
                ->exists();

        if ($previousPending) {
            return back()->withErrors([
                'task' =>
                    'Primero debes finalizar las tareas anteriores de esta orden.',
            ]);
        }

        DB::transaction(
            function () use ($task) {
                $task->load(
                    'workOrder'
                );

                $this
                    ->workOrderExecutionService
                    ->start(
                        $task->workOrder,
                        auth()->id()
                    );

                $task->update([
                    'status' =>
                        'in_progress',

                    'started_at' =>
                        $task->started_at
                        ?? now(),

                    'paused_at' =>
                        null,
                ]);

                $this->event(
                    $task,
                    'started',
                    'Tarea iniciada.'
                );
            }
        );

        return back()->with(
            'success',
            'Tarea iniciada.'
        );
    }

    public function pause(
        Task $task
    ): RedirectResponse {
        if (
            $task->status !==
            'in_progress'
        ) {
            return back()->withErrors([
                'task' =>
                    'Solo puedes pausar una tarea en progreso.',
            ]);
        }

        DB::transaction(
            function () use ($task) {
                $task->update([
                    'status' =>
                        'paused',

                    'paused_at' =>
                        now(),
                ]);

                $task->workOrder()
                    ->update([
                        'status' =>
                            'paused',
                    ]);

                $this->event(
                    $task,
                    'paused',
                    'Tarea pausada.'
                );
            }
        );

        return back()->with(
            'success',
            'Tarea pausada.'
        );
    }

    public function resume(
        Task $task
    ): RedirectResponse {
        if (
            !in_array(
                $task->status,
                [
                    'paused',
                    'issue',
                    'review',
                ],
                true
            )
        ) {
            return back()->withErrors([
                'task' =>
                    'Esta tarea no puede reanudarse.',
            ]);
        }

        DB::transaction(
            function () use ($task) {
                $task->update([
                    'status' =>
                        'in_progress',

                    'paused_at' =>
                        null,

                    'issue_description' =>
                        null,

                    'issue_reported_at' =>
                        null,
                ]);

                $task->workOrder()
                    ->update([
                        'status' =>
                            'in_progress',
                    ]);

                $this->event(
                    $task,
                    'resumed',
                    'Tarea reanudada.'
                );
            }
        );

        return back()->with(
            'success',
            'Tarea reanudada.'
        );
    }

    public function finish(
        Task $task
    ): RedirectResponse {
        if (
            !in_array(
                $task->status,
                [
                    'in_progress',
                    'review',
                ],
                true
            )
        ) {
            return back()->withErrors([
                'task' =>
                    'Esta tarea no puede finalizarse.',
            ]);
        }

        DB::transaction(
            function () use ($task) {
                $task->update([
                    'status' =>
                        'completed',

                    'completed_at' =>
                        now(),
                ]);

                $this->event(
                    $task,
                    'completed',
                    'Tarea finalizada.'
                );

                /*
                |--------------------------------------------------------------------------
                | HABILITAR AUTOMÁTICAMENTE LA SIGUIENTE TAREA
                |--------------------------------------------------------------------------
                */

                $nextTask =
                    Task::query()
                        ->where(
                            'work_order_id',
                            $task
                                ->work_order_id
                        )
                        ->where(
                            'sort_order',
                            '>',
                            $task
                                ->sort_order
                        )
                        ->where(
                            'status',
                            'blocked'
                        )
                        ->orderBy(
                            'sort_order'
                        )
                        ->first();

                if ($nextTask) {
                    $nextTask->update([
                        'status' =>
                            'pending',
                    ]);

                    $this->event(
                        $nextTask,
                        'unblocked',
                        'Tarea habilitada automáticamente al finalizar la tarea anterior.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | SI TODAS TERMINARON → ORDEN FINALIZADA
                |--------------------------------------------------------------------------
                */

                $remainingTasks =
                    Task::query()
                        ->where(
                            'work_order_id',
                            $task
                                ->work_order_id
                        )
                        ->where(
                            'status',
                            '!=',
                            'completed'
                        )
                        ->exists();

                if (!$remainingTasks) {
                    WorkOrder::query()
                        ->whereKey(
                            $task
                                ->work_order_id
                        )
                        ->update([
                            'status' =>
                                'completed',

                            'completed_at' =>
                                now(),
                        ]);
                } else {
                    WorkOrder::query()
                        ->whereKey(
                            $task
                                ->work_order_id
                        )
                        ->update([
                            'status' =>
                                'in_progress',
                        ]);
                }
            }
        );

        return back()->with(
            'success',
            'Tarea finalizada correctamente.'
        );
    }

    public function sendForReview(
        Task $task
    ): RedirectResponse {
        if (
            $task->status !==
            'in_progress'
        ) {
            return back()->withErrors([
                'task' =>
                    'Solo una tarea en progreso puede enviarse a revisión.',
            ]);
        }

        DB::transaction(
            function () use ($task) {
                $task->update([
                    'status' =>
                        'review',

                    'review_at' =>
                        now(),
                ]);

                $task->workOrder()
                    ->update([
                        'status' =>
                            'review',
                    ]);

                $this->event(
                    $task,
                    'review',
                    'Tarea enviada a revisión.'
                );
            }
        );

        return back()->with(
            'success',
            'Tarea enviada a revisión.'
        );
    }

    public function reportIssue(
        Request $request,
        Task $task
    ): RedirectResponse {
        $validated =
            $request->validate([
                'issue_description' => [
                    'required',
                    'string',
                    'max:2000',
                ],
            ]);

        if (
            !in_array(
                $task->status,
                [
                    'in_progress',
                    'paused',
                ],
                true
            )
        ) {
            return back()->withErrors([
                'task' =>
                    'No puedes reportar un inconveniente en esta tarea.',
            ]);
        }

        DB::transaction(
            function () use (
                $task,
                $validated
            ) {
                $task->update([
                    'status' =>
                        'issue',

                    'issue_description' =>
                        $validated[
                            'issue_description'
                        ],

                    'issue_reported_at' =>
                        now(),
                ]);

                $task->workOrder()
                    ->update([
                        'status' =>
                            'paused',
                    ]);

                $this->event(
                    $task,
                    'issue',
                    $validated[
                        'issue_description'
                    ]
                );
            }
        );

        return back()->with(
            'success',
            'Inconveniente registrado.'
        );
    }

    private function event(
        Task $task,
        string $eventType,
        ?string $notes = null
    ): void {
        TaskEvent::create([
            'task_id' =>
                $task->id,

            'user_id' =>
                auth()->id(),

            'event_type' =>
                $eventType,

            'notes' =>
                $notes,

            'occurred_at' =>
                now(),
        ]);
    }

    private function taskForIndex(
        Task $task
    ): array {
        return [
            'id' =>
                $task->id,

            'task_number' =>
                $task->task_number,

            'title' =>
                $task->title,

            'status' =>
                $task->status,

            'priority' =>
                $task->priority,

            'sort_order' =>
                $task->sort_order,

            'is_review_task' =>
                $task->is_review_task,

            'work_order_id' =>
                $task->work_order_id,

            'work_order_number' =>
                $task
                    ->workOrder
                    ?->work_order_number,

            'work_order_title' =>
                $task
                    ->workOrder
                    ?->title,

            'client' =>
                $task
                    ->workOrder
                    ?->client
                    ?->display_name,

            'responsible' =>
                $task
                    ->responsibleEmployee
                    ?->full_name,
        ];
    }

    private function taskForFrontend(
        Task $task
    ): array {
        return [
            'id' =>
                $task->id,

            'task_number' =>
                $task->task_number,

            'title' =>
                $task->title,

            'description' =>
                $task->description,

            'status' =>
                $task->status,

            'priority' =>
                $task->priority,

            'is_review_task' =>
                $task->is_review_task,

            'started_at' =>
                $task
                    ->started_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'paused_at' =>
                $task
                    ->paused_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'completed_at' =>
                $task
                    ->completed_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'review_at' =>
                $task
                    ->review_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'issue_description' =>
                $task
                    ->issue_description,

            'responsible' =>
                $task
                    ->responsibleEmployee
                    ? [
                        'id' =>
                            $task
                                ->responsibleEmployee
                                ->id,

                        'name' =>
                            $task
                                ->responsibleEmployee
                                ->full_name,
                    ]
                    : null,

            'work_order' => [
                'id' =>
                    $task
                        ->workOrder
                        ?->id,

                'number' =>
                    $task
                        ->workOrder
                        ?->work_order_number,

                'title' =>
                    $task
                        ->workOrder
                        ?->title,

                'status' =>
                    $task
                        ->workOrder
                        ?->status,

                'client' =>
                    $task
                        ->workOrder
                        ?->client
                        ?->display_name,
            ],

            'events' =>
                $task
                    ->events
                    ->sortByDesc(
                        'occurred_at'
                    )
                    ->map(
                        fn (
                            TaskEvent $event
                        ) => [
                            'id' =>
                                $event->id,

                            'event_type' =>
                                $event
                                    ->event_type,

                            'notes' =>
                                $event->notes,

                            'user' =>
                                $event
                                    ->user
                                    ?->name,

                            'occurred_at' =>
                                $event
                                    ->occurred_at
                                    ?->format(
                                        'Y-m-d H:i'
                                    ),
                        ]
                    )
                    ->values(),
        ];
    }
}