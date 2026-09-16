<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PrepareProductionData extends Command
{
    protected $signature =
        'adn:prepare-production
        {--admin-id= : ID del usuario administrador que se conservará}
        {--force : Ejecuta definitivamente la limpieza}';

    protected $description =
        'Limpia los datos operativos de prueba conservando configuración, catálogo y un administrador.';

    /**
     * Tablas operativas conocidas.
     *
     * ÚNICAMENTE estas tablas serán vaciadas.
     *
     * Si alguna no existe, simplemente se ignora.
     *
     * @var array<int, string>
     */
    private array $operationalTables = [

        /*
        |--------------------------------------------------------------------------
        | CLIENTES
        |--------------------------------------------------------------------------
        */

        'clients',

        /*
        |--------------------------------------------------------------------------
        | COTIZACIONES
        |--------------------------------------------------------------------------
        */

        'quotation_items',
        'quotation_status_histories',
        'quotation_histories',
        'quotations',

        /*
        |--------------------------------------------------------------------------
        | VENTAS / PAGOS / RECIBOS
        |--------------------------------------------------------------------------
        */

        'sale_items',
        'sale_payments',
        'sale_status_histories',
        'receipts',
        'sales',

        /*
        |--------------------------------------------------------------------------
        | CUENTAS POR COBRAR
        |--------------------------------------------------------------------------
        */

        'accounts_receivable_payments',
        'accounts_receivables',
        'accounts_receivable',

        /*
        |--------------------------------------------------------------------------
        | CAJA
        |--------------------------------------------------------------------------
        */

        'cash_movements',
        'cash_session_movements',
        'cash_sessions',

        /*
        |--------------------------------------------------------------------------
        | INGRESOS / GASTOS
        |--------------------------------------------------------------------------
        */

        'incomes',
        'expenses',

        /*
        |--------------------------------------------------------------------------
        | INVENTARIO OPERATIVO
        |--------------------------------------------------------------------------
        */

        'inventory_reservations',
        'inventory_movements',
        'inventory_adjustments',
        'inventory_transactions',
        'inventory_items',

        /*
        |--------------------------------------------------------------------------
        | ÓRDENES DE TRABAJO
        |--------------------------------------------------------------------------
        */

        'work_order_materials',
        'work_order_items',
        'work_order_status_histories',
        'work_order_deliveries',
        'work_orders',

        /*
        |--------------------------------------------------------------------------
        | TAREAS
        |--------------------------------------------------------------------------
        */

        'tasks',

        /*
        |--------------------------------------------------------------------------
        | INSTALACIONES
        |--------------------------------------------------------------------------
        */

        'installation_status_histories',
        'installations',

        /*
        |--------------------------------------------------------------------------
        | CCTV
        |--------------------------------------------------------------------------
        */

        'cctv_maintenance_records',
        'cctv_credentials',
        'cctv_devices',
        'cctv_projects',

        /*
        |--------------------------------------------------------------------------
        | SOLICITUDES DEL SITIO WEB
        |--------------------------------------------------------------------------
        */

        'website_leads',

        /*
        |--------------------------------------------------------------------------
        | NOTIFICACIONES
        |--------------------------------------------------------------------------
        */

        'push_subscriptions',
        'notifications',

        /*
        |--------------------------------------------------------------------------
        | AUDITORÍA
        |--------------------------------------------------------------------------
        */

        'audit_logs',

        /*
        |--------------------------------------------------------------------------
        | SESIONES / AUTENTICACIÓN TEMPORAL
        |--------------------------------------------------------------------------
        */

        'sessions',
        'password_reset_tokens',

        /*
        |--------------------------------------------------------------------------
        | COLAS
        |--------------------------------------------------------------------------
        */

        'jobs',
        'job_batches',
        'failed_jobs',

        /*
        |--------------------------------------------------------------------------
        | CACHE
        |--------------------------------------------------------------------------
        */

        'cache',
        'cache_locks',
    ];

    /**
     * Alias aceptados para detectar automáticamente
     * el rol de Administrador.
     *
     * @var array<int, string>
     */
    private array $administratorAliases = [
        'administrator',
        'admin',
        'administrador',
        'administracion',
        'administración',
    ];

    public function handle(): int
    {
        $this->newLine();

        $this->info(
            'ADN PUBLICIDAD - PREPARACIÓN DE BASE DE PRODUCCIÓN'
        );

        $this->line(
            str_repeat(
                '=',
                64
            )
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDAR SEGURIDAD
        |--------------------------------------------------------------------------
        */

        foreach (
            [
                'users',
                'roles',
                'role_user',
            ]
            as $table
        ) {
            if (
                !Schema::hasTable(
                    $table
                )
            ) {
                $this->error(
                    "No existe la tabla requerida: {$table}"
                );

                return self::FAILURE;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | OBTENER USUARIOS
        |--------------------------------------------------------------------------
        */

        try {
            $users =
                $this->usersWithRoles();
        } catch (
            Throwable $exception
        ) {
            $this->error(
                'No fue posible consultar los usuarios.'
            );

            $this->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }

        if (
            $users->isEmpty()
        ) {
            $this->error(
                'No existe ningún usuario en la base de datos.'
            );

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | DETERMINAR ADMINISTRADOR A CONSERVAR
        |--------------------------------------------------------------------------
        */

        $admin =
            $this->resolveAdministrator(
                $users
            );

        if (
            !$admin
        ) {
            $this->newLine();

            $this->warn(
                'No se pudo determinar automáticamente qué usuario conservar.'
            );

            $this->newLine();

            $this->comment(
                'USUARIOS DISPONIBLES:'
            );

            $this->showUsers(
                $users
            );

            $this->newLine();

            $this->warn(
                'NO se eliminó ningún dato.'
            );

            $this->newLine();

            $this->line(
                'Vuelve a ejecutar indicando el ID del administrador:'
            );

            $this->newLine();

            $this->info(
                'php artisan adn:prepare-production --admin-id=ID'
            );

            $this->newLine();

            $this->line(
                'Ejemplo:'
            );

            $this->info(
                'php artisan adn:prepare-production --admin-id=1'
            );

            $this->newLine();

            return self::FAILURE;
        }

        /*
        |--------------------------------------------------------------------------
        | MOSTRAR ADMINISTRADOR
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'USUARIO QUE SE CONSERVARÁ:'
        );

        $this->table(
            [
                'ID',
                'Nombre',
                'Correo',
                'Roles',
                'Empleado',
            ],
            [[
                $admin->id,

                $admin->name
                    ?? '—',

                $admin->email
                    ?? '—',

                $admin->roles_label
                    ?? 'Sin rol',

                $admin->employee_id
                    ?? 'Sin vínculo',
            ]]
        );

        /*
        |--------------------------------------------------------------------------
        | TABLAS OPERATIVAS EXISTENTES
        |--------------------------------------------------------------------------
        */

        $tablesToClean =
            collect(
                $this->operationalTables
            )
                ->filter(
                    fn (
                        string $table
                    ) =>
                        Schema::hasTable(
                            $table
                        )
                )
                ->unique()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | PREVISUALIZAR
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->comment(
            'TABLAS OPERATIVAS QUE SE VACIARÁN:'
        );

        if (
            $tablesToClean->isEmpty()
        ) {
            $this->line(
                '  No se encontró ninguna tabla operativa conocida.'
            );
        } else {
            foreach (
                $tablesToClean
                as $table
            ) {
                $count =
                    $this->safeCount(
                        $table
                    );

                $this->line(
                    sprintf(
                        '  - %-38s %s',
                        $table,
                        $count !== null
                            ? "({$count} registros)"
                            : ''
                    )
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TABLAS MAESTRAS IMPORTANTES
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->comment(
            'DATOS IMPORTANTES QUE NO SE VACIARÁN:'
        );

        $masterTables = [
            'catalog_items',
            'catalog_production_steps',
            'catalog_material_recipes',
            'roles',
            'permissions',
            'system_settings',
            'financial_accounts',
            'website_contents',
        ];

        foreach (
            $masterTables
            as $table
        ) {
            if (
                !Schema::hasTable(
                    $table
                )
            ) {
                continue;
            }

            $count =
                $this->safeCount(
                    $table
                );

            $this->line(
                sprintf(
                    '  ✓ %-38s %s',
                    $table,
                    $count !== null
                        ? "({$count} registros)"
                        : ''
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | USUARIOS
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->comment(
            'LIMPIEZA DE USUARIOS:'
        );

        $this->line(
            '  Usuarios actuales: ' .
            $users->count()
        );

        $this->line(
            '  Usuarios que quedarán: 1'
        );

        $this->line(
            '  Usuario conservado: ' .
            (
                $admin->name
                ?? ('ID ' . $admin->id)
            )
        );

        /*
        |--------------------------------------------------------------------------
        | SOLO PREVISUALIZACIÓN
        |--------------------------------------------------------------------------
        */

        if (
            !$this->option(
                'force'
            )
        ) {
            $this->newLine();

            $this->warn(
                'PREVISUALIZACIÓN: todavía NO se eliminó ningún dato.'
            );

            $this->newLine();

            $this->line(
                'Si todo lo mostrado arriba es correcto, ejecuta:'
            );

            $this->newLine();

            $command =
                'php artisan adn:prepare-production';

            if (
                $this->option(
                    'admin-id'
                )
            ) {
                $command .=
                    ' --admin-id=' .
                    $admin->id;
            }

            $command .=
                ' --force';

            $this->info(
                $command
            );

            $this->newLine();

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | LIMPIEZA DEFINITIVA
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->warn(
            'INICIANDO LIMPIEZA DEFINITIVA...'
        );

        DB::statement(
            'SET FOREIGN_KEY_CHECKS=0'
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | VACIAR DATOS OPERATIVOS
            |--------------------------------------------------------------------------
            */

            foreach (
                $tablesToClean
                as $table
            ) {
                DB::table(
                    $table
                )->truncate();

                $this->line(
                    "  ✓ {$table}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ELIMINAR ROLES DE OTROS USUARIOS
            |--------------------------------------------------------------------------
            */

            DB::table(
                'role_user'
            )
                ->where(
                    'user_id',
                    '<>',
                    $admin->id
                )
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | ELIMINAR PERMISOS DIRECTOS DE OTROS USUARIOS
            |--------------------------------------------------------------------------
            */

            if (
                Schema::hasTable(
                    'permission_user'
                )
            ) {
                DB::table(
                    'permission_user'
                )
                    ->where(
                        'user_id',
                        '<>',
                        $admin->id
                    )
                    ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | ELIMINAR OTROS USUARIOS
            |--------------------------------------------------------------------------
            */

            DB::table(
                'users'
            )
                ->where(
                    'id',
                    '<>',
                    $admin->id
                )
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | CONSERVAR ÚNICAMENTE EMPLEADO DEL ADMIN
            |--------------------------------------------------------------------------
            */

            if (
                Schema::hasTable(
                    'employees'
                )
            ) {
                if (
                    $admin->employee_id
                    !== null
                ) {
                    DB::table(
                        'employees'
                    )
                        ->where(
                            'id',
                            '<>',
                            $admin->employee_id
                        )
                        ->delete();

                    $employeeUpdate =
                        [];

                    if (
                        Schema::hasColumn(
                            'employees',
                            'active'
                        )
                    ) {
                        $employeeUpdate[
                            'active'
                        ] =
                            true;
                    }

                    if (
                        Schema::hasColumn(
                            'employees',
                            'deleted_at'
                        )
                    ) {
                        $employeeUpdate[
                            'deleted_at'
                        ] =
                            null;
                    }

                    if (
                        $employeeUpdate !==
                        []
                    ) {
                        DB::table(
                            'employees'
                        )
                            ->where(
                                'id',
                                $admin->employee_id
                            )
                            ->update(
                                $employeeUpdate
                            );
                    }
                } else {
                    DB::table(
                        'employees'
                    )->truncate();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ASEGURAR USUARIO CONSERVADO ACTIVO
            |--------------------------------------------------------------------------
            */

            $userUpdate =
                [];

            if (
                Schema::hasColumn(
                    'users',
                    'active'
                )
            ) {
                $userUpdate[
                    'active'
                ] =
                    true;
            }

            if (
                Schema::hasColumn(
                    'users',
                    'remember_token'
                )
            ) {
                $userUpdate[
                    'remember_token'
                ] =
                    null;
            }

            if (
                Schema::hasColumn(
                    'users',
                    'deleted_at'
                )
            ) {
                $userUpdate[
                    'deleted_at'
                ] =
                    null;
            }

            if (
                Schema::hasColumn(
                    'users',
                    'last_login_at'
                )
            ) {
                $userUpdate[
                    'last_login_at'
                ] =
                    null;
            }

            if (
                Schema::hasColumn(
                    'users',
                    'last_login_ip'
                )
            ) {
                $userUpdate[
                    'last_login_ip'
                ] =
                    null;
            }

            if (
                $userUpdate !==
                []
            ) {
                DB::table(
                    'users'
                )
                    ->where(
                        'id',
                        $admin->id
                    )
                    ->update(
                        $userUpdate
                    );
            }
        } catch (
            Throwable $exception
        ) {
            $this->newLine();

            $this->error(
                'La limpieza encontró un error:'
            );

            $this->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        } finally {
            DB::statement(
                'SET FOREIGN_KEY_CHECKS=1'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RESULTADO
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'LIMPIEZA COMPLETADA CORRECTAMENTE.'
        );

        $this->newLine();

        $this->line(
            'Usuario conservado: ' .
            (
                $admin->name
                ?? ('ID ' . $admin->id)
            )
        );

        $this->line(
            'Usuarios restantes: ' .
            DB::table(
                'users'
            )->count()
        );

        $this->showFinalCount(
            'clients',
            'Clientes'
        );

        $this->showFinalCount(
            'quotations',
            'Cotizaciones'
        );

        $this->showFinalCount(
            'sales',
            'Ventas'
        );

        $this->showFinalCount(
            'sale_payments',
            'Pagos / recibos'
        );

        $this->showFinalCount(
            'work_orders',
            'Órdenes de trabajo'
        );

        $this->showFinalCount(
            'website_leads',
            'Solicitudes web'
        );

        $this->showFinalCount(
            'audit_logs',
            'Auditoría'
        );

        $this->newLine();

        $this->comment(
            'La base está preparada para cargar el tarifario oficial.'
        );

        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Obtener todos los usuarios con sus roles.
     */
    private function usersWithRoles(): Collection
    {
        $columns = [
            'id',
            'name',
            'email',
            'employee_id',
        ];

        $select =
            [
                'users.id',
            ];

        foreach (
            array_slice(
                $columns,
                1
            )
            as $column
        ) {
            if (
                Schema::hasColumn(
                    'users',
                    $column
                )
            ) {
                $select[] =
                    "users.{$column}";
            } else {
                $select[] =
                    DB::raw(
                        "NULL AS {$column}"
                    );
            }
        }

        $users =
            DB::table(
                'users'
            )
                ->select(
                    $select
                )
                ->orderBy(
                    'users.id'
                )
                ->get();

        return $users
            ->map(
                function (
                    object $user
                ) {
                    $roles =
                        DB::table(
                            'role_user'
                        )
                            ->join(
                                'roles',
                                'roles.id',
                                '=',
                                'role_user.role_id'
                            )
                            ->where(
                                'role_user.user_id',
                                $user->id
                            )
                            ->select(
                                $this->roleSelectColumns()
                            )
                            ->get();

                    $user->roles =
                        $roles;

                    $user->roles_label =
                        $roles
                            ->map(
                                function (
                                    object $role
                                ): string {
                                    $name =
                                        $role->name
                                        ?? null;

                                    $slug =
                                        $role->slug
                                        ?? null;

                                    if (
                                        $name
                                        &&
                                        $slug
                                        &&
                                        $name !==
                                        $slug
                                    ) {
                                        return
                                            "{$name} ({$slug})";
                                    }

                                    return
                                        (string)
                                        (
                                            $name
                                            ??
                                            $slug
                                            ??
                                            'Sin nombre'
                                        );
                                }
                            )
                            ->implode(
                                ', '
                            );

                    return $user;
                }
            );
    }

    /**
     * Columnas existentes en roles.
     *
     * @return array<int, mixed>
     */
    private function roleSelectColumns(): array
    {
        $select =
            [
                'roles.id',
            ];

        if (
            Schema::hasColumn(
                'roles',
                'name'
            )
        ) {
            $select[] =
                'roles.name';
        } else {
            $select[] =
                DB::raw(
                    'NULL AS name'
                );
        }

        if (
            Schema::hasColumn(
                'roles',
                'slug'
            )
        ) {
            $select[] =
                'roles.slug';
        } else {
            $select[] =
                DB::raw(
                    'NULL AS slug'
                );
        }

        return $select;
    }

    /**
     * Determinar qué administrador conservar.
     */
    private function resolveAdministrator(
        Collection $users
    ): ?object {
        /*
        |--------------------------------------------------------------------------
        | OPCIÓN EXPLÍCITA
        |--------------------------------------------------------------------------
        */

        $requestedId =
            $this->option(
                'admin-id'
            );

        if (
            $requestedId !==
            null
            &&
            $requestedId !==
            ''
        ) {
            $admin =
                $users->first(
                    fn (
                        object $user
                    ) =>
                        (string)
                        $user->id
                        ===
                        (string)
                        $requestedId
                );

            if (
                !$admin
            ) {
                $this->error(
                    "No existe un usuario con ID {$requestedId}."
                );

                return null;
            }

            return $admin;
        }

        /*
        |--------------------------------------------------------------------------
        | DETECCIÓN AUTOMÁTICA POR ROL
        |--------------------------------------------------------------------------
        */

        $candidates =
            $users
                ->filter(
                    function (
                        object $user
                    ): bool {
                        foreach (
                            $user->roles
                            as $role
                        ) {
                            foreach (
                                [
                                    $role->slug
                                        ?? null,

                                    $role->name
                                        ?? null,
                                ]
                                as $value
                            ) {
                                if (
                                    $this->isAdministratorRole(
                                        $value
                                    )
                                ) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    }
                )
                ->values();

        if (
            $candidates->count()
            === 1
        ) {
            return
                $candidates->first();
        }

        /*
        |--------------------------------------------------------------------------
        | SI SOLO EXISTE UN USUARIO EN TODA LA BASE
        |--------------------------------------------------------------------------
        */

        if (
            $users->count()
            === 1
        ) {
            return
                $users->first();
        }

        return null;
    }

    /**
     * Comprobar alias de Administrador.
     */
    private function isAdministratorRole(
        mixed $value
    ): bool {
        if (
            $value ===
            null
        ) {
            return false;
        }

        $normalized =
            strtolower(
                trim(
                    Str::ascii(
                        (string)
                        $value
                    )
                )
            );

        $aliases =
            collect(
                $this->administratorAliases
            )
                ->map(
                    fn (
                        string $alias
                    ) =>
                        strtolower(
                            trim(
                                Str::ascii(
                                    $alias
                                )
                            )
                        )
                )
                ->all();

        return in_array(
            $normalized,
            $aliases,
            true
        );
    }

    /**
     * Mostrar usuarios y roles.
     */
    private function showUsers(
        Collection $users
    ): void {
        $this->table(
            [
                'ID',
                'Nombre',
                'Correo',
                'Roles',
                'Empleado',
            ],
            $users
                ->map(
                    fn (
                        object $user
                    ) => [
                        $user->id,

                        $user->name
                            ?? '—',

                        $user->email
                            ?? '—',

                        $user->roles_label
                            !== ''
                            ? $user->roles_label
                            : 'Sin rol',

                        $user->employee_id
                            ?? '—',
                    ]
                )
                ->all()
        );
    }

    /**
     * Contar registros de una tabla de manera segura.
     */
    private function safeCount(
        string $table
    ): ?int {
        try {
            return DB::table(
                $table
            )->count();
        } catch (
            Throwable
        ) {
            return null;
        }
    }

    /**
     * Mostrar conteo final si la tabla existe.
     */
    private function showFinalCount(
        string $table,
        string $label
    ): void {
        if (
            !Schema::hasTable(
                $table
            )
        ) {
            return;
        }

        $this->line(
            $label .
            ' restantes: ' .
            DB::table(
                $table
            )->count()
        );
    }
}