<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_code',
        'name',
        'category',
        'measurement_unit',
        'current_stock',
        'minimum_stock',
        'unit_cost',
        'supplier',
        'location',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'active' => 'boolean',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(
            InventoryMovement::class
        );
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(
            InventoryReservation::class
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

    public function getReservedStockAttribute(): float
    {
        $reserved = $this
            ->reservations()
            ->whereIn(
                'status',
                [
                    'reserved',
                    'partial',
                ]
            )
            ->selectRaw(
                '
                COALESCE(
                    SUM(
                        GREATEST(
                            quantity_reserved - quantity_consumed,
                            0
                        )
                    ),
                    0
                ) AS total_reserved
                '
            )
            ->value('total_reserved');

        return round(
            (float) ($reserved ?? 0),
            3
        );
    }

    public function getAvailableStockAttribute(): float
    {
        return max(
            round(
                (float) $this->current_stock
                -
                $this->reserved_stock,
                3
            ),
            0
        );
    }
}