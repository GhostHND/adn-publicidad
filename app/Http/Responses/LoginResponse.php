<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        /*
        |--------------------------------------------------------------------------
        | ELIMINAR DESTINOS ANTERIORES
        |--------------------------------------------------------------------------
        |
        | No queremos que una URL pública o un destino guardado anteriormente
        | pueda mandar al usuario fuera del sistema después de iniciar sesión.
        |
        */

        if ($request instanceof Request) {
            $request->session()->forget('url.intended');
        }

        /*
        |--------------------------------------------------------------------------
        | DESTINO ÚNICO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->to('/dashboard')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, private',
                'Pragma' => 'no-cache',
                'Expires' => 'Thu, 01 Jan 1970 00:00:00 GMT',
            ]);
    }
}