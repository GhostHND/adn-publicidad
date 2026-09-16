<?php

namespace App\Observers;

use App\Models\SalePayment;
use App\Services\BusinessDocumentService;
use Throwable;

class SalePaymentDocumentObserver
{
    public function created(
        SalePayment $payment
    ): void {
        try {
            app(
                BusinessDocumentService::class
            )->generateReceipt(
                $payment
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