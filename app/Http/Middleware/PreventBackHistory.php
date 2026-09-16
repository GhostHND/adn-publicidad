<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        /*
        |--------------------------------------------------------------------------
        | EVITAR CACHÉ DE PÁGINAS DEL SISTEMA
        |--------------------------------------------------------------------------
        |
        | Después de cerrar sesión no queremos que el navegador muestre una
        | copia antigua del Dashboard u otro módulo usando el botón Atrás.
        |
        */

        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0, private'
        );

        $response->headers->set(
            'Pragma',
            'no-cache'
        );

        $response->headers->set(
            'Expires',
            'Thu, 01 Jan 1970 00:00:00 GMT'
        );

        return $response;
    }
}