<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'status',
        'opening_balance',
        'expected_balance',
        'closing_balance',
        'difference',
        'opening_notes',
        'closing_notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_balance' => 'decimal:2',
            'expected_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'opened_by'
        );
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'closed_by'
        );
    }

    public function movements(): HasMany
    {
        return $this->hasMany(
            CashMovement::class
        );
    }

    public function cashPayments(): HasMany
    {
        return $this->hasMany(
            SalePayment::class,
            'cash_session_id'
        );
    }
}