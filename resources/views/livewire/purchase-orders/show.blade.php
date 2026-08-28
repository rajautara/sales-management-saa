<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Purchase Order {{ $purchaseOrder->number }}</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
        <div class="flex flex-col md:flex-row justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Supplier</p>
                <p class="text-lg font-medium text-gray-900">{{ $purchaseOrder->supplier?->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Date</p>
                <p class="text-lg font-medium text-gray-900">{{ $purchaseOrder->date->format('Y-m-d') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="text-lg font-medium text-gray-900">{{ $purchaseOrder->status->label() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-lg font-medium text-gray-900">{{ number_format($purchaseOrder->total, 2) }}</p>
            </div>
        </div>
        @if ($purchaseOrder->notes)
            <div class="mt-4">
                <p class="text-sm text-gray-500">Notes</p>
                <p class="text-sm text-gray-900">{{ $purchaseOrder->notes }}</p>
            </div>
        @endif
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg text-gray-800">Status Actions</h3>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($statuses as $status)
                <button type="button" wire:click="updateStatus('{{ $status->value }}')" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 {{ $purchaseOrder->status === $status ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700' }}">
                    {{ $status->label() }}
                </button>
            @endforeach
            @if (! in_array($purchaseOrder->status, [\App\Enums\PurchaseOrderStatus::RECEIVED, \App\Enums\PurchaseOrderStatus::CANCELLED]))
                <button type="button" wire:click="$set('showReceiveForm', {{ $showReceiveForm ? 'false' : 'true' }})" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ $showReceiveForm ? 'Cancel Receive' : 'Receive Goods' }}
                </button>
            @endif
        </div>
    </div>

    @if ($showReceiveForm)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-6">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Receive Goods</h3>
            <form wire:submit="saveReceive" class="space-y-4">
                <x-table-scroll>
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ordered</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Received</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Receive Now</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $item->description }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ number_format($item->qty, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ number_format($item->received_qty, 2) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <input type="number" step="0.01" wire:model="received.{{ $item->id }}.qty" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-24 text-right">
                                    @error("received.{$item->id}.qty") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </x-table-scroll>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Save Receipt
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <h3 class="font-semibold text-lg text-gray-800 px-6 py-4 border-b border-gray-200">Line Items</h3>
        <x-table-scroll>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($purchaseOrder->items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->qty, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->received_qty, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->tax, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="6" class="px-6 py-3 text-right text-sm font-medium text-gray-700">Subtotal</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($purchaseOrder->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-6 py-3 text-right text-sm font-medium text-gray-700">Tax</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($purchaseOrder->tax, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">{{ number_format($purchaseOrder->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        </x-table-scroll>
    </div>
</div>
