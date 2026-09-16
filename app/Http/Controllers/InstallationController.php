<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Installation;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InstallationController extends Controller
{
    public function index(): Response
    {
        $installations =
            Installation::query()
                ->with([
                    'client',
                    'workOrder',
                    'responsibleEmployee',
                ])
                ->orderByRaw("
                    CASE status
                        WHEN 'in_progress' THEN 1
                        WHEN 'on_route' THEN 2
                        WHEN 'scheduled' THEN 3
                        WHEN 'pending_schedule' THEN 4
                        WHEN 'completed' THEN 5
                        WHEN 'cancelled' THEN 6
                        ELSE 7
                    END
                ")
                ->orderByRaw(
                    'scheduled_at IS NULL'
                )
                ->orderBy(
                    'scheduled_at'
                )
                ->orderByDesc('id')
                ->get()
                ->map(
                    fn (
                        Installation $installation
                    ) =>
                        $this->installationData(
                            $installation
                        )
                )
                ->values();

        $summary = [
            'pending_schedule' =>
                Installation::query()
                    ->where(
                        'status',
                        'pending_schedule'
                    )
                    ->count(),

            'scheduled' =>
                Installation::query()
                    ->where(
                        'status',
                        'scheduled'
                    )
                    ->count(),

            'on_route' =>
                Installation::query()
                    ->where(
                        'status',
                        'on_route'
                    )
                    ->count(),

            'in_progress' =>
                Installation::query()
                    ->where(
                        'status',
                        'in_progress'
                    )
                    ->count(),

            'completed' =>
                Installation::query()
                    ->where(
                        'status',
                        'completed'
                    )
                    ->count(),
        ];

        return Inertia::render(
            'Installations/Index',
            [
                'installations' =>
                    $installations,

                'summary' =>
                    $summary,
            ]
        );
    }

    public function create(): Response
    {
        $usedWorkOrders =
            Installation::withTrashed()
                ->pluck(
                    'work_order_id'
                );

        $workOrders =
            WorkOrder::query()
                ->with([
                    'client',
                    'responsibleEmployee',
                ])
                ->whereNotIn(
                    'id',
                    $usedWorkOrders
                )
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->latest('id')
                ->get()
                ->map(
                    fn (
                        WorkOrder $order
                    ) => [
                        'id' =>
                            $order->id,

                        'number' =>
                            $order
                                ->work_order_number,

                        'title' =>
                            $order
                                ->title,

                        'client' =>
                            $order
                                ->client
                                ?->display_name,

                        'client_id' =>
                            $order
                                ->client_id,

                        'address' =>
                            data_get(
                                $order->client,
                                'address'
                            ),

                        'city' =>
                            data_get(
                                $order->client,
                                'city'
                            ),

                        'phone' =>
                            data_get(
                                $order->client,
                                'phone'
                            ),

                        'responsible_employee_id' =>
                            $order
                                ->responsible_employee_id,
                    ]
                )
                ->values();

        return Inertia::render(
            'Installations/Create',
            [
                'workOrders' =>
                    $workOrders,

                'employees' =>
                    $this
                        ->employeesForForm(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this
                ->validateInstallation(
                    $request,
                    true
                );

        $workOrder =
            WorkOrder::query()
                ->with('client')
                ->findOrFail(
                    $validated[
                        'work_order_id'
                    ]
                );

        $installation =
            DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $workOrder
                ) {
                    $scheduledAt =
                        $validated[
                            'scheduled_at'
                        ] ?? null;

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
                            $validated[
                                'responsible_employee_id'
                            ]
                            ??
                            $workOrder
                                ->responsible_employee_id,

                        'created_by' =>
                            $request
                                ->user()
                                ?->id,

                        'source' =>
                            'manual',

                        'status' =>
                            $scheduledAt
                                ? 'scheduled'
                                : 'pending_schedule',

                        'scheduled_at' =>
                            $scheduledAt,

                        'contact_name' =>
                            $validated[
                                'contact_name'
                            ]
                            ??
                            $workOrder
                                ->client
                                ?->display_name,

                        'contact_phone' =>
                            $validated[
                                'contact_phone'
                            ]
                            ??
                            data_get(
                                $workOrder->client,
                                'phone'
                            ),

                        'address' =>
                            $validated[
                                'address'
                            ]
                            ??
                            data_get(
                                $workOrder->client,
                                'address'
                            ),

                        'city' =>
                            $validated[
                                'city'
                            ]
                            ??
                            data_get(
                                $workOrder->client,
                                'city'
                            ),

                        'reference' =>
                            $validated[
                                'reference'
                            ] ?? null,

                        'estimated_duration_minutes' =>
                            $validated[
                                'estimated_duration_minutes'
                            ] ?? null,

                        'notes' =>
                            $validated[
                                'notes'
                            ] ?? null,
                    ]);
                }
            );

        return redirect(
            '/installations/' .
            $installation->id
        )->with(
            'success',
            'Instalación creada correctamente.'
        );
    }

    public function show(
        Installation $installation
    ): Response {
        $installation->load([
            'client',
            'workOrder.items',
            'responsibleEmployee',
            'creator',
        ]);

        return Inertia::render(
            'Installations/Show',
            [
                'installation' =>
                    $this
                        ->installationData(
                            $installation,
                            true
                        ),

                'allowedStatuses' =>
                    $this
                        ->allowedStatuses(
                            $installation
                                ->status
                        ),
            ]
        );
    }

    public function edit(
        Installation $installation
    ): Response {
        $installation->load([
            'client',
            'workOrder',
            'responsibleEmployee',
        ]);

        return Inertia::render(
            'Installations/Edit',
            [
                'installation' =>
                    $this
                        ->installationData(
                            $installation,
                            true
                        ),

                'employees' =>
                    $this
                        ->employeesForForm(),
            ]
        );
    }

    public function update(
        Request $request,
        Installation $installation
    ): RedirectResponse {
        $validated =
            $this
                ->validateInstallation(
                    $request,
                    false
                );

        DB::transaction(
            function () use (
                $validated,
                $installation
            ) {
                $newStatus =
                    $validated[
                        'status'
                    ]
                    ??
                    $installation
                        ->status;

                if (
                    $newStatus
                    !==
                    $installation
                        ->status
                ) {
                    $allowed =
                        $this
                            ->allowedStatuses(
                                $installation
                                    ->status
                            );

                    if (
                        !in_array(
                            $newStatus,
                            $allowed,
                            true
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'status' =>
                                'El cambio de estado solicitado no es válido.',
                        ]);
                    }
                }

                $scheduledAt =
                    array_key_exists(
                        'scheduled_at',
                        $validated
                    )
                        ? $validated[
                            'scheduled_at'
                        ]
                        : $installation
                            ->scheduled_at;

                if (
                    $newStatus ===
                    'scheduled'
                    &&
                    !$scheduledAt
                ) {
                    throw ValidationException::withMessages([
                        'scheduled_at' =>
                            'Debes indicar fecha y hora para programar la instalación.',
                    ]);
                }

                $data = [
                    'responsible_employee_id' =>
                        $validated[
                            'responsible_employee_id'
                        ]
                        ??
                        $installation
                            ->responsible_employee_id,

                    'scheduled_at' =>
                        $scheduledAt,

                    'contact_name' =>
                        $validated[
                            'contact_name'
                        ]
                        ??
                        $installation
                            ->contact_name,

                    'contact_phone' =>
                        $validated[
                            'contact_phone'
                        ]
                        ??
                        $installation
                            ->contact_phone,

                    'address' =>
                        $validated[
                            'address'
                        ]
                        ??
                        $installation
                            ->address,

                    'city' =>
                        $validated[
                            'city'
                        ]
                        ??
                        $installation
                            ->city,

                    'reference' =>
                        array_key_exists(
                            'reference',
                            $validated
                        )
                            ? $validated[
                                'reference'
                            ]
                            : $installation
                                ->reference,

                    'estimated_duration_minutes' =>
                        $validated[
                            'estimated_duration_minutes'
                        ]
                        ??
                        $installation
                            ->estimated_duration_minutes,

                    'notes' =>
                        array_key_exists(
                            'notes',
                            $validated
                        )
                            ? $validated[
                                'notes'
                            ]
                            : $installation
                                ->notes,

                    'completion_notes' =>
                        array_key_exists(
                            'completion_notes',
                            $validated
                        )
                            ? $validated[
                                'completion_notes'
                            ]
                            : $installation
                                ->completion_notes,

                    'status' =>
                        $newStatus,
                ];

                if (
                    $newStatus ===
                    'scheduled'
                ) {
                    $data[
                        'departed_at'
                    ] = null;

                    $data[
                        'started_at'
                    ] = null;

                    $data[
                        'completed_at'
                    ] = null;
                }

                if (
                    $newStatus ===
                    'on_route'
                    &&
                    !$installation
                        ->departed_at
                ) {
                    $data[
                        'departed_at'
                    ] = now();
                }

                if (
                    $newStatus ===
                    'in_progress'
                    &&
                    !$installation
                        ->started_at
                ) {
                    $data[
                        'started_at'
                    ] = now();
                }

                if (
                    $newStatus ===
                    'completed'
                    &&
                    !$installation
                        ->completed_at
                ) {
                    $data[
                        'completed_at'
                    ] = now();
                }

                $installation->update(
                    $data
                );
            }
        );

        return redirect(
            '/installations/' .
            $installation->id
        )->with(
            'success',
            'Instalación actualizada correctamente.'
        );
    }

    public function destroy(
        Installation $installation
    ): RedirectResponse {
        if (
            in_array(
                $installation
                    ->status,
                [
                    'on_route',
                    'in_progress',
                    'completed',
                ],
                true
            )
        ) {
            return back()
                ->withErrors([
                    'installation' =>
                        'No puedes eliminar una instalación que ya comenzó.',
                ]);
        }

        $installation->delete();

        return redirect(
            '/installations'
        )->with(
            'success',
            'Instalación eliminada.'
        );
    }

    private function validateInstallation(
        Request $request,
        bool $creating
    ): array {
        $rules = [
            'responsible_employee_id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'employees',
                    'id'
                )->whereNull(
                    'deleted_at'
                ),
            ],

            'scheduled_at' => [
                'nullable',
                'date',
            ],

            'contact_name' => [
                'nullable',
                'string',
                'max:200',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'city' => [
                'nullable',
                'string',
                'max:150',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'estimated_duration_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'completion_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'nullable',

                Rule::in([
                    'pending_schedule',
                    'scheduled',
                    'on_route',
                    'in_progress',
                    'completed',
                    'cancelled',
                ]),
            ],
        ];

        if ($creating) {
            $rules[
                'work_order_id'
            ] = [
                'required',
                'integer',

                Rule::exists(
                    'work_orders',
                    'id'
                ),

                Rule::unique(
                    'installations',
                    'work_order_id'
                )->whereNull(
                    'deleted_at'
                ),
            ];
        }

        return $request
            ->validate(
                $rules
            );
    }

    private function allowedStatuses(
        string $status
    ): array {
        return match (
            $status
        ) {
            'pending_schedule' => [
                'scheduled',
                'cancelled',
            ],

            'scheduled' => [
                'on_route',
                'in_progress',
                'cancelled',
            ],

            'on_route' => [
                'in_progress',
                'cancelled',
            ],

            'in_progress' => [
                'completed',
                'cancelled',
            ],

            'cancelled' => [
                'scheduled',
            ],

            default => [],
        };
    }

    private function employeesForForm(): array
    {
        return Employee::query()
            ->where(
                'active',
                true
            )
            ->orderBy(
                'first_name'
            )
            ->orderBy(
                'last_name'
            )
            ->get()
            ->map(
                fn (
                    Employee $employee
                ) => [
                    'id' =>
                        $employee->id,

                    'code' =>
                        $employee
                            ->employee_code,

                    'name' =>
                        $employee
                            ->full_name,

                    'position' =>
                        $employee
                            ->position,
                ]
            )
            ->values()
            ->all();
    }

    private function installationData(
        Installation $installation,
        bool $detailed = false
    ): array {
        $data = [
            'id' =>
                $installation->id,

            'installation_number' =>
                $installation
                    ->installation_number,

            'work_order_id' =>
                $installation
                    ->work_order_id,

            'work_order_number' =>
                $installation
                    ->workOrder
                    ?->work_order_number,

            'work_order_title' =>
                $installation
                    ->workOrder
                    ?->title,

            'client_id' =>
                $installation
                    ->client_id,

            'client_name' =>
                $installation
                    ->client
                    ?->display_name,

            'responsible_employee_id' =>
                $installation
                    ->responsible_employee_id,

            'responsible' =>
                $installation
                    ->responsibleEmployee
                    ?->full_name,

            'source' =>
                $installation
                    ->source,

            'status' =>
                $installation
                    ->status,

            'scheduled_at' =>
                $installation
                    ->scheduled_at
                    ?->format(
                        'Y-m-d\TH:i'
                    ),

            'scheduled_display' =>
                $installation
                    ->scheduled_at
                    ?->format(
                        'd/m/Y h:i A'
                    ),

            'departed_at' =>
                $installation
                    ->departed_at
                    ?->format(
                        'd/m/Y h:i A'
                    ),

            'started_at' =>
                $installation
                    ->started_at
                    ?->format(
                        'd/m/Y h:i A'
                    ),

            'completed_at' =>
                $installation
                    ->completed_at
                    ?->format(
                        'd/m/Y h:i A'
                    ),

            'contact_name' =>
                $installation
                    ->contact_name,

            'contact_phone' =>
                $installation
                    ->contact_phone,

            'address' =>
                $installation
                    ->address,

            'city' =>
                $installation
                    ->city,

            'reference' =>
                $installation
                    ->reference,

            'estimated_duration_minutes' =>
                $installation
                    ->estimated_duration_minutes,

            'notes' =>
                $installation
                    ->notes,

            'completion_notes' =>
                $installation
                    ->completion_notes,
        ];

        if (
            $detailed
            &&
            $installation
                ->workOrder
        ) {
            $data['items'] =
                $installation
                    ->workOrder
                    ->items
                    ->map(
                        fn ($item) => [
                            'id' =>
                                $item->id,

                            'name' =>
                                $item
                                    ->item_name,

                            'quantity' =>
                                $item
                                    ->quantity,

                            'description' =>
                                $item
                                    ->description,
                        ]
                    )
                    ->values();
        }

        return $data;
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