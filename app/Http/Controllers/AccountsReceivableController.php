<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\Sale;
use App\Models\SalePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AccountsReceivableController extends Controller
{
    public function index(): Response
    {
        $sales = Sale::query()
            ->with([
                'client',
                'payments',
            ])
            ->whereNotIn(
                'status',
                [
                    'paid',
                    'cancelled',
                ]
            )
            ->latest('sale_date')
            ->latest('id')
            ->get()
            ->filter(
                fn (
                    Sale $sale
                ) =>
                    $sale->balance > 0
            )
            ->map(
                fn (
                    Sale $sale
                ) => [
                    'id' =>
                        $sale->id,

                    'sale_number' =>
                        $sale
                            ->sale_number,

                    'client' =>
                        $sale
                            ->client
                            ?->display_name
                        ?? 'Sin cliente',

                    'client_code' =>
                        $sale
                            ->client
                            ?->client_code,

                    'sale_date' =>
                        $sale
                            ->sale_date
                            ?->format('Y-m-d'),

                    'status' =>
                        $sale
                            ->status,

                    'total' =>
                        $sale
                            ->total,

                    'paid_amount' =>
                        $sale
                            ->paid_amount,

                    'balance' =>
                        $sale
                            ->balance,

                    'days_outstanding' =>
                        $sale
                            ->sale_date
                            ? $sale
                                ->sale_date
                                ->diffInDays(
                                    now()
                                )
                            : 0,
                ]
            )
            ->values();

        return Inertia::render(
            'AccountsReceivable/Index',
            [
                'accounts' =>
                    $sales,
            ]
        );
    }

    public function show(
        Sale $sale
    ): Response {
        $sale->load([
            'client',
            'items',
            'payments.financialAccount',
        ]);

        return Inertia::render(
            'AccountsReceivable/Show',
            [
                'account' => [
                    'id' =>
                        $sale->id,

                    'sale_number' =>
                        $sale
                            ->sale_number,

                    'sale_date' =>
                        $sale
                            ->sale_date
                            ?->format('Y-m-d'),

                    'status' =>
                        $sale
                            ->status,

                    'subtotal' =>
                        $sale
                            ->subtotal,

                    'discount' =>
                        $sale
                            ->discount,

                    'total' =>
                        $sale
                            ->total,

                    'paid_amount' =>
                        $sale
                            ->paid_amount,

                    'balance' =>
                        $sale
                            ->balance,

                    'days_outstanding' =>
                        $sale
                            ->sale_date
                            ? $sale
                                ->sale_date
                                ->diffInDays(
                                    now()
                                )
                            : 0,

                    'notes' =>
                        $sale
                            ->notes,

                    'client' => [
                        'id' =>
                            $sale
                                ->client?->id,

                        'code' =>
                            $sale
                                ->client
                                ?->client_code,

                        'name' =>
                            $sale
                                ->client
                                ?->display_name,

                        'phone' =>
                            $sale
                                ->client
                                ?->phone,

                        'email' =>
                            $sale
                                ->client
                                ?->email,

                        'address' =>
                            $sale
                                ->client
                                ?->address,
                    ],

                    'items' =>
                        $sale
                            ->items
                            ->map(
                                fn (
                                    $item
                                ) => [
                                    'id' =>
                                        $item->id,

                                    'item_name' =>
                                        $item
                                            ->item_name,

                                    'quantity' =>
                                        $item
                                            ->quantity,

                                    'subtotal' =>
                                        $item
                                            ->subtotal,
                                ]
                            )
                            ->values(),

                    'payments' =>
                        $sale
                            ->payments
                            ->map(
                                fn (
                                    SalePayment $payment
                                ) => [
                                    'id' =>
                                        $payment
                                            ->id,

                                    'receipt_number' =>
                                        $payment
                                            ->receipt_number,

                                    'payment_date' =>
                                        $payment
                                            ->payment_date
                                            ?->format('Y-m-d'),

                                    'amount' =>
                                        $payment
                                            ->amount,

                                    'payment_method' =>
                                        $payment
                                            ->payment_method,

                                    'financial_account' =>
                                        $payment
                                            ->financialAccount
                                            ?->name,

                                    'reference' =>
                                        $payment
                                            ->reference,

                                    'notes' =>
                                        $payment
                                            ->notes,
                                ]
                            )
                            ->values(),
                ],

                'financialAccounts' =>
                    $this
                        ->financialAccountsForForm(),
            ]
        );
    }

    public function storePayment(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        $sale->load(
            'payments'
        );

        if (
            $sale->status ===
            'cancelled'
        ) {
            return back()
                ->withErrors([
                    'amount' =>
                        'No se pueden registrar pagos en una venta cancelada.',
                ]);
        }

        if (
            $sale->balance <= 0
        ) {
            return back()
                ->withErrors([
                    'amount' =>
                        'Esta cuenta ya está pagada completamente.',
                ]);
        }

        $validated =
            $request->validate([
                'payment_date' => [
                    'required',
                    'date',
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],

                'payment_method' => [
                    'required',

                    Rule::in([
                        'cash',
                        'transfer',
                        'card',
                        'other',
                    ]),
                ],

                'financial_account_id' => [
                    Rule::requiredIf(
                        fn () =>
                            $request
                                ->payment_method
                            !== 'cash'
                    ),

                    'nullable',
                    'integer',

                    Rule::exists(
                        'financial_accounts',
                        'id'
                    )->where(
                        fn ($query) =>
                            $query
                                ->where(
                                    'active',
                                    true
                                )
                                ->whereNull(
                                    'deleted_at'
                                )
                    ),
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        $amount =
            round(
                (float)
                $validated['amount'],
                2
            );

        if (
            $amount >
            $sale->balance
        ) {
            return back()
                ->withErrors([
                    'amount' =>
                        'El abono no puede superar el saldo pendiente de L ' .
                        number_format(
                            $sale->balance,
                            2
                        ) .
                        '.',
                ]);
        }

        $payment =
            DB::transaction(
                function () use (
                    $sale,
                    $validated,
                    $amount,
                    $request
                ) {
                    $payment =
                        SalePayment::create([
                            'receipt_number' =>
                                $this
                                    ->nextReceiptNumber(),

                            'sale_id' =>
                                $sale->id,

                            'financial_account_id' =>
                                $validated[
                                    'payment_method'
                                ] === 'cash'
                                    ? null
                                    : (
                                        $validated[
                                            'financial_account_id'
                                        ]
                                        ?? null
                                    ),

                            'received_by' =>
                                $request
                                    ->user()?->id,

                            'payment_date' =>
                                $validated[
                                    'payment_date'
                                ],

                            'amount' =>
                                $amount,

                            'payment_method' =>
                                $validated[
                                    'payment_method'
                                ],

                            'reference' =>
                                $validated[
                                    'reference'
                                ]
                                ?? null,

                            'notes' =>
                                $validated[
                                    'notes'
                                ]
                                ?? null,
                        ]);

                    $this
                        ->refreshSaleStatus(
                            $sale
                        );

                    return $payment;
                }
            );

        /*
        |--------------------------------------------------------------------------
        | ABONO → COMPARTIR RECIBO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'receipts.share',
                [
                    'payment' =>
                        $payment->id,
                ]
            )
            ->with(
                'success',
                'Abono registrado correctamente. El recibo está listo para compartir.'
            );
    }

    private function refreshSaleStatus(
        Sale $sale
    ): void {
        $sale->load(
            'payments'
        );

        if (
            $sale->status ===
            'cancelled'
        ) {
            return;
        }

        $paid =
            (float)
            $sale
                ->paid_amount;

        $total =
            (float)
            $sale
                ->total;

        if (
            $paid <= 0
        ) {
            $status =
                'pending';
        } elseif (
            $paid <
            $total
        ) {
            $status =
                'partial';
        } else {
            $status =
                'paid';
        }

        $sale->update([
            'status' =>
                $status,
        ]);
    }

    private function nextReceiptNumber(): string
    {
        $next =
            (
                SalePayment::withTrashed()
                    ->max('id')
                ?? 0
            )
            + 1;

        return
            'REC-' .
            now()
                ->format('Y') .
            '-' .
            str_pad(
                (string) $next,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    private function financialAccountsForForm(): array
    {
        return FinancialAccount::query()
            ->active()
            ->orderBy(
                'name'
            )
            ->get()
            ->map(
                fn (
                    FinancialAccount $account
                ) => [
                    'id' =>
                        $account->id,

                    'code' =>
                        $account
                            ->account_code,

                    'name' =>
                        $account
                            ->name,

                    'institution' =>
                        $account
                            ->institution,

                    'account_type' =>
                        $account
                            ->account_type,
                ]
            )
            ->values()
            ->all();
    }
}