<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory</h2>
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
                <input type="text" wire:model.live="search" placeholder="Name, SKU..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-64 mt-1">
            </div>

            <div class="flex items-center h-full pt-6">
                <input id="lowStockOnly" type="checkbox" wire:model.live="lowStockOnly" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="lowStockOnly" class="ml-2 block text-sm text-gray-700">Low stock only</label>
            </div>
        </div>
    </div>

    <x-responsive-list class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-slot:cards>
            @forelse ($products as $product)
                @php
                    $isLow = $product->low_stock_threshold > 0 && $product->quantity_on_hand <= $product->low_stock_threshold;
                @endphp
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-900">{{ $product->name }}</span>
                        @if ($isLow)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 shrink-0">Low Stock</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shrink-0">OK</span>
                        @endif
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <div>
                            <dt class="text-gray-500">SKU</dt>
                            <dd class="text-gray-900">{{ $product->sku ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Stock</dt>
                            <dd class="text-gray-900 font-medium">{{ number_format($product->quantity_on_hand, 2) }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Low Threshold</dt>
                            <dd class="text-gray-900">{{ number_format($product->low_stock_threshold, 2) }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                        <a href="{{ route('inventory.show', $product) }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-900 py-1">View</a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-sm text-gray-500 text-center">No products found.</div>
            @endforelse
        </x-slot:cards>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Low Threshold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($products as $product)
                    @php
                        $isLow = $product->low_stock_threshold > 0 && $product->quantity_on_hand <= $product->low_stock_threshold;
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($product->quantity_on_hand, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($product->low_stock_threshold, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if ($isLow)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Low Stock
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    OK
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('inventory.show', $product) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot:footer>
            {{ $products->links() }}
        </x-slot:footer>
    </x-responsive-list>
</div>
