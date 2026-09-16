<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\WebsiteLead;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class AdnWebContactLeadController extends Controller
{
    public function store(
        Request $request
    ): JsonResponse {
        $validated =
            $request->validate([
                'source_reference' => [
                    'required',
                    'string',
                    'max:80',
                    'regex:/^CON-WEB-\d{6,}$/',
                ],

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
                    'nullable',
                    'string',
                    'max:40',
                    'required_without:email',
                ],

                'email' => [
                    'nullable',
                    'email:rfc',
                    'max:180',
                    'required_without:phone',
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

                'preferred_contact' => [
                    'nullable',

                    Rule::in([
                        'whatsapp',
                        'phone',
                        'email',
                    ]),
                ],

                'client_ip' => [
                    'nullable',
                    'ip',
                    'max:45',
                ],

                'client_user_agent' => [
                    'nullable',
                    'string',
                    'max:1000',
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
                ->orderBy(
                    'id'
                )
                ->first();

        $message =
            $this->messageForApp(
                $validated[
                    'message'
                ],
                $validated[
                    'preferred_contact'
                ]
                ?? null
            );

        [
            $lead,
            $created,
        ] =
            DB::transaction(
                function () use (
                    $validated,
                    $operator,
                    $message
                ): array {
                    $lead =
                        WebsiteLead::query()
                            ->firstOrCreate(
                                [
                                    'external_reference' =>
                                        $validated[
                                            'source_reference'
                                        ],
                                ],
                                [
                                    'lead_number' =>
                                        null,

                                    'name' =>
                                        $validated[
                                            'name'
                                        ],

                                    'business_name' =>
                                        $validated[
                                            'business_name'
                                        ]
                                        ?? null,

                                    'phone' =>
                                        $validated[
                                            'phone'
                                        ]
                                        ?? null,

                                    'email' =>
                                        $validated[
                                            'email'
                                        ]
                                        ?? null,

                                    'service_interest' =>
                                        $validated[
                                            'service_interest'
                                        ]
                                        ?? null,

                                    'message' =>
                                        $message,

                                    'source' =>
                                        'adn-web-contact',

                                    'status' =>
                                        'new',

                                    'assigned_employee_id' =>
                                        $operator?->id,

                                    'ip_address' =>
                                        $validated[
                                            'client_ip'
                                        ]
                                        ?? null,

                                    'user_agent' =>
                                        $validated[
                                            'client_user_agent'
                                        ]
                                        ?? null,
                                ]
                            );

                    $created =
                        $lead
                            ->wasRecentlyCreated;

                    if (
                        $created
                        &&
                        !$lead
                            ->lead_number
                    ) {
                        $lead->update([
                            'lead_number' =>
                                'SOL-'
                                .
                                now()->format(
                                    'Y'
                                )
                                .
                                '-'
                                .
                                str_pad(
                                    (string)
                                    $lead->id,
                                    4,
                                    '0',
                                    STR_PAD_LEFT
                                ),
                        ]);
                    }

                    return [
                        $lead->fresh(),
                        $created,
                    ];
                }
            );

        /*
        |--------------------------------------------------------------------------
        | NOTIFICACIÓN
        |--------------------------------------------------------------------------
        |
        | Solamente notificamos cuando realmente se creó una solicitud nueva.
        | Un reintento idempotente no genera una segunda notificación.
        |
        */

        if (
            $created
        ) {
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
        }

        return response()->json([
            'ok' =>
                true,

            'created' =>
                $created,

            'lead' => [
                'id' =>
                    $lead->id,

                'lead_number' =>
                    $lead
                        ->lead_number,

                'external_reference' =>
                    $lead
                        ->external_reference,

                'status' =>
                    $lead->status,
            ],
        ]);
    }

    private function messageForApp(
        string $message,
        ?string $preferredContact
    ): string {
        $label =
            match (
                $preferredContact
            ) {
                'whatsapp' =>
                    'WhatsApp',

                'phone' =>
                    'Llamada telefónica',

                'email' =>
                    'Correo electrónico',

                default =>
                    null,
            };

        if (
            !$label
        ) {
            return $message;
        }

        return $message
            .
            "\n\n"
            .
            'Preferencia de contacto: '
            .
            $label;
    }
}