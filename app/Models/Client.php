<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_code',
        'client_type',
        'first_name',
        'middle_name',
        'last_name',
        'second_last_name',
        'business_name',
        'identity_number',
        'rtn',
        'contact_person',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'notes',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
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

    public function getDisplayNameAttribute(): string
    {
        if ($this->business_name) {
            return $this->business_name;
        }

        if ($this->full_name) {
            return $this->full_name;
        }

        return 'Cliente sin nombre';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}