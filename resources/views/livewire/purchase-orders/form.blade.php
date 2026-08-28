<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $purchaseOrderId ? 'Edit Purchase Order' : 'Create Purchase Order' }}</h2>
</x-slot>

<div class="max-w-7xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block font-medium text-sm text-gray-700">Supplier <span class="text-red-500">*</span></label>
                <select wire:model="supplierId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option value="">Select Supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplierId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Date <span class="text-red-500">*</span></label>
                <input type="date" wire:model="date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Notes</label>
                <input type="text" wire:model="notes" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <div class="flex justify-between items-center mb-2">
                <h3 class="font-medium text-gray-700">Line Items</h3>
                <button type="button" wire:click="addItem" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Add Item
                </button>
            </div>

            @error('items') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Tax %</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($items as $index => $item)
                            <tr>
                                <td class="px-4 py-2">
                                    <select wire:model.live="items.{{ $index }}.product_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-48">
                                        <option value="">-- Select --</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("items.{$index}.product_id") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" wire:model="items.{{ $index }}.description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                    @error("items.{$index}.description") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" wire:model.live="items.{{ $index }}.qty" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-24 text-right">
                                    @error("items.{$index}.qty") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" wire:model.live="items.{{ $index }}.unit_price" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-28 text-right">
                                    @error("items.{$index}.unit_price") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" wire:model.live="items.{{ $index }}.tax_rate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-20 text-right">
                                    @error("items.{$index}.tax_rate") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700">
                                    {{ number_format($this->rowTotal($index), 2) }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        @php $totals = $this->totals(); @endphp
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right font-medium text-gray-700">Subtotal</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($totals['subtotal'], 2) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right font-medium text-gray-700">Tax</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($totals['tax'], 2) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right font-bold text-gray-900">Total</td>
                            <td class="px-4 py-2 text-right font-bold text-gray-900">{{ number_format($totals['total'], 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save
            </button>
        </div>
    </form>
</div>
