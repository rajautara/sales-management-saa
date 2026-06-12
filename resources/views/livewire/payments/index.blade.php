<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payments</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-col md:flex-row gap-4 items-end justify-between">
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <div>
                <label class="block font-medium text-sm text-gray-700">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Reference, invoice, customer..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-64 mt-1">
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Method</label>
                <select wire:model.live="method" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-48 mt-1">
                    <option value="">All</option>
                    @foreach ($methods as $methodOption)
                        <option value="{{ $methodOption->value }}">{{ $methodOption->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('invoices.show', $payment->invoice) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">{{ $payment->invoice->number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->invoice->customer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->method->label() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($payment->receipt)
                                <a href="{{ route('receipts.show', $payment->receipt) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">{{ $payment->receipt->number }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if ($payment->receipt)
                                <a href="{{ route('receipts.show', $payment->receipt) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">View Receipt</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $payments->links() }}
        </div>
    </div>
</div>
