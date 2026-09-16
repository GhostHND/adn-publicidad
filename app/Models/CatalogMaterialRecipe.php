<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogMaterialRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_item_id',
        'inventory_item_id',
        'calculation_method',
        'quantity_rate',
        'waste_percentage',
        'notes',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'quantity_rate' => 'decimal:6',
            'waste_percentage' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(
            CatalogItem::class
        );
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(
            InventoryItem::class
        );
    }
}