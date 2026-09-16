<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integration\StoreAdnWebLeadRequest;
use App\Models\Client;
use App\Models\Employee;
use App\Models\WebsiteLead;
use App\Services\WebPushService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AdnWebLeadController extends Controller
{
    public function store(
        StoreAdnWebLeadRequest $request
    ): JsonResponse {
        $validated =
            $request->validated();

        $idempotencyKey =
            trim(
                (string) $request->header(
                    'X-ADN-Idempotency-Key',
                    ''
                )
            );

        $requestNumber =
            $validated[
                'request_number'
            ];

        $marker =
            '[ADN-WEB:'
            . $requestNumber
            . ']';

        try {
            $result =
                Cache::lock(
                    'adn-web-lead:'
                    . hash(
                        'sha256',
                        $idempotencyKey
                    ),
                    15
                )->block(
                    5,
                    function () use (
                        $validated,
                        $marker
                    ): array {
                        /*
                        |--------------------------------------------------------------------------
                        | IDEMPOTENCIA
                        |--------------------------------------------------------------------------
                        |
                        | Si ADN Web vuelve a enviar la misma solicitud,
                        | reutilizamos el WebsiteLead existente.
                        |
                        | Además, si ese lead fue creado durante el periodo
                        | en el que la integración no vinculaba clientes,
                        | lo reparamos automáticamente.
                        |
                        */

                        $existing =
                            WebsiteLead::query()
                                ->where(
                                    'source',
                                    'adn_web'
                                )
                                ->where(
                                    'message',
                                    'like',
                                    $marker
                                    . '%'
                                )
                                ->first();

                        if (
                            $existing
                        ) {
                            $existing =
                                $this
                                    ->ensureClient(
                                        $existing
                                    );

                            return [
                                'lead' =>
                                    $existing,

                                'duplicate' =>
                                    true,
                            ];
                        }

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
                            $this
                                ->buildMessage(
                                    $validated,
                                    $marker
                                );

                        $serviceInterest =
                            $this
                                ->buildServiceInterest(
                                    $validated
                                );

                        $lead =
                            DB::transaction(
                                function () use (
                                    $validated,
                                    $operator,
                                    $message,
                                    $serviceInterest
                                ): WebsiteLead {
                                    /*
                                    |--------------------------------------------------------------------------
                                    | CREAR SOLICITUD
                                    |--------------------------------------------------------------------------
                                    */

                                    $lead =
                                        WebsiteLead::create([
                                            'lead_number' =>
                                                null,

                                            'name' =>
                                                $validated[
                                                    'client_name'
                                                ],

                                            'business_name' =>
                                                $validated[
                                                    'company'
                                                ]
                                                ?? null,

                                            'phone' =>
                                                $validated[
                                                    'phone'
                                                ],

                                            'email' =>
                                                $validated[
                                                    'email'
                                                ]
                                                ?? null,

                                            'service_interest' =>
                                                $serviceInterest,

                                            'message' =>
                                                $message,

                                            'source' =>
                                                'adn_web',

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
                                        ]);

                                    /*
                                    |--------------------------------------------------------------------------
                                    | NÚMERO DE SOLICITUD
                                    |--------------------------------------------------------------------------
                                    */

                                    $lead->update([
                                        'lead_number' =>
                                            'SOL-'
                                            . now()->format(
                                                'Y'
                                            )
                                            . '-'
                                            . str_pad(
                                                (string)
                                                $lead->id,
                                                4,
                                                '0',
                                                STR_PAD_LEFT
                                            ),
                                    ]);

                                    $lead->refresh();

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CLIENTE
                                    |--------------------------------------------------------------------------
                                    |
                                    | Restauramos el comportamiento original:
                                    |
                                    | 1. Buscar por correo o teléfono.
                                    | 2. Reutilizarlo si existe.
                                    | 3. Crearlo si no existe.
                                    | 4. Vincular client_id al WebsiteLead.
                                    |
                                    */

                                    $client =
                                        $this
                                            ->findExistingClient(
                                                $lead
                                            );

                                    if (
                                        !$client
                                    ) {
                                        $client =
                                            $this
                                                ->createClientFromLead(
                                                    $lead
                                                );
                                    }

                                    $lead->update([
                                        'client_id' =>
                                            $client->id,
                                    ]);

                                    return $lead
                                        ->fresh();
                                }
                            );

                        return [
                            'lead' =>
                                $lead,

                            'duplicate' =>
                                false,
                        ];
                    }
                );
        } catch (
            LockTimeoutException
        ) {
            return response()->json(
                [
                    'ok' =>
                        false,

                    'message' =>
                        'La solicitud está siendo procesada.',
                ],
                409
            );
        }

        /** @var WebsiteLead $lead */
        $lead =
            $result[
                'lead'
            ];

        $duplicate =
            (bool) $result[
                'duplicate'
            ];

        /*
        |--------------------------------------------------------------------------
        | NOTIFICACIÓN
        |--------------------------------------------------------------------------
        |
        | Solo notificamos cuando la solicitud realmente se acaba de crear.
        |
        */

        if (
            !$duplicate
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

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA PARA ADN WEB
        |--------------------------------------------------------------------------
        |
        | Ahora client_id ya existe antes de responder.
        |
        | ADN Web podrá guardar:
        |
        | - app_lead_id
        | - app_client_id
        |
        */

        return response()->json([
            'ok' =>
                true,

            'duplicate' =>
                $duplicate,

            'request_number' =>
                $validated[
                    'request_number'
                ],

            'lead' => [
                'id' =>
                    $lead->id,

                'lead_number' =>
                    $lead
                        ->lead_number,

                'status' =>
                    $lead->status,

                'client_id' =>
                    $lead
                        ->client_id,

                'quotation_id' =>
                    $lead
                        ->quotation_id,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ASEGURAR CLIENTE EN LEADS EXISTENTES
    |--------------------------------------------------------------------------
    |
    | Permite reparar solicitudes creadas anteriormente con client_id = null.
    |
    */

    private function ensureClient(
        WebsiteLead $lead
    ): WebsiteLead {
        if (
            $lead->client_id
        ) {
            return $lead
                ->fresh();
        }

        return DB::transaction(
            function () use (
                $lead
            ): WebsiteLead {
                $lockedLead =
                    WebsiteLead::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $lead->id
                        );

                if (
                    $lockedLead
                        ->client_id
                ) {
                    return $lockedLead
                        ->fresh();
                }

                $client =
                    $this
                        ->findExistingClient(
                            $lockedLead
                        );

                if (
                    !$client
                ) {
                    $client =
                        $this
                            ->createClientFromLead(
                                $lockedLead
                            );
                }

                $lockedLead->update([
                    'client_id' =>
                        $client->id,
                ]);

                return $lockedLead
                    ->fresh();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCAR CLIENTE EXISTENTE
    |--------------------------------------------------------------------------
    |
    | Se conserva la misma lógica que ya utiliza la APP al atender
    | manualmente una solicitud.
    |
    */

    private function findExistingClient(
        WebsiteLead $lead
    ): ?Client {
        $phone =
            preg_replace(
                '/\D+/',
                '',
                (string) $lead->phone
            );

        return Client::query()
            ->where(
                function (
                    $query
                ) use (
                    $lead,
                    $phone
                ): void {
                    if (
                        $lead->email
                    ) {
                        $query->orWhere(
                            'email',
                            $lead->email
                        );
                    }

                    if (
                        $phone
                    ) {
                        $query->orWhereRaw(
                            "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '(', ''), ')', ''), '+', '') LIKE ?",
                            [
                                '%'
                                .
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

    /*
    |--------------------------------------------------------------------------
    | CREAR CLIENTE DESDE SOLICITUD
    |--------------------------------------------------------------------------
    */

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
            count(
                $names
            )
                ? implode(
                    ' ',
                    $names
                )
                : null;

        /*
        |--------------------------------------------------------------------------
        | CÓDIGO CLIENTE
        |--------------------------------------------------------------------------
        |
        | Conservamos el esquema utilizado actualmente por ADN APP:
        |
        | CLI-0001
        | CLI-0002
        | ...
        |
        */

        $next =
            (
                Client::withTrashed()
                    ->max(
                        'id'
                    )
                ??
                0
            )
            +
            1;

        $data = [
            'client_code' =>
                'CLI-'
                .
                str_pad(
                    (string) $next,
                    4,
                    '0',
                    STR_PAD_LEFT
                ),

            'client_type' =>
                $lead
                    ->business_name
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
                $lead
                    ->email,

            'phone' =>
                $lead
                    ->phone,

            'notes' =>
                'Creado automáticamente desde '
                .
                $lead
                    ->lead_number
                .
                '. Solicitud original: '
                .
                $lead
                    ->message,

            'active' =>
                true,
        ];

        /*
        |--------------------------------------------------------------------------
        | COMPATIBILIDAD CON EL ESQUEMA REAL
        |--------------------------------------------------------------------------
        */

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

    private function buildServiceInterest(
        array $data
    ): string {
        $items =
            $data[
                'items'
            ]
            ?? [];

        $first =
            $items[
                0
            ]
            ?? [];

        $productName =
            $this->cleanLine(
                (string) (
                    $first[
                        'product_name'
                    ]
                    ?? 'Solicitud web'
                )
            );

        $appReference =
            $this->cleanLine(
                (string) (
                    $first[
                        'app_reference_code'
                    ]
                    ?? ''
                )
            );

        $label =
            $productName;

        if (
            $appReference !==
            ''
        ) {
            $label .=
                ' · '
                .
                $appReference;
        }

        if (
            count(
                $items
            ) >
            1
        ) {
            $label .=
                ' +'
                .
                (
                    count(
                        $items
                    )
                    -
                    1
                );
        }

        return Str::limit(
            $label,
            180,
            ''
        );
    }

    private function buildMessage(
        array $data,
        string $marker
    ): string {
        $lines = [
            $marker,

            'Referencia ADN Web: '
            .
            $data[
                'request_number'
            ],

            'Origen: Sitio web ADN Publicidad',
        ];

        $whatsapp =
            $this->cleanLine(
                (string) (
                    $data[
                        'whatsapp'
                    ]
                    ?? ''
                )
            );

        $phone =
            $this->cleanLine(
                (string)
                $data[
                    'phone'
                ]
            );

        if (
            $whatsapp !==
                ''
            &&
            $whatsapp !==
                $phone
        ) {
            $lines[] =
                'WhatsApp: '
                .
                $whatsapp;
        }

        $lines[] =
            '';

        foreach (
            $data[
                'items'
            ]
            as
            $index =>
            $item
        ) {
            if (
                $index >
                0
            ) {
                $lines[] =
                    '';

                $lines[] =
                    str_repeat(
                        '-',
                        32
                    );

                $lines[] =
                    '';
            }

            if (
                count(
                    $data[
                        'items'
                    ]
                ) >
                1
            ) {
                $lines[] =
                    'PRODUCTO '
                    .
                    (
                        $index
                        +
                        1
                    );
            }

            $lines[] =
                'Producto: '
                .
                $this->cleanLine(
                    (string)
                    $item[
                        'product_name'
                    ]
                );

            if (
                !empty(
                    $item[
                        'product_code'
                    ]
                )
            ) {
                $lines[] =
                    'Código web: '
                    .
                    $this->cleanLine(
                        (string)
                        $item[
                            'product_code'
                        ]
                    );
            }

            if (
                !empty(
                    $item[
                        'app_reference_code'
                    ]
                )
            ) {
                $lines[] =
                    'Referencia APP: '
                    .
                    $this->cleanLine(
                        (string)
                        $item[
                            'app_reference_code'
                        ]
                    );
            }

            $lines[] =
                'Cantidad: '
                .
                $item[
                    'quantity'
                ];

            if (
                !empty(
                    $item[
                        'quote_mode'
                    ]
                )
            ) {
                $lines[] =
                    'Modo de cotización: '
                    .
                    $this->cleanLine(
                        (string)
                        $item[
                            'quote_mode'
                        ]
                    );
            }

            $values =
                $item[
                    'values'
                ]
                ?? [];

            if (
                count(
                    $values
                ) >
                0
            ) {
                $lines[] =
                    '';

                $lines[] =
                    'Configuración:';

                foreach (
                    $values
                    as
                    $value
                ) {
                    $label =
                        $this->cleanLine(
                            (string)
                            $value[
                                'label'
                            ]
                        );

                    $valueText =
                        $this->cleanLine(
                            (string) (
                                $value[
                                    'value'
                                ]
                                ?? ''
                            )
                        );

                    $unit =
                        $this->cleanLine(
                            (string) (
                                $value[
                                    'unit'
                                ]
                                ?? ''
                            )
                        );

                    $line =
                        '- '
                        .
                        $label
                        .
                        ': '
                        .
                        (
                            $valueText !==
                                ''
                                ? $valueText
                                : '—'
                        );

                    if (
                        $unit !==
                        ''
                    ) {
                        $line .=
                            ' '
                            .
                            $unit;
                    }

                    $lines[] =
                        $line;
                }
            }

            $files =
                $item[
                    'files'
                ]
                ?? [];

            if (
                count(
                    $files
                ) >
                0
            ) {
                $lines[] =
                    '';

                $lines[] =
                    'Archivos adjuntos en ADN Web: '
                    .
                    count(
                        $files
                    );

                foreach (
                    $files
                    as
                    $fileIndex =>
                    $file
                ) {
                    $lines[] =
                        '- Archivo '
                        .
                        (
                            $fileIndex
                            +
                            1
                        )
                        .
                        ': '
                        .
                        $this->cleanLine(
                            (string)
                            $file[
                                'original_name'
                            ]
                        );
                }
            }
        }

        if (
            !empty(
                $data[
                    'notes'
                ]
            )
        ) {
            $lines[] =
                '';

            $lines[] =
                'Notas del cliente:';

            $lines[] =
                trim(
                    (string)
                    $data[
                        'notes'
                    ]
                );
        }

        return Str::limit(
            implode(
                PHP_EOL,
                $lines
            ),
            5000,
            ''
        );
    }

    private function cleanLine(
        string $value
    ): string {
        return trim(
            preg_replace(
                '/\s+/u',
                ' ',
                $value
            )
            ??
            ''
        );
    }
}