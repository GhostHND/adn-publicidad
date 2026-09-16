<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SalePayment;
use App\Services\BusinessDocumentService;
use App\Services\DocumentShareService;
use Inertia\Inertia;
use Inertia\Response;

class DocumentShareController extends Controller
{
    public function quotation(
        Quotation $quotation,
        BusinessDocumentService $documents,
        DocumentShareService $share
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Asegurar que el PDF exista
        |--------------------------------------------------------------------------
        */

        $generated =
            $documents
                ->generateQuotation(
                    $quotation
                );

        $shareData =
            $share
                ->quotation(
                    quotation:
                        $quotation,

                    pdfUrl:
                        route(
                            'quotations.pdf',
                            [
                                'quotation' =>
                                    $quotation->id,
                            ]
                        ),

                    downloadUrl:
                        route(
                            'quotations.pdf',
                            [
                                'quotation' =>
                                    $quotation->id,

                                'download' =>
                                    1,
                            ]
                        ),

                    filename:
                        $generated[
                            'filename'
                        ]
                );

        return Inertia::render(
            'DocumentShare/Show',
            [
                'share' =>
                    $shareData,
            ]
        );
    }

    public function receipt(
        SalePayment $payment,
        BusinessDocumentService $documents,
        DocumentShareService $share
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Asegurar que el recibo exista
        |--------------------------------------------------------------------------
        */

        $generated =
            $documents
                ->generateReceipt(
                    $payment
                );

        $shareData =
            $share
                ->receipt(
                    payment:
                        $payment,

                    pdfUrl:
                        route(
                            'receipts.pdf',
                            [
                                'payment' =>
                                    $payment->id,
                            ]
                        ),

                    downloadUrl:
                        route(
                            'receipts.pdf',
                            [
                                'payment' =>
                                    $payment->id,

                                'download' =>
                                    1,
                            ]
                        ),

                    filename:
                        $generated[
                            'filename'
                        ]
                );

        return Inertia::render(
            'DocumentShare/Show',
            [
                'share' =>
                    $shareData,
            ]
        );
    }
}