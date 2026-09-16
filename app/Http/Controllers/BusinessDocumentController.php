<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SalePayment;
use App\Services\BusinessDocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BusinessDocumentController extends Controller
{
    public function quotation(
        Request $request,
        Quotation $quotation,
        BusinessDocumentService $documents
    ): Response {
        $generated =
            $documents
                ->generateQuotation(
                    $quotation
                );

        return $this
            ->pdfResponse(
                $request,
                $generated[
                    'content'
                ],
                $generated[
                    'filename'
                ]
            );
    }

    public function receipt(
        Request $request,
        SalePayment $payment,
        BusinessDocumentService $documents
    ): Response {
        $generated =
            $documents
                ->generateReceipt(
                    $payment
                );

        return $this
            ->pdfResponse(
                $request,
                $generated[
                    'content'
                ],
                $generated[
                    'filename'
                ]
            );
    }

    private function pdfResponse(
        Request $request,
        string $content,
        string $filename
    ): Response {
        $disposition =
            $request
                ->boolean(
                    'download'
                )
                ? 'attachment'
                : 'inline';

        return response(
            $content,
            200,
            [
                'Content-Type' =>
                    'application/pdf',

                'Content-Disposition' =>
                    $disposition .
                    '; filename="' .
                    $filename .
                    '"',

                'Content-Length' =>
                    (string)
                    strlen(
                        $content
                    ),

                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',
            ]
        );
    }
}