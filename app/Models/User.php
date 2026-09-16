<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property int|null $employee_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property bool $active
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'employee_id',
    'name',
    'email',
    'email_verified_at',
    'active',
    'password',
])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory,
        Notifiable,
        PasskeyAuthenticatable,
        TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' =>
                'datetime',

            'active' =>
                'boolean',

            'password' =>
                'hashed',

            'two_factor_confirmed_at' =>
                'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_user'
        );
    }

    public function hasRole(
        string $slug
    ): bool {
        if (
            $this->relationLoaded(
                'roles'
            )
        ) {
            return $this
                ->roles
                ->contains(
                    'slug',
                    $slug
                );
        }

        return $this
            ->roles()
            ->where(
                'slug',
                $slug
            )
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(
            'admin'
        );
    }

    public function hasPermissionTo(
        string $permission
    ): bool {
        if (!$this->active) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        |
        | El administrador siempre tiene acceso total,
        | incluso si posteriormente se agregan permisos nuevos.
        |
        */

        if ($this->isAdmin()) {
            return true;
        }

        return $this
            ->roles()
            ->whereHas(
                'permissions',
                fn ($query) =>
                    $query->where(
                        'key',
                        $permission
                    )
            )
            ->exists();
    }

    public function permissionKeys(): array
    {
        if ($this->isAdmin()) {
            return Permission::query()
                ->orderBy(
                    'key'
                )
                ->pluck(
                    'key'
                )
                ->all();
        }

        return $this
            ->roles()
            ->with(
                'permissions:id,key'
            )
            ->get()
            ->pluck(
                'permissions'
            )
            ->flatten()
            ->pluck(
                'key'
            )
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}