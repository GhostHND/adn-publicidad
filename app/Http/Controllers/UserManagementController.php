<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        $users =
            User::query()
                ->with([
                    'employee',
                    'roles',
                ])
                ->orderBy('name')
                ->get()
                ->map(
                    fn (
                        User $user
                    ) => [
                        'id' =>
                            $user->id,

                        'name' =>
                            $user->name,

                        'email' =>
                            $user->email,

                        'active' =>
                            $user->active,

                        'employee' =>
                            $user
                                ->employee
                                ?->full_name,

                        'employee_code' =>
                            $user
                                ->employee
                                ?->employee_code,

                        'roles' =>
                            $user
                                ->roles
                                ->map(
                                    fn (
                                        Role $role
                                    ) => [
                                        'id' =>
                                            $role->id,

                                        'name' =>
                                            $role->name,

                                        'slug' =>
                                            $role->slug,
                                    ]
                                )
                                ->values(),

                        'created_at' =>
                            $user
                                ->created_at
                                ?->format(
                                    'd/m/Y'
                                ),
                    ]
                )
                ->values();

        return Inertia::render(
            'Users/Index',
            [
                'users' =>
                    $users,
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Users/Create',
            [
                'employees' =>
                    $this
                        ->employeesForForm(),

                'roles' =>
                    $this
                        ->rolesForForm(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'employee_id' => [
                    'nullable',
                    'integer',

                    Rule::exists(
                        'employees',
                        'id'
                    )->whereNull(
                        'deleted_at'
                    ),

                    Rule::unique(
                        'users',
                        'employee_id'
                    ),
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],

                'role_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'role_ids.*' => [
                    'integer',
                    Rule::exists(
                        'roles',
                        'id'
                    ),
                ],
            ]);

        $user =
            DB::transaction(
                function () use (
                    $validated
                ) {
                    $user =
                        User::create([
                            'employee_id' =>
                                $validated[
                                    'employee_id'
                                ]
                                ?? null,

                            'name' =>
                                $validated[
                                    'name'
                                ],

                            'email' =>
                                $validated[
                                    'email'
                                ],

                            'email_verified_at' =>
                                now(),

                            'active' =>
                                true,

                            'password' =>
                                $validated[
                                    'password'
                                ],
                        ]);

                    $user
                        ->roles()
                        ->sync(
                            $validated[
                                'role_ids'
                            ]
                        );

                    return $user;
                }
            );

        return redirect()
            ->route(
                'users.edit',
                $user
            )
            ->with(
                'success',
                'Usuario creado correctamente.'
            );
    }

    public function edit(
        User $user
    ): Response {
        $user->load([
            'employee',
            'roles',
        ]);

        return Inertia::render(
            'Users/Edit',
            [
                'user' => [
                    'id' =>
                        $user->id,

                    'name' =>
                        $user->name,

                    'email' =>
                        $user->email,

                    'employee_id' =>
                        $user->employee_id,

                    'active' =>
                        $user->active,

                    'role_ids' =>
                        $user
                            ->roles
                            ->pluck('id')
                            ->values(),

                    'roles' =>
                        $user
                            ->roles
                            ->map(
                                fn (
                                    Role $role
                                ) => [
                                    'id' =>
                                        $role->id,

                                    'name' =>
                                        $role->name,

                                    'slug' =>
                                        $role->slug,
                                ]
                            )
                            ->values(),
                ],

                'employees' =>
                    $this
                        ->employeesForForm(
                            $user
                        ),

                'roles' =>
                    $this
                        ->rolesForForm(),
            ]
        );
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',

                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $user->id
                    ),
                ],

                'employee_id' => [
                    'nullable',
                    'integer',

                    Rule::exists(
                        'employees',
                        'id'
                    )->whereNull(
                        'deleted_at'
                    ),

                    Rule::unique(
                        'users',
                        'employee_id'
                    )->ignore(
                        $user->id
                    ),
                ],

                'role_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'role_ids.*' => [
                    'integer',

                    Rule::exists(
                        'roles',
                        'id'
                    ),
                ],
            ]);

        $this
            ->ensureAdminIsNotRemoved(
                $user,
                $validated[
                    'role_ids'
                ]
            );

        DB::transaction(
            function () use (
                $user,
                $validated
            ) {
                $user->update([
                    'name' =>
                        $validated[
                            'name'
                        ],

                    'email' =>
                        $validated[
                            'email'
                        ],

                    'employee_id' =>
                        $validated[
                            'employee_id'
                        ]
                        ?? null,
                ]);

                $user
                    ->roles()
                    ->sync(
                        $validated[
                            'role_ids'
                        ]
                    );
            }
        );

        return back()->with(
            'success',
            'Usuario actualizado correctamente.'
        );
    }

    public function updateStatus(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated =
            $request->validate([
                'active' => [
                    'required',
                    'boolean',
                ],
            ]);

        $active =
            (bool)
            $validated['active'];

        if (
            !$active
            &&
            $request
                ->user()?->id ===
                $user->id
        ) {
            return back()
                ->withErrors([
                    'user' =>
                        'No puedes desactivar tu propio usuario.',
                ]);
        }

        if (
            !$active
            &&
            $user->hasRole(
                'admin'
            )
            &&
            $this
                ->activeAdminCount()
                <= 1
        ) {
            return back()
                ->withErrors([
                    'user' =>
                        'No puedes desactivar el último administrador activo.',
                ]);
        }

        $user->update([
            'active' =>
                $active,
        ]);

        return back()->with(
            'success',
            $active
                ? 'Usuario activado correctamente.'
                : 'Usuario desactivado correctamente.'
        );
    }

    public function resetPassword(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated =
            $request->validate([
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ]);

        $user->update([
            'password' =>
                $validated[
                    'password'
                ],
        ]);

        return back()->with(
            'success',
            'Contraseña actualizada correctamente.'
        );
    }

    public function permissions(): Response
    {
        $roles =
            Role::query()
                ->with([
                    'permissions',
                    'users:id',
                ])
                ->orderBy(
                    'id'
                )
                ->get()
                ->map(
                    fn (
                        Role $role
                    ) => [
                        'id' =>
                            $role->id,

                        'name' =>
                            $role->name,

                        'slug' =>
                            $role->slug,

                        'description' =>
                            $role->description,

                        'is_system' =>
                            $role->is_system,

                        'protected' =>
                            $role->slug ===
                            'admin',

                        'user_count' =>
                            $role
                                ->users
                                ->count(),

                        'permission_ids' =>
                            $role
                                ->permissions
                                ->pluck('id')
                                ->values(),
                    ]
                )
                ->values();

        $groups =
            Permission::query()
                ->orderBy(
                    'group_name'
                )
                ->orderBy(
                    'name'
                )
                ->get()
                ->groupBy(
                    'group_name'
                )
                ->map(
                    fn (
                        $permissions,
                        $group
                    ) => [
                        'name' =>
                            $group,

                        'permissions' =>
                            $permissions
                                ->map(
                                    fn (
                                        Permission $permission
                                    ) => [
                                        'id' =>
                                            $permission->id,

                                        'key' =>
                                            $permission->key,

                                        'name' =>
                                            $permission->name,
                                    ]
                                )
                                ->values(),
                    ]
                )
                ->values();

        return Inertia::render(
            'Users/Permissions',
            [
                'roles' =>
                    $roles,

                'permissionGroups' =>
                    $groups,
            ]
        );
    }

    public function storeRole(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    'unique:roles,name',
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'permission_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'permission_ids.*' => [
                    'integer',

                    Rule::exists(
                        'permissions',
                        'id'
                    ),
                ],
            ]);

        $slug =
            Str::slug(
                $validated['name']
            );

        if (
            Role::query()
                ->where(
                    'slug',
                    $slug
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' =>
                    'Ya existe un rol con un nombre equivalente.',
            ]);
        }

        DB::transaction(
            function () use (
                $validated,
                $slug
            ) {
                $role =
                    Role::create([
                        'name' =>
                            $validated[
                                'name'
                            ],

                        'slug' =>
                            $slug,

                        'description' =>
                            $validated[
                                'description'
                            ]
                            ?? null,

                        'is_system' =>
                            false,
                    ]);

                $role
                    ->permissions()
                    ->sync(
                        $validated[
                            'permission_ids'
                        ]
                    );
            }
        );

        return back()->with(
            'success',
            'Rol creado correctamente.'
        );
    }

    public function updateRole(
        Request $request,
        Role $role
    ): RedirectResponse {
        if (
            $role->slug ===
            'admin'
        ) {
            return back()
                ->withErrors([
                    'role' =>
                        'El rol Administrador está protegido.',
                ]);
        }

        $validated =
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'roles',
                        'name'
                    )->ignore(
                        $role->id
                    ),
                ],

                'description' => [
                    'nullable',
                    'string',
                ],

                'permission_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'permission_ids.*' => [
                    'integer',

                    Rule::exists(
                        'permissions',
                        'id'
                    ),
                ],
            ]);

        DB::transaction(
            function () use (
                $role,
                $validated
            ) {
                $role->update([
                    'name' =>
                        $validated[
                            'name'
                        ],

                    'description' =>
                        $validated[
                            'description'
                        ]
                        ?? null,
                ]);

                $role
                    ->permissions()
                    ->sync(
                        $validated[
                            'permission_ids'
                        ]
                    );
            }
        );

        return back()->with(
            'success',
            'Rol actualizado correctamente.'
        );
    }

    public function destroyRole(
        Role $role
    ): RedirectResponse {
        if (
            $role->is_system
        ) {
            return back()
                ->withErrors([
                    'role' =>
                        'Los roles predeterminados del sistema no pueden eliminarse.',
                ]);
        }

        if (
            $role
                ->users()
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'role' =>
                        'No puedes eliminar un rol que tiene usuarios asignados.',
                ]);
        }

        $role->delete();

        return back()->with(
            'success',
            'Rol eliminado correctamente.'
        );
    }

    private function employeesForForm(
        ?User $currentUser = null
    ): array {
        return Employee::query()
            ->active()
            ->where(
                function (
                    $query
                ) use (
                    $currentUser
                ) {
                    $query
                        ->whereDoesntHave(
                            'user'
                        );

                    if (
                        $currentUser
                            ?->employee_id
                    ) {
                        $query
                            ->orWhere(
                                'id',
                                $currentUser
                                    ->employee_id
                            );
                    }
                }
            )
            ->orderBy(
                'first_name'
            )
            ->orderBy(
                'last_name'
            )
            ->get()
            ->map(
                fn (
                    Employee $employee
                ) => [
                    'id' =>
                        $employee->id,

                    'code' =>
                        $employee
                            ->employee_code,

                    'name' =>
                        $employee
                            ->full_name,

                    'position' =>
                        $employee
                            ->position,

                    'email' =>
                        $employee
                            ->email,
                ]
            )
            ->values()
            ->all();
    }

    private function rolesForForm(): array
    {
        return Role::query()
            ->orderBy(
                'name'
            )
            ->get()
            ->map(
                fn (
                    Role $role
                ) => [
                    'id' =>
                        $role->id,

                    'name' =>
                        $role->name,

                    'slug' =>
                        $role->slug,

                    'description' =>
                        $role
                            ->description,
                ]
            )
            ->values()
            ->all();
    }

    private function ensureAdminIsNotRemoved(
        User $user,
        array $roleIds
    ): void {
        if (
            !$user->hasRole(
                'admin'
            )
        ) {
            return;
        }

        $keepsAdmin =
            Role::query()
                ->whereIn(
                    'id',
                    $roleIds
                )
                ->where(
                    'slug',
                    'admin'
                )
                ->exists();

        if ($keepsAdmin) {
            return;
        }

        if (
            $this
                ->activeAdminCount()
                <= 1
        ) {
            throw ValidationException::withMessages([
                'role_ids' =>
                    'No puedes retirar el rol Administrador al último administrador activo.',
            ]);
        }
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->where(
                'active',
                true
            )
            ->whereHas(
                'roles',
                fn ($query) =>
                    $query->where(
                        'slug',
                        'admin'
                    )
            )
            ->count();
    }
}