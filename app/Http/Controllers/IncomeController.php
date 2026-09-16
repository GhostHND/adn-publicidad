<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\FinancialAccount;
use App\Models\Income;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class IncomeController extends Controller
{
    public function index(): Response
    {
        $incomes = Income::query()
            ->with('financialAccount')
            ->latest('income_date')
            ->latest('id')
            ->get()
            ->map(fn (Income $income) => [
                'id' => $income->id,
                'income_number' => $income->income_number,
                'income_date' => $income->income_date?->format('Y-m-d'),
                'category' => $income->category,
                'description' => $income->description,
                'amount' => $income->amount,
                'payment_method' => $income->payment_method,
                'reference' => $income->reference,
                'financial_account' =>
                    $income->financialAccount?->name,
                'cash_registered' =>
                    $income->cash_movement_id !== null,
            ])
            ->values();

        return Inertia::render('Income/Index', [
            'incomes' => $incomes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Income/Create', [
            'financialAccounts' =>
                $this->financialAccountsForForm(),
        ]);
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validateIncome($request);

        DB::transaction(function () use (
            $validated,
            $request
        ) {
            $income = Income::create([
                'income_number' =>
                    $this->nextIncomeNumber(),

                'created_by' =>
                    $request->user()?->id,

                'financial_account_id' =>
                    $validated['payment_method'] === 'cash'
                        ? null
                        : $validated['financial_account_id'],

                'income_date' =>
                    $validated['income_date'],

                'category' =>
                    $validated['category'],

                'description' =>
                    $validated['description'],

                'amount' => round(
                    (float) $validated['amount'],
                    2
                ),

                'payment_method' =>
                    $validated['payment_method'],

                'reference' =>
                    $validated['reference'] ?? null,

                'notes' =>
                    $validated['notes'] ?? null,
            ]);

            $this->syncCashMovement(
                $income,
                $request->user()?->id
            );
        });

        return redirect()
            ->route('income.index')
            ->with(
                'success',
                'Ingreso registrado correctamente.'
            );
    }

    public function edit(
        Income $income
    ): Response {
        return Inertia::render('Income/Edit', [
            'income' => [
                'id' => $income->id,
                'income_number' =>
                    $income->income_number,
                'income_date' =>
                    $income->income_date?->format(
                        'Y-m-d'
                    ),
                'category' =>
                    $income->category,
                'description' =>
                    $income->description,
                'amount' =>
                    $income->amount,
                'payment_method' =>
                    $income->payment_method,
                'financial_account_id' =>
                    $income->financial_account_id,
                'reference' =>
                    $income->reference,
                'notes' =>
                    $income->notes,
            ],

            'financialAccounts' =>
                $this->financialAccountsForForm(),
        ]);
    }

    public function update(
        Request $request,
        Income $income
    ): RedirectResponse {
        $validated =
            $this->validateIncome($request);

        DB::transaction(function () use (
            $validated,
            $request,
            $income
        ) {
            $this->ensureCashMovementEditable(
                $income
            );

            $income->update([
                'income_date' =>
                    $validated['income_date'],

                'category' =>
                    $validated['category'],

                'description' =>
                    $validated['description'],

                'amount' => round(
                    (float) $validated['amount'],
                    2
                ),

                'payment_method' =>
                    $validated['payment_method'],

                'financial_account_id' =>
                    $validated['payment_method'] === 'cash'
                        ? null
                        : $validated['financial_account_id'],

                'reference' =>
                    $validated['reference'] ?? null,

                'notes' =>
                    $validated['notes'] ?? null,
            ]);

            $this->syncCashMovement(
                $income,
                $request->user()?->id
            );
        });

        return redirect()
            ->route('income.index')
            ->with(
                'success',
                'Ingreso actualizado correctamente.'
            );
    }

    private function validateIncome(
        Request $request
    ): array {
        return $request->validate([
            'income_date' => [
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
                        $request->payment_method
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
        Income $income,
        ?int $userId
    ): void {
        $movement = $income
            ->cashMovement()
            ->with('cashSession')
            ->first();

        if (
            $income->payment_method
            !== 'cash'
        ) {
            if ($movement) {
                $movement->delete();

                $income->updateQuietly([
                    'cash_movement_id' => null,
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
            $session = CashSession::query()
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
                    'Para registrar un ingreso en efectivo debes abrir la caja primero.',
            ]);
        }

        $data = [
            'cash_session_id' =>
                $session->id,

            'created_by' =>
                $userId,

            'movement_type' =>
                'income',

            'amount' =>
                $income->amount,

            'description' =>
                $income->income_number .
                ' · ' .
                $income->description,

            'reference' =>
                $income->reference
                ?: $income->income_number,

            'moved_at' =>
                now(),
        ];

        if ($movement) {
            $movement->update($data);

            return;
        }

        $movement =
            CashMovement::create($data);

        $income->updateQuietly([
            'cash_movement_id' =>
                $movement->id,
        ]);
    }

    private function ensureCashMovementEditable(
        Income $income
    ): void {
        if (!$income->cash_movement_id) {
            return;
        }

        $movement = $income
            ->cashMovement()
            ->with('cashSession')
            ->first();

        if (
            $movement?->cashSession?->status
            === 'closed'
        ) {
            throw ValidationException::withMessages([
                'amount' =>
                    'Este ingreso pertenece a una caja cerrada y no puede modificarse.',
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
                'id' => $account->id,
                'code' =>
                    $account->account_code,
                'name' =>
                    $account->name,
                'institution' =>
                    $account->institution,
                'account_type' =>
                    $account->account_type,
            ])
            ->values()
            ->all();
    }

    private function nextIncomeNumber(): string
    {
        $next =
            (Income::withTrashed()->max('id') ?? 0)
            + 1;

        return 'ING-' .
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