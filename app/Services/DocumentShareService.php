<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\SalePayment;

class DocumentShareService
{
    public function quotation(
        Quotation $quotation,
        string $pdfUrl,
        string $downloadUrl,
        string $filename
    ): array {
        $quotation->loadMissing(
            'client'
        );

        $client =
            $quotation->client;

        $clientName =
            $client?->display_name
            ?? 'Cliente';

        $number =
            $quotation->quotation_number
            ?? (
                'COT-' .
                $quotation->id
            );

        $total =
            $this->money(
                $quotation->total
                ?? 0
            );

        $message =
            $this->replaceVariables(
                config(
                    'adn.share.quotation_message',
                    'Hola {{cliente}}, le comparto la cotización {{numero}} de ADN Publicidad por un total de {{total}}. Quedo atento a cualquier consulta.'
                ),
                [
                    'cliente' =>
                        $clientName,

                    'numero' =>
                        $number,

                    'total' =>
                        $total,
                ]
            );

        return $this->payload(
            type:
                'quotation',

            title:
                'Cotización ' .
                $number,

            documentNumber:
                $number,

            clientName:
                $clientName,

            phone:
                $this->clientPhone(
                    $client
                ),

            message:
                $message,

            pdfUrl:
                $pdfUrl,

            downloadUrl:
                $downloadUrl,

            filename:
                $filename
        );
    }

    public function receipt(
        SalePayment $payment,
        string $pdfUrl,
        string $downloadUrl,
        string $filename
    ): array {
        $payment->loadMissing([
            'sale.client',
            'sale.payments',
        ]);

        $sale =
            $payment->sale;

        $client =
            $sale?->client;

        $clientName =
            $client?->display_name
            ?? 'Cliente';

        $number =
            $payment->receipt_number
            ?? (
                'REC-' .
                $payment->id
            );

        $amount =
            $this->money(
                $payment->amount
                ?? 0
            );

        $saleTotal =
            (float)
            (
                $sale?->total
                ?? 0
            );

        $paid =
            (float)
            (
                $sale
                    ?->payments
                    ?->sum(
                        'amount'
                    )
                ?? 0
            );

        $balance =
            $this->money(
                max(
                    $saleTotal
                    -
                    $paid,
                    0
                )
            );

        $message =
            $this->replaceVariables(
                config(
                    'adn.share.receipt_message',
                    'Hola {{cliente}}, gracias por su pago. Le comparto el recibo {{numero}} por {{monto}}. Saldo pendiente: {{saldo}}. Gracias por confiar en ADN Publicidad.'
                ),
                [
                    'cliente' =>
                        $clientName,

                    'numero' =>
                        $number,

                    'monto' =>
                        $amount,

                    'saldo' =>
                        $balance,
                ]
            );

        return $this->payload(
            type:
                'receipt',

            title:
                'Recibo ' .
                $number,

            documentNumber:
                $number,

            clientName:
                $clientName,

            phone:
                $this->clientPhone(
                    $client
                ),

            message:
                $message,

            pdfUrl:
                $pdfUrl,

            downloadUrl:
                $downloadUrl,

            filename:
                $filename
        );
    }

    private function payload(
        string $type,
        string $title,
        string $documentNumber,
        string $clientName,
        ?string $phone,
        string $message,
        string $pdfUrl,
        string $downloadUrl,
        string $filename
    ): array {
        $normalizedPhone =
            $this->normalizePhone(
                $phone
            );

        $whatsappUrl =
            'https://wa.me/';

        if ($normalizedPhone) {
            $whatsappUrl .=
                $normalizedPhone;
        }

        $whatsappUrl .=
            '?text=' .
            rawurlencode(
                $message
            );

        return [
            'type' =>
                $type,

            'title' =>
                $title,

            'document_number' =>
                $documentNumber,

            'client_name' =>
                $clientName,

            'phone' =>
                $phone,

            'normalized_phone' =>
                $normalizedPhone,

            'message' =>
                $message,

            'whatsapp_url' =>
                $whatsappUrl,

            'pdf_url' =>
                $pdfUrl,

            'download_url' =>
                $downloadUrl,

            'filename' =>
                $filename,
        ];
    }

    private function clientPhone(
        mixed $client
    ): ?string {
        if (!$client) {
            return null;
        }

        $attributes = [
            'phone',
            'mobile',
            'phone_primary',
            'phone_secondary',
            'whatsapp',
            'whatsapp_phone',
        ];

        foreach (
            $attributes
            as $attribute
        ) {
            $value =
                data_get(
                    $client,
                    $attribute
                );

            if (
                $value !== null
                &&
                trim(
                    (string)
                    $value
                ) !== ''
            ) {
                return (string)
                    $value;
            }
        }

        return null;
    }

    private function normalizePhone(
        ?string $phone
    ): ?string {
        if (!$phone) {
            return null;
        }

        $digits =
            preg_replace(
                '/\D+/',
                '',
                $phone
            );

        if (!$digits) {
            return null;
        }

        if (
            str_starts_with(
                $digits,
                '00'
            )
        ) {
            $digits =
                substr(
                    $digits,
                    2
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Honduras
        |--------------------------------------------------------------------------
        |
        | Si el cliente tiene solamente los 8 dígitos locales,
        | agregamos automáticamente 504.
        |
        */

        if (
            strlen(
                $digits
            ) === 8
        ) {
            $digits =
                '504' .
                $digits;
        }

        if (
            strlen(
                $digits
            ) < 10
        ) {
            return null;
        }

        return $digits;
    }

    private function replaceVariables(
        string $template,
        array $variables
    ): string {
        $message =
            $template;

        foreach (
            $variables
            as $key => $value
        ) {
            $message =
                str_replace(
                    '{{' .
                    $key .
                    '}}',
                    (string)
                    $value,
                    $message
                );
        }

        return $message;
    }

    private function money(
        mixed $value
    ): string {
        return
            'L ' .
            number_format(
                (float)
                $value,
                2,
                '.',
                ','
            );
    }
}