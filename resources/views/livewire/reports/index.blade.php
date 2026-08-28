<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block font-medium text-sm text-gray-700">Report Type</label>
                <select wire:model.live="reportType" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option value="sales">Sales</option>
                    <option value="outstanding_invoices">Outstanding Invoices</option>
                    <option value="payments">Payments</option>
                    <option value="stock">Stock</option>
                    <option value="expenses">Expenses</option>
                    <option value="profit">Profit</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Date From</label>
                <input type="date" wire:model.live="dateFrom" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Date To</label>
                <input type="date" wire:model.live="dateTo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
            </div>

            @if (in_array($reportType, ['sales']))
                <div>
                    <label class="block font-medium text-sm text-gray-700">Customer</label>
                    <select wire:model.live="customerId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                        <option value="">All</option>
                        @foreach ($customers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Product</label>
                    <select wire:model.live="productId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                        <option value="">All</option>
                        @foreach ($products as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <button type="button" wire:click="export" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Export Excel
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-table-scroll>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach ($headings as $heading)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($data as $row)
                        <tr>
                            @foreach ($headings as $heading)
                                @php
                                    $value = $row[$heading] ?? '-';
                                    $isNumeric = is_numeric($value);
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $isNumeric ? 'text-gray-900 text-right' : 'text-gray-500' }}">
                                    @if ($isNumeric)
                                        {{ number_format($value, 2) }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headings) }}" class="px-6 py-4 text-sm text-gray-500 text-center">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-table-scroll>
    </div>
</div>
