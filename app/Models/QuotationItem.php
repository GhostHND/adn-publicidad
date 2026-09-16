<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'catalog_item_id',
        'item_code',
        'item_name',
        'description',
        'pricing_method',
        'measurement_unit',
        'quantity',
        'width',
        'height',
        'unit_price',
        'unit_cost',
        'sale_rate',
        'cost_rate',
        'subtotal',
        'estimated_cost',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'width' => 'decimal:4',
            'height' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'sale_rate' => 'decimal:4',
            'cost_rate' => 'decimal:4',
            'subtotal' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }
}