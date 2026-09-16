<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkOrderMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'inventory_item_id',
        'source',
        'quantity_planned',
        'quantity_consumed',
        'unit_cost_snapshot',
        'consumed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_planned' => 'decimal:3',
            'quantity_consumed' => 'decimal:3',
            'unit_cost_snapshot' => 'decimal:4',
            'consumed_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrder::class
        );
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class
        );
    }

    public function reservation(): HasOne
    {
        return $this->hasOne(
            InventoryReservation::class
        );
    }
}