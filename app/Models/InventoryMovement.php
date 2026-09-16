<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'created_by',
        'movement_type',
        'quantity',
        'previous_stock',
        'resulting_stock',
        'unit_cost',
        'reference',
        'notes',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'previous_stock' => 'decimal:3',
            'resulting_stock' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'moved_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}