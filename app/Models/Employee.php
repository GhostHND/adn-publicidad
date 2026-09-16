<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'second_last_name',
        'identity_number',
        'gender',
        'birth_date',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'position',
        'hire_date',
        'notes',
        'active',
        'is_default_operator',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' =>
                'date',

            'hire_date' =>
                'date',

            'active' =>
                'boolean',

            'is_default_operator' =>
                'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->second_last_name,
        ])
            ->filter()
            ->implode(' ');
    }

    public function user(): HasOne
    {
        return $this->hasOne(
            User::class
        );
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'active',
            true
        );
    }

    public function scopeDefaultOperator(
        Builder $query
    ): Builder {
        return $query->where(
            'is_default_operator',
            true
        );
    }
}