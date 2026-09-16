<?php

namespace App\Providers;

use App\Http\Middleware\PreventBackHistory;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Features;
use Throwable;

class SecurityServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | REGISTRO
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | DESTINO DESPUÉS DEL LOGIN
        |--------------------------------------------------------------------------
        */

        config([
            'fortify.home' => '/dashboard',
        ]);

        /*
        |--------------------------------------------------------------------------
        | DESHABILITAR REGISTRO PÚBLICO
        |--------------------------------------------------------------------------
        */

        $features = config(
            'fortify.features',
            []
        );

        $features = array_values(
            array_filter(
                $features,
                static fn ($feature): bool =>
                    $feature !== Features::registration()
            )
        );

        config([
            'fortify.features' => $features,
        ]);

        /*
        |--------------------------------------------------------------------------
        | RESPUESTAS PERSONALIZADAS
        |--------------------------------------------------------------------------
        */

        $this->app->singleton(
            LoginResponseContract::class,
            LoginResponse::class
        );

        $this->app->singleton(
            LogoutResponseContract::class,
            LogoutResponse::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    public function boot(Router $router): void
    {
        /*
        |--------------------------------------------------------------------------
        | PREVENIR CACHÉ DE PÁGINAS DEL SISTEMA
        |--------------------------------------------------------------------------
        */

        $router->pushMiddlewareToGroup(
            'web',
            PreventBackHistory::class
        );

        /*
        |--------------------------------------------------------------------------
        | INFORMACIÓN DE AUTORIZACIÓN PARA INERTIA
        |--------------------------------------------------------------------------
        |
        | Esto controla únicamente la interfaz.
        |
        | La seguridad real continúa siendo responsabilidad de Laravel y del
        | middleware EnforceRoutePermissions.
        |
        */

        Inertia::share(
            'authz',
            function (Request $request): array {
                $user = $request->user();

                if (!$user) {
                    return [
                        'roles' => [],
                        'permissions' => [],
                        'is_admin' => false,
                    ];
                }

                $roles = collect();
                $permissions = collect();

                /*
                |--------------------------------------------------------------------------
                | ROLES
                |--------------------------------------------------------------------------
                */

                try {
                    if (method_exists($user, 'roles')) {
                        $roles = $user
                            ->roles()
                            ->get()
                            ->map(function ($role) {
                                /*
                                |--------------------------------------------------------------------------
                                | DETECTAR NOMBRE DEL ROL
                                |--------------------------------------------------------------------------
                                |
                                | Soportamos diferentes nombres de columna por
                                | compatibilidad con la estructura existente.
                                |
                                */

                                $roleName =
                                    $role->name
                                    ?? $role->slug
                                    ?? $role->code
                                    ?? $role->role
                                    ?? '';

                                return $this->normalizeRole(
                                    (string) $roleName
                                );
                            })
                            ->filter()
                            ->unique()
                            ->values();
                    }
                } catch (Throwable $exception) {
                    report($exception);
                }

                /*
                |--------------------------------------------------------------------------
                | PERMISOS DIRECTOS DEL USUARIO
                |--------------------------------------------------------------------------
                */

                try {
                    if (method_exists($user, 'permissions')) {
                        $directPermissions = $user
                            ->permissions()
                            ->get()
                            ->map(function ($permission) {
                                $permissionName =
                                    $permission->name
                                    ?? $permission->slug
                                    ?? $permission->code
                                    ?? '';

                                return $this->normalizePermission(
                                    (string) $permissionName
                                );
                            });

                        $permissions = $permissions
                            ->merge(
                                $directPermissions
                            );
                    }
                } catch (Throwable $exception) {
                    report($exception);
                }

                /*
                |--------------------------------------------------------------------------
                | PERMISOS HEREDADOS DESDE LOS ROLES
                |--------------------------------------------------------------------------
                */

                try {
                    if (method_exists($user, 'roles')) {
                        $userRoles = $user
                            ->roles()
                            ->get();

                        foreach ($userRoles as $role) {
                            if (
                                !method_exists(
                                    $role,
                                    'permissions'
                                )
                            ) {
                                continue;
                            }

                            $rolePermissions = $role
                                ->permissions()
                                ->get()
                                ->map(function ($permission) {
                                    $permissionName =
                                        $permission->name
                                        ?? $permission->slug
                                        ?? $permission->code
                                        ?? '';

                                    return $this->normalizePermission(
                                        (string) $permissionName
                                    );
                                });

                            $permissions = $permissions
                                ->merge(
                                    $rolePermissions
                                );
                        }
                    }
                } catch (Throwable $exception) {
                    report($exception);
                }

                /*
                |--------------------------------------------------------------------------
                | NORMALIZAR PERMISOS
                |--------------------------------------------------------------------------
                */

                $permissions = $permissions
                    ->filter()
                    ->unique()
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | DETECTAR ADMINISTRADOR
                |--------------------------------------------------------------------------
                */

                $isAdmin =
                    $roles->contains(
                        'admin'
                    );

                return [
                    'roles' =>
                        $roles->all(),

                    'permissions' =>
                        $permissions->all(),

                    'is_admin' =>
                        $isAdmin,
                ];
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR ROL
    |--------------------------------------------------------------------------
    |
    | En la base de datos los roles pueden aparecer visualmente como:
    |
    | Administrador
    | Diseño
    | Instalador
    | Producción
    | Ventas
    |
    | El frontend utiliza identificadores internos estables:
    |
    | admin
    | diseno
    | instalador
    | produccion
    | ventas
    |
    */

    private function normalizeRole(string $role): string
    {
        $normalized = Str::of(
            $role
        )
            ->trim()
            ->ascii()
            ->lower()
            ->replace(
                [' ', '-', '.'],
                '_'
            )
            ->replaceMatches(
                '/_+/',
                '_'
            )
            ->trim('_')
            ->toString();

        /*
        |--------------------------------------------------------------------------
        | ALIASES
        |--------------------------------------------------------------------------
        */

        return match ($normalized) {
            /*
            |------------------------------------------------------------------
            | ADMINISTRADOR
            |------------------------------------------------------------------
            */

            'administrador',
            'administrator',
            'administracion',
            'administrador_general',
            'superadmin',
            'super_admin',
            'admin' =>
                'admin',

            /*
            |------------------------------------------------------------------
            | DISEÑO
            |------------------------------------------------------------------
            */

            'disenador',
            'disenadora',
            'diseno',
            'designer' =>
                'diseno',

            /*
            |------------------------------------------------------------------
            | PRODUCCIÓN
            |------------------------------------------------------------------
            */

            'produccion',
            'productor',
            'production' =>
                'produccion',

            /*
            |------------------------------------------------------------------
            | INSTALADOR
            |------------------------------------------------------------------
            */

            'instalador',
            'instalacion',
            'installation' =>
                'instalador',

            /*
            |------------------------------------------------------------------
            | VENTAS
            |------------------------------------------------------------------
            */

            'venta',
            'ventas',
            'vendedor',
            'vendedora',
            'sales' =>
                'ventas',

            default =>
                $normalized,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR PERMISO
    |--------------------------------------------------------------------------
    */

    private function normalizePermission(string $permission): string
    {
        return Str::of(
            $permission
        )
            ->trim()
            ->ascii()
            ->lower()
            ->replace(
                [' ', '-', '.'],
                '_'
            )
            ->replaceMatches(
                '/_+/',
                '_'
            )
            ->trim('_')
            ->toString();
    }
}