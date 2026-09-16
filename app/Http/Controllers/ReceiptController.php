<?php

namespace App\Http\Controllers;

use App\Models\SalePayment;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptController extends Controller
{
    public function index(): Response
    {
        $receipts = SalePayment::query()
            ->with([
                'sale.client',
            ])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (SalePayment $payment) => [
                'id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'sale_id' => $payment->sale_id,
                'sale_number' => $payment->sale?->sale_number,
                'client' => $payment->sale?->client?->display_name
                    ?? 'Sin cliente',
                'payment_date' => $payment->payment_date?->format('Y-m-d'),
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'reference' => $payment->reference,
            ])
            ->values();

        return Inertia::render('Receipts/Index', [
            'receipts' => $receipts,
        ]);
    }

    public function show(SalePayment $salePayment): Response
    {
        $salePayment->load([
            'sale.client',
            'receiver',
        ]);

        return Inertia::render('Receipts/Show', [
            'receipt' => $this->receiptForFrontend($salePayment),
        ]);
    }

    public function print(SalePayment $salePayment): Response
    {
        $salePayment->load([
            'sale.client',
            'receiver',
        ]);

        return Inertia::render('Receipts/Print', [
            'receipt' => $this->receiptForFrontend($salePayment),
        ]);
    }

    private function receiptForFrontend(
        SalePayment $payment
    ): array {
        $sale = $payment->sale;
        $client = $sale?->client;

        return [
            'id' => $payment->id,
            'receipt_number' => $payment->receipt_number,

            'payment_date' => $payment->payment_date?->format('Y-m-d'),

            'amount' => $payment->amount,

            'payment_method' => $payment->payment_method,

            'reference' => $payment->reference,

            'notes' => $payment->notes,

            'received_by' => $payment->receiver?->name,

            'sale' => [
                'id' => $sale?->id,
                'sale_number' => $sale?->sale_number,
                'sale_date' => $sale?->sale_date?->format('Y-m-d'),
                'total' => $sale?->total,
                'status' => $sale?->status,
            ],

            'client' => [
                'code' => $client?->client_code,
                'name' => $client?->display_name,
                'identity_number' => $client?->identity_number,
                'rtn' => $client?->rtn,
                'phone' => $client?->phone,
                'email' => $client?->email,
                'address' => $client?->address,
            ],
        ];
    }
}