<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\WebsiteLead;
use App\Services\WebPushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class WebsiteLeadController extends Controller
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

                'status' => [
                    'nullable',

                    Rule::in([
                        'new',
                        'reviewed',
                        'attending',
                        'quoted',
                        'won',
                        'lost',
                    ]),
                ],
            ]);

        $query =
            WebsiteLead::query()
                ->with([
                    'assignedEmployee',
                    'client',
                    'quotation',
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
                            'lead_number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'business_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'service_interest',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'message',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (
            !empty(
                $filters['status']
            )
        ) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        $leads =
            $query
                ->latest('id')
                ->paginate(30)
                ->withQueryString()
                ->through(
                    fn (
                        WebsiteLead $lead
                    ) => [
                        'id' =>
                            $lead->id,

                        'lead_number' =>
                            $lead->lead_number,

                        'name' =>
                            $lead->name,

                        'business_name' =>
                            $lead->business_name,

                        'phone' =>
                            $lead->phone,

                        'email' =>
                            $lead->email,

                        'service_interest' =>
                            $lead->service_interest,

                        'message' =>
                            $lead->message,

                        'status' =>
                            $lead->status,

                        'source' =>
                            $lead->source,

                        'assigned_employee' =>
                            $lead
                                ->assignedEmployee
                                ?->full_name,

                        'client_id' =>
                            $lead->client_id,

                        'client_name' =>
                            $lead
                                ->client
                                ?->display_name,

                        'quotation_id' =>
                            $lead->quotation_id,

                        'quotation_number' =>
                            $lead
                                ->quotation
                                ?->quotation_number,

                        'reviewed_at' =>
                            $lead
                                ->reviewed_at
                                ?->format(
                                    'd/m/Y H:i'
                                ),

                        'attended_at' =>
                            $lead
                                ->attended_at
                                ?->format(
                                    'd/m/Y H:i'
                                ),

                        'created_at' =>
                            $lead
                                ->created_at
                                ?->format(
                                    'd/m/Y H:i'
                                ),
                    ]
                );

        return Inertia::render(
            'Website/Leads',
            [
                'leads' =>
                    $leads,

                'filters' => [
                    'search' =>
                        $filters['search']
                        ?? '',

                    'status' =>
                        $filters['status']
                        ?? '',
                ],

                'highlightId' =>
                    (int)
                    $request->query(
                        'highlight',
                        0
                    ),

                'stats' => [
                    'total' =>
                        WebsiteLead::query()
                            ->count(),

                    'new' =>
                        WebsiteLead::query()
                            ->unreviewed()
                            ->count(),

                    'open' =>
                        WebsiteLead::query()
                            ->open()
                            ->count(),

                    'won' =>
                        WebsiteLead::query()
                            ->where(
                                'status',
                                'won'
                            )
                            ->count(),
                ],
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        if (
            filled(
                $request->input(
                    'website'
                )
            )
        ) {
            return back()->with(
                'success',
                'Solicitud recibida correctamente.'
            );
        }

        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'business_name' => [
                    'nullable',
                    'string',
                    'max:180',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:40',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:180',
                ],

                'service_interest' => [
                    'nullable',
                    'string',
                    'max:180',
                ],

                'message' => [
                    'required',
                    'string',
                    'min:10',
                    'max:5000',
                ],
            ]);

        $operator =
            Employee::query()
                ->active()
                ->defaultOperator()
                ->first()
            ??
            Employee::query()
                ->active()
                ->orderBy('id')
                ->first();

        $lead =
            DB::transaction(
                function () use (
                    $validated,
                    $operator,
                    $request
                ) {
                    $lead =
                        WebsiteLead::create([
                            'lead_number' =>
                                null,

                            'name' =>
                                $validated['name'],

                            'business_name' =>
                                $validated[
                                    'business_name'
                                ]
                                ?? null,

                            'phone' =>
                                $validated['phone'],

                            'email' =>
                                $validated['email']
                                ?? null,

                            'service_interest' =>
                                $validated[
                                    'service_interest'
                                ]
                                ?? null,

                            'message' =>
                                $validated['message'],

                            'source' =>
                                'website',

                            'status' =>
                                'new',

                            'assigned_employee_id' =>
                                $operator?->id,

                            'ip_address' =>
                                $request->ip(),

                            'user_agent' =>
                                $request
                                    ->userAgent(),
                        ]);

                    $lead->update([
                        'lead_number' =>
                            'SOL-' .
                            now()->format(
                                'Y'
                            ) .
                            '-' .
                            str_pad(
                                (string)
                                $lead->id,
                                4,
                                '0',
                                STR_PAD_LEFT
                            ),
                    ]);

                    return $lead;
                }
            );

        try {
            app(
                WebPushService::class
            )->sendNewLead(
                $lead
            );
        } catch (
            Throwable $exception
        ) {
            report(
                $exception
            );
        }

        return back()->with(
            'success',
            'Solicitud recibida correctamente. Nos pondremos en contacto contigo.'
        );
    }

    public function markReviewed(
        WebsiteLead $websiteLead
    ): RedirectResponse {
        if (
            !$websiteLead->reviewed_at
        ) {
            $websiteLead->update([
                'reviewed_at' =>
                    now(),

                'status' =>
                    $websiteLead->status
                    === 'new'
                        ? 'reviewed'
                        : $websiteLead
                            ->status,
            ]);
        }

        return back()->with(
            'success',
            'Solicitud marcada como revisada.'
        );
    }

    public function attend(
        WebsiteLead $websiteLead
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | SI YA HAY UNA COTIZACIÓN
        |--------------------------------------------------------------------------
        */

        if (
            $websiteLead->quotation_id
        ) {
            return redirect(
                '/quotations/' .
                $websiteLead
                    ->quotation_id
            );
        }

        $client =
            DB::transaction(
                function () use (
                    $websiteLead
                ) {
                    $client =
                        $this
                            ->findExistingClient(
                                $websiteLead
                            );

                    if (!$client) {
                        $client =
                            $this
                                ->createClientFromLead(
                                    $websiteLead
                                );
                    }

                    $websiteLead->update([
                        'client_id' =>
                            $client->id,

                        'reviewed_at' =>
                            $websiteLead
                                ->reviewed_at
                            ?? now(),

                        'attended_at' =>
                            $websiteLead
                                ->attended_at
                            ?? now(),

                        'contacted_at' =>
                            $websiteLead
                                ->contacted_at
                            ?? now(),

                        'status' =>
                            'attending',
                    ]);

                    return $client;
                }
            );

        /*
        |--------------------------------------------------------------------------
        | CONTEXTO PARA LA COTIZACIÓN
        |--------------------------------------------------------------------------
        */

        session([
            'website_lead_context' => [
                'lead_id' =>
                    $websiteLead->id,

                'client_id' =>
                    $client->id,
            ],
        ]);

        return redirect()->route(
            'quotations.create',
            [
                'client_id' =>
                    $client->id,

                'lead_id' =>
                    $websiteLead->id,
            ]
        );
    }

    public function updateStatus(
        Request $request,
        WebsiteLead $websiteLead
    ): RedirectResponse {
        $validated =
            $request->validate([
                'status' => [
                    'required',

                    Rule::in([
                        'new',
                        'reviewed',
                        'attending',
                        'quoted',
                        'won',
                        'lost',
                    ]),
                ],
            ]);

        $status =
            $validated['status'];

        $updates = [
            'status' =>
                $status,
        ];

        if (
            $status === 'new'
        ) {
            $updates[
                'reviewed_at'
            ] =
                null;
        }

        if (
            $status !== 'new'
            &&
            !$websiteLead
                ->reviewed_at
        ) {
            $updates[
                'reviewed_at'
            ] =
                now();
        }

        if (
            in_array(
                $status,
                [
                    'attending',
                    'quoted',
                    'won',
                    'lost',
                ],
                true
            )
            &&
            !$websiteLead
                ->contacted_at
        ) {
            $updates[
                'contacted_at'
            ] =
                now();
        }

        if (
            in_array(
                $status,
                [
                    'won',
                    'lost',
                ],
                true
            )
        ) {
            $updates[
                'closed_at'
            ] =
                now();
        } else {
            $updates[
                'closed_at'
            ] =
                null;
        }

        $websiteLead->update(
            $updates
        );

        return back()->with(
            'success',
            'Estado actualizado correctamente.'
        );
    }

    public function destroy(
        WebsiteLead $websiteLead
    ): RedirectResponse {
        $websiteLead->delete();

        return back()->with(
            'success',
            'Solicitud eliminada correctamente.'
        );
    }

    private function findExistingClient(
        WebsiteLead $lead
    ): ?Client {
        $phone =
            preg_replace(
                '/\D+/',
                '',
                $lead->phone
            );

        return Client::query()
            ->where(
                function (
                    $query
                ) use (
                    $lead,
                    $phone
                ) {
                    if (
                        $lead->email
                    ) {
                        $query->orWhere(
                            'email',
                            $lead->email
                        );
                    }

                    if ($phone) {
                        $query->orWhereRaw(
                            "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '(', ''), ')', ''), '+', '') LIKE ?",
                            [
                                '%' .
                                substr(
                                    $phone,
                                    -8
                                ),
                            ]
                        );
                    }
                }
            )
            ->first();
    }

    private function createClientFromLead(
        WebsiteLead $lead
    ): Client {
        $names =
            preg_split(
                '/\s+/',
                trim(
                    $lead->name
                )
            )
            ?: [];

        $firstName =
            array_shift(
                $names
            )
            ?: $lead->name;

        $lastName =
            count($names)
                ? implode(
                    ' ',
                    $names
                )
                : null;

        $next =
            (
                Client::withTrashed()
                    ->max('id')
                ?? 0
            )
            + 1;

        $data = [
            'client_code' =>
                'CLI-' .
                str_pad(
                    (string)
                    $next,
                    4,
                    '0',
                    STR_PAD_LEFT
                ),

            'client_type' =>
                $lead->business_name
                    ? 'business'
                    : 'natural',

            'first_name' =>
                $firstName,

            'last_name' =>
                $lastName,

            'business_name' =>
                $lead
                    ->business_name,

            'email' =>
                $lead->email,

            'phone' =>
                $lead->phone,

            'notes' =>
                'Creado automáticamente desde ' .
                $lead->lead_number .
                '. Solicitud original: ' .
                $lead->message,

            'active' =>
                true,
        ];

        $columns =
            Schema::getColumnListing(
                'clients'
            );

        $data =
            collect(
                $data
            )
                ->only(
                    $columns
                )
                ->all();

        return Client::create(
            $data
        );
    }
}