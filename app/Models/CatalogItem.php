<?php

namespace App\Models;

use App\Services\AdnWebCatalogSyncService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Throwable;

class CatalogItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'item_type',
        'category',
        'pricing_method',
        'measurement_unit',
        'cost_price',
        'sale_price',
        'cost_rate',
        'sale_rate',
        'margin_percentage',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' =>
                'decimal:2',

            'sale_price' =>
                'decimal:2',

            'cost_rate' =>
                'decimal:4',

            'sale_rate' =>
                'decimal:4',

            'margin_percentage' =>
                'decimal:2',

            'active' =>
                'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(
            function (
                CatalogItem $item
            ): void {
                self::scheduleWebSync(
                    $item
                );
            }
        );

        static::updated(
            function (
                CatalogItem $item
            ): void {
                self::scheduleWebSync(
                    $item
                );
            }
        );

        static::deleted(
            function (
                CatalogItem $item
            ): void {
                self::scheduleWebSync(
                    $item
                );
            }
        );

        static::restored(
            function (
                CatalogItem $item
            ): void {
                self::scheduleWebSync(
                    $item
                );
            }
        );
    }

    public function productionSteps(): HasMany
    {
        return $this->hasMany(
            CatalogProductionStep::class
        )->orderBy(
            'sort_order'
        );
    }

    public function materialRecipes(): HasMany
    {
        return $this->hasMany(
            CatalogMaterialRecipe::class
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

    private static function scheduleWebSync(
        CatalogItem $item
    ): void {
        /*
        |--------------------------------------------------------------------------
        | SNAPSHOT
        |--------------------------------------------------------------------------
        |
        | Conservamos los valores del evento. Esto también es importante en
        | un SoftDelete, donde necesitamos conocer deleted_at.
        |
        */

        $snapshot =
            clone $item;

        $callback =
            function () use (
                $snapshot
            ): void {
                try {
                    app(
                        AdnWebCatalogSyncService::class
                    )->sync(
                        $snapshot
                    );
                } catch (
                    Throwable $exception
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | NUNCA BLOQUEAR LA APP
                    |--------------------------------------------------------------------------
                    |
                    | Si ADN Web está temporalmente fuera de línea, guardar un
                    | producto en la APP debe continuar funcionando.
                    |
                    */

                    report(
                        $exception
                    );
                }
            };

        if (
            DB::transactionLevel() >
            0
        ) {
            DB::afterCommit(
                $callback
            );

            return;
        }

        $callback();
    }
}