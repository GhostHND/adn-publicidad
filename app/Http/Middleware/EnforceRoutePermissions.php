<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnforceRoutePermissions
{
    /*
    |--------------------------------------------------------------------------
    | ROLES INTERNOS
    |--------------------------------------------------------------------------
    */

    private const ADMIN = 'administrator';
    private const SALES = 'sales';
    private const DESIGNER = 'designer';
    private const PRODUCTION = 'production';
    private const INSTALLER = 'installer';

    /*
    |--------------------------------------------------------------------------
    | ACCESO BASE POR MÓDULO
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    |
    | No dependemos de una columna roles.active porque tu tabla roles
    | actualmente no posee esa columna.
    |
    */

    private const MODULE_ROLES = [
        'dashboard' => [
            self::ADMIN,
            self::SALES,
            self::DESIGNER,
            self::PRODUCTION,
            self::INSTALLER,
        ],

        'employees' => [
            self::ADMIN,
        ],

        'clients' => [
            self::ADMIN,
            self::SALES,
        ],

        'catalog' => [
            self::ADMIN,
            self::SALES,
            self::DESIGNER,
            self::PRODUCTION,
        ],

        'inventory' => [
            self::ADMIN,
            self::SALES,
            self::PRODUCTION,
        ],

        'quotations' => [
            self::ADMIN,
            self::SALES,
        ],

        'sales' => [
            self::ADMIN,
            self::SALES,
        ],

        'receipts' => [
            self::ADMIN,
            self::SALES,
        ],

        'accounts-receivable' => [
            self::ADMIN,
            self::SALES,
        ],

        'work-orders' => [
            self::ADMIN,
            self::SALES,
            self::DESIGNER,
            self::PRODUCTION,
            self::INSTALLER,
        ],

        'tasks' => [
            self::ADMIN,
            self::DESIGNER,
            self::PRODUCTION,
            self::INSTALLER,
        ],

        'installations' => [
            self::ADMIN,
            self::PRODUCTION,
            self::INSTALLER,
        ],

        'cctv' => [
            self::ADMIN,
            self::INSTALLER,
        ],

        'leads' => [
            self::ADMIN,
            self::SALES,
        ],

        'cash' => [
            self::ADMIN,
        ],

        'income' => [
            self::ADMIN,
        ],

        'expenses' => [
            self::ADMIN,
        ],

        'reports' => [
            self::ADMIN,
        ],

        'website' => [
            self::ADMIN,
        ],

        'users' => [
            self::ADMIN,
        ],

        'audit' => [
            self::ADMIN,
        ],

        'system-settings' => [
            self::ADMIN,
        ],

        'pwa' => [
            self::ADMIN,
            self::SALES,
            self::DESIGNER,
            self::PRODUCTION,
            self::INSTALLER,
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | HANDLE
    |--------------------------------------------------------------------------
    */

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | INVITADOS
        |--------------------------------------------------------------------------
        |
        | Este middleware no sustituye al middleware auth.
        |
        */

        if (!$user) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | USUARIO DESACTIVADO
        |--------------------------------------------------------------------------
        */

        if (
            property_exists($user, 'active')
            || isset($user->active)
        ) {
            if ($user->active === false || $user->active === 0) {
                Auth::guard('web')->logout();

                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return redirect()
                    ->to('/login')
                    ->withErrors([
                        'email' =>
                            'Tu cuenta de acceso se encuentra desactivada.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RUTAS SIEMPRE PERMITIDAS
        |--------------------------------------------------------------------------
        */

        if ($this->isAlwaysAllowed($request)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | OBTENER ROLES REALES
        |--------------------------------------------------------------------------
        |
        | No usamos:
        |
        | where('roles.active', true)
        |
        | porque esa columna no existe en tu estructura actual.
        |
        */

        $roles =
            $this->userRoleSlugs(
                $user
            );

        /*
        |--------------------------------------------------------------------------
        | ADMINISTRADOR
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                self::ADMIN,
                $roles,
                true
            )
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | IDENTIFICAR MÓDULO
        |--------------------------------------------------------------------------
        */

        $module =
            $this->resolveModule(
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | DENEGAR RUTAS ADMINISTRATIVAS NO CLASIFICADAS
        |--------------------------------------------------------------------------
        |
        | Para usuarios que no son administradores usamos deny-by-default.
        |
        */

        if ($module === null) {
            abort(
                403,
                'No tienes autorización para acceder a esta sección.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PERMISO ESPECÍFICO
        |--------------------------------------------------------------------------
        |
        | Si tu sistema ya tiene un permiso explícito para esta acción,
        | intentamos respetarlo.
        |
        | Si la lógica personalizada de permisos encuentra una diferencia de
        | esquema, no dejamos caer el sistema: continuamos con la seguridad
        | por rol.
        |
        */

        $permission =
            $this->resolveExistingPermission(
                $request,
                $module
            );

        if ($permission !== null) {
            $permissionResult =
                $this->checkUserPermission(
                    $user,
                    $permission
                );

            if ($permissionResult === true) {
                return $next($request);
            }

            if ($permissionResult === false) {
                abort(
                    403,
                    'No tienes el permiso requerido para realizar esta acción.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SEGURIDAD POR ROL
        |--------------------------------------------------------------------------
        */

        $allowedRoles =
            self::MODULE_ROLES[$module]
            ?? [];

        foreach ($roles as $role) {
            if (
                in_array(
                    $role,
                    $allowedRoles,
                    true
                )
            ) {
                return $next($request);
            }
        }

        abort(
            403,
            'No tienes autorización para acceder a esta sección.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER ROLES DEL USUARIO
    |--------------------------------------------------------------------------
    |
    | Esta es la corrección principal.
    |
    | Consultamos la relación roles SIN asumir que existe roles.active.
    |
    */

    private function userRoleSlugs(
        $user
    ): array {
        if (
            !method_exists(
                $user,
                'roles'
            )
        ) {
            return [];
        }

        try {
            $roles =
                $user
                    ->roles()
                    ->get();

            return $roles
                ->map(
                    function ($role): string {
                        $value =
                            $role->slug
                            ?? $role->name
                            ?? $role->code
                            ?? $role->role
                            ?? '';

                        return $this->normalizeRole(
                            (string) $value
                        );
                    }
                )
                ->filter()
                ->unique()
                ->values()
                ->all();
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR ROLES
    |--------------------------------------------------------------------------
    |
    | Soporta tanto los slugs internos como los nombres visibles.
    |
    */

    private function normalizeRole(
        string $role
    ): string {
        $normalized =
            Str::of($role)
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

        return match ($normalized) {
            /*
            |--------------------------------------------------------------------------
            | ADMINISTRADOR
            |--------------------------------------------------------------------------
            */

            'admin',
            'administrator',
            'administrador',
            'administrador_general',
            'administracion',
            'superadmin',
            'super_admin' =>
                self::ADMIN,

            /*
            |--------------------------------------------------------------------------
            | VENTAS
            |--------------------------------------------------------------------------
            */

            'sales',
            'venta',
            'ventas',
            'vendedor',
            'vendedora' =>
                self::SALES,

            /*
            |--------------------------------------------------------------------------
            | DISEÑO
            |--------------------------------------------------------------------------
            */

            'designer',
            'design',
            'diseno',
            'disenador',
            'disenadora' =>
                self::DESIGNER,

            /*
            |--------------------------------------------------------------------------
            | PRODUCCIÓN
            |--------------------------------------------------------------------------
            */

            'production',
            'produccion',
            'productor' =>
                self::PRODUCTION,

            /*
            |--------------------------------------------------------------------------
            | INSTALACIÓN
            |--------------------------------------------------------------------------
            */

            'installer',
            'instalador',
            'instalacion',
            'installation' =>
                self::INSTALLER,

            default =>
                $normalized,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | COMPROBAR PERMISO INDIVIDUAL
    |--------------------------------------------------------------------------
    |
    | Retorna:
    |
    | true  = permitido
    | false = denegado
    | null  = no se pudo evaluar con seguridad
    |
    */

    private function checkUserPermission(
        $user,
        string $permission
    ): ?bool {
        if (
            !method_exists(
                $user,
                'hasPermission'
            )
        ) {
            return null;
        }

        try {
            return (bool) $user
                ->hasPermission(
                    $permission
                );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | NO ROMPER EL SISTEMA POR UNA DIFERENCIA DE ESQUEMA
            |--------------------------------------------------------------------------
            |
            | La seguridad por roles continuará aplicándose.
            |
            */

            report($exception);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RUTAS SIEMPRE PERMITIDAS
    |--------------------------------------------------------------------------
    */

    private function isAlwaysAllowed(
        Request $request
    ): bool {
        $path =
            trim(
                $request->path(),
                '/'
            );

        $routeName =
            $this->routeName(
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | SITIO PÚBLICO
        |--------------------------------------------------------------------------
        */

        if (
            $path === ''
            || $path === 'servicios'
            || $path === 'portafolio'
            || $path === 'contacto'
            || str_starts_with(
                $path,
                'contacto/'
            )
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | AUTENTICACIÓN
        |--------------------------------------------------------------------------
        */

        if (
            $path === 'login'
            || $path === 'logout'
            || str_starts_with(
                $path,
                'forgot-password'
            )
            || str_starts_with(
                $path,
                'reset-password'
            )
            || str_starts_with(
                $path,
                'email/'
            )
            || str_starts_with(
                $path,
                'two-factor-'
            )
            || str_starts_with(
                $path,
                'user/confirm-password'
            )
            || str_starts_with(
                $path,
                'user/confirmed-password-status'
            )
            || str_starts_with(
                $path,
                '.well-known/'
            )
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN PERSONAL
        |--------------------------------------------------------------------------
        |
        | settings/system NO está incluido aquí.
        |
        */

        if (
            $path === 'settings'
            || str_starts_with(
                $path,
                'settings/profile'
            )
            || str_starts_with(
                $path,
                'settings/security'
            )
            || str_starts_with(
                $path,
                'settings/appearance'
            )
            || str_starts_with(
                $path,
                'settings/password'
            )
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | NOMBRES DE RUTA PERMITIDOS
        |--------------------------------------------------------------------------
        */

        $allowedRoutePrefixes = [
            'home',
            'public.',
            'login',
            'logout',
            'password.',
            'verification.',
            'two-factor.',
            'passkey',
            'passkeys.',
            'profile.',
            'appearance.',
            'security.',
        ];

        foreach (
            $allowedRoutePrefixes
            as $prefix
        ) {
            if (
                $routeName === $prefix
                || str_starts_with(
                    $routeName,
                    $prefix
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | RESOLVER MÓDULO
    |--------------------------------------------------------------------------
    */

    private function resolveModule(
        Request $request
    ): ?string {
        $path =
            trim(
                $request->path(),
                '/'
            );

        /*
        |--------------------------------------------------------------------------
        | RUTAS ESPECIALES
        |--------------------------------------------------------------------------
        */

        if (
            $this->matches(
                $path,
                'settings/system'
            )
        ) {
            return 'system-settings';
        }

        if (
            $this->matches(
                $path,
                'website/leads'
            )
            || $this->matches(
                $path,
                'notifications/leads'
            )
        ) {
            return 'leads';
        }

        if (
            $this->matches(
                $path,
                'notifications/push'
            )
        ) {
            return 'pwa';
        }

        /*
        |--------------------------------------------------------------------------
        | MÓDULOS DIRECTOS
        |--------------------------------------------------------------------------
        */

        $modules = [
            'dashboard' =>
                'dashboard',

            'employees' =>
                'employees',

            'clients' =>
                'clients',

            'catalog' =>
                'catalog',

            'inventory' =>
                'inventory',

            'quotations' =>
                'quotations',

            'sales' =>
                'sales',

            'receipts' =>
                'receipts',

            'accounts-receivable' =>
                'accounts-receivable',

            'work-orders' =>
                'work-orders',

            'tasks' =>
                'tasks',

            'installations' =>
                'installations',

            'cctv' =>
                'cctv',

            'cash' =>
                'cash',

            'income' =>
                'income',

            'expenses' =>
                'expenses',

            'reports' =>
                'reports',

            'website' =>
                'website',

            'users' =>
                'users',

            'audit' =>
                'audit',
        ];

        foreach (
            $modules
            as $prefix =>
                $module
        ) {
            if (
                $this->matches(
                    $path,
                    $prefix
                )
            ) {
                return $module;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | COMPARAR PREFIJO
    |--------------------------------------------------------------------------
    */

    private function matches(
        string $path,
        string $prefix
    ): bool {
        return (
            $path === $prefix
            || str_starts_with(
                $path,
                $prefix . '/'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESOLVER PERMISO EXISTENTE
    |--------------------------------------------------------------------------
    */

    private function resolveExistingPermission(
        Request $request,
        string $module
    ): ?string {
        try {
            if (
                !Schema::hasTable(
                    'permissions'
                )
            ) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }

        $candidates =
            $this->permissionCandidates(
                $request,
                $module
            );

        if ($candidates === []) {
            return null;
        }

        try {
            foreach (
                $candidates
                as $candidate
            ) {
                if (
                    Permission::query()
                        ->where(
                            'slug',
                            $candidate
                        )
                        ->exists()
                ) {
                    return $candidate;
                }
            }
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | CANDIDATOS DE PERMISO
    |--------------------------------------------------------------------------
    */

    private function permissionCandidates(
        Request $request,
        string $module
    ): array {
        $routeName =
            $this->routeName(
                $request
            );

        $method =
            strtoupper(
                $request->method()
            );

        $base =
            match ($module) {
                'accounts-receivable' =>
                    'accounts-receivable',

                'work-orders' =>
                    'work-orders',

                'system-settings' =>
                    'settings',

                'leads' =>
                    'leads',

                default =>
                    $module,
            };

        /*
        |--------------------------------------------------------------------------
        | CONSULTA
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $method,
                ['GET', 'HEAD'],
                true
            )
        ) {
            return [
                "{$base}.view",
                "{$base}.manage",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | CREAR
        |--------------------------------------------------------------------------
        */

        if (
            str_ends_with(
                $routeName,
                '.store'
            )
            || str_ends_with(
                $routeName,
                '.create'
            )
        ) {
            return [
                "{$base}.create",
                "{$base}.manage",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ELIMINAR
        |--------------------------------------------------------------------------
        */

        if (
            $method === 'DELETE'
            || str_contains(
                $routeName,
                'destroy'
            )
        ) {
            return [
                "{$base}.delete",
                "{$base}.manage",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $method,
                [
                    'POST',
                    'PUT',
                    'PATCH',
                ],
                true
            )
        ) {
            return [
                "{$base}.update",
                "{$base}.manage",
            ];
        }

        return [
            "{$base}.manage",
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NOMBRE DE LA RUTA
    |--------------------------------------------------------------------------
    */

    private function routeName(
        Request $request
    ): string {
        $route =
            $request->route();

        if (
            !is_object($route)
            || !method_exists(
                $route,
                'getName'
            )
        ) {
            return '';
        }

        return (string) (
            $route->getName()
            ?? ''
        );
    }
}