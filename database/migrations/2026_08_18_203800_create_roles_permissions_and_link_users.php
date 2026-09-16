<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();

                $table->string('name', 100);

                $table->string('slug', 100)
                    ->unique();

                $table->text('description')
                    ->nullable();

                $table->boolean('is_system')
                    ->default(false);

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | PERMISOS
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();

                $table->string('key', 150)
                    ->unique();

                $table->string('name', 150);

                $table->string('group_name', 100);

                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ROL ↔ PERMISO
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->foreignId('role_id')
                    ->constrained('roles')
                    ->cascadeOnDelete();

                $table->foreignId('permission_id')
                    ->constrained('permissions')
                    ->cascadeOnDelete();

                $table->primary([
                    'role_id',
                    'permission_id',
                ]);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | USUARIO ↔ ROL
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId('role_id')
                    ->constrained('roles')
                    ->cascadeOnDelete();

                $table->primary([
                    'user_id',
                    'role_id',
                ]);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | USUARIO ↔ EMPLEADO
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('employee_id')
                    ->nullable()
                    ->unique()
                    ->after('id')
                    ->constrained('employees')
                    ->nullOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADO DEL USUARIO
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('users', 'active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('active')
                    ->default(true)
                    ->after('email_verified_at');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | CATÁLOGO DE PERMISOS
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'dashboard.view' => [
                'Ver dashboard',
                'General',
            ],

            'employees.view' => [
                'Ver empleados',
                'Empleados',
            ],

            'employees.manage' => [
                'Administrar empleados',
                'Empleados',
            ],

            'clients.view' => [
                'Ver clientes',
                'Clientes',
            ],

            'clients.manage' => [
                'Administrar clientes',
                'Clientes',
            ],

            'catalog.view' => [
                'Ver catálogo',
                'Catálogo',
            ],

            'catalog.manage' => [
                'Administrar catálogo',
                'Catálogo',
            ],

            'inventory.view' => [
                'Ver inventario',
                'Inventario',
            ],

            'inventory.manage' => [
                'Administrar inventario',
                'Inventario',
            ],

            'quotations.view' => [
                'Ver cotizaciones',
                'Cotizaciones',
            ],

            'quotations.manage' => [
                'Administrar cotizaciones',
                'Cotizaciones',
            ],

            'sales.view' => [
                'Ver ventas',
                'Ventas',
            ],

            'sales.manage' => [
                'Administrar ventas y pagos',
                'Ventas',
            ],

            'receipts.view' => [
                'Ver y compartir recibos',
                'Recibos',
            ],

            'accounts_receivable.view' => [
                'Ver cuentas por cobrar',
                'Cuentas por cobrar',
            ],

            'accounts_receivable.manage' => [
                'Registrar abonos',
                'Cuentas por cobrar',
            ],

            'work_orders.view' => [
                'Ver órdenes de trabajo',
                'Producción',
            ],

            'work_orders.manage' => [
                'Administrar órdenes de trabajo',
                'Producción',
            ],

            'tasks.view' => [
                'Ver tareas',
                'Producción',
            ],

            'tasks.manage' => [
                'Administrar y ejecutar tareas',
                'Producción',
            ],

            'installations.view' => [
                'Ver instalaciones',
                'Instalaciones',
            ],

            'installations.manage' => [
                'Administrar instalaciones',
                'Instalaciones',
            ],

            'cctv.view' => [
                'Ver proyectos CCTV',
                'CCTV',
            ],

            'cctv.manage' => [
                'Administrar proyectos CCTV',
                'CCTV',
            ],

            'cash.view' => [
                'Ver caja',
                'Finanzas',
            ],

            'cash.manage' => [
                'Administrar caja',
                'Finanzas',
            ],

            'income.view' => [
                'Ver ingresos',
                'Finanzas',
            ],

            'income.manage' => [
                'Administrar ingresos',
                'Finanzas',
            ],

            'expenses.view' => [
                'Ver gastos',
                'Finanzas',
            ],

            'expenses.manage' => [
                'Administrar gastos',
                'Finanzas',
            ],

            'reports.view' => [
                'Ver reportes',
                'Reportes',
            ],

            'website.view' => [
                'Ver administración web',
                'Sitio web',
            ],

            'website.manage' => [
                'Administrar sitio web',
                'Sitio web',
            ],

            'users.view' => [
                'Ver usuarios',
                'Seguridad',
            ],

            'users.manage' => [
                'Administrar usuarios, roles y permisos',
                'Seguridad',
            ],

            'audit.view' => [
                'Ver auditoría',
                'Seguridad',
            ],

            'settings.manage' => [
                'Administrar configuración del sistema',
                'Configuración',
            ],
        ];

        $now = now();

        foreach ($permissions as $key => [$name, $group]) {
            DB::table('permissions')->updateOrInsert(
                [
                    'key' => $key,
                ],
                [
                    'name' => $name,
                    'group_name' => $group,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ROLES PREDETERMINADOS
        |--------------------------------------------------------------------------
        */

        $roles = [
            'admin' => [
                'Administrador',
                'Acceso completo a ADN Publicidad.',
                true,
            ],

            'ventas' => [
                'Ventas',
                'Clientes, cotizaciones, ventas, recibos y cuentas por cobrar.',
                true,
            ],

            'diseno' => [
                'Diseño',
                'Cotizaciones, órdenes de trabajo y tareas de diseño.',
                true,
            ],

            'produccion' => [
                'Producción',
                'Inventario, órdenes de trabajo y tareas de producción.',
                true,
            ],

            'instalador' => [
                'Instalador',
                'Instalaciones, CCTV y tareas de campo.',
                true,
            ],
        ];

        foreach ($roles as $slug => [$name, $description, $system]) {
            DB::table('roles')->updateOrInsert(
                [
                    'slug' => $slug,
                ],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_system' => $system,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $permissionIds = DB::table('permissions')
            ->pluck('id', 'key');

        $roleIds = DB::table('roles')
            ->pluck('id', 'slug');

        /*
        |--------------------------------------------------------------------------
        | PERMISOS POR ROL
        |--------------------------------------------------------------------------
        */

        $rolePermissions = [
            'admin' => array_keys($permissions),

            'ventas' => [
                'dashboard.view',
                'clients.view',
                'clients.manage',
                'catalog.view',
                'quotations.view',
                'quotations.manage',
                'sales.view',
                'sales.manage',
                'receipts.view',
                'accounts_receivable.view',
                'accounts_receivable.manage',
            ],

            'diseno' => [
                'dashboard.view',
                'clients.view',
                'catalog.view',
                'quotations.view',
                'work_orders.view',
                'tasks.view',
                'tasks.manage',
            ],

            'produccion' => [
                'dashboard.view',
                'catalog.view',
                'inventory.view',
                'inventory.manage',
                'work_orders.view',
                'work_orders.manage',
                'tasks.view',
                'tasks.manage',
            ],

            'instalador' => [
                'dashboard.view',
                'inventory.view',
                'work_orders.view',
                'tasks.view',
                'tasks.manage',
                'installations.view',
                'installations.manage',
                'cctv.view',
                'cctv.manage',
            ],
        ];

        foreach ($rolePermissions as $roleSlug => $keys) {
            $roleId = $roleIds[$roleSlug] ?? null;

            if (!$roleId) {
                continue;
            }

            foreach ($keys as $permissionKey) {
                $permissionId =
                    $permissionIds[$permissionKey]
                    ?? null;

                if (!$permissionId) {
                    continue;
                }

                DB::table('permission_role')
                    ->insertOrIgnore([
                        'role_id' =>
                            $roleId,

                        'permission_id' =>
                            $permissionId,
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | USUARIOS EXISTENTES → ADMIN
        |--------------------------------------------------------------------------
        |
        | Así evitamos bloquear el acceso al sistema durante la migración.
        |
        */

        $adminRoleId =
            $roleIds['admin']
            ?? null;

        if ($adminRoleId) {
            $existingUsers =
                DB::table('users')
                    ->pluck('id');

            foreach ($existingUsers as $userId) {
                DB::table('role_user')
                    ->insertOrIgnore([
                        'user_id' =>
                            $userId,

                        'role_id' =>
                            $adminRoleId,
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MODO OPERADOR ÚNICO
        |--------------------------------------------------------------------------
        |
        | Si existe un solo usuario y un operador predeterminado,
        | los vinculamos automáticamente.
        |
        */

        $userIds =
            DB::table('users')
                ->pluck('id');

        $defaultEmployees =
            DB::table('employees')
                ->where(
                    'active',
                    true
                )
                ->where(
                    'is_default_operator',
                    true
                )
                ->whereNull(
                    'deleted_at'
                )
                ->pluck('id');

        if (
            $userIds->count() === 1
            &&
            $defaultEmployees->count() === 1
        ) {
            DB::table('users')
                ->where(
                    'id',
                    $userIds->first()
                )
                ->whereNull(
                    'employee_id'
                )
                ->update([
                    'employee_id' =>
                        $defaultEmployees->first(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'permission_role'
        );

        Schema::dropIfExists(
            'role_user'
        );

        Schema::dropIfExists(
            'permissions'
        );

        Schema::dropIfExists(
            'roles'
        );

        if (
            Schema::hasColumn(
                'users',
                'employee_id'
            )
        ) {
            Schema::table(
                'users',
                function (
                    Blueprint $table
                ) {
                    $table
                        ->dropConstrainedForeignId(
                            'employee_id'
                        );
                }
            );
        }

        if (
            Schema::hasColumn(
                'users',
                'active'
            )
        ) {
            Schema::table(
                'users',
                function (
                    Blueprint $table
                ) {
                    $table->dropColumn(
                        'active'
                    );
                }
            );
        }
    }
};