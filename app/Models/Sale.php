<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sale_number',
        'client_id',
        'quotation_id',
        'created_by',
        'sale_date',
        'status',
        'subtotal',
        'discount',
        'total',
        'estimated_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class)
            ->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class)
            ->orderBy('payment_date')
            ->orderBy('id');
    }

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return round(
                (float) $this->payments->sum('amount'),
                2
            );
        }

        return round(
            (float) $this->payments()->sum('amount'),
            2
        );
    }

    public function getBalanceAttribute(): float
    {
        return max(
            round((float) $this->total - $this->paid_amount, 2),
            0
        );
    }
}