<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Receipts</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-col md:flex-row gap-4 items-stretch md:items-end justify-between">
        <div>
            <label class="block font-medium text-sm text-gray-700">Search</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Receipt or invoice number..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-64 mt-1">
        </div>
    </div>

    <x-responsive-list class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-slot:cards>
            @forelse ($receipts as $receipt)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-900">{{ $receipt->number }}</span>
                        <span class="text-sm font-medium text-gray-900 shrink-0">{{ number_format($receipt->payment->amount, 2) }}</span>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <div>
                            <dt class="text-gray-500">Date</dt>
                            <dd class="text-gray-900">{{ $receipt->date->format('Y-m-d') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Invoice</dt>
                            <dd>
                                <a href="{{ route('invoices.show', $receipt->payment->invoice) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">{{ $receipt->payment->invoice->number }}</a>
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Customer</dt>
                            <dd class="text-gray-900">{{ $receipt->payment->invoice->customer->name }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                        <a href="{{ route('receipts.show', $receipt) }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-900 py-1">View</a>
                        <a href="{{ route('receipts.pdf', $receipt) }}" target="_blank" class="text-sm font-medium text-slate-600 hover:text-slate-900 py-1">PDF</a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-sm text-gray-500 text-center">No receipts found.</div>
            @endforelse
        </x-slot:cards>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left"><x-sortable-header field="number" :current="$sortField" :direction="$sortDirection">Receipt Number</x-sortable-header></th>
                    <th class="px-6 py-3 text-left"><x-sortable-header field="date" :current="$sortField" :direction="$sortDirection">Date</x-sortable-header></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($receipts as $receipt)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $receipt->number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receipt->date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="{{ route('invoices.show', $receipt->payment->invoice) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">{{ $receipt->payment->invoice->number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receipt->payment->invoice->customer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($receipt->payment->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('receipts.show', $receipt) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">View</a>
                            <a href="{{ route('receipts.pdf', $receipt) }}" target="_blank" class="text-slate-600 hover:text-slate-900 ml-2">PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No receipts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot:footer>
            {{ $receipts->links() }}
        </x-slot:footer>
    </x-responsive-list>
</div>
