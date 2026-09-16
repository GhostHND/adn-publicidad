<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_code',
        'name',
        'institution',
        'account_type',
        'currency',
        'opening_balance',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function salePayments(): HasMany
    {
        return $this->hasMany(
            SalePayment::class,
            'financial_account_id'
        );
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(
            Income::class,
            'financial_account_id'
        );
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(
            Expense::class,
            'financial_account_id'
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
}