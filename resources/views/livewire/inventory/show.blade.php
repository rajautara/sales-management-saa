<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory: {{ $product->name }}</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500">Current Stock</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($product->quantity_on_hand, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Low Stock Threshold</p>
                <p class="text-lg font-medium text-gray-900">{{ number_format($product->low_stock_threshold, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">SKU</p>
                <p class="text-lg font-medium text-gray-900">{{ $product->sku }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-lg text-gray-800 mb-4">Manual Stock Adjustment</h3>
        <form wire:submit="adjustStock" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block font-medium text-sm text-gray-700">Type</label>
                <select wire:model="adjustmentType" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('adjustmentType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Quantity</label>
                <input type="number" step="0.01" wire:model="adjustmentQty" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('adjustmentQty') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Notes</label>
                <input type="text" wire:model="adjustmentNotes" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('adjustmentNotes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Adjust Stock
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <h3 class="font-semibold text-lg text-gray-800 px-6 py-4 border-b border-gray-200">Stock Movements</h3>
        <x-table-scroll>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($movements as $movement)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement->type->label() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($movement->qty, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($movement->balance, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($movement->reference_type === 'App\\Models\\PurchaseOrder')
                                PO #{{ $movement->reference?->number }}
                            @elseif ($movement->reference_type === 'App\\Models\\DeliveryOrder')
                                DO #{{ $movement->reference?->number }}
                            @else
                                Manual
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $movement->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No stock movements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </x-table-scroll>

        <div class="px-6 py-4 overflow-x-auto">
            {{ $movements->links() }}
        </div>
    </div>
</div>
