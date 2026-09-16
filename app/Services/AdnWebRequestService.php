<?php

namespace App\Services;

use App\Models\WebsiteLead;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AdnWebRequestService
{
    public function detail(
        WebsiteLead $lead
    ): array {
        $requestNumber =
            $this->requestNumber(
                $lead
            );

        $path =
            '/integrations/adn-app/quote-requests/'
            . rawurlencode(
                $requestNumber
            );

        $response =
            $this->signedGet(
                $path
            );

        $payload =
            $response->json();

        if (
            !$response->successful()
            ||
            !is_array(
                $payload
            )
            ||
            (
                $payload[
                    'ok'
                ]
                ?? false
            ) !==
                true
        ) {
            $message =
                is_array(
                    $payload
                )
                    ? (
                        $payload[
                            'message'
                        ]
                        ?? null
                    )
                    : null;

            throw new RuntimeException(
                $message
                ?: (
                    'ADN Web respondió HTTP '
                    . $response->status()
                )
            );
        }

        $webRequest =
            $payload[
                'request'
            ]
            ?? null;

        if (
            !is_array(
                $webRequest
            )
        ) {
            throw new RuntimeException(
                'ADN Web no devolvió la solicitud solicitada.'
            );
        }

        return $webRequest;
    }

    public function file(
        WebsiteLead $lead,
        int $fileId,
        string $mode
    ): Response {
        if (
            !in_array(
                $mode,
                [
                    'view',
                    'download',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Modo de archivo inválido.'
            );
        }

        $requestNumber =
            $this->requestNumber(
                $lead
            );

        $path =
            '/integrations/adn-app/quote-requests/'
            . rawurlencode(
                $requestNumber
            )
            . '/files/'
            . $fileId
            . '/'
            . $mode;

        return $this
            ->signedGet(
                $path
            );
    }

    private function requestNumber(
        WebsiteLead $lead
    ): string {
        if (
            $lead->source !==
            'adn_web'
        ) {
            throw new RuntimeException(
                'La solicitud no pertenece a ADN Web.'
            );
        }

        $matches =
            [];

        $found =
            preg_match(
                '/\[ADN-WEB:(SOL-WEB-\d{6,})\]/',
                (string) $lead->message,
                $matches
            );

        if (
            $found !==
                1
            ||
            empty(
                $matches[
                    1
                ]
            )
        ) {
            throw new RuntimeException(
                'No se encontró la referencia original de ADN Web.'
            );
        }

        return (string) $matches[
            1
        ];
    }

    private function signedGet(
        string $path
    ): Response {
        $baseUrl =
            trim(
                (string) config(
                    'adn_integration.web_url',
                    ''
                )
            );

        $secret =
            trim(
                (string) config(
                    'adn_integration.web_secret',
                    ''
                )
            );

        if (
            $baseUrl ===
                ''
            ||
            $secret ===
                ''
        ) {
            throw new RuntimeException(
                'La conexión con ADN Web no está configurada.'
            );
        }

        $timestamp =
            (string) now()
                ->timestamp;

        $requestKey =
            'adn-app:'
            . now()->format(
                'YmdHis'
            )
            . ':'
            . Str::uuid()
                ->toString();

        $accessKey =
            hash_hmac(
                'sha256',
                'app-to-web',
                $secret,
                true
            );

        $payload =
            $timestamp
            . "\n"
            . $requestKey
            . "\n"
            . 'GET'
            . "\n"
            . $path
            . "\n";

        $signature =
            hash_hmac(
                'sha256',
                $payload,
                $accessKey
            );

        $url =
            rtrim(
                $baseUrl,
                '/'
            )
            . $path;

        return Http::timeout(
            max(
                1,
                (int) config(
                    'adn_integration.web_timeout',
                    10
                )
            )
        )
            ->connectTimeout(
                max(
                    1,
                    (int) config(
                        'adn_integration.web_connect_timeout',
                        3
                    )
                )
            )
            ->withHeaders([
                'X-ADN-Timestamp' =>
                    $timestamp,

                'X-ADN-Idempotency-Key' =>
                    $requestKey,

                'X-ADN-Signature' =>
                    $signature,
            ])
            ->get(
                $url
            );
    }
}