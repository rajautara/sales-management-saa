<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Expenses</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 flex flex-col lg:flex-row gap-4 items-stretch lg:items-end justify-between">
        <div class="flex flex-col md:flex-row gap-4 w-full lg:w-auto flex-wrap">
            <div>
                <label class="block font-medium text-sm text-gray-700">Search</label>
                <input type="text" wire:model.live="search" placeholder="Description, supplier..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-64 mt-1">
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Category</label>
                <select wire:model.live="categoryId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-48 mt-1">
                    <option value="">All</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Date From</label>
                <input type="date" wire:model.live="dateFrom" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-40 mt-1">
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Date To</label>
                <input type="date" wire:model.live="dateTo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-40 mt-1">
            </div>
        </div>

        <div class="flex flex-wrap gap-2 w-full lg:w-auto">
            <a href="{{ route('expense-categories.index') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 flex-1 lg:flex-none">
                Categories
            </a>
            <a href="{{ route('expenses.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 flex-1 lg:flex-none">
                Add Expense
            </a>
        </div>
    </div>

    <x-responsive-list class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-slot:cards>
            @forelse ($expenses as $expense)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-900">{{ $expense->description }}</span>
                        <span class="text-sm font-medium text-gray-900 shrink-0">{{ number_format($expense->amount, 2) }}</span>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <div>
                            <dt class="text-gray-500">Date</dt>
                            <dd class="text-gray-900">{{ $expense->date->format('Y-m-d') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Category</dt>
                            <dd class="text-gray-900">{{ $expense->category?->name ?: '-' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Supplier</dt>
                            <dd class="text-gray-900">{{ $expense->supplier?->name ?: '-' }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                        <a href="{{ route('expenses.edit', $expense) }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-900 py-1">Edit</a>
                        @if ($expense->receipt_attachment)
                            <a href="{{ route('expenses.receipt', $expense) }}" target="_blank" class="text-sm font-medium text-slate-600 hover:text-slate-900 py-1">Receipt</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-sm text-gray-500 text-center">No expenses found.</div>
            @endforelse
        </x-slot:cards>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left"><x-sortable-header field="date" :current="$sortField" :direction="$sortDirection">Date</x-sortable-header></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-right"><x-sortable-header field="amount" align="right" :current="$sortField" :direction="$sortDirection">Amount</x-sortable-header></th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($expenses as $expense)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $expense->date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $expense->category?->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $expense->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $expense->supplier?->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($expense->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            @if ($expense->receipt_attachment)
                                <a href="{{ route('expenses.receipt', $expense) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">View</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('expenses.edit', $expense) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">No expenses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot:footer>
            {{ $expenses->links() }}
        </x-slot:footer>
    </x-responsive-list>
</div>
