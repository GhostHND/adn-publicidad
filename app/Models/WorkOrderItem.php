<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'catalog_item_id',
        'item_code',
        'item_name',
        'description',
        'pricing_method',
        'measurement_unit',
        'width',
        'height',
        'quantity',
        'unit_price',
        'subtotal',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'decimal:4',
            'height' => 'decimal:4',
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrder::class
        );
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(
            CatalogItem::class
        );
    }
}