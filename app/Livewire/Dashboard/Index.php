<?php

namespace App\Livewire\Dashboard;

use App\Enums\InvoiceStatus;
use App\Enums\QuotationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalSalesThisMonth = (float) Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('total');

        $totalOutstanding = (float) Invoice::query()
            ->whereNotIn('status', [InvoiceStatus::PAID, InvoiceStatus::VOID, InvoiceStatus::DRAFT])
            ->selectRaw('SUM(total - amount_paid) as total')
            ->value('total') ?? 0;

        $overdueInvoicesCount = Invoice::query()
            ->where('status', InvoiceStatus::OVERDUE)
            ->count();

        $pendingQuotationsCount = Quotation::query()
            ->whereIn('status', [QuotationStatus::DRAFT, QuotationStatus::SENT])
            ->count();

        $lowStockProductsCount = Product::query()
            ->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->count();

        $recentInvoices = Invoice::query()
            ->with('customer')
            ->latest('date')
            ->limit(5)
            ->get();

        $recentPayments = Payment::query()
            ->with('invoice.customer')
            ->latest('date')
            ->limit(5)
            ->get();

        $chartData = $this->monthlySalesChart();

        return view('livewire.dashboard.index', compact(
            'totalSalesThisMonth',
            'totalOutstanding',
            'overdueInvoicesCount',
            'pendingQuotationsCount',
            'lowStockProductsCount',
            'recentInvoices',
            'recentPayments',
            'chartData'
        ));
    }

    protected function monthlySalesChart(): array
    {
        $months = collect(range(11, 0))->map(function ($i) {
            $date = now()->subMonths($i);

            return [
                'label' => $date->format('M Y'),
                'year' => $date->year,
                'month' => $date->month,
            ];
        });

        $driver = DB::connection()->getDriverName();
        $selectRaw = $driver === 'sqlite'
            ? "strftime('%Y', date) as year, strftime('%m', date) as month, SUM(total) as total"
            : "YEAR(date) as year, MONTH(date) as month, SUM(total) as total";

        $sales = Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->where('date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw($selectRaw)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($row) => (int) $row->year.'-'.(int) $row->month);

        return $months->map(function ($month) use ($sales) {
            $key = $month['year'].'-'.$month['month'];

            return [
                'label' => $month['label'],
                'total' => (float) ($sales[$key]?->total ?? 0),
            ];
        })->values()->toArray();
    }
}
