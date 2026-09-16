<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAdnWebSignature
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $secret =
            (string) config(
                'adn_integration.web_secret',
                ''
            );

        if (
            $secret ===
            ''
        ) {
            return response()->json(
                [
                    'ok' =>
                        false,

                    'message' =>
                        'Integración no configurada.',
                ],
                503
            );
        }

        $timestampHeader =
            trim(
                (string) $request->header(
                    'X-ADN-Timestamp',
                    ''
                )
            );

        $idempotencyKey =
            trim(
                (string) $request->header(
                    'X-ADN-Idempotency-Key',
                    ''
                )
            );

        $signature =
            strtolower(
                trim(
                    (string) $request->header(
                        'X-ADN-Signature',
                        ''
                    )
                )
            );

        if (
            !preg_match(
                '/^\d{10}$/',
                $timestampHeader
            )
            ||
            $idempotencyKey ===
                ''
            ||
            mb_strlen(
                $idempotencyKey
            ) >
                190
            ||
            !preg_match(
                '/^[a-f0-9]{64}$/',
                $signature
            )
        ) {
            return response()->json(
                [
                    'ok' =>
                        false,

                    'message' =>
                        'Firma de integración inválida.',
                ],
                401
            );
        }

        $timestamp =
            (int) $timestampHeader;

        $maxClockSkew =
            max(
                30,
                (int) config(
                    'adn_integration.max_clock_skew',
                    300
                )
            );

        if (
            abs(
                now()->timestamp
                -
                $timestamp
            ) >
            $maxClockSkew
        ) {
            return response()->json(
                [
                    'ok' =>
                        false,

                    'message' =>
                        'La solicitud de integración expiró.',
                ],
                401
            );
        }

        $rawBody =
            $request->getContent();

        $payloadToSign =
            $timestampHeader
            . "\n"
            . $idempotencyKey
            . "\n"
            . $rawBody;

        $expectedSignature =
            hash_hmac(
                'sha256',
                $payloadToSign,
                $secret
            );

        if (
            !hash_equals(
                $expectedSignature,
                $signature
            )
        ) {
            return response()->json(
                [
                    'ok' =>
                        false,

                    'message' =>
                        'Firma de integración inválida.',
                ],
                401
            );
        }

        return $next(
            $request
        );
    }
}