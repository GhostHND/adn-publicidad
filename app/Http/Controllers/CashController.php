<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\Income;
use App\Models\SalePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CashController extends Controller
{
    public function index(): Response
    {
        $session = CashSession::query()
            ->with('opener')
            ->where('status', 'open')
            ->latest('id')
            ->first();

        $lastClosedSession =
            CashSession::query()
                ->where('status', 'closed')
                ->latest('closed_at')
                ->first();

        return Inertia::render('Cash/Index', [
            'session' =>
                $session
                    ? [
                        'id' =>
                            $session->id,

                        'opened_at' =>
                            $session->opened_at
                                ?->format(
                                    'Y-m-d H:i'
                                ),

                        'opened_by' =>
                            $session
                                ->opener
                                ?->name,

                        'opening_balance' =>
                            $session
                                ->opening_balance,

                        'opening_notes' =>
                            $session
                                ->opening_notes,
                    ]
                    : null,

            'summary' =>
                $session
                    ? $this->buildSummary(
                        $session
                    )
                    : null,

            'movements' =>
                $session
                    ? $this->movementTimeline(
                        $session
                    )
                    : [],

            'financialAccounts' =>
                $this
                    ->financialAccountsSummary(),

            'digitalSummary' =>
                $this
                    ->digitalSummary(),

            'lastClosedSession' =>
                $lastClosedSession
                    ? $this->historyItem(
                        $lastClosedSession
                    )
                    : null,
        ]);
    }

    public function history(): Response
    {
        $sessions = CashSession::query()
            ->with([
                'opener',
                'closer',
            ])
            ->where(
                'status',
                'closed'
            )
            ->latest(
                'closed_at'
            )
            ->get()
            ->map(
                fn (
                    CashSession $session
                ) =>
                    $this->historyItem(
                        $session
                    )
            )
            ->values();

        return Inertia::render(
            'Cash/History',
            [
                'sessions' =>
                    $sessions,
            ]
        );
    }

    public function open(
        Request $request
    ): RedirectResponse {
        if (
            CashSession::query()
                ->where(
                    'status',
                    'open'
                )
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'opening_balance' =>
                        'Ya existe una caja abierta.',
                ]);
        }

        $validated =
            $request->validate([
                'opening_balance' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'opening_notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        CashSession::create([
            'opened_by' =>
                $request->user()?->id,

            'opened_at' =>
                now(),

            'status' =>
                'open',

            'opening_balance' =>
                round(
                    (float)
                    $validated[
                        'opening_balance'
                    ],
                    2
                ),

            'opening_notes' =>
                $validated[
                    'opening_notes'
                ] ?? null,
        ]);

        return back()->with(
            'success',
            'Caja abierta correctamente.'
        );
    }

    public function close(
        Request $request
    ): RedirectResponse {
        $session =
            CashSession::query()
                ->where(
                    'status',
                    'open'
                )
                ->latest('id')
                ->first();

        if (!$session) {
            return back()
                ->withErrors([
                    'closing_balance' =>
                        'No existe una caja abierta.',
                ]);
        }

        $validated =
            $request->validate([
                'closing_balance' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'closing_notes' => [
                    'nullable',
                    'string',
                ],
            ]);

        DB::transaction(
            function () use (
                $session,
                $validated,
                $request
            ) {
                $summary =
                    $this->buildSummary(
                        $session
                    );

                $expected =
                    (float)
                    $summary[
                        'expected_cash'
                    ];

                $closing =
                    round(
                        (float)
                        $validated[
                            'closing_balance'
                        ],
                        2
                    );

                $session->update([
                    'closed_by' =>
                        $request
                            ->user()?->id,

                    'closed_at' =>
                        now(),

                    'status' =>
                        'closed',

                    'expected_balance' =>
                        $expected,

                    'closing_balance' =>
                        $closing,

                    'difference' =>
                        round(
                            $closing
                            - $expected,
                            2
                        ),

                    'closing_notes' =>
                        $validated[
                            'closing_notes'
                        ] ?? null,
                ]);
            }
        );

        return back()->with(
            'success',
            'Caja cerrada correctamente.'
        );
    }

    public function storeMovement(
        Request $request
    ): RedirectResponse {
        $session =
            CashSession::query()
                ->where(
                    'status',
                    'open'
                )
                ->latest('id')
                ->first();

        if (!$session) {
            return back()
                ->withErrors([
                    'amount' =>
                        'Debes abrir la caja primero.',
                ]);
        }

        $validated =
            $request->validate([
                'movement_type' => [
                    'required',

                    Rule::in([
                        'income',
                        'expense',
                        'adjustment_in',
                        'adjustment_out',
                    ]),
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],

                'description' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:150',
                ],
            ]);

        CashMovement::create([
            'cash_session_id' =>
                $session->id,

            'created_by' =>
                $request->user()?->id,

            'movement_type' =>
                $validated[
                    'movement_type'
                ],

            'amount' =>
                round(
                    (float)
                    $validated['amount'],
                    2
                ),

            'description' =>
                $validated[
                    'description'
                ],

            'reference' =>
                $validated[
                    'reference'
                ] ?? null,

            'moved_at' =>
                now(),
        ]);

        return back()->with(
            'success',
            'Movimiento registrado correctamente.'
        );
    }

    private function buildSummary(
        CashSession $session
    ): array {
        $cashSales =
            (float)
            SalePayment::query()
                ->where(
                    'cash_session_id',
                    $session->id
                )
                ->where(
                    'payment_method',
                    'cash'
                )
                ->sum('amount');

        $manualIncome =
            (float)
            CashMovement::query()
                ->where(
                    'cash_session_id',
                    $session->id
                )
                ->whereIn(
                    'movement_type',
                    [
                        'income',
                        'adjustment_in',
                    ]
                )
                ->sum('amount');

        $manualExpense =
            (float)
            CashMovement::query()
                ->where(
                    'cash_session_id',
                    $session->id
                )
                ->whereIn(
                    'movement_type',
                    [
                        'expense',
                        'adjustment_out',
                    ]
                )
                ->sum('amount');

        $opening =
            (float)
            $session
                ->opening_balance;

        $expectedCash =
            round(
                $opening
                + $cashSales
                + $manualIncome
                - $manualExpense,
                2
            );

        return [
            'opening_balance' =>
                round(
                    $opening,
                    2
                ),

            'cash_sales' =>
                round(
                    $cashSales,
                    2
                ),

            'manual_income' =>
                round(
                    $manualIncome,
                    2
                ),

            'manual_expense' =>
                round(
                    $manualExpense,
                    2
                ),

            'expected_cash' =>
                $expectedCash,
        ];
    }

    private function digitalSummary(): array
    {
        $transferIn =
            (float)
            SalePayment::query()
                ->where(
                    'payment_method',
                    'transfer'
                )
                ->sum('amount')
            +
            (float)
            Income::query()
                ->where(
                    'payment_method',
                    'transfer'
                )
                ->sum('amount');

        $cardIn =
            (float)
            SalePayment::query()
                ->where(
                    'payment_method',
                    'card'
                )
                ->sum('amount')
            +
            (float)
            Income::query()
                ->where(
                    'payment_method',
                    'card'
                )
                ->sum('amount');

        $transferOut =
            (float)
            Expense::query()
                ->where(
                    'payment_method',
                    'transfer'
                )
                ->sum('amount');

        $cardOut =
            (float)
            Expense::query()
                ->where(
                    'payment_method',
                    'card'
                )
                ->sum('amount');

        return [
            'transfer_in' =>
                round(
                    $transferIn,
                    2
                ),

            'card_in' =>
                round(
                    $cardIn,
                    2
                ),

            'transfer_out' =>
                round(
                    $transferOut,
                    2
                ),

            'card_out' =>
                round(
                    $cardOut,
                    2
                ),
        ];
    }

    private function financialAccountsSummary(): array
    {
        return FinancialAccount::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(
                function (
                    FinancialAccount $account
                ) {
                    $salesIn =
                        (float)
                        SalePayment::query()
                            ->where(
                                'financial_account_id',
                                $account->id
                            )
                            ->sum(
                                'amount'
                            );

                    $otherIncome =
                        (float)
                        Income::query()
                            ->where(
                                'financial_account_id',
                                $account->id
                            )
                            ->sum(
                                'amount'
                            );

                    $expenses =
                        (float)
                        Expense::query()
                            ->where(
                                'financial_account_id',
                                $account->id
                            )
                            ->sum(
                                'amount'
                            );

                    $opening =
                        (float)
                        $account
                            ->opening_balance;

                    return [
                        'id' =>
                            $account->id,

                        'code' =>
                            $account
                                ->account_code,

                        'name' =>
                            $account->name,

                        'institution' =>
                            $account
                                ->institution,

                        'account_type' =>
                            $account
                                ->account_type,

                        'opening_balance' =>
                            round(
                                $opening,
                                2
                            ),

                        'sales_income' =>
                            round(
                                $salesIn,
                                2
                            ),

                        'other_income' =>
                            round(
                                $otherIncome,
                                2
                            ),

                        'expenses' =>
                            round(
                                $expenses,
                                2
                            ),

                        'net_movement' =>
                            round(
                                $salesIn
                                + $otherIncome
                                - $expenses,
                                2
                            ),

                        'calculated_balance' =>
                            round(
                                $opening
                                + $salesIn
                                + $otherIncome
                                - $expenses,
                                2
                            ),
                    ];
                }
            )
            ->values()
            ->all();
    }

    private function movementTimeline(
        CashSession $session
    ): array {
        $payments =
            SalePayment::query()
                ->with([
                    'sale.client',
                ])
                ->where(
                    'cash_session_id',
                    $session->id
                )
                ->where(
                    'payment_method',
                    'cash'
                )
                ->get()
                ->map(
                    fn (
                        SalePayment $payment
                    ) => [
                        'id' =>
                            'payment-' .
                            $payment->id,

                        'occurred_at' =>
                            $payment
                                ->created_at
                                ?->format(
                                    'Y-m-d H:i:s'
                                ),

                        'direction' =>
                            'in',

                        'type' =>
                            'Pago de venta',

                        'description' =>
                            $payment
                                ->receipt_number .
                            ' · ' .
                            (
                                $payment
                                    ->sale
                                    ?->client
                                    ?->display_name
                                ?? 'Cliente'
                            ),

                        'reference' =>
                            $payment
                                ->sale
                                ?->sale_number,

                        'amount' =>
                            (float)
                            $payment
                                ->amount,

                        'receipt_id' =>
                            $payment->id,
                    ]
                );

        $manual =
            CashMovement::query()
                ->where(
                    'cash_session_id',
                    $session->id
                )
                ->get()
                ->map(
                    fn (
                        CashMovement $movement
                    ) => [
                        'id' =>
                            'movement-' .
                            $movement->id,

                        'occurred_at' =>
                            $movement
                                ->moved_at
                                ?->format(
                                    'Y-m-d H:i:s'
                                ),

                        'direction' =>
                            in_array(
                                $movement
                                    ->movement_type,
                                [
                                    'income',
                                    'adjustment_in',
                                ]
                            )
                                ? 'in'
                                : 'out',

                        'type' =>
                            $this
                                ->movementLabel(
                                    $movement
                                        ->movement_type
                                ),

                        'description' =>
                            $movement
                                ->description,

                        'reference' =>
                            $movement
                                ->reference,

                        'amount' =>
                            (float)
                            $movement
                                ->amount,

                        'receipt_id' =>
                            null,
                    ]
                );

        return $payments
            ->concat($manual)
            ->sortByDesc(
                'occurred_at'
            )
            ->values()
            ->all();
    }

    private function movementLabel(
        string $type
    ): string {
        return match ($type) {
            'income' =>
                'Entrada de efectivo',

            'expense' =>
                'Salida de efectivo',

            'adjustment_in' =>
                'Ajuste positivo',

            'adjustment_out' =>
                'Ajuste negativo',

            default =>
                'Movimiento',
        };
    }

    private function historyItem(
        CashSession $session
    ): array {
        return [
            'id' =>
                $session->id,

            'opened_at' =>
                $session
                    ->opened_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'closed_at' =>
                $session
                    ->closed_at
                    ?->format(
                        'Y-m-d H:i'
                    ),

            'opened_by' =>
                $session
                    ->opener
                    ?->name,

            'closed_by' =>
                $session
                    ->closer
                    ?->name,

            'opening_balance' =>
                $session
                    ->opening_balance,

            'expected_balance' =>
                $session
                    ->expected_balance,

            'closing_balance' =>
                $session
                    ->closing_balance,

            'difference' =>
                $session
                    ->difference,
        ];
    }
}