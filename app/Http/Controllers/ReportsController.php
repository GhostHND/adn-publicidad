<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class ReportsController extends Controller
{
    public function index(Request $request): Response
    {
        /*
        |--------------------------------------------------------------------------
        | FECHAS DEL REPORTE
        |--------------------------------------------------------------------------
        */

        $startDate =
            $request
                ->string('start_date')
                ->toString();

        $endDate =
            $request
                ->string('end_date')
                ->toString();

        if (!$startDate) {
            $startDate =
                now()
                    ->startOfMonth()
                    ->toDateString();
        }

        if (!$endDate) {
            $endDate =
                now()
                    ->toDateString();
        }

        $start =
            Carbon::parse(
                $startDate
            )->startOfDay();

        $end =
            Carbon::parse(
                $endDate
            )->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | DETECTAR COLUMNAS REALES
        |--------------------------------------------------------------------------
        |
        | En lugar de asumir que todas las tablas utilizan "date",
        | detectamos la columna realmente existente.
        |
        */

        $incomeDateColumn =
            $this->resolveDateColumn(
                'incomes',
                [
                    'date',
                    'income_date',
                    'movement_date',
                    'transaction_date',
                    'created_at',
                ]
            );

        $expenseDateColumn =
            $this->resolveDateColumn(
                'expenses',
                [
                    'date',
                    'expense_date',
                    'movement_date',
                    'transaction_date',
                    'created_at',
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | VENTAS
        |--------------------------------------------------------------------------
        */

        $salesQuery =
            DB::table('sales')
                ->whereNull(
                    'deleted_at'
                )
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->whereDate(
                    'sale_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    'sale_date',
                    '<=',
                    $end
                        ->toDateString()
                );

        $salesTotal =
            (float)
            (clone $salesQuery)
                ->sum('total');

        $estimatedCost =
            (float)
            (clone $salesQuery)
                ->sum(
                    'estimated_cost'
                );

        $salesCount =
            (int)
            (clone $salesQuery)
                ->count();

        $estimatedGrossProfit =
            round(
                $salesTotal
                -
                $estimatedCost,
                2
            );

        /*
        |--------------------------------------------------------------------------
        | COBROS REALES
        |--------------------------------------------------------------------------
        */

        $paymentsQuery =
            DB::table(
                'sale_payments'
            )
                ->whereDate(
                    'payment_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    'payment_date',
                    '<=',
                    $end
                        ->toDateString()
                );

        $paymentsTotal =
            (float)
            (clone $paymentsQuery)
                ->sum('amount');

        $paymentsCount =
            (int)
            (clone $paymentsQuery)
                ->count();

        /*
        |--------------------------------------------------------------------------
        | OTROS INGRESOS
        |--------------------------------------------------------------------------
        */

        $incomeQuery =
            DB::table('incomes');

        if (
            Schema::hasColumn(
                'incomes',
                'deleted_at'
            )
        ) {
            $incomeQuery
                ->whereNull(
                    'deleted_at'
                );
        }

        $incomeQuery =
            $this->applyDateRange(
                $incomeQuery,
                $incomeDateColumn,
                $start,
                $end
            );

        $otherIncome =
            (float)
            $incomeQuery
                ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | GASTOS
        |--------------------------------------------------------------------------
        */

        $expenseQuery =
            DB::table('expenses');

        if (
            Schema::hasColumn(
                'expenses',
                'deleted_at'
            )
        ) {
            $expenseQuery
                ->whereNull(
                    'deleted_at'
                );
        }

        $expenseQuery =
            $this->applyDateRange(
                $expenseQuery,
                $expenseDateColumn,
                $start,
                $end
            );

        $expensesTotal =
            (float)
            $expenseQuery
                ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | FLUJO REAL DEL PERIODO
        |--------------------------------------------------------------------------
        |
        | Venta no significa efectivo recibido.
        |
        | Flujo =
        | Cobros de ventas
        | + otros ingresos
        | - gastos
        |
        */

        $cashFlow =
            round(
                $paymentsTotal
                +
                $otherIncome
                -
                $expensesTotal,
                2
            );

        /*
        |--------------------------------------------------------------------------
        | CUENTAS POR COBRAR ACTUALES
        |--------------------------------------------------------------------------
        */

        $paymentTotals =
            DB::table(
                'sale_payments'
            )
                ->select(
                    'sale_id',
                    DB::raw(
                        'SUM(amount) AS paid_amount'
                    )
                )
                ->groupBy(
                    'sale_id'
                );

        $receivables =
            DB::table(
                'sales as s'
            )
                ->leftJoin(
                    'clients as c',
                    'c.id',
                    '=',
                    's.client_id'
                )
                ->leftJoinSub(
                    $paymentTotals,
                    'p',
                    'p.sale_id',
                    '=',
                    's.id'
                )
                ->whereNull(
                    's.deleted_at'
                )
                ->whereNotIn(
                    's.status',
                    [
                        'paid',
                        'cancelled',
                    ]
                )
                ->select([
                    's.id',
                    's.sale_number',
                    's.sale_date',
                    's.total',

                    DB::raw(
                        '
                        COALESCE(
                            p.paid_amount,
                            0
                        ) AS paid_amount
                        '
                    ),

                    DB::raw(
                        '
                        (
                            s.total
                            -
                            COALESCE(
                                p.paid_amount,
                                0
                            )
                        ) AS balance
                        '
                    ),

                    DB::raw(
                        "
                        CASE
                            WHEN
                                c.business_name IS NOT NULL
                                AND c.business_name != ''
                            THEN
                                c.business_name

                            ELSE
                                TRIM(
                                    CONCAT(
                                        COALESCE(c.first_name, ''),
                                        ' ',
                                        COALESCE(c.middle_name, ''),
                                        ' ',
                                        COALESCE(c.last_name, ''),
                                        ' ',
                                        COALESCE(c.second_last_name, '')
                                    )
                                )
                        END AS client_name
                        "
                    ),
                ])
                ->having(
                    'balance',
                    '>',
                    0
                )
                ->orderByDesc(
                    'balance'
                )
                ->get();

        $receivableTotal =
            round(
                (float)
                $receivables
                    ->sum('balance'),
                2
            );

        /*
        |--------------------------------------------------------------------------
        | MÉTODOS DE PAGO
        |--------------------------------------------------------------------------
        */

        $paymentMethods =
            DB::table(
                'sale_payments'
            )
                ->select(
                    'payment_method',

                    DB::raw(
                        'SUM(amount) AS total'
                    ),

                    DB::raw(
                        'COUNT(*) AS transactions'
                    )
                )
                ->whereDate(
                    'payment_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    'payment_date',
                    '<=',
                    $end
                        ->toDateString()
                )
                ->groupBy(
                    'payment_method'
                )
                ->orderByDesc(
                    'total'
                )
                ->get()
                ->map(
                    fn ($row) => [
                        'method' =>
                            $row
                                ->payment_method,

                        'total' =>
                            round(
                                (float)
                                $row->total,
                                2
                            ),

                        'transactions' =>
                            (int)
                            $row
                                ->transactions,
                    ]
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | GASTOS POR CATEGORÍA
        |--------------------------------------------------------------------------
        */

        $expensesByCategoryQuery =
            DB::table(
                'expenses'
            );

        if (
            Schema::hasColumn(
                'expenses',
                'deleted_at'
            )
        ) {
            $expensesByCategoryQuery
                ->whereNull(
                    'deleted_at'
                );
        }

        $expensesByCategoryQuery =
            $this->applyDateRange(
                $expensesByCategoryQuery,
                $expenseDateColumn,
                $start,
                $end
            );

        $expensesByCategory =
            $expensesByCategoryQuery
                ->select(
                    DB::raw(
                        "
                        COALESCE(
                            NULLIF(
                                category,
                                ''
                            ),
                            'Sin categoría'
                        ) AS category
                        "
                    ),

                    DB::raw(
                        'SUM(amount) AS total'
                    ),

                    DB::raw(
                        'COUNT(*) AS transactions'
                    )
                )
                ->groupBy(
                    'category'
                )
                ->orderByDesc(
                    'total'
                )
                ->get()
                ->map(
                    fn ($row) => [
                        'category' =>
                            $row
                                ->category,

                        'total' =>
                            round(
                                (float)
                                $row->total,
                                2
                            ),

                        'transactions' =>
                            (int)
                            $row
                                ->transactions,
                    ]
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | CLIENTES QUE MÁS COMPRAN
        |--------------------------------------------------------------------------
        */

        $topClients =
            DB::table(
                'sales as s'
            )
                ->join(
                    'clients as c',
                    'c.id',
                    '=',
                    's.client_id'
                )
                ->whereNull(
                    's.deleted_at'
                )
                ->where(
                    's.status',
                    '!=',
                    'cancelled'
                )
                ->whereDate(
                    's.sale_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    's.sale_date',
                    '<=',
                    $end
                        ->toDateString()
                )
                ->select(
                    'c.id',

                    DB::raw(
                        "
                        CASE
                            WHEN
                                c.business_name IS NOT NULL
                                AND c.business_name != ''
                            THEN
                                c.business_name

                            ELSE
                                TRIM(
                                    CONCAT(
                                        COALESCE(c.first_name, ''),
                                        ' ',
                                        COALESCE(c.middle_name, ''),
                                        ' ',
                                        COALESCE(c.last_name, ''),
                                        ' ',
                                        COALESCE(c.second_last_name, '')
                                    )
                                )
                        END AS client_name
                        "
                    ),

                    DB::raw(
                        'SUM(s.total) AS total'
                    ),

                    DB::raw(
                        'COUNT(s.id) AS sales_count'
                    )
                )
                ->groupBy(
                    'c.id',
                    'c.business_name',
                    'c.first_name',
                    'c.middle_name',
                    'c.last_name',
                    'c.second_last_name'
                )
                ->orderByDesc(
                    'total'
                )
                ->limit(10)
                ->get()
                ->map(
                    fn ($row) => [
                        'id' =>
                            $row->id,

                        'name' =>
                            $row
                                ->client_name,

                        'total' =>
                            round(
                                (float)
                                $row->total,
                                2
                            ),

                        'sales_count' =>
                            (int)
                            $row
                                ->sales_count,
                    ]
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS / SERVICIOS MÁS VENDIDOS
        |--------------------------------------------------------------------------
        */

        $topProducts =
            DB::table(
                'sale_items as si'
            )
                ->join(
                    'sales as s',
                    's.id',
                    '=',
                    'si.sale_id'
                )
                ->whereNull(
                    's.deleted_at'
                )
                ->where(
                    's.status',
                    '!=',
                    'cancelled'
                )
                ->whereDate(
                    's.sale_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    's.sale_date',
                    '<=',
                    $end
                        ->toDateString()
                )
                ->select(
                    'si.item_name',

                    DB::raw(
                        'SUM(si.quantity) AS quantity'
                    ),

                    DB::raw(
                        'SUM(si.subtotal) AS total'
                    ),

                    DB::raw(
                        'COUNT(DISTINCT s.id) AS sales_count'
                    )
                )
                ->groupBy(
                    'si.item_name'
                )
                ->orderByDesc(
                    'total'
                )
                ->limit(10)
                ->get()
                ->map(
                    fn ($row) => [
                        'name' =>
                            $row
                                ->item_name,

                        'quantity' =>
                            round(
                                (float)
                                $row
                                    ->quantity,
                                3
                            ),

                        'total' =>
                            round(
                                (float)
                                $row->total,
                                2
                            ),

                        'sales_count' =>
                            (int)
                            $row
                                ->sales_count,
                    ]
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | ACTIVIDAD DIARIA - VENTAS
        |--------------------------------------------------------------------------
        */

        $dailySalesRaw =
            DB::table('sales')
                ->whereNull(
                    'deleted_at'
                )
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->whereDate(
                    'sale_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    'sale_date',
                    '<=',
                    $end
                        ->toDateString()
                )
                ->select(
                    'sale_date',

                    DB::raw(
                        'SUM(total) AS total'
                    )
                )
                ->groupBy(
                    'sale_date'
                )
                ->pluck(
                    'total',
                    'sale_date'
                );

        /*
        |--------------------------------------------------------------------------
        | ACTIVIDAD DIARIA - COBROS
        |--------------------------------------------------------------------------
        */

        $dailyPaymentsRaw =
            DB::table(
                'sale_payments'
            )
                ->whereDate(
                    'payment_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    'payment_date',
                    '<=',
                    $end
                        ->toDateString()
                )
                ->select(
                    'payment_date',

                    DB::raw(
                        'SUM(amount) AS total'
                    )
                )
                ->groupBy(
                    'payment_date'
                )
                ->pluck(
                    'total',
                    'payment_date'
                );

        $daily =
            collect();

        $cursor =
            $start->copy();

        while (
            $cursor->lte(
                $end
            )
        ) {
            $date =
                $cursor
                    ->toDateString();

            $daily->push([
                'date' =>
                    $date,

                'label' =>
                    $cursor
                        ->format(
                            'd/m'
                        ),

                'sales' =>
                    round(
                        (float)
                        (
                            $dailySalesRaw[
                                $date
                            ]
                            ?? 0
                        ),
                        2
                    ),

                'payments' =>
                    round(
                        (float)
                        (
                            $dailyPaymentsRaw[
                                $date
                            ]
                            ?? 0
                        ),
                        2
                    ),
            ]);

            $cursor
                ->addDay();
        }

        /*
        |--------------------------------------------------------------------------
        | COTIZACIONES DEL PERIODO
        |--------------------------------------------------------------------------
        */

        $quotations =
            DB::table(
                'quotations'
            )
                ->whereNull(
                    'deleted_at'
                )
                ->whereDate(
                    'quotation_date',
                    '>=',
                    $start
                        ->toDateString()
                )
                ->whereDate(
                    'quotation_date',
                    '<=',
                    $end
                        ->toDateString()
                )
                ->select(
                    'status',

                    DB::raw(
                        'COUNT(*) AS total'
                    )
                )
                ->groupBy(
                    'status'
                )
                ->pluck(
                    'total',
                    'status'
                );

        $quotationSummary = [
            'draft' =>
                (int)
                (
                    $quotations[
                        'draft'
                    ]
                    ?? 0
                ),

            'sent' =>
                (int)
                (
                    $quotations[
                        'sent'
                    ]
                    ?? 0
                ),

            'approved' =>
                (int)
                (
                    $quotations[
                        'approved'
                    ]
                    ?? 0
                ),

            'rejected' =>
                (int)
                (
                    $quotations[
                        'rejected'
                    ]
                    ?? 0
                ),

            'expired' =>
                (int)
                (
                    $quotations[
                        'expired'
                    ]
                    ?? 0
                ),
        ];

        $totalDecided =
            $quotationSummary[
                'approved'
            ]
            +
            $quotationSummary[
                'rejected'
            ];

        $approvalRate =
            $totalDecided > 0
                ? round(
                    (
                        $quotationSummary[
                            'approved'
                        ]
                        /
                        $totalDecided
                    )
                    * 100,
                    1
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA AL FRONTEND
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Reports/Index',
            [
                'filters' => [
                    'start_date' =>
                        $start
                            ->toDateString(),

                    'end_date' =>
                        $end
                            ->toDateString(),
                ],

                'summary' => [
                    'sales_total' =>
                        round(
                            $salesTotal,
                            2
                        ),

                    'sales_count' =>
                        $salesCount,

                    'estimated_cost' =>
                        round(
                            $estimatedCost,
                            2
                        ),

                    'estimated_gross_profit' =>
                        $estimatedGrossProfit,

                    'payments_total' =>
                        round(
                            $paymentsTotal,
                            2
                        ),

                    'payments_count' =>
                        $paymentsCount,

                    'other_income' =>
                        round(
                            $otherIncome,
                            2
                        ),

                    'expenses_total' =>
                        round(
                            $expensesTotal,
                            2
                        ),

                    'cash_flow' =>
                        $cashFlow,

                    'receivable_total' =>
                        $receivableTotal,

                    'receivable_count' =>
                        $receivables
                            ->count(),

                    'approval_rate' =>
                        $approvalRate,
                ],

                'paymentMethods' =>
                    $paymentMethods,

                'expensesByCategory' =>
                    $expensesByCategory,

                'topClients' =>
                    $topClients,

                'topProducts' =>
                    $topProducts,

                'daily' =>
                    $daily,

                'quotationSummary' =>
                    $quotationSummary,

                'receivables' =>
                    $receivables
                        ->take(10)
                        ->map(
                            fn ($row) => [
                                'id' =>
                                    $row->id,

                                'sale_number' =>
                                    $row
                                        ->sale_number,

                                'client' =>
                                    $row
                                        ->client_name,

                                'sale_date' =>
                                    $row
                                        ->sale_date,

                                'total' =>
                                    round(
                                        (float)
                                        $row->total,
                                        2
                                    ),

                                'paid' =>
                                    round(
                                        (float)
                                        $row
                                            ->paid_amount,
                                        2
                                    ),

                                'balance' =>
                                    round(
                                        (float)
                                        $row
                                            ->balance,
                                        2
                                    ),
                            ]
                        )
                        ->values(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DETECTAR COLUMNA DE FECHA
    |--------------------------------------------------------------------------
    */

    private function resolveDateColumn(
        string $table,
        array $candidates
    ): string {
        foreach (
            $candidates
            as $column
        ) {
            if (
                Schema::hasColumn(
                    $table,
                    $column
                )
            ) {
                return $column;
            }
        }

        throw new RuntimeException(
            'No se encontró una columna de fecha válida en la tabla "' .
            $table .
            '". Columnas verificadas: ' .
            implode(
                ', ',
                $candidates
            ) .
            '.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | APLICAR PERIODO A DATE O DATETIME
    |--------------------------------------------------------------------------
    */

    private function applyDateRange(
        Builder $query,
        string $column,
        Carbon $start,
        Carbon $end
    ): Builder {
        return $query
            ->whereDate(
                $column,
                '>=',
                $start
                    ->toDateString()
            )
            ->whereDate(
                $column,
                '<=',
                $end
                    ->toDateString()
            );
    }
}