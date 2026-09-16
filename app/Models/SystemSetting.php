<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'group_name',
        'label',
        'value',
        'type',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' =>
                'integer',
        ];
    }

    public function scopeGroup(
        Builder $query,
        string $group
    ): Builder {
        return $query->where(
            'group_name',
            $group
        );
    }

    public static function value(
        string $key,
        mixed $default = null
    ): mixed {
        $setting =
            static::query()
                ->where(
                    'key',
                    $key
                )
                ->first();

        return $setting?->value
            ?? $default;
    }

    public static function setValue(
        string $key,
        mixed $value
    ): bool {
        $setting =
            static::query()
                ->where(
                    'key',
                    $key
                )
                ->first();

        if (!$setting) {
            return false;
        }

        $setting->update([
            'value' =>
                $value,
        ]);

        return true;
    }
}