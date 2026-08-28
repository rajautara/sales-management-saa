<x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <h2 class="font-bold text-2xl text-slate-900 tracking-tight">Dashboard Overview</h2>
        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60 shadow-sm">{{ now()->format('l, d M Y') }}</span>
    </div>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6" x-data="{ chartData: @js($chartData) }" x-init="
    const ctx = $refs.salesChart.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(d => d.label),
            datasets: [{
                label: 'Monthly Sales',
                data: chartData.map(d => d.total),
                backgroundColor: 'rgba(99, 102, 241, 0.85)',
                hoverBackgroundColor: 'rgba(79, 70, 229, 0.95)',
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 16
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' } }
                },
                y: {
                    border: { dash: [4, 4] },
                    grid: { color: 'rgba(148, 163, 184, 0.12)' },
                    ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' } }
                }
            }
        }
    });
">
    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- Sales Card -->
        <div class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-premium hover-translate-up hover:shadow-premium-hover border-l-4 border-l-emerald-500 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sales This Month</p>
                <p class="text-xl font-bold text-slate-900">RM {{ number_format($totalSalesThisMonth, 2) }}</p>
            </div>
            <div class="bg-emerald-50 text-emerald-600 p-3 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>

        <!-- Total Outstanding Card -->
        <div class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-premium hover-translate-up hover:shadow-premium-hover border-l-4 border-l-amber-500 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Outstanding</p>
                <p class="text-xl font-bold text-slate-900">RM {{ number_format($totalOutstanding, 2) }}</p>
            </div>
            <div class="bg-amber-50 text-amber-600 p-3 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Overdue Invoices Card -->
        <div class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-premium hover-translate-up hover:shadow-premium-hover border-l-4 border-l-rose-500 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Overdue Invoices</p>
                <p class="text-xl font-bold text-slate-900">{{ $overdueInvoicesCount }}</p>
            </div>
            <div class="bg-rose-50 text-rose-600 p-3 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Pending Quotations Card -->
        <div class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-premium hover-translate-up hover:shadow-premium-hover border-l-4 border-l-indigo-500 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Quotations</p>
                <p class="text-xl font-bold text-slate-900">{{ $pendingQuotationsCount }}</p>
            </div>
            <div class="bg-indigo-50 text-indigo-600 p-3 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>

        <!-- Low Stock Products Card -->
        <div class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-premium hover-translate-up hover:shadow-premium-hover border-l-4 border-l-orange-500 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Low Stock Products</p>
                <p class="text-xl font-bold text-slate-900">{{ $lowStockProductsCount }}</p>
            </div>
            <div class="bg-orange-50 text-orange-600 p-3 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Analytics Chart -->
    <div class="bg-white border border-slate-100/80 rounded-2xl p-6 shadow-premium">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
            <h3 class="font-bold text-base text-slate-900">Sales Trend (Last 12 Months)</h3>
            <span class="text-xs text-indigo-600 font-semibold bg-indigo-50/50 border border-indigo-100/60 px-2.5 py-1 rounded-full">Revenue (MYR)</span>
        </div>
        <div class="relative h-56 sm:h-80 w-full">
            <canvas x-ref="salesChart"></canvas>
        </div>
    </div>

    <!-- Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-premium overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                <h3 class="font-bold text-base text-slate-900">Recent Invoices</h3>
                <a href="{{ route('invoices.index') }}" class="text-xs text-indigo-600 font-semibold hover:text-indigo-800 transition-colors">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Number</th>
                            <th class="px-6 py-3.5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer</th>
                            <th class="px-6 py-3.5 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total</th>
                            <th class="px-6 py-3.5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($recentInvoices as $invoice)
                            <tr class="hover:bg-slate-50/20 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-900">{{ $invoice->number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">{{ $invoice->customer?->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-900 font-bold text-right">RM {{ number_format($invoice->total, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span class="px-2.5 py-1 inline-flex text-[10px] leading-4 font-bold rounded-full border
                                        @if ($invoice->status === \App\Enums\InvoiceStatus::PAID) bg-emerald-50 text-emerald-700 border-emerald-100/80
                                        @elseif ($invoice->status === \App\Enums\InvoiceStatus::OVERDUE) bg-rose-50 text-rose-700 border-rose-100/80
                                        @elseif ($invoice->status === \App\Enums\InvoiceStatus::PARTIAL) bg-amber-50 text-amber-700 border-amber-100/80
                                        @else bg-slate-50 text-slate-600 border-slate-200/60
                                        @endif">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-xs text-slate-400 text-center font-medium">No recent invoices.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white border border-slate-100/80 rounded-2xl shadow-premium overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                <h3 class="font-bold text-base text-slate-900">Recent Payments</h3>
                <a href="{{ route('payments.index') }}" class="text-xs text-indigo-600 font-semibold hover:text-indigo-800 transition-colors">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date</th>
                            <th class="px-6 py-3.5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer</th>
                            <th class="px-6 py-3.5 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Method</th>
                            <th class="px-6 py-3.5 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($recentPayments as $payment)
                            <tr class="hover:bg-slate-50/20 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-slate-600">{{ $payment->date->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 font-medium">{{ $payment->invoice?->customer?->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span class="text-slate-700 font-semibold bg-slate-100 border border-slate-200/50 px-2 py-0.5 rounded text-[10px]">
                                        {{ $payment->method->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-900 font-bold text-right">RM {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-xs text-slate-400 text-center font-medium">No recent payments.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
