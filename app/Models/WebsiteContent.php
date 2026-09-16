<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content_type',
        'title',
        'subtitle',
        'description',
        'image_path',
        'link_url',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where(
            'active',
            true
        );
    }

    public function scopeType(
        Builder $query,
        string $type
    ): Builder {
        return $query->where(
            'content_type',
            $type
        );
    }
}