<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogProductionStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_item_id',
        'title',
        'description',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(
            CatalogItem::class
        );
    }
}