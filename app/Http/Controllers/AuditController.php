<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'module' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'action' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'user_id' => [
                    'nullable',
                    'integer',
                ],

                'date_from' => [
                    'nullable',
                    'date',
                ],

                'date_to' => [
                    'nullable',
                    'date',
                ],
            ]);

        $query =
            AuditLog::query()
                ->with([
                    'user:id,name,email',
                    'employee:id,employee_code,first_name,middle_name,last_name,second_last_name',
                ]);

        if (
            !empty(
                $filters['search']
            )
        ) {
            $search =
                trim(
                    $filters['search']
                );

            $query->where(
                function (
                    $query
                ) use (
                    $search
                ) {
                    $query
                        ->where(
                            'description',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'route_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'ip_address',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'user',
                            fn ($userQuery) =>
                                $userQuery
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%"
                                    )
                        );
                }
            );
        }

        if (
            !empty(
                $filters['module']
            )
        ) {
            $query->where(
                'module',
                $filters['module']
            );
        }

        if (
            !empty(
                $filters['action']
            )
        ) {
            $query->where(
                'action',
                $filters['action']
            );
        }

        if (
            !empty(
                $filters['user_id']
            )
        ) {
            $query->where(
                'user_id',
                $filters['user_id']
            );
        }

        if (
            !empty(
                $filters['date_from']
            )
        ) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['date_from']
            );
        }

        if (
            !empty(
                $filters['date_to']
            )
        ) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['date_to']
            );
        }

        $logs =
            $query
                ->latest('id')
                ->paginate(40)
                ->withQueryString()
                ->through(
                    fn (
                        AuditLog $log
                    ) => [
                        'id' =>
                            $log->id,

                        'module' =>
                            $log->module,

                        'action' =>
                            $log->action,

                        'description' =>
                            $log->description,

                        'route_name' =>
                            $log->route_name,

                        'method' =>
                            $log->method,

                        'status_code' =>
                            $log->status_code,

                        'ip_address' =>
                            $log->ip_address,

                        'user' =>
                            $log
                                ->user?->name
                            ?? 'Sistema',

                        'user_email' =>
                            $log
                                ->user?->email,

                        'employee' =>
                            $log->employee
                                ? collect([
                                    $log
                                        ->employee
                                        ->first_name,

                                    $log
                                        ->employee
                                        ->middle_name,

                                    $log
                                        ->employee
                                        ->last_name,

                                    $log
                                        ->employee
                                        ->second_last_name,
                                ])
                                    ->filter()
                                    ->implode(' ')
                                : null,

                        'created_at' =>
                            $log
                                ->created_at
                                ?->format(
                                    'd/m/Y H:i:s'
                                ),
                    ]
                );

        return Inertia::render(
            'Audit/Index',
            [
                'logs' =>
                    $logs,

                'filters' => [
                    'search' =>
                        $filters['search']
                        ?? '',

                    'module' =>
                        $filters['module']
                        ?? '',

                    'action' =>
                        $filters['action']
                        ?? '',

                    'user_id' =>
                        $filters['user_id']
                        ?? '',

                    'date_from' =>
                        $filters['date_from']
                        ?? '',

                    'date_to' =>
                        $filters['date_to']
                        ?? '',
                ],

                'modules' =>
                    AuditLog::query()
                        ->whereNotNull(
                            'module'
                        )
                        ->distinct()
                        ->orderBy(
                            'module'
                        )
                        ->pluck(
                            'module'
                        )
                        ->values(),

                'actions' =>
                    AuditLog::query()
                        ->whereNotNull(
                            'action'
                        )
                        ->distinct()
                        ->orderBy(
                            'action'
                        )
                        ->pluck(
                            'action'
                        )
                        ->values(),

                'users' =>
                    User::query()
                        ->orderBy(
                            'name'
                        )
                        ->get([
                            'id',
                            'name',
                            'email',
                        ]),

                'stats' => [
                    'total' =>
                        AuditLog::query()
                            ->count(),

                    'today' =>
                        AuditLog::query()
                            ->whereDate(
                                'created_at',
                                today()
                            )
                            ->count(),

                    'users' =>
                        AuditLog::query()
                            ->whereNotNull(
                                'user_id'
                            )
                            ->distinct(
                                'user_id'
                            )
                            ->count(
                                'user_id'
                            ),

                    'errors' =>
                        AuditLog::query()
                            ->where(
                                'status_code',
                                '>=',
                                400
                            )
                            ->count(),
                ],
            ]
        );
    }

    public function show(
        AuditLog $auditLog
    ): Response {
        $auditLog->load([
            'user',
            'employee',
        ]);

        return Inertia::render(
            'Audit/Show',
            [
                'log' => [
                    'id' =>
                        $auditLog->id,

                    'module' =>
                        $auditLog->module,

                    'action' =>
                        $auditLog->action,

                    'description' =>
                        $auditLog->description,

                    'route_name' =>
                        $auditLog->route_name,

                    'method' =>
                        $auditLog->method,

                    'url' =>
                        $auditLog->url,

                    'status_code' =>
                        $auditLog
                            ->status_code,

                    'entity_type' =>
                        $auditLog
                            ->entity_type,

                    'entity_id' =>
                        $auditLog
                            ->entity_id,

                    'request_payload' =>
                        $auditLog
                            ->request_payload,

                    'ip_address' =>
                        $auditLog
                            ->ip_address,

                    'user_agent' =>
                        $auditLog
                            ->user_agent,

                    'user' => [
                        'id' =>
                            $auditLog
                                ->user?->id,

                        'name' =>
                            $auditLog
                                ->user?->name,

                        'email' =>
                            $auditLog
                                ->user?->email,
                    ],

                    'employee' =>
                        $auditLog->employee
                            ? [
                                'id' =>
                                    $auditLog
                                        ->employee
                                        ->id,

                                'code' =>
                                    $auditLog
                                        ->employee
                                        ->employee_code,

                                'name' =>
                                    $auditLog
                                        ->employee
                                        ->full_name,
                            ]
                            : null,

                    'created_at' =>
                        $auditLog
                            ->created_at
                            ?->format(
                                'd/m/Y H:i:s'
                            ),
                ],
            ]
        );
    }
}