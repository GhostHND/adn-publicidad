<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'sale_id',
        'cash_session_id',
        'financial_account_id',
        'received_by',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SalePayment $payment) {
            if (
                $payment->payment_method === 'cash'
                && !$payment->cash_session_id
            ) {
                $payment->cash_session_id = CashSession::query()
                    ->where('status', 'open')
                    ->latest('id')
                    ->value('id');
            }

            if ($payment->payment_method === 'cash') {
                $payment->financial_account_id = null;
            }
        });
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sale::class,
            'sale_id'
        );
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'received_by'
        );
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(
            CashSession::class,
            'cash_session_id'
        );
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(
            FinancialAccount::class,
            'financial_account_id'
        );
    }
}