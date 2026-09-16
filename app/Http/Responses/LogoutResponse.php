<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        /*
        |--------------------------------------------------------------------------
        | CERRAR AUTENTICACIÓN
        |--------------------------------------------------------------------------
        */

        Auth::guard('web')->logout();

        /*
        |--------------------------------------------------------------------------
        | DESTRUIR SESIÓN
        |--------------------------------------------------------------------------
        */

        if ($request instanceof Request) {
            $request->session()->invalidate();

            $request->session()->regenerateToken();

            $request->session()->forget('url.intended');
        }

        /*
        |--------------------------------------------------------------------------
        | REGRESAR SIEMPRE AL LOGIN
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->to('/login')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, private',
                'Pragma' => 'no-cache',
                'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT',
            ]);
    }
}