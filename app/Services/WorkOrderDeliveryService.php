<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkOrderDeliveryService
{
    public function deliver(
        WorkOrder $workOrder,
        array $data,
        ?int $userId = null
    ): ?SalePayment {
        return DB::transaction(
            function () use (
                $workOrder,
                $data,
                $userId
            ) {
                /*
                |--------------------------------------------------------------------------
                | Si la OT no tiene venta asociada
                |--------------------------------------------------------------------------
                |
                | Puede existir una orden manual o interna.
                | En ese caso simplemente se entrega.
                |
                */

                if (!$workOrder->sale_id) {
                    $this->markWorkOrderDelivered(
                        $workOrder
                    );

                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | Bloquear venta mientras procesamos el pago
                |--------------------------------------------------------------------------
                */

                $sale = Sale::query()
                    ->whereKey(
                        $workOrder->sale_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $sale->status ===
                    'cancelled'
                ) {
                    throw ValidationException::withMessages([
                        'delivery' =>
                            'La venta relacionada está cancelada y no puede procesarse la entrega.',
                    ]);
                }

                $currentPaid =
                    (float)
                    $sale
                        ->payments()
                        ->sum('amount');

                $saleTotal =
                    (float)
                    $sale->total;

                $balance = max(
                    round(
                        $saleTotal -
                        $currentPaid,
                        2
                    ),
                    0
                );

                /*
                |--------------------------------------------------------------------------
                | Si ya está pagada
                |--------------------------------------------------------------------------
                */

                if ($balance <= 0) {
                    $this->updateSaleStatus(
                        $sale
                    );

                    $this->markWorkOrderDelivered(
                        $workOrder
                    );

                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | Debe registrar un pago al entregar
                |--------------------------------------------------------------------------
                */

                $amount = round(
                    (float) (
                        $data[
                            'delivery_payment_amount'
                        ] ?? 0
                    ),
                    2
                );

                if ($amount <= 0) {
                    throw ValidationException::withMessages([
                        'delivery_payment_amount' =>
                            'Debes indicar el monto recibido al entregar el trabajo.',
                    ]);
                }

                if ($amount > $balance) {
                    throw ValidationException::withMessages([
                        'delivery_payment_amount' =>
                            'El pago no puede superar el saldo pendiente de L ' .
                            number_format(
                                $balance,
                                2
                            ) .
                            '.',
                    ]);
                }

                $paymentMethod =
                    $data[
                        'delivery_payment_method'
                    ] ?? null;

                if (
                    !in_array(
                        $paymentMethod,
                        [
                            'cash',
                            'transfer',
                            'card',
                            'other',
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'delivery_payment_method' =>
                            'Selecciona un método de pago válido.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | EFECTIVO
                |--------------------------------------------------------------------------
                */

                $cashSessionId = null;

                if (
                    $paymentMethod ===
                    'cash'
                ) {
                    $openCashSession =
                        CashSession::query()
                            ->where(
                                'status',
                                'open'
                            )
                            ->latest(
                                'opened_at'
                            )
                            ->first();

                    if (!$openCashSession) {
                        throw ValidationException::withMessages([
                            'delivery_payment_method' =>
                                'Para recibir efectivo debes tener una caja abierta.',
                        ]);
                    }

                    $cashSessionId =
                        $openCashSession->id;
                }

                /*
                |--------------------------------------------------------------------------
                | TRANSFERENCIA / TARJETA / OTRO
                |--------------------------------------------------------------------------
                */

                $financialAccountId = null;

                if (
                    $paymentMethod !==
                    'cash'
                ) {
                    $financialAccountId =
                        $data[
                            'delivery_financial_account_id'
                        ] ?? null;

                    if (!$financialAccountId) {
                        throw ValidationException::withMessages([
                            'delivery_financial_account_id' =>
                                'Selecciona la cuenta o banco donde ingresó el dinero.',
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Crear pago y recibo
                |--------------------------------------------------------------------------
                */

                $payment = SalePayment::create([
                    'receipt_number' =>
                        $this->nextReceiptNumber(),

                    'sale_id' =>
                        $sale->id,

                    'cash_session_id' =>
                        $cashSessionId,

                    'financial_account_id' =>
                        $financialAccountId,

                    'received_by' =>
                        $userId,

                    'payment_date' =>
                        $data[
                            'delivery_payment_date'
                        ] ?? now()->toDateString(),

                    'amount' =>
                        $amount,

                    'payment_method' =>
                        $paymentMethod,

                    'reference' =>
                        $data[
                            'delivery_reference'
                        ] ?? null,

                    'notes' =>
                        $data[
                            'delivery_payment_notes'
                        ]
                        ?? 'Pago registrado automáticamente durante la entrega de ' .
                            $workOrder
                                ->work_order_number .
                            '.',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Actualizar estado de venta
                |--------------------------------------------------------------------------
                */

                $this->updateSaleStatus(
                    $sale
                );

                /*
                |--------------------------------------------------------------------------
                | Marcar OT como entregada
                |--------------------------------------------------------------------------
                */

                $this->markWorkOrderDelivered(
                    $workOrder
                );

                return $payment;
            }
        );
    }

    private function updateSaleStatus(
        Sale $sale
    ): void {
        $paid =
            (float)
            $sale
                ->payments()
                ->sum('amount');

        $total =
            (float)
            $sale->total;

        if (
            $total <= 0
            || $paid >= $total
        ) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } else {
            $status = 'pending';
        }

        $sale->update([
            'status' =>
                $status,
        ]);
    }

    private function markWorkOrderDelivered(
        WorkOrder $workOrder
    ): void {
        if (!$workOrder->completed_at) {
            $workOrder->completed_at =
                now();
        }

        $workOrder->status =
            'delivered';

        $workOrder->delivered_at =
            now();

        $workOrder->save();
    }

    private function nextReceiptNumber(): string
    {
        $next =
            (
                SalePayment::withTrashed()
                    ->max('id')
                ?? 0
            ) + 1;

        return 'REC-' .
            now()->format('Y') .
            '-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}