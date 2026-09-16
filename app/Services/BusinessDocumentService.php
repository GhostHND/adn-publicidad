<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\SalePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class BusinessDocumentService
{
    /*
    |--------------------------------------------------------------------------
    | COTIZACIÓN
    |--------------------------------------------------------------------------
    */

    public function generateQuotation(
        Quotation $quotation
    ): array {
        $quotation->loadMissing([
            'client',
            'items',
        ]);

        $document =
            $this->quotationData(
                $quotation
            );

        $company =
            $this->companyData();

        $pdf =
            Pdf::loadView(
                'pdf.quotation',
                [
                    'document' =>
                        $document,

                    'company' =>
                        $company,
                ]
            )
                ->setPaper(
                    'letter',
                    'portrait'
                );

        $content =
            $pdf->output();

        $filename =
            $this->safeFilename(
                $quotation
                    ->quotation_number
                ?? (
                    'COT-' .
                    $quotation->id
                )
            ) .
            '.pdf';

        $storagePath =
            'documents/quotations/' .
            $filename;

        Storage::disk('local')
            ->put(
                $storagePath,
                $content
            );

        return [
            'filename' =>
                $filename,

            'storage_path' =>
                $storagePath,

            'content' =>
                $content,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RECIBO
    |--------------------------------------------------------------------------
    */

    public function generateReceipt(
        SalePayment $payment
    ): array {
        $payment->loadMissing([
            'sale.client',
            'sale.items',
            'sale.payments',
            'financialAccount',
        ]);

        $document =
            $this->receiptData(
                $payment
            );

        $company =
            $this->companyData();

        $pdf =
            Pdf::loadView(
                'pdf.receipt',
                [
                    'document' =>
                        $document,

                    'company' =>
                        $company,
                ]
            )
                ->setPaper(
                    'letter',
                    'portrait'
                );

        $content =
            $pdf->output();

        $filename =
            $this->safeFilename(
                $payment
                    ->receipt_number
                ?? (
                    'REC-' .
                    $payment->id
                )
            ) .
            '.pdf';

        $storagePath =
            'documents/receipts/' .
            $filename;

        Storage::disk('local')
            ->put(
                $storagePath,
                $content
            );

        return [
            'filename' =>
                $filename,

            'storage_path' =>
                $storagePath,

            'content' =>
                $content,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DE COTIZACIÓN
    |--------------------------------------------------------------------------
    */

    private function quotationData(
        Quotation $quotation
    ): array {
        $client =
            $quotation->client;

        $items =
            $quotation
                ->items
                ->map(
                    function ($item) {
                        $quantity =
                            (float)
                            (
                                $item->quantity
                                ?? 1
                            );

                        $subtotal =
                            (float)
                            (
                                $item->subtotal
                                ?? 0
                            );

                        $unitPrice =
                            $item->unit_price;

                        if (
                            $unitPrice === null
                            &&
                            $quantity > 0
                        ) {
                            $unitPrice =
                                $subtotal /
                                $quantity;
                        }

                        $width =
                            $item->width;

                        $height =
                            $item->height;

                        $measurementUnit =
                            $item
                                ->measurement_unit;

                        $dimensions = null;

                        if (
                            $width !== null
                            &&
                            $height !== null
                            &&
                            (
                                (float) $width > 0
                                ||
                                (float) $height > 0
                            )
                        ) {
                            $dimensions =
                                $this
                                    ->number(
                                        $width
                                    ) .
                                ' x ' .
                                $this
                                    ->number(
                                        $height
                                    );

                            if (
                                $measurementUnit
                            ) {
                                $dimensions .=
                                    ' ' .
                                    $measurementUnit;
                            }
                        } elseif (
                            $measurementUnit
                        ) {
                            $dimensions =
                                $measurementUnit;
                        }

                        return [
                            'name' =>
                                $this
                                    ->firstValue(
                                        $item,
                                        [
                                            'item_name',
                                            'name',
                                            'description',
                                        ],
                                        'Producto / servicio'
                                    ),

                            'description' =>
                                $this
                                    ->firstValue(
                                        $item,
                                        [
                                            'description',
                                            'notes',
                                        ]
                                    ),

                            'quantity' =>
                                $quantity,

                            'dimensions' =>
                                $dimensions,

                            'unit_price' =>
                                (float)
                                ($unitPrice ?? 0),

                            'subtotal' =>
                                $subtotal,
                        ];
                    }
                )
                ->values()
                ->all();

        $subtotal =
            (float)
            (
                $quotation->subtotal
                ??
                collect($items)
                    ->sum(
                        'subtotal'
                    )
            );

        $discount =
            (float)
            (
                $this->firstValue(
                    $quotation,
                    [
                        'discount_amount',
                        'discount',
                    ],
                    0
                )
                ?? 0
            );

        $tax =
            (float)
            (
                $this->firstValue(
                    $quotation,
                    [
                        'tax_amount',
                        'tax',
                    ],
                    0
                )
                ?? 0
            );

        $total =
            (float)
            (
                $quotation->total
                ??
                (
                    $subtotal
                    -
                    $discount
                    +
                    $tax
                )
            );

        return [
            'id' =>
                $quotation->id,

            'number' =>
                $quotation
                    ->quotation_number
                ??
                (
                    'COT-' .
                    $quotation->id
                ),

            'status' =>
                $quotation
                    ->status,

            'date' =>
                $this->formatDate(
                    $quotation
                        ->quotation_date
                    ??
                    $quotation
                        ->created_at
                ),

            'valid_until' =>
                $this->formatDate(
                    $quotation
                        ->valid_until
                ),

            'client' => [
                'name' =>
                    $client
                        ?->display_name
                    ??
                    'Cliente',

                'identity' =>
                    $this
                        ->firstValue(
                            $client,
                            [
                                'rtn',
                                'identity_number',
                                'tax_id',
                            ]
                        ),

                'phone' =>
                    $this
                        ->firstValue(
                            $client,
                            [
                                'phone',
                                'mobile',
                                'phone_primary',
                                'phone_secondary',
                            ]
                        ),

                'email' =>
                    $client
                        ?->email,

                'address' =>
                    $client
                        ?->address,

                'city' =>
                    $client
                        ?->city,
            ],

            'items' =>
                $items,

            'subtotal' =>
                $subtotal,

            'discount' =>
                $discount,

            'tax' =>
                $tax,

            'total' =>
                $total,

            'notes' =>
                $this
                    ->firstValue(
                        $quotation,
                        [
                            'notes',
                            'customer_notes',
                            'description',
                        ]
                    ),

            'terms' =>
                $this
                    ->firstValue(
                        $quotation,
                        [
                            'terms',
                            'terms_conditions',
                        ]
                    ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DEL RECIBO
    |--------------------------------------------------------------------------
    */

    private function receiptData(
        SalePayment $payment
    ): array {
        $sale =
            $payment->sale;

        $client =
            $sale?->client;

        $payments =
            $sale?->payments
            ?? collect();

        $saleTotal =
            (float)
            (
                $sale?->total
                ?? 0
            );

        $paidTotal =
            (float)
            $payments->sum(
                'amount'
            );

        $balance =
            max(
                round(
                    $saleTotal
                    -
                    $paidTotal,
                    2
                ),
                0
            );

        $items =
            $sale
                ?->items
                ?->map(
                    function ($item) {
                        return [
                            'name' =>
                                $this
                                    ->firstValue(
                                        $item,
                                        [
                                            'item_name',
                                            'name',
                                            'description',
                                        ],
                                        'Producto / servicio'
                                    ),

                            'quantity' =>
                                (float)
                                (
                                    $item->quantity
                                    ?? 1
                                ),

                            'subtotal' =>
                                (float)
                                (
                                    $item->subtotal
                                    ?? 0
                                ),
                        ];
                    }
                )
                ?->values()
                ?->all()
            ?? [];

        return [
            'id' =>
                $payment->id,

            'number' =>
                $payment
                    ->receipt_number
                ??
                (
                    'REC-' .
                    $payment->id
                ),

            'date' =>
                $this->formatDateTime(
                    $payment
                        ->payment_date
                    ??
                    $payment
                        ->created_at
                ),

            'sale_number' =>
                $sale
                    ?->sale_number,

            'client' => [
                'name' =>
                    $client
                        ?->display_name
                    ??
                    'Cliente',

                'identity' =>
                    $this
                        ->firstValue(
                            $client,
                            [
                                'rtn',
                                'identity_number',
                                'tax_id',
                            ]
                        ),

                'phone' =>
                    $this
                        ->firstValue(
                            $client,
                            [
                                'phone',
                                'mobile',
                                'phone_primary',
                                'phone_secondary',
                            ]
                        ),

                'email' =>
                    $client
                        ?->email,

                'address' =>
                    $client
                        ?->address,

                'city' =>
                    $client
                        ?->city,
            ],

            'payment' => [
                'amount' =>
                    (float)
                    $payment->amount,

                'method' =>
                    $payment
                        ->payment_method,

                'method_label' =>
                    $this
                        ->paymentMethodLabel(
                            $payment
                                ->payment_method
                        ),

                'reference' =>
                    $this
                        ->firstValue(
                            $payment,
                            [
                                'reference',
                                'transaction_reference',
                                'bank_reference',
                            ]
                        ),

                'financial_account' =>
                    $payment
                        ->financialAccount
                        ?->name,
            ],

            'items' =>
                $items,

            'sale_total' =>
                $saleTotal,

            'paid_total' =>
                $paidTotal,

            'balance' =>
                $balance,

            'notes' =>
                $this
                    ->firstValue(
                        $payment,
                        [
                            'notes',
                            'description',
                        ]
                    ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | INFORMACIÓN CORPORATIVA
    |--------------------------------------------------------------------------
    */

    private function companyData(): array
    {
        return [
            'name' =>
                config(
                    'adn.business_name',
                    'ADN Publicidad'
                ),

            'location' =>
                config(
                    'adn.business_location',
                    'Danlí, El Paraíso, Honduras'
                ),

            'phone' =>
                config(
                    'adn.phone'
                ),

            'email' =>
                config(
                    'adn.email'
                ),

            'website' =>
                config(
                    'adn.website',
                    'adnpublicidad.site'
                ),

            'currency_symbol' =>
                config(
                    'adn.currency_symbol',
                    'L'
                ),

            'logo' =>
                $this
                    ->resolveLogo(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | LOGO LOCAL → DATA URI
    |--------------------------------------------------------------------------
    */

    private function resolveLogo(): ?string
    {
        $configured =
            config(
                'adn.logo_path'
            );

        $paths = [];

        if ($configured) {
            $paths[] =
                $configured;

            $paths[] =
                public_path(
                    ltrim(
                        $configured,
                        '/\\'
                    )
                );
        }

        $paths[] =
            public_path(
                'images/adn-logo.png'
            );

        $paths[] =
            public_path(
                'images/logo.png'
            );

        $paths[] =
            public_path(
                'logo.png'
            );

        foreach (
            array_unique($paths)
            as $path
        ) {
            if (
                !$path
                ||
                !is_file($path)
            ) {
                continue;
            }

            try {
                $contents =
                    file_get_contents(
                        $path
                    );

                if (
                    $contents === false
                ) {
                    continue;
                }

                $mime =
                    mime_content_type(
                        $path
                    )
                    ?: 'image/png';

                return
                    'data:' .
                    $mime .
                    ';base64,' .
                    base64_encode(
                        $contents
                    );
            } catch (
                Throwable
            ) {
                continue;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function paymentMethodLabel(
        ?string $method
    ): string {
        return match ($method) {
            'cash' =>
                'Efectivo',

            'transfer' =>
                'Transferencia',

            'card' =>
                'Tarjeta',

            'other' =>
                'Otro',

            default =>
                $method
                ?: 'No especificado',
        };
    }

    private function firstValue(
        mixed $object,
        array $attributes,
        mixed $default = null
    ): mixed {
        if (!$object) {
            return $default;
        }

        foreach (
            $attributes
            as $attribute
        ) {
            $value =
                data_get(
                    $object,
                    $attribute
                );

            if (
                $value !== null
                &&
                $value !== ''
            ) {
                return $value;
            }
        }

        return $default;
    }

    private function formatDate(
        mixed $value
    ): ?string {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse(
                $value
            )->format(
                'd/m/Y'
            );
        } catch (
            Throwable
        ) {
            return (string) $value;
        }
    }

    private function formatDateTime(
        mixed $value
    ): ?string {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse(
                $value
            )->format(
                'd/m/Y h:i A'
            );
        } catch (
            Throwable
        ) {
            return (string) $value;
        }
    }

    private function safeFilename(
        string $value
    ): string {
        $safe =
            preg_replace(
                '/[^A-Za-z0-9\-_]/',
                '-',
                $value
            );

        return trim(
            $safe
                ?: 'documento',
            '-'
        );
    }

    private function number(
        mixed $value
    ): string {
        return rtrim(
            rtrim(
                number_format(
                    (float) $value,
                    3,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }
}