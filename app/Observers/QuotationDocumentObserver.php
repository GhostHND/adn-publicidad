<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Services\BusinessDocumentService;
use Throwable;

class QuotationDocumentObserver
{
    public function saved(
        Quotation $quotation
    ): void {
        /*
        |--------------------------------------------------------------------------
        | GENERAR PDF AL ENTRAR EN "ENVIADA"
        |--------------------------------------------------------------------------
        */

        if (
            $quotation->status !==
            'sent'
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar regenerar innecesariamente
        |--------------------------------------------------------------------------
        */

        if (
            !$quotation->wasChanged(
                'status'
            )
            &&
            !$quotation
                ->wasRecentlyCreated
        ) {
            return;
        }

        try {
            app(
                BusinessDocumentService::class
            )->generateQuotation(
                $quotation
            );
        } catch (
            Throwable $exception
        ) {
            report(
                $exception
            );
        }
    }
}