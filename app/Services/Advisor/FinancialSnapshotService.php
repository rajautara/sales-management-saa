<?php

namespace App\Services\Advisor;

use App\Enums\InvoiceStatus;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Builds an aggregated, anonymised financial snapshot for the current company.
 *
 * Company scoping is automatic via the BelongsToCompany global scope. The
 * snapshot contains only aggregate figures — never customer names or other PII —
 * so it is safe to send to an external AI endpoint.
 */
class FinancialSnapshotService
{
    /**
     * @return array<string, mixed>
     */
    public function build(string $period = 'this_month'): array
    {
        [$from, $to] = $this->range($period);
        [$prevFrom, $prevTo] = $this->previousRange($period);

        $current = $this->periodMetrics($from, $to);
        $previous = $this->periodMetrics($prevFrom, $prevTo);

        return [
            'currency' => auth()->user()?->company?->currency ?? 'MYR',
            'period' => [
                'label' => $this->label($period),
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'profit_and_loss' => array_merge($current, [
                'sales_change_pct' => $this->pctChange($current['sales'], $previous['sales']),
                'net_profit_change_pct' => $this->pctChange($current['net_profit'], $previous['net_profit']),
                'previous_sales' => $previous['sales'],
                'previous_net_profit' => $previous['net_profit'],
            ]),
            'receivables' => $this->receivables(),
            'sales_trend_12m' => $this->salesTrend(),
            'top_products' => $this->topProducts($from, $to),
            'expense_breakdown' => $this->expenseBreakdown($from, $to),
            'inventory_alerts' => $this->inventoryAlerts(),
        ];
    }

    /**
     * @return array{sales: float, cogs: float, expenses: float, gross_profit: float, net_profit: float, margin_pct: float}
     */
    protected function periodMetrics(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $paidInvoiceIds = Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('id');

        $sales = (float) Invoice::query()
            ->whereIn('id', $paidInvoiceIds)
            ->sum('total');

        $cogs = (float) DB::table('invoice_items')
            ->whereIn('invoice_id', $paidInvoiceIds)
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(invoice_items.qty * products.cost_price) as total_cost')
            ->value('total_cost') ?? 0.0;

        $expenses = (float) Expense::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $grossProfit = $sales - $cogs;
        $netProfit = $grossProfit - $expenses;
        $margin = $sales > 0 ? round($netProfit / $sales * 100, 1) : 0.0;

        return [
            'sales' => round($sales, 2),
            'cogs' => round((float) $cogs, 2),
            'expenses' => round($expenses, 2),
            'gross_profit' => round($grossProfit, 2),
            'net_profit' => round($netProfit, 2),
            'margin_pct' => $margin,
        ];
    }

    /**
     * Outstanding receivables with anonymised aging — no customer names.
     *
     * @return array<string, mixed>
     */
    protected function receivables(): array
    {
        $invoices = Invoice::query()
            ->whereNotIn('status', [InvoiceStatus::PAID, InvoiceStatus::VOID, InvoiceStatus::DRAFT])
            ->get(['id', 'due_date', 'total', 'amount_paid']);

        $today = CarbonImmutable::now()->startOfDay();
        $buckets = [
            'not_due' => ['count' => 0, 'amount' => 0.0],
            'd1_30' => ['count' => 0, 'amount' => 0.0],
            'd31_60' => ['count' => 0, 'amount' => 0.0],
            'd61_90' => ['count' => 0, 'amount' => 0.0],
            'over_90' => ['count' => 0, 'amount' => 0.0],
        ];

        $totalOutstanding = 0.0;
        $overdueAmount = 0.0;
        $overdueCount = 0;

        foreach ($invoices as $invoice) {
            $due = max((float) $invoice->total - (float) $invoice->amount_paid, 0);
            if ($due <= 0) {
                continue;
            }
            $totalOutstanding += $due;

            $dueDate = CarbonImmutable::parse($invoice->due_date)->startOfDay();
            $daysPastDue = $dueDate->lessThan($today) ? $dueDate->diffInDays($today) : 0;

            $key = match (true) {
                $dueDate->greaterThanOrEqualTo($today) => 'not_due',
                $daysPastDue <= 30 => 'd1_30',
                $daysPastDue <= 60 => 'd31_60',
                $daysPastDue <= 90 => 'd61_90',
                default => 'over_90',
            };

            $buckets[$key]['count']++;
            $buckets[$key]['amount'] = round($buckets[$key]['amount'] + $due, 2);

            if ($key !== 'not_due') {
                $overdueAmount += $due;
                $overdueCount++;
            }
        }

        return [
            'total_outstanding' => round($totalOutstanding, 2),
            'overdue_amount' => round($overdueAmount, 2),
            'overdue_invoice_count' => $overdueCount,
            'aging' => $buckets,
        ];
    }

    /**
     * @return array<int, array{label: string, total: float}>
     */
    protected function salesTrend(): array
    {
        $months = collect(range(11, 0))->map(function ($i) {
            $date = CarbonImmutable::now()->subMonths($i);

            return ['label' => $date->format('M Y'), 'year' => $date->year, 'month' => $date->month];
        });

        $driver = DB::connection()->getDriverName();
        $selectRaw = $driver === 'sqlite'
            ? "strftime('%Y', date) as year, strftime('%m', date) as month, SUM(total) as total"
            : 'YEAR(date) as year, MONTH(date) as month, SUM(total) as total';

        $sales = Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->where('date', '>=', CarbonImmutable::now()->subMonths(11)->startOfMonth()->toDateString())
            ->selectRaw($selectRaw)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($row) => (int) $row->year.'-'.(int) $row->month);

        return $months->map(fn ($month) => [
            'label' => $month['label'],
            'total' => round((float) ($sales[$month['year'].'-'.$month['month']]?->total ?? 0), 2),
        ])->values()->all();
    }

    /**
     * Top products by revenue in the period (product names are not PII).
     *
     * @return array<int, array{product: string, revenue: float, qty: float}>
     */
    protected function topProducts(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $paidInvoiceIds = Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('id');

        return DB::table('invoice_items')
            ->whereIn('invoice_id', $paidInvoiceIds)
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as product, SUM(invoice_items.total) as revenue, SUM(invoice_items.qty) as qty')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'product' => (string) $row->product,
                'revenue' => round((float) $row->revenue, 2),
                'qty' => round((float) $row->qty, 2),
            ])
            ->all();
    }

    /**
     * @return array<int, array{category: string, amount: float}>
     */
    protected function expenseBreakdown(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Expense::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->with('category')
            ->get()
            ->groupBy(fn (Expense $expense) => $expense->category?->name ?? 'Uncategorised')
            ->map(fn ($group, $name) => [
                'category' => (string) $name,
                'amount' => round((float) $group->sum('amount'), 2),
            ])
            ->sortByDesc('amount')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{product: string, on_hand: float, threshold: float}>
     */
    protected function inventoryAlerts(): array
    {
        return Product::query()
            ->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->orderBy('name')
            ->get(['name', 'quantity_on_hand', 'low_stock_threshold'])
            ->map(fn (Product $product) => [
                'product' => $product->name,
                'on_hand' => round((float) $product->quantity_on_hand, 2),
                'threshold' => round((float) $product->low_stock_threshold, 2),
            ])
            ->all();
    }

    protected function pctChange(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return null;
        }

        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function range(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'last_month' => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
            'this_quarter' => [$now->startOfQuarter(), $now->endOfQuarter()],
            'this_year' => [$now->startOfYear(), $now->endOfYear()],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function previousRange(string $period): array
    {
        $now = CarbonImmutable::now();

        return match ($period) {
            'last_month' => [$now->subMonths(2)->startOfMonth(), $now->subMonths(2)->endOfMonth()],
            'this_quarter' => [$now->subQuarter()->startOfQuarter(), $now->subQuarter()->endOfQuarter()],
            'this_year' => [$now->subYear()->startOfYear(), $now->subYear()->endOfYear()],
            default => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
        };
    }

    protected function label(string $period): string
    {
        return match ($period) {
            'last_month' => 'Last Month',
            'this_quarter' => 'This Quarter',
            'this_year' => 'This Year',
            default => 'This Month',
        };
    }
}
