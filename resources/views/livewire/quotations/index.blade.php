<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quotations</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-col md:flex-row gap-4 items-stretch md:items-end justify-between">
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <div>
                <label class="block font-medium text-sm text-gray-700">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Number, customer..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-64 mt-1">
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Status</label>
                <select wire:model.live="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-48 mt-1">
                    <option value="">All</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <a href="{{ route('quotations.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full md:w-auto">
            Create Quotation
        </a>
    </div>

    <x-responsive-list class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-slot:cards>
            @forelse ($quotations as $quotation)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">{{ $quotation->number }}</a>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 shrink-0">
                            {{ $quotation->status->label() }}
                        </span>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <div class="col-span-2">
                            <dt class="text-gray-500">Customer</dt>
                            <dd class="text-gray-900">{{ $quotation->customer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Date</dt>
                            <dd class="text-gray-900">{{ $quotation->date->format('Y-m-d') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Valid Until</dt>
                            <dd class="text-gray-900">{{ $quotation->valid_until?->format('Y-m-d') }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Total</dt>
                            <dd class="text-gray-900 font-medium">{{ number_format($quotation->total, 2) }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                        <a href="{{ route('quotations.edit', $quotation) }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-900 py-1">Edit</a>
                        <a href="{{ route('quotations.pdf', $quotation) }}" target="_blank" class="text-sm font-medium text-slate-600 hover:text-slate-900 py-1">PDF</a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-sm text-gray-500 text-center">No quotations found.</div>
            @endforelse
        </x-slot:cards>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left"><x-sortable-header field="number" :current="$sortField" :direction="$sortDirection">Number</x-sortable-header></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left"><x-sortable-header field="date" :current="$sortField" :direction="$sortDirection">Date</x-sortable-header></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Until</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($quotations as $quotation)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">{{ $quotation->number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->customer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->valid_until?->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ $quotation->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($quotation->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('quotations.edit', $quotation) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <a href="{{ route('quotations.pdf', $quotation) }}" target="_blank" class="text-slate-600 hover:text-slate-900 ml-2">PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">No quotations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot:footer>
            {{ $quotations->links() }}
        </x-slot:footer>
    </x-responsive-list>
</div>
