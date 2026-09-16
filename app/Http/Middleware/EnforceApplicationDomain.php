<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceApplicationDomain
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | DESACTIVADO
        |--------------------------------------------------------------------------
        |
        | Durante desarrollo local ADN_ENFORCE_DOMAINS=false.
        |
        */

        if (
            !config(
                'adn_domains.enforce',
                false
            )
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | LOCALHOST
        |--------------------------------------------------------------------------
        */

        $host =
            strtolower(
                $request->getHost()
            );

        if (
            in_array(
                $host,
                [
                    'localhost',
                    '127.0.0.1',
                    '::1',
                ],
                true
            )
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | DOMINIOS CONFIGURADOS
        |--------------------------------------------------------------------------
        */

        $publicUrl =
            rtrim(
                (string)
                config(
                    'adn_domains.public_url'
                ),
                '/'
            );

        $adminUrl =
            rtrim(
                (string)
                config(
                    'adn_domains.admin_url'
                ),
                '/'
            );

        $publicHost =
            $this->hostFromUrl(
                $publicUrl
            );

        $adminHost =
            $this->hostFromUrl(
                $adminUrl
            );

        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN INCOMPLETA
        |--------------------------------------------------------------------------
        */

        if (
            !$publicHost
            ||
            !$adminHost
        ) {
            return $next($request);
        }

        $path =
            trim(
                $request->path(),
                '/'
            );

        /*
        |--------------------------------------------------------------------------
        | SITIO PÚBLICO
        |--------------------------------------------------------------------------
        |
        | Si alguien intenta entrar a una ruta administrativa desde
        | adnpublicidad.site, lo mandamos a app.adnpublicidad.site.
        |
        */

        if (
            $this->sameHost(
                $host,
                $publicHost
            )
        ) {
            if (
                $this->isAdministrativePath(
                    $path
                )
            ) {
                return redirect()
                    ->away(
                        $this->targetUrl(
                            $adminUrl,
                            $request
                        )
                    );
            }

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | APP ADMINISTRATIVA
        |--------------------------------------------------------------------------
        |
        | Las páginas comerciales pertenecen al sitio público.
        |
        */

        if (
            $this->sameHost(
                $host,
                $adminHost
            )
        ) {
            /*
            |--------------------------------------------------------------------------
            | RAÍZ DEL SUBDOMINIO APP
            |--------------------------------------------------------------------------
            */

            if (
                $path === ''
                &&
                $request->isMethod(
                    'GET'
                )
            ) {
                return redirect()
                    ->to(
                        $request->user()
                            ? '/dashboard'
                            : '/login'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | PÁGINAS PÚBLICAS
            |--------------------------------------------------------------------------
            */

            if (
                $this->isPublicPage(
                    $request,
                    $path
                )
            ) {
                return redirect()
                    ->away(
                        $this->targetUrl(
                            $publicUrl,
                            $request
                        )
                    );
            }

            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | HOST DESCONOCIDO
        |--------------------------------------------------------------------------
        |
        | No bloqueamos previews o configuraciones temporales del servidor.
        |
        */

        return $next($request);
    }

    /*
    |--------------------------------------------------------------------------
    | RUTAS ADMINISTRATIVAS
    |--------------------------------------------------------------------------
    */

    private function isAdministrativePath(
        string $path
    ): bool {
        $prefixes = [

            /*
            |--------------------------------------------------------------------------
            | AUTENTICACIÓN
            |--------------------------------------------------------------------------
            */

            'login',
            'logout',
            'forgot-password',
            'reset-password',
            'email',
            'two-factor',
            'user',

            /*
            |--------------------------------------------------------------------------
            | OPERACIÓN
            |--------------------------------------------------------------------------
            */

            'dashboard',
            'employees',
            'clients',
            'catalog',
            'inventory',
            'quotations',
            'sales',
            'receipts',
            'accounts-receivable',
            'work-orders',
            'tasks',
            'installations',
            'cctv',

            /*
            |--------------------------------------------------------------------------
            | FINANZAS
            |--------------------------------------------------------------------------
            */

            'cash',
            'income',
            'expenses',
            'reports',

            /*
            |--------------------------------------------------------------------------
            | ADMINISTRACIÓN
            |--------------------------------------------------------------------------
            */

            'users',
            'audit',
            'settings',
            'notifications',

            /*
            |--------------------------------------------------------------------------
            | ADMINISTRACIÓN DEL SITIO
            |--------------------------------------------------------------------------
            */

            'website',
        ];

        foreach (
            $prefixes
            as $prefix
        ) {
            if (
                $path === $prefix
                ||
                str_starts_with(
                    $path,
                    $prefix . '/'
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | PÁGINAS PÚBLICAS
    |--------------------------------------------------------------------------
    */

    private function isPublicPage(
        Request $request,
        string $path
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | SOLO REDIRECCIONAMOS NAVEGACIÓN
        |--------------------------------------------------------------------------
        |
        | No redireccionamos POST /contacto/solicitud para no perder datos.
        |
        */

        if (
            !$request->isMethod(
                'GET'
            )
            &&
            !$request->isMethod(
                'HEAD'
            )
        ) {
            return false;
        }

        if (
            $path === 'servicios'
            ||
            $path === 'portafolio'
            ||
            $path === 'contacto'
        ) {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | HOST DESDE URL
    |--------------------------------------------------------------------------
    */

    private function hostFromUrl(
        string $url
    ): ?string {
        if (
            trim(
                $url
            ) === ''
        ) {
            return null;
        }

        $host =
            parse_url(
                $url,
                PHP_URL_HOST
            );

        if (
            !is_string(
                $host
            )
            ||
            $host === ''
        ) {
            return null;
        }

        return strtolower(
            $host
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COMPARAR DOMINIOS
    |--------------------------------------------------------------------------
    |
    | Permitimos www y sin www para el sitio público.
    |
    */

    private function sameHost(
        string $current,
        string $configured
    ): bool {
        $current =
            strtolower(
                $current
            );

        $configured =
            strtolower(
                $configured
            );

        if (
            $current ===
            $configured
        ) {
            return true;
        }

        return
            preg_replace(
                '/^www\./',
                '',
                $current
            )
            ===
            preg_replace(
                '/^www\./',
                '',
                $configured
            );
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR URL DESTINO
    |--------------------------------------------------------------------------
    */

    private function targetUrl(
        string $baseUrl,
        Request $request
    ): string {
        $uri =
            $request->getRequestUri();

        if (
            $uri === '/'
        ) {
            return $baseUrl;
        }

        return
            rtrim(
                $baseUrl,
                '/'
            )
            .
            '/'
            .
            ltrim(
                $uri,
                '/'
            );
    }
}