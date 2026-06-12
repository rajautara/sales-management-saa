<?php

namespace App\Livewire\Reports;

use App\Enums\InvoiceStatus;
use App\Exports\ReportExport;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $reportType = 'sales';
    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $customerId = null;
    public ?int $productId = null;

    public function mount(): void
    {
        if (! auth()->user()?->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedReportType(): void
    {
        $this->customerId = null;
        $this->productId = null;
    }

    public function export()
    {
        return (new ReportExport($this->reportData(), $this->headings(), $this->reportType))
            ->download($this->reportType.'_report_'.now()->format('Ymd_His').'.xlsx');
    }

    public function render()
    {
        return view('livewire.reports.index', [
            'data' => $this->reportData(),
            'headings' => $this->headings(),
            'customers' => Customer::orderBy('name')->pluck('name', 'id'),
            'products' => Product::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    protected function dateRange(): array
    {
        return [
            $this->dateFrom ?: now()->startOfMonth()->format('Y-m-d'),
            $this->dateTo ?: now()->endOfMonth()->format('Y-m-d'),
        ];
    }

    protected function headings(): array
    {
        return match ($this->reportType) {
            'sales' => ['Date', 'Invoice Number', 'Customer', 'Total', 'Status'],
            'outstanding_invoices' => ['Date', 'Invoice Number', 'Customer', 'Total', 'Paid', 'Due', 'Due Date'],
            'payments' => ['Date', 'Customer', 'Invoice Number', 'Method', 'Reference', 'Amount'],
            'stock' => ['Product', 'SKU', 'Current Stock', 'Low Threshold', 'Status'],
            'expenses' => ['Date', 'Category', 'Description', 'Supplier', 'Amount'],
            'profit' => ['Metric', 'Amount'],
            default => [],
        };
    }

    protected function reportData(): array
    {
        return match ($this->reportType) {
            'sales' => $this->salesData(),
            'outstanding_invoices' => $this->outstandingInvoicesData(),
            'payments' => $this->paymentsData(),
            'stock' => $this->stockData(),
            'expenses' => $this->expensesData(),
            'profit' => $this->profitData(),
            default => [],
        };
    }

    protected function salesData(): array
    {
        [$from, $to] = $this->dateRange();

        $query = Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('date', [$from, $to])
            ->with('customer');

        if ($this->customerId) {
            $query->where('customer_id', $this->customerId);
        }

        if ($this->productId) {
            $query->whereHas('items', fn ($q) => $q->where('product_id', $this->productId));
        }

        return $query->orderBy('date')
            ->get()
            ->map(fn (Invoice $invoice) => [
                'Date' => $invoice->date->format('Y-m-d'),
                'Invoice Number' => $invoice->number,
                'Customer' => $invoice->customer?->name,
                'Total' => (float) $invoice->total,
                'Status' => $invoice->status->label(),
            ])
            ->toArray();
    }

    protected function outstandingInvoicesData(): array
    {
        [$from, $to] = $this->dateRange();

        return Invoice::query()
            ->whereBetween('date', [$from, $to])
            ->whereNotIn('status', [InvoiceStatus::PAID, InvoiceStatus::VOID])
            ->with('customer')
            ->orderBy('date')
            ->get()
            ->map(fn (Invoice $invoice) => [
                'Date' => $invoice->date->format('Y-m-d'),
                'Invoice Number' => $invoice->number,
                'Customer' => $invoice->customer?->name,
                'Total' => (float) $invoice->total,
                'Paid' => (float) $invoice->amount_paid,
                'Due' => $invoice->amountDue(),
                'Due Date' => $invoice->due_date->format('Y-m-d'),
            ])
            ->toArray();
    }

    protected function paymentsData(): array
    {
        [$from, $to] = $this->dateRange();

        return Payment::query()
            ->whereBetween('date', [$from, $to])
            ->with(['invoice.customer'])
            ->orderBy('date')
            ->get()
            ->map(fn (Payment $payment) => [
                'Date' => $payment->date->format('Y-m-d'),
                'Customer' => $payment->invoice?->customer?->name,
                'Invoice Number' => $payment->invoice?->number,
                'Method' => $payment->method->label(),
                'Reference' => $payment->reference_no,
                'Amount' => (float) $payment->amount,
            ])
            ->toArray();
    }

    protected function stockData(): array
    {
        return Product::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'Product' => $product->name,
                'SKU' => $product->sku,
                'Current Stock' => (float) $product->quantity_on_hand,
                'Low Threshold' => (float) $product->low_stock_threshold,
                'Status' => ($product->low_stock_threshold > 0 && $product->quantity_on_hand <= $product->low_stock_threshold) ? 'Low Stock' : 'OK',
            ])
            ->toArray();
    }

    protected function expensesData(): array
    {
        [$from, $to] = $this->dateRange();

        return Expense::query()
            ->whereBetween('date', [$from, $to])
            ->with(['category', 'supplier'])
            ->orderBy('date')
            ->get()
            ->map(fn (Expense $expense) => [
                'Date' => $expense->date->format('Y-m-d'),
                'Category' => $expense->category?->name,
                'Description' => $expense->description,
                'Supplier' => $expense->supplier?->name,
                'Amount' => (float) $expense->amount,
            ])
            ->toArray();
    }

    protected function profitData(): array
    {
        [$from, $to] = $this->dateRange();

        $sales = (float) Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('date', [$from, $to])
            ->sum('total');

        $invoiceIds = Invoice::query()
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('date', [$from, $to])
            ->pluck('id');

        $cost = (float) DB::table('invoice_items')
            ->whereIn('invoice_id', $invoiceIds)
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(invoice_items.qty * products.cost_price) as total_cost')
            ->value('total_cost') ?? 0;

        $expenses = (float) Expense::query()
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $profit = $sales - $cost - $expenses;

        return [
            ['Metric' => 'Sales', 'Amount' => $sales],
            ['Metric' => 'Cost of Goods Sold', 'Amount' => $cost],
            ['Metric' => 'Expenses', 'Amount' => $expenses],
            ['Metric' => 'Profit', 'Amount' => $profit],
        ];
    }
}
