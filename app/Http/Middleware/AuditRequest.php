<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditRequest
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | EJECUTAR PETICIÓN
        |--------------------------------------------------------------------------
        */

        $response =
            $next(
                $request
            );

        /*
        |--------------------------------------------------------------------------
        | SOLO USUARIOS AUTENTICADOS
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();

        if (!$user) {
            return $response;
        }

        /*
        |--------------------------------------------------------------------------
        | SOLO OPERACIONES QUE MODIFICAN DATOS
        |--------------------------------------------------------------------------
        |
        | Los GET no se registran para evitar llenar la auditoría únicamente
        | por navegar en el sistema.
        |
        */

        if (
            !in_array(
                strtoupper(
                    $request->method()
                ),
                [
                    'POST',
                    'PUT',
                    'PATCH',
                    'DELETE',
                ],
                true
            )
        ) {
            return $response;
        }

        $route =
            $request->route();

        $routeName =
            $route?->getName();

        /*
        |--------------------------------------------------------------------------
        | EVITAR AUDITAR LA PROPIA AUDITORÍA
        |--------------------------------------------------------------------------
        */

        if (
            $routeName
            &&
            str_starts_with(
                $routeName,
                'audit.'
            )
        ) {
            return $response;
        }

        try {
            [
                $entityType,
                $entityId,
            ] =
                $this
                    ->resolveEntity(
                        $request
                    );

            $module =
                $this
                    ->resolveModule(
                        $routeName
                    );

            $action =
                $this
                    ->resolveAction(
                        $request,
                        $routeName
                    );

            AuditLog::create([
                'user_id' =>
                    $user->id,

                'employee_id' =>
                    $user->employee_id,

                'module' =>
                    $module,

                'action' =>
                    $action,

                'description' =>
                    $this
                        ->description(
                            $module,
                            $action,
                            $routeName
                        ),

                'route_name' =>
                    $routeName,

                'method' =>
                    strtoupper(
                        $request->method()
                    ),

                'url' =>
                    $request->fullUrl(),

                'status_code' =>
                    $response
                        ->getStatusCode(),

                'entity_type' =>
                    $entityType,

                'entity_id' =>
                    $entityId,

                'request_payload' =>
                    $this
                        ->sanitizePayload(
                            $request->all()
                        ),

                'ip_address' =>
                    $request->ip(),

                'user_agent' =>
                    $request->userAgent(),
            ]);
        } catch (
            Throwable $exception
        ) {
            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA NUNCA DEBE ROMPER EL SISTEMA
            |--------------------------------------------------------------------------
            */

            report(
                $exception
            );
        }

        return $response;
    }

    private function sanitizePayload(
        mixed $value,
        ?string $currentKey = null
    ): mixed {
        /*
        |--------------------------------------------------------------------------
        | CAMPOS SENSIBLES
        |--------------------------------------------------------------------------
        */

        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            '_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'secret',
            'secret_value',
        ];

        if (
            $currentKey
            &&
            in_array(
                strtolower(
                    $currentKey
                ),
                $sensitiveKeys,
                true
            )
        ) {
            return '[PROTEGIDO]';
        }

        /*
        |--------------------------------------------------------------------------
        | ARCHIVOS
        |--------------------------------------------------------------------------
        */

        if (
            $value instanceof
            UploadedFile
        ) {
            return [
                'file' =>
                    $value
                        ->getClientOriginalName(),

                'mime' =>
                    $value
                        ->getClientMimeType(),

                'size' =>
                    $value
                        ->getSize(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ARRAYS
        |--------------------------------------------------------------------------
        */

        if (
            is_array(
                $value
            )
        ) {
            $result = [];

            foreach (
                $value
                as $key => $item
            ) {
                $result[$key] =
                    $this
                        ->sanitizePayload(
                            $item,
                            (string)
                            $key
                        );
            }

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | OBJETOS
        |--------------------------------------------------------------------------
        */

        if (
            is_object(
                $value
            )
        ) {
            return method_exists(
                $value,
                '__toString'
            )
                ? (string)
                    $value
                : get_class(
                    $value
                );
        }

        return $value;
    }

    private function resolveModule(
        ?string $routeName
    ): string {
        if (!$routeName) {
            return 'Sistema';
        }

        $prefix =
            explode(
                '.',
                $routeName
            )[0];

        $modules = [
            'employees' =>
                'Empleados',

            'clients' =>
                'Clientes',

            'catalog' =>
                'Catálogo',

            'inventory' =>
                'Inventario',

            'quotations' =>
                'Cotizaciones',

            'sales' =>
                'Ventas',

            'receipts' =>
                'Recibos',

            'accounts-receivable' =>
                'Cuentas por cobrar',

            'work-orders' =>
                'Órdenes de trabajo',

            'tasks' =>
                'Tareas',

            'installations' =>
                'Instalaciones',

            'cctv' =>
                'CCTV',

            'cash' =>
                'Caja',

            'income' =>
                'Ingresos',

            'expenses' =>
                'Gastos',

            'reports' =>
                'Reportes',

            'website' =>
                'Sitio web',

            'users' =>
                'Usuarios',

            'system-settings' =>
                'Configuración',
        ];

        return $modules[$prefix]
            ?? ucfirst(
                str_replace(
                    '-',
                    ' ',
                    $prefix
                )
            );
    }

    private function resolveAction(
        Request $request,
        ?string $routeName
    ): string {
        if ($routeName) {
            $last =
                collect(
                    explode(
                        '.',
                        $routeName
                    )
                )->last();

            $known = [
                'store' =>
                    'Crear',

                'update' =>
                    'Actualizar',

                'destroy' =>
                    'Eliminar',

                'delete' =>
                    'Eliminar',

                'cancel' =>
                    'Cancelar',

                'status' =>
                    'Cambiar estado',

                'start' =>
                    'Iniciar',

                'pause' =>
                    'Pausar',

                'resume' =>
                    'Reanudar',

                'finish' =>
                    'Finalizar',

                'send-for-review' =>
                    'Enviar a revisión',

                'report-issue' =>
                    'Reportar problema',

                'open' =>
                    'Abrir',

                'close' =>
                    'Cerrar',

                'reset-password' =>
                    'Restablecer contraseña',
            ];

            if (
                isset(
                    $known[$last]
                )
            ) {
                return $known[$last];
            }
        }

        return match (
            strtoupper(
                $request->method()
            )
        ) {
            'POST' =>
                'Crear',

            'PUT',
            'PATCH' =>
                'Actualizar',

            'DELETE' =>
                'Eliminar',

            default =>
                'Modificar',
        };
    }

    private function description(
        string $module,
        string $action,
        ?string $routeName
    ): string {
        $description =
            $action .
            ' en ' .
            $module;

        if ($routeName) {
            $description .=
                ' (' .
                $routeName .
                ')';
        }

        return $description;
    }

    private function resolveEntity(
        Request $request
    ): array {
        $route =
            $request->route();

        if (!$route) {
            return [
                null,
                null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | BUSCAR MODELO EN LOS PARÁMETROS DE RUTA
        |--------------------------------------------------------------------------
        */

        foreach (
            $route->parameters()
            as $name => $value
        ) {
            if (
                is_object(
                    $value
                )
                &&
                method_exists(
                    $value,
                    'getKey'
                )
            ) {
                return [
                    get_class(
                        $value
                    ),

                    $value
                        ->getKey(),
                ];
            }

            if (
                is_numeric(
                    $value
                )
            ) {
                return [
                    $name,
                    (int)
                    $value,
                ];
            }
        }

        return [
            null,
            null,
        ];
    }
}