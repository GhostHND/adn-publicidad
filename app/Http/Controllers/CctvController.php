<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CctvCredential;
use App\Models\CctvDevice;
use App\Models\CctvMaintenanceRecord;
use App\Models\CctvProject;
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

class CctvController extends Controller
{
    public function index(): Response
    {
        $projects =
            CctvProject::query()
                ->with([
                    'client',
                    'workOrder',
                    'installation',
                    'responsibleEmployee',
                ])
                ->withCount(
                    'devices'
                )
                ->orderByRaw("
                    CASE status
                        WHEN 'installation_in_progress' THEN 1
                        WHEN 'installation_scheduled' THEN 2
                        WHEN 'ready_installation' THEN 3
                        WHEN 'equipment_pending' THEN 4
                        WHEN 'survey_pending' THEN 5
                        WHEN 'planning' THEN 6
                        WHEN 'maintenance' THEN 7
                        WHEN 'installed' THEN 8
                        WHEN 'cancelled' THEN 9
                        ELSE 10
                    END
                ")
                ->latest('id')
                ->get()
                ->map(
                    fn (
                        CctvProject $project
                    ) =>
                        $this
                            ->projectData(
                                $project
                            )
                )
                ->values();

        return Inertia::render(
            'Cctv/Index',
            [
                'projects' =>
                    $projects,

                'summary' => [
                    'planning' =>
                        CctvProject::query()
                            ->whereIn(
                                'status',
                                [
                                    'planning',
                                    'survey_pending',
                                    'equipment_pending',
                                ]
                            )
                            ->count(),

                    'ready' =>
                        CctvProject::query()
                            ->where(
                                'status',
                                'ready_installation'
                            )
                            ->count(),

                    'installation' =>
                        CctvProject::query()
                            ->whereIn(
                                'status',
                                [
                                    'installation_scheduled',
                                    'installation_in_progress',
                                ]
                            )
                            ->count(),

                    'installed' =>
                        CctvProject::query()
                            ->where(
                                'status',
                                'installed'
                            )
                            ->count(),

                    'maintenance' =>
                        CctvProject::query()
                            ->where(
                                'status',
                                'maintenance'
                            )
                            ->count(),
                ],
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Cctv/Create',
            [
                'clients' =>
                    $this
                        ->clientsForForm(),

                'employees' =>
                    $this
                        ->employeesForForm(),

                'workOrders' =>
                    $this
                        ->workOrdersForForm(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'client_id' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'clients',
                        'id'
                    )->whereNull(
                        'deleted_at'
                    ),
                ],

                'work_order_id' => [
                    'nullable',
                    'integer',

                    Rule::exists(
                        'work_orders',
                        'id'
                    ),

                    Rule::unique(
                        'cctv_projects',
                        'work_order_id'
                    )->whereNull(
                        'deleted_at'
                    ),
                ],

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

                'system_type' => [
                    'required',

                    Rule::in([
                        'unspecified',
                        'analog',
                        'ip',
                        'hybrid',
                    ]),
                ],

                'status' => [
                    'required',

                    Rule::in([
                        'planning',
                        'survey_pending',
                        'equipment_pending',
                    ]),
                ],

                'site_name' => [
                    'nullable',
                    'string',
                    'max:200',
                ],

                'address' => [
                    'nullable',
                    'string',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:150',
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

                'internet_provider' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'network_notes' => [
                    'nullable',
                    'string',
                ],

                'site_survey_scheduled_at' => [
                    'nullable',
                    'date',
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        $workOrder =
            !empty(
                $validated[
                    'work_order_id'
                ]
            )
                ? WorkOrder::query()
                    ->with([
                        'sale',
                        'installation',
                    ])
                    ->findOrFail(
                        $validated[
                            'work_order_id'
                        ]
                    )
                : null;

        $project =
            CctvProject::create([
                'project_number' =>
                    $this
                        ->nextProjectNumber(),

                'client_id' =>
                    $validated[
                        'client_id'
                    ],

                'quotation_id' =>
                    data_get(
                        $workOrder?->sale,
                        'quotation_id'
                    ),

                'sale_id' =>
                    $workOrder
                        ?->sale_id,

                'work_order_id' =>
                    $workOrder
                        ?->id,

                'installation_id' =>
                    $workOrder
                        ?->installation
                        ?->id,

                'responsible_employee_id' =>
                    $validated[
                        'responsible_employee_id'
                    ]
                    ?? null,

                'created_by' =>
                    $request
                        ->user()
                        ?->id,

                'source' =>
                    'manual',

                'system_type' =>
                    $validated[
                        'system_type'
                    ],

                'status' =>
                    $validated[
                        'status'
                    ],

                'site_name' =>
                    $validated[
                        'site_name'
                    ] ?? null,

                'address' =>
                    $validated[
                        'address'
                    ] ?? null,

                'city' =>
                    $validated[
                        'city'
                    ] ?? null,

                'contact_name' =>
                    $validated[
                        'contact_name'
                    ] ?? null,

                'contact_phone' =>
                    $validated[
                        'contact_phone'
                    ] ?? null,

                'internet_provider' =>
                    $validated[
                        'internet_provider'
                    ] ?? null,

                'network_notes' =>
                    $validated[
                        'network_notes'
                    ] ?? null,

                'site_survey_scheduled_at' =>
                    $validated[
                        'site_survey_scheduled_at'
                    ] ?? null,

                'notes' =>
                    $validated[
                        'notes'
                    ] ?? null,
            ]);

        return redirect(
            '/cctv/' .
            $project->id
        )->with(
            'success',
            'Proyecto CCTV creado correctamente.'
        );
    }

    public function show(
        CctvProject $cctv
    ): Response {
        $cctv->load([
            'client',
            'quotation',
            'sale',
            'workOrder',
            'installation',
            'responsibleEmployee',

            'devices' =>
                fn ($query) =>
                    $query
                        ->orderBy(
                            'device_type'
                        )
                        ->orderBy(
                            'device_code'
                        ),

            'credentials.device',

            'maintenanceRecords' =>
                fn ($query) =>
                    $query
                        ->with([
                            'device',
                            'performedBy',
                        ])
                        ->latest(
                            'maintenance_date'
                        )
                        ->latest('id'),
        ]);

        return Inertia::render(
            'Cctv/Show',
            [
                'project' =>
                    $this
                        ->projectData(
                            $cctv,
                            true
                        ),

                'employees' =>
                    $this
                        ->employeesForForm(),

                'allowedStatuses' =>
                    $this
                        ->allowedStatuses(
                            $cctv
                                ->status
                        ),
            ]
        );
    }

    public function edit(
        CctvProject $cctv
    ): Response {
        $cctv->load([
            'client',
            'workOrder',
            'responsibleEmployee',
        ]);

        return Inertia::render(
            'Cctv/Edit',
            [
                'project' =>
                    $this
                        ->projectData(
                            $cctv
                        ),

                'clients' =>
                    $this
                        ->clientsForForm(),

                'employees' =>
                    $this
                        ->employeesForForm(),
            ]
        );
    }

    public function update(
        Request $request,
        CctvProject $cctv
    ): RedirectResponse {
        $operation =
            $request->string(
                'operation'
            )->toString();

        return match ($operation) {
            'status' =>
                $this->updateStatus(
                    $request,
                    $cctv
                ),

            'device' =>
                $this->saveDevice(
                    $request,
                    $cctv
                ),

            'delete_device' =>
                $this->deleteDevice(
                    $request,
                    $cctv
                ),

            'credential' =>
                $this->saveCredential(
                    $request,
                    $cctv
                ),

            'delete_credential' =>
                $this->deleteCredential(
                    $request,
                    $cctv
                ),

            'maintenance' =>
                $this->saveMaintenance(
                    $request,
                    $cctv
                ),

            default =>
                $this->updateGeneral(
                    $request,
                    $cctv
                ),
        };
    }

    public function destroy(
        CctvProject $cctv
    ): RedirectResponse {
        if (
            in_array(
                $cctv->status,
                [
                    'installed',
                    'maintenance',
                    'installation_in_progress',
                ],
                true
            )
        ) {
            return back()
                ->withErrors([
                    'cctv' =>
                        'No puedes eliminar un proyecto CCTV que ya fue instalado o está en ejecución.',
                ]);
        }

        $cctv->delete();

        return redirect(
            '/cctv'
        )->with(
            'success',
            'Proyecto CCTV eliminado.'
        );
    }

    private function updateGeneral(
        Request $request,
        CctvProject $project
    ): RedirectResponse {
        $validated =
            $request->validate([
                'client_id' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'clients',
                        'id'
                    ),
                ],

                'responsible_employee_id' => [
                    'nullable',
                    'integer',
                    Rule::exists(
                        'employees',
                        'id'
                    ),
                ],

                'system_type' => [
                    'required',

                    Rule::in([
                        'unspecified',
                        'analog',
                        'ip',
                        'hybrid',
                    ]),
                ],

                'site_name' => [
                    'nullable',
                    'string',
                    'max:200',
                ],

                'address' => [
                    'nullable',
                    'string',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:150',
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

                'internet_provider' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'network_notes' => [
                    'nullable',
                    'string',
                ],

                'site_survey_scheduled_at' => [
                    'nullable',
                    'date',
                ],

                'site_survey_completed_at' => [
                    'nullable',
                    'date',
                ],

                'site_survey_notes' => [
                    'nullable',
                    'string',
                ],

                'maintenance_due_at' => [
                    'nullable',
                    'date',
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        $project->update(
            $validated
        );

        return redirect(
            '/cctv/' .
            $project->id
        )->with(
            'success',
            'Proyecto CCTV actualizado.'
        );
    }

    private function updateStatus(
        Request $request,
        CctvProject $project
    ): RedirectResponse {
        $validated =
            $request->validate([
                'status' => [
                    'required',
                    'string',
                ],
            ]);

        $status =
            $validated[
                'status'
            ];

        if (
            !in_array(
                $status,
                $this
                    ->allowedStatuses(
                        $project
                            ->status
                    ),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'status' =>
                    'El cambio de estado solicitado no es válido.',
            ]);
        }

        $data = [
            'status' =>
                $status,
        ];

        if (
            $status ===
            'installed'
            &&
            !$project
                ->installed_at
        ) {
            $data[
                'installed_at'
            ] = now();
        }

        $project->update(
            $data
        );

        return back()->with(
            'success',
            'Estado del proyecto actualizado.'
        );
    }

    private function saveDevice(
        Request $request,
        CctvProject $project
    ): RedirectResponse {
        $validated =
            $request->validate([
                'device_id' => [
                    'nullable',
                    'integer',
                ],

                'device_type' => [
                    'required',

                    Rule::in([
                        'camera',
                        'nvr',
                        'dvr',
                        'xvr',
                        'poe_switch',
                        'router',
                        'ups',
                        'hdd',
                        'monitor',
                        'other',
                    ]),
                ],

                'brand' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'model' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'serial_number' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'mac_address' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'ip_address' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'channel' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:256',
                ],

                'location' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'status' => [
                    'required',

                    Rule::in([
                        'active',
                        'spare',
                        'faulty',
                        'replaced',
                        'removed',
                    ]),
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        $deviceId =
            $validated[
                'device_id'
            ] ?? null;

        unset(
            $validated[
                'device_id'
            ]
        );

        if ($deviceId) {
            $device =
                CctvDevice::query()
                    ->where(
                        'cctv_project_id',
                        $project->id
                    )
                    ->findOrFail(
                        $deviceId
                    );

            $device->update(
                $validated
            );
        } else {
            CctvDevice::create([
                ...$validated,

                'cctv_project_id' =>
                    $project->id,

                'device_code' =>
                    $this
                        ->nextDeviceCode(),

                'installed_at' =>
                    in_array(
                        $project
                            ->status,
                        [
                            'installed',
                            'maintenance',
                        ],
                        true
                    )
                        ? now()
                        : null,
            ]);
        }

        return back()->with(
            'success',
            'Equipo CCTV guardado.'
        );
    }

    private function deleteDevice(
        Request $request,
        CctvProject $project
    ): RedirectResponse {
        $validated =
            $request->validate([
                'device_id' => [
                    'required',
                    'integer',
                ],
            ]);

        $device =
            CctvDevice::query()
                ->where(
                    'cctv_project_id',
                    $project->id
                )
                ->findOrFail(
                    $validated[
                        'device_id'
                    ]
                );

        $device->delete();

        return back()->with(
            'success',
            'Equipo retirado del proyecto.'
        );
    }

    private function saveCredential(
        Request $request,
        CctvProject $project
    ): RedirectResponse {
        $validated =
            $request->validate([
                'cctv_device_id' => [
                    'nullable',
                    'integer',
                ],

                'credential_type' => [
                    'required',

                    Rule::in([
                        'device_admin',
                        'router',
                        'wifi',
                        'cloud',
                        'ddns',
                        'email',
                        'app',
                        'other',
                    ]),
                ],

                'label' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'username' => [
                    'nullable',
                    'string',
                    'max:200',
                ],

                'secret_value' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'host' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'port' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:65535',
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        if (
            !empty(
                $validated[
                    'cctv_device_id'
                ]
            )
        ) {
            CctvDevice::query()
                ->where(
                    'cctv_project_id',
                    $project->id
                )
                ->findOrFail(
                    $validated[
                        'cctv_device_id'
                    ]
                );
        }

        CctvCredential::create([
            ...$validated,

            'cctv_project_id' =>
                $project->id,
        ]);

        return back()->with(
            'success',
            'Credencial guardada de forma cifrada.'
        );
    }

    private function deleteCredential(
        Request $request,
        CctvProject $project
    ): RedirectResponse {
        $validated =
            $request->validate([
                'credential_id' => [
                    'required',
                    'integer',
                ],
            ]);

        $credential =
            CctvCredential::query()
                ->where(
                    'cctv_project_id',
                    $project->id
                )
                ->findOrFail(
                    $validated[
                        'credential_id'
                    ]
                );

        $credential->delete();

        return back()->with(
            'success',
            'Credencial eliminada.'
        );
    }

    private function saveMaintenance(
        Request $request,
        CctvProject $project
    ): RedirectResponse {
        $validated =
            $request->validate([
                'cctv_device_id' => [
                    'nullable',
                    'integer',
                ],

                'performed_by_employee_id' => [
                    'nullable',
                    'integer',
                ],

                'maintenance_type' => [
                    'required',

                    Rule::in([
                        'preventive',
                        'corrective',
                        'inspection',
                        'configuration',
                    ]),
                ],

                'maintenance_date' => [
                    'required',
                    'date',
                ],

                'description' => [
                    'required',
                    'string',
                ],

                'findings' => [
                    'nullable',
                    'string',
                ],

                'actions_taken' => [
                    'nullable',
                    'string',
                ],

                'cost' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'next_due_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:maintenance_date',
                ],
            ]);

        CctvMaintenanceRecord::create([
            ...$validated,

            'cctv_project_id' =>
                $project->id,

            'created_by' =>
                $request
                    ->user()
                    ?->id,

            'cost' =>
                $validated[
                    'cost'
                ] ?? 0,
        ]);

        if (
            !empty(
                $validated[
                    'next_due_date'
                ]
            )
        ) {
            $project->update([
                'maintenance_due_at' =>
                    $validated[
                        'next_due_date'
                    ],
            ]);
        }

        return back()->with(
            'success',
            'Mantenimiento registrado.'
        );
    }

    private function projectData(
        CctvProject $project,
        bool $detailed = false
    ): array {
        $data = [
            'id' =>
                $project->id,

            'project_number' =>
                $project
                    ->project_number,

            'client_id' =>
                $project
                    ->client_id,

            'client_name' =>
                $project
                    ->client
                    ?->display_name,

            'quotation_id' =>
                $project
                    ->quotation_id,

            'quotation_number' =>
                $project
                    ->quotation
                    ?->quotation_number,

            'sale_id' =>
                $project
                    ->sale_id,

            'sale_number' =>
                $project
                    ->sale
                    ?->sale_number,

            'work_order_id' =>
                $project
                    ->work_order_id,

            'work_order_number' =>
                $project
                    ->workOrder
                    ?->work_order_number,

            'installation_id' =>
                $project
                    ->installation_id,

            'installation_number' =>
                $project
                    ->installation
                    ?->installation_number,

            'responsible_employee_id' =>
                $project
                    ->responsible_employee_id,

            'responsible' =>
                $project
                    ->responsibleEmployee
                    ?->full_name,

            'source' =>
                $project
                    ->source,

            'system_type' =>
                $project
                    ->system_type,

            'status' =>
                $project
                    ->status,

            'site_name' =>
                $project
                    ->site_name,

            'address' =>
                $project
                    ->address,

            'city' =>
                $project
                    ->city,

            'contact_name' =>
                $project
                    ->contact_name,

            'contact_phone' =>
                $project
                    ->contact_phone,

            'internet_provider' =>
                $project
                    ->internet_provider,

            'network_notes' =>
                $project
                    ->network_notes,

            'site_survey_scheduled_at' =>
                $project
                    ->site_survey_scheduled_at
                    ?->format(
                        'Y-m-d\TH:i'
                    ),

            'site_survey_completed_at' =>
                $project
                    ->site_survey_completed_at
                    ?->format(
                        'Y-m-d\TH:i'
                    ),

            'site_survey_notes' =>
                $project
                    ->site_survey_notes,

            'installed_at' =>
                $project
                    ->installed_at
                    ?->format(
                        'd/m/Y h:i A'
                    ),

            'maintenance_due_at' =>
                $project
                    ->maintenance_due_at
                    ?->format(
                        'Y-m-d'
                    ),

            'notes' =>
                $project
                    ->notes,

            'devices_count' =>
                $project
                    ->devices_count
                ??
                $project
                    ->devices
                    ->count(),
        ];

        if ($detailed) {
            $data['devices'] =
                $project
                    ->devices
                    ->map(
                        fn (
                            CctvDevice $device
                        ) => [
                            'id' =>
                                $device->id,

                            'device_code' =>
                                $device
                                    ->device_code,

                            'device_type' =>
                                $device
                                    ->device_type,

                            'brand' =>
                                $device
                                    ->brand,

                            'model' =>
                                $device
                                    ->model,

                            'serial_number' =>
                                $device
                                    ->serial_number,

                            'mac_address' =>
                                $device
                                    ->mac_address,

                            'ip_address' =>
                                $device
                                    ->ip_address,

                            'channel' =>
                                $device
                                    ->channel,

                            'location' =>
                                $device
                                    ->location,

                            'status' =>
                                $device
                                    ->status,

                            'notes' =>
                                $device
                                    ->notes,
                        ]
                    )
                    ->values();

            /*
            |--------------------------------------------------------------------------
            | NO enviamos secret_value al navegador.
            |--------------------------------------------------------------------------
            */

            $data['credentials'] =
                $project
                    ->credentials
                    ->map(
                        fn (
                            CctvCredential $credential
                        ) => [
                            'id' =>
                                $credential->id,

                            'device' =>
                                $credential
                                    ->device
                                    ?->device_code,

                            'credential_type' =>
                                $credential
                                    ->credential_type,

                            'label' =>
                                $credential
                                    ->label,

                            'username' =>
                                $credential
                                    ->username,

                            'host' =>
                                $credential
                                    ->host,

                            'port' =>
                                $credential
                                    ->port,

                            'has_secret' =>
                                !empty(
                                    $credential
                                        ->secret_value
                                ),

                            'notes' =>
                                $credential
                                    ->notes,
                        ]
                    )
                    ->values();

            $data['maintenance'] =
                $project
                    ->maintenanceRecords
                    ->map(
                        fn (
                            CctvMaintenanceRecord $record
                        ) => [
                            'id' =>
                                $record->id,

                            'device' =>
                                $record
                                    ->device
                                    ?->device_code,

                            'performed_by' =>
                                $record
                                    ->performedBy
                                    ?->full_name,

                            'maintenance_type' =>
                                $record
                                    ->maintenance_type,

                            'maintenance_date' =>
                                $record
                                    ->maintenance_date
                                    ?->format(
                                        'Y-m-d'
                                    ),

                            'description' =>
                                $record
                                    ->description,

                            'findings' =>
                                $record
                                    ->findings,

                            'actions_taken' =>
                                $record
                                    ->actions_taken,

                            'cost' =>
                                $record
                                    ->cost,

                            'next_due_date' =>
                                $record
                                    ->next_due_date
                                    ?->format(
                                        'Y-m-d'
                                    ),
                        ]
                    )
                    ->values();
        }

        return $data;
    }

    private function allowedStatuses(
        string $status
    ): array {
        return match ($status) {
            'planning' => [
                'survey_pending',
                'equipment_pending',
                'cancelled',
            ],

            'survey_pending' => [
                'equipment_pending',
                'cancelled',
            ],

            'equipment_pending' => [
                'ready_installation',
                'cancelled',
            ],

            'ready_installation' => [
                'installation_scheduled',
                'installed',
                'cancelled',
            ],

            'installation_scheduled' => [
                'installation_in_progress',
                'installed',
                'cancelled',
            ],

            'installation_in_progress' => [
                'installed',
            ],

            'installed' => [
                'maintenance',
            ],

            'maintenance' => [
                'installed',
            ],

            'cancelled' => [
                'planning',
            ],

            default => [],
        };
    }

    private function clientsForForm(): array
    {
        return Client::query()
            ->where(
                'active',
                true
            )
            ->orderBy('id')
            ->get()
            ->map(
                fn (
                    Client $client
                ) => [
                    'id' =>
                        $client->id,

                    'name' =>
                        $client
                            ->display_name,

                    'phone' =>
                        data_get(
                            $client,
                            'phone'
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
                ]
            )
            ->values()
            ->all();
    }

    private function employeesForForm(): array
    {
        return Employee::query()
            ->where(
                'active',
                true
            )
            ->orderBy('id')
            ->get()
            ->map(
                fn (
                    Employee $employee
                ) => [
                    'id' =>
                        $employee->id,

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

    private function workOrdersForForm(): array
    {
        $used =
            CctvProject::withTrashed()
                ->whereNotNull(
                    'work_order_id'
                )
                ->pluck(
                    'work_order_id'
                );

        return WorkOrder::query()
            ->with('client')
            ->whereNotIn(
                'id',
                $used
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
                    WorkOrder $workOrder
                ) => [
                    'id' =>
                        $workOrder->id,

                    'number' =>
                        $workOrder
                            ->work_order_number,

                    'title' =>
                        $workOrder
                            ->title,

                    'client' =>
                        $workOrder
                            ->client
                            ?->display_name,

                    'client_id' =>
                        $workOrder
                            ->client_id,
                ]
            )
            ->values()
            ->all();
    }

    private function nextProjectNumber(): string
    {
        $next =
            (
                CctvProject::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'CCTV-' .
            now()->format('Y') .
            '-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    private function nextDeviceCode(): string
    {
        $next =
            (
                CctvDevice::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'DEV-' .
            str_pad(
                (string) $next,
                5,
                '0',
                STR_PAD_LEFT
            );
    }
}