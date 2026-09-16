<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Expense;
use App\Models\FinancialAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(): Response
    {
        $expenses = Expense::query()
            ->with('financialAccount')
            ->latest('expense_date')
            ->latest('id')
            ->get()
            ->map(fn (
                Expense $expense
            ) => [
                'id' =>
                    $expense->id,

                'expense_number' =>
                    $expense->expense_number,

                'expense_date' =>
                    $expense->expense_date?->format(
                        'Y-m-d'
                    ),

                'category' =>
                    $expense->category,

                'description' =>
                    $expense->description,

                'supplier' =>
                    $expense->supplier,

                'amount' =>
                    $expense->amount,

                'payment_method' =>
                    $expense->payment_method,

                'financial_account' =>
                    $expense
                        ->financialAccount
                        ?->name,

                'reference' =>
                    $expense->reference,

                'cash_registered' =>
                    $expense->cash_movement_id
                    !== null,
            ])
            ->values();

        return Inertia::render(
            'Expenses/Index',
            [
                'expenses' =>
                    $expenses,
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Expenses/Create',
            [
                'financialAccounts' =>
                    $this
                        ->financialAccountsForForm(),
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validateExpense(
                $request
            );

        DB::transaction(function () use (
            $validated,
            $request
        ) {
            $expense = Expense::create([
                'expense_number' =>
                    $this
                        ->nextExpenseNumber(),

                'created_by' =>
                    $request->user()?->id,

                'financial_account_id' =>
                    $validated[
                        'payment_method'
                    ] === 'cash'
                        ? null
                        : $validated[
                            'financial_account_id'
                        ],

                'expense_date' =>
                    $validated[
                        'expense_date'
                    ],

                'category' =>
                    $validated['category'],

                'description' =>
                    $validated[
                        'description'
                    ],

                'supplier' =>
                    $validated[
                        'supplier'
                    ] ?? null,

                'amount' => round(
                    (float) $validated[
                        'amount'
                    ],
                    2
                ),

                'payment_method' =>
                    $validated[
                        'payment_method'
                    ],

                'reference' =>
                    $validated[
                        'reference'
                    ] ?? null,

                'notes' =>
                    $validated[
                        'notes'
                    ] ?? null,
            ]);

            $this->syncCashMovement(
                $expense,
                $request->user()?->id
            );
        });

        return redirect()
            ->route('expenses.index')
            ->with(
                'success',
                'Gasto registrado correctamente.'
            );
    }

    public function edit(
        Expense $expense
    ): Response {
        return Inertia::render(
            'Expenses/Edit',
            [
                'expense' => [
                    'id' =>
                        $expense->id,

                    'expense_number' =>
                        $expense->expense_number,

                    'expense_date' =>
                        $expense
                            ->expense_date
                            ?->format('Y-m-d'),

                    'category' =>
                        $expense->category,

                    'description' =>
                        $expense->description,

                    'supplier' =>
                        $expense->supplier,

                    'amount' =>
                        $expense->amount,

                    'payment_method' =>
                        $expense
                            ->payment_method,

                    'financial_account_id' =>
                        $expense
                            ->financial_account_id,

                    'reference' =>
                        $expense->reference,

                    'notes' =>
                        $expense->notes,
                ],

                'financialAccounts' =>
                    $this
                        ->financialAccountsForForm(),
            ]
        );
    }

    public function update(
        Request $request,
        Expense $expense
    ): RedirectResponse {
        $validated =
            $this->validateExpense(
                $request
            );

        DB::transaction(function () use (
            $validated,
            $request,
            $expense
        ) {
            $this
                ->ensureCashMovementEditable(
                    $expense
                );

            $expense->update([
                'expense_date' =>
                    $validated[
                        'expense_date'
                    ],

                'category' =>
                    $validated['category'],

                'description' =>
                    $validated[
                        'description'
                    ],

                'supplier' =>
                    $validated[
                        'supplier'
                    ] ?? null,

                'amount' => round(
                    (float) $validated[
                        'amount'
                    ],
                    2
                ),

                'payment_method' =>
                    $validated[
                        'payment_method'
                    ],

                'financial_account_id' =>
                    $validated[
                        'payment_method'
                    ] === 'cash'
                        ? null
                        : $validated[
                            'financial_account_id'
                        ],

                'reference' =>
                    $validated[
                        'reference'
                    ] ?? null,

                'notes' =>
                    $validated[
                        'notes'
                    ] ?? null,
            ]);

            $this->syncCashMovement(
                $expense,
                $request->user()?->id
            );
        });

        return redirect()
            ->route('expenses.index')
            ->with(
                'success',
                'Gasto actualizado correctamente.'
            );
    }

    private function validateExpense(
        Request $request
    ): array {
        return $request->validate([
            'expense_date' => [
                'required',
                'date',
            ],

            'category' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'required',
                'string',
                'max:200',
            ],

            'supplier' => [
                'nullable',
                'string',
                'max:150',
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
                )
                    ->where(
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
    }

    private function syncCashMovement(
        Expense $expense,
        ?int $userId
    ): void {
        $movement = $expense
            ->cashMovement()
            ->with('cashSession')
            ->first();

        if (
            $expense->payment_method
            !== 'cash'
        ) {
            if ($movement) {
                $movement->delete();

                $expense->updateQuietly([
                    'cash_movement_id' =>
                        null,
                ]);
            }

            return;
        }

        $session =
            $movement?->cashSession;

        if (
            !$session
            || $session->status !== 'open'
        ) {
            $session =
                CashSession::query()
                    ->where(
                        'status',
                        'open'
                    )
                    ->latest('id')
                    ->first();
        }

        if (!$session) {
            throw ValidationException::withMessages([
                'payment_method' =>
                    'Para registrar un gasto en efectivo debes abrir la caja primero.',
            ]);
        }

        $data = [
            'cash_session_id' =>
                $session->id,

            'created_by' =>
                $userId,

            'movement_type' =>
                'expense',

            'amount' =>
                $expense->amount,

            'description' =>
                $expense
                    ->expense_number .
                ' · ' .
                $expense
                    ->description,

            'reference' =>
                $expense->reference
                ?: $expense
                    ->expense_number,

            'moved_at' =>
                now(),
        ];

        if ($movement) {
            $movement->update($data);

            return;
        }

        $movement =
            CashMovement::create($data);

        $expense->updateQuietly([
            'cash_movement_id' =>
                $movement->id,
        ]);
    }

    private function ensureCashMovementEditable(
        Expense $expense
    ): void {
        if (!$expense->cash_movement_id) {
            return;
        }

        $movement = $expense
            ->cashMovement()
            ->with('cashSession')
            ->first();

        if (
            $movement
                ?->cashSession
                ?->status === 'closed'
        ) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Este gasto pertenece a una caja cerrada y no puede modificarse.',
            ]);
        }
    }

    private function financialAccountsForForm(): array
    {
        return FinancialAccount::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (
                FinancialAccount $account
            ) => [
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
            ])
            ->values()
            ->all();
    }

    private function nextExpenseNumber(): string
    {
        $next =
            (Expense::withTrashed()
                ->max('id') ?? 0)
            + 1;

        return 'GAS-' .
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