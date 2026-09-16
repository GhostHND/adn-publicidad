<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Services\InventoryReservationService;

class InventoryItemObserver
{
    public function updated(
        InventoryItem $inventoryItem
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Solo nos interesa cuando cambia el stock
        |--------------------------------------------------------------------------
        */

        if (
            !$inventoryItem
                ->wasChanged(
                    'current_stock'
                )
        ) {
            return;
        }

        $previousStock =
            (float)
            $inventoryItem
                ->getOriginal(
                    'current_stock'
                );

        $currentStock =
            (float)
            $inventoryItem
                ->current_stock;

        /*
        |--------------------------------------------------------------------------
        | Si el stock disminuyó no hay nada nuevo para repartir.
        |--------------------------------------------------------------------------
        */

        if (
            $currentStock
            <=
            $previousStock
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Entró material nuevo.
        |
        | Reintentar automáticamente las reservas pendientes.
        |--------------------------------------------------------------------------
        */

        app(
            InventoryReservationService::class
        )->refreshPendingForInventoryItem(
            $inventoryItem
        );
    }
}