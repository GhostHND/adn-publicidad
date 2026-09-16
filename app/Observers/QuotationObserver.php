<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Services\BusinessAutomationService;

class QuotationObserver
{
    public function saved(
        Quotation $quotation
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Solo procesamos cotizaciones aprobadas
        |--------------------------------------------------------------------------
        |
        | El servicio es idempotente:
        |
        | - No duplica la venta.
        | - No duplica la orden.
        | - Repara/sincroniza sus productos.
        |
        */

        if (
            $quotation->status
            !== 'approved'
        ) {
            return;
        }

        app(
            BusinessAutomationService::class
        )->processApprovedQuotation(
            $quotation
        );
    }
}