<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'work_order_material_id',
        'inventory_item_id',
        'quantity_required',
        'quantity_reserved',
        'quantity_consumed',
        'status',
        'reserved_at',
        'consumed_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_required' => 'decimal:3',
            'quantity_reserved' => 'decimal:3',
            'quantity_consumed' => 'decimal:3',
            'reserved_at' => 'datetime',
            'consumed_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrder::class
        );
    }

    public function workOrderMaterial(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrderMaterial::class
        );
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class
        );
    }

    public function getMissingQuantityAttribute(): float
    {
        return max(
            round(
                (float) $this->quantity_required
                -
                (float) $this->quantity_reserved,
                3
            ),
            0
        );
    }
}