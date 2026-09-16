<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\WebsiteLead;
use Illuminate\Http\RedirectResponse;

class AdnWebEditorAccessController extends Controller
{
    public function open(
        WebsiteLead $websiteLead
    ): RedirectResponse {
        abort_unless(
            $websiteLead->source ===
                'adn_web',
            404
        );

        $matches =
            [];

        $found =
            preg_match(
                '/\[ADN-WEB:(SOL-WEB-\d{6,})\]/',
                (string) $websiteLead->message,
                $matches
            );

        abort_unless(
            $found ===
                1
            &&
            !empty(
                $matches[
                    1
                ]
            ),
            404
        );

        $requestNumber =
            (string) $matches[
                1
            ];

        $secret =
            trim(
                (string) config(
                    'adn_integration.web_secret',
                    ''
                )
            );

        abort_if(
            $secret ===
                '',
            503,
            'La integración con ADN Web no está configurada.'
        );

        $editorUrl =
            trim(
                (string) config(
                    'adn_integration.editor_url',
                    ''
                )
            );

        abort_if(
            $editorUrl ===
                '',
            503,
            'La URL del Editor ADN no está configurada.'
        );

        $ttl =
            max(
                30,
                min(
                    300,
                    (int) config(
                        'adn_integration.editor_access_ttl',
                        90
                    )
                )
            );

        $expires =
            now()
                ->addSeconds(
                    $ttl
                )
                ->timestamp;

        $nonce =
            bin2hex(
                random_bytes(
                    32
                )
            );

        $accessKey =
            hash_hmac(
                'sha256',
                'editor-access',
                $secret,
                true
            );

        $payload =
            $requestNumber
            . "\n"
            . $expires
            . "\n"
            . $nonce;

        $signature =
            hash_hmac(
                'sha256',
                $payload,
                $accessKey
            );

        $query =
            http_build_query(
                [
                    'request_number' =>
                        $requestNumber,

                    'expires' =>
                        $expires,

                    'nonce' =>
                        $nonce,

                    'signature' =>
                        $signature,
                ],
                '',
                '&',
                PHP_QUERY_RFC3986
            );

        $url =
            rtrim(
                $editorUrl,
                '/'
            )
            . '/editor-access/from-app?'
            . $query;

        return redirect()
            ->away(
                $url
            );
    }
}