<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $deliveryOrderId ? 'Edit Delivery Order' : 'Create Delivery Order' }}</h2>
</x-slot>

<div class="max-w-7xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    @if (! $salesOrderId && ! $deliveryOrderId)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
            Delivery orders must be created from a sales order.
        </div>
    @else
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Sales Order</label>
                    <div class="mt-1 text-sm text-gray-900">
                        @if ($deliveryOrderId)
                            <a href="{{ route('sales-orders.show', $salesOrderId) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">{{ optional(\App\Models\SalesOrder::find($salesOrderId))->number ?? '-' }}</a>
                        @else
                            {{ optional(\App\Models\SalesOrder::find($salesOrderId))->number ?? '-' }}
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Date <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Notes</label>
                <textarea wire:model="notes" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"></textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <h3 class="font-medium text-gray-700 mb-2">Items to Deliver</h3>

                @error('items') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ordered Qty</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Delivered Qty</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">This Delivery</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($items as $index => $item)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $item['description'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($item['max_qty'] + ($deliveryOrderId ? (float) $item['qty'] : optional(\App\Models\SalesOrderItem::find($item['sales_order_item_id']))->delivered_qty ?? 0), 2) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format(optional(\App\Models\SalesOrderItem::find($item['sales_order_item_id']))->delivered_qty ?? 0, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($item['max_qty'], 2) }}</td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" wire:model="items.{{ $index }}.qty" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-28 text-right">
                                    @error("items.{$index}.qty") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('delivery-orders.index') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Save
                </button>
            </div>
        </form>
    @endif
</div>
