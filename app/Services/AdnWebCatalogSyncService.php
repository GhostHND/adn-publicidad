<?php

namespace App\Services;

use App\Models\CatalogItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AdnWebCatalogSyncService
{
    public function sync(
        CatalogItem $item
    ): bool {
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
            report(
                new RuntimeException(
                    'La integración con ADN Web no está configurada.'
                )
            );

            return false;
        }

        $payload = [
            'app_catalog_item_id' =>
                $item->id,

            'item_code' =>
                $item->item_code,

            'name' =>
                $item->name,

            'description' =>
                $item->description,

            'item_type' =>
                $item->item_type,

            'category' =>
                $item->category,

            'pricing_method' =>
                $item
                    ->pricing_method,

            'measurement_unit' =>
                $item
                    ->measurement_unit,

            /*
            |--------------------------------------------------------------------------
            | IMPORTANTE
            |--------------------------------------------------------------------------
            |
            | El producto deja de estar disponible para el sitio si:
            | - active = false
            | - fue eliminado mediante SoftDeletes.
            |
            */

            'active' =>
                (bool) $item->active
                &&
                !$item->trashed(),

            'source_created_at' =>
                $item
                    ->created_at
                    ?->toIso8601String(),

            'source_updated_at' =>
                $item
                    ->updated_at
                    ?->toIso8601String(),

            'source_deleted_at' =>
                $item
                    ->deleted_at
                    ?->toIso8601String(),
        ];

        /*
        |--------------------------------------------------------------------------
        | NO SE ENVÍAN
        |--------------------------------------------------------------------------
        |
        | cost_price
        | sale_price
        | cost_rate
        | sale_rate
        | margin_percentage
        | recetas
        | inventario
        |
        */

        $path =
            '/integrations/adn-app/catalog-products';

        try {
            $json =
                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                    |
                    JSON_UNESCAPED_SLASHES
                    |
                    JSON_THROW_ON_ERROR
                );

            $timestamp =
                (string) now()
                    ->timestamp;

            $requestKey =
                'catalog-item:'
                . $item->id
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

            $signaturePayload =
                $timestamp
                . "\n"
                . $requestKey
                . "\n"
                . 'POST'
                . "\n"
                . $path
                . "\n"
                . $json;

            $signature =
                hash_hmac(
                    'sha256',
                    $signaturePayload,
                    $accessKey
                );

            $response =
                Http::acceptJson()
                    ->timeout(
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
                    ->withBody(
                        $json,
                        'application/json'
                    )
                    ->post(
                        rtrim(
                            $baseUrl,
                            '/'
                        )
                        . $path
                    );

            $responsePayload =
                $response->json();

            if (
                !$response->successful()
                ||
                !is_array(
                    $responsePayload
                )
                ||
                (
                    $responsePayload[
                        'ok'
                    ]
                    ?? false
                ) !==
                    true
            ) {
                $message =
                    is_array(
                        $responsePayload
                    )
                        ? (
                            $responsePayload[
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

            return true;
        } catch (
            Throwable $exception
        ) {
            report(
                $exception
            );

            return false;
        }
    }
}