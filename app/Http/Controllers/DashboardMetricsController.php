<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardMetricsController extends Controller
{
    public function index(): JsonResponse
    {
        $now =
            CarbonImmutable::now(
                config(
                    'app.timezone',
                    'America/Tegucigalpa'
                )
            );

        $todayStart =
            $now->startOfDay();

        $todayEnd =
            $now->endOfDay();

        $monthStart =
            $now->startOfMonth();

        $monthEnd =
            $now->endOfMonth();

        $todayIncome =
            $this->sumPeriod(
                [
                    'incomes',
                    'income',
                ],
                [
                    'amount',
                    'total',
                    'value',
                ],
                [
                    'income_date',
                    'date',
                    'created_at',
                ],
                $todayStart,
                $todayEnd
            );

        $todayExpenses =
            $this->sumPeriod(
                [
                    'expenses',
                    'expense',
                ],
                [
                    'amount',
                    'total',
                    'value',
                ],
                [
                    'expense_date',
                    'date',
                    'created_at',
                ],
                $todayStart,
                $todayEnd
            );

        $monthIncome =
            $this->sumPeriod(
                [
                    'incomes',
                    'income',
                ],
                [
                    'amount',
                    'total',
                    'value',
                ],
                [
                    'income_date',
                    'date',
                    'created_at',
                ],
                $monthStart,
                $monthEnd
            );

        $monthExpenses =
            $this->sumPeriod(
                [
                    'expenses',
                    'expense',
                ],
                [
                    'amount',
                    'total',
                    'value',
                ],
                [
                    'expense_date',
                    'date',
                    'created_at',
                ],
                $monthStart,
                $monthEnd
            );

        $todaySales =
            $this->sumPeriod(
                [
                    'sales',
                ],
                [
                    'total',
                    'grand_total',
                    'total_amount',
                    'amount',
                ],
                [
                    'sale_date',
                    'date',
                    'created_at',
                ],
                $todayStart,
                $todayEnd
            );

        return response()->json([
            'money' => [
                'today_income' =>
                    $todayIncome,

                'today_expenses' =>
                    $todayExpenses,

                'today_balance' =>
                    $todayIncome
                    -
                    $todayExpenses,

                'today_sales' =>
                    $todaySales,

                'month_income' =>
                    $monthIncome,

                'month_expenses' =>
                    $monthExpenses,

                'month_balance' =>
                    $monthIncome
                    -
                    $monthExpenses,

                'receivable_balance' =>
                    $this
                        ->receivableBalance(),
            ],

            'counts' => [
                'clients' =>
                    $this
                        ->clientCount(),

                'new_leads' =>
                    $this
                        ->newLeadCount(),

                'quotations_active' =>
                    $this
                        ->activeQuotationCount(),

                'work_orders_active' =>
                    $this
                        ->activeWorkOrderCount(),
            ],

            'generated_at' =>
                $now->toIso8601String(),
        ]);
    }

    private function sumPeriod(
        array $tableCandidates,
        array $amountCandidates,
        array $dateCandidates,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): float {
        try {
            $table =
                $this->resolveTable(
                    $tableCandidates
                );

            if (!$table) {
                return 0.0;
            }

            $columns =
                Schema::getColumnListing(
                    $table
                );

            $amountColumn =
                $this->resolveColumn(
                    $columns,
                    $amountCandidates
                );

            $dateColumn =
                $this->resolveColumn(
                    $columns,
                    $dateCandidates
                );

            if (
                !$amountColumn
                ||
                !$dateColumn
            ) {
                return 0.0;
            }

            $query =
                DB::table(
                    $table
                );

            if (
                str_ends_with(
                    $dateColumn,
                    '_date'
                )
                ||
                $dateColumn ===
                'date'
            ) {
                $query->whereBetween(
                    $dateColumn,
                    [
                        $start
                            ->toDateString(),

                        $end
                            ->toDateString(),
                    ]
                );
            } else {
                $query->whereBetween(
                    $dateColumn,
                    [
                        $start,
                        $end,
                    ]
                );
            }

            return (float)
                $query->sum(
                    $amountColumn
                );
        } catch (
            Throwable
        ) {
            return 0.0;
        }
    }

    private function receivableBalance(): float
    {
        try {
            $table =
                $this->resolveTable([
                    'accounts_receivables',
                    'accounts_receivable',
                    'receivables',
                ]);

            if (!$table) {
                return 0.0;
            }

            $columns =
                Schema::getColumnListing(
                    $table
                );

            $balanceColumn =
                $this->resolveColumn(
                    $columns,
                    [
                        'balance',
                        'pending_balance',
                        'remaining_balance',
                        'amount_due',
                        'balance_due',
                        'pending_amount',
                    ]
                );

            $query =
                DB::table(
                    $table
                );

            if (
                in_array(
                    'status',
                    $columns,
                    true
                )
            ) {
                $query->whereNotIn(
                    'status',
                    [
                        'paid',
                        'cancelled',
                        'canceled',
                    ]
                );
            }

            if ($balanceColumn) {
                return (float)
                    $query->sum(
                        $balanceColumn
                    );
            }

            $totalColumn =
                $this->resolveColumn(
                    $columns,
                    [
                        'total',
                        'total_amount',
                        'amount',
                    ]
                );

            $paidColumn =
                $this->resolveColumn(
                    $columns,
                    [
                        'paid',
                        'paid_amount',
                        'amount_paid',
                    ]
                );

            if (
                $totalColumn
                &&
                $paidColumn
            ) {
                $rows =
                    $query->get([
                        $totalColumn,
                        $paidColumn,
                    ]);

                return (float)
                    $rows->sum(
                        fn ($row) =>
                            max(
                                0,
                                (float)
                                $row
                                    ->{$totalColumn}
                                -
                                (float)
                                $row
                                    ->{$paidColumn}
                            )
                    );
            }

            return 0.0;
        } catch (
            Throwable
        ) {
            return 0.0;
        }
    }

    private function clientCount(): int
    {
        try {
            if (
                !Schema::hasTable(
                    'clients'
                )
            ) {
                return 0;
            }

            $columns =
                Schema::getColumnListing(
                    'clients'
                );

            $query =
                DB::table(
                    'clients'
                );

            if (
                in_array(
                    'deleted_at',
                    $columns,
                    true
                )
            ) {
                $query->whereNull(
                    'deleted_at'
                );
            }

            if (
                in_array(
                    'active',
                    $columns,
                    true
                )
            ) {
                $query->where(
                    'active',
                    true
                );
            }

            return $query->count();
        } catch (
            Throwable
        ) {
            return 0;
        }
    }

    private function newLeadCount(): int
    {
        try {
            if (
                !Schema::hasTable(
                    'website_leads'
                )
            ) {
                return 0;
            }

            return DB::table(
                'website_leads'
            )
                ->where(
                    'status',
                    'new'
                )
                ->whereNull(
                    'reviewed_at'
                )
                ->count();
        } catch (
            Throwable
        ) {
            return 0;
        }
    }

    private function activeQuotationCount(): int
    {
        try {
            if (
                !Schema::hasTable(
                    'quotations'
                )
            ) {
                return 0;
            }

            $columns =
                Schema::getColumnListing(
                    'quotations'
                );

            $query =
                DB::table(
                    'quotations'
                );

            if (
                in_array(
                    'deleted_at',
                    $columns,
                    true
                )
            ) {
                $query->whereNull(
                    'deleted_at'
                );
            }

            if (
                in_array(
                    'status',
                    $columns,
                    true
                )
            ) {
                $query->whereNotIn(
                    'status',
                    [
                        'rejected',
                        'cancelled',
                        'canceled',
                        'expired',
                    ]
                );
            }

            return $query->count();
        } catch (
            Throwable
        ) {
            return 0;
        }
    }

    private function activeWorkOrderCount(): int
    {
        try {
            if (
                !Schema::hasTable(
                    'work_orders'
                )
            ) {
                return 0;
            }

            $columns =
                Schema::getColumnListing(
                    'work_orders'
                );

            $query =
                DB::table(
                    'work_orders'
                );

            if (
                in_array(
                    'deleted_at',
                    $columns,
                    true
                )
            ) {
                $query->whereNull(
                    'deleted_at'
                );
            }

            if (
                in_array(
                    'status',
                    $columns,
                    true
                )
            ) {
                $query->whereNotIn(
                    'status',
                    [
                        'completed',
                        'delivered',
                        'cancelled',
                        'canceled',
                    ]
                );
            }

            return $query->count();
        } catch (
            Throwable
        ) {
            return 0;
        }
    }

    private function resolveTable(
        array $candidates
    ): ?string {
        foreach (
            $candidates
            as $candidate
        ) {
            if (
                Schema::hasTable(
                    $candidate
                )
            ) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveColumn(
        array $columns,
        array $candidates
    ): ?string {
        foreach (
            $candidates
            as $candidate
        ) {
            if (
                in_array(
                    $candidate,
                    $columns,
                    true
                )
            ) {
                return $candidate;
            }
        }

        return null;
    }
}