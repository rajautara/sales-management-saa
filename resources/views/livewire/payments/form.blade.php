<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Payment</h2>
</x-slot>

<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Invoice:</span>
                <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-900 ml-1">{{ $invoice->number }}</a>
            </div>
            <div>
                <span class="text-gray-500">Customer:</span>
                <span class="font-medium text-gray-900 ml-1">{{ $invoice->customer->name }}</span>
            </div>
            <div>
                <span class="text-gray-500">Amount Due:</span>
                <span class="font-medium text-gray-900 ml-1">{{ number_format($amountDue, 2) }}</span>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Date <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Amount <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" wire:model="amount" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Method <span class="text-red-500">*</span></label>
                    <select wire:model="method" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                        @foreach ($methods as $methodOption)
                            <option value="{{ $methodOption->value }}">{{ $methodOption->label() }}</option>
                        @endforeach
                    </select>
                    @error('method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Reference No</label>
                    <input type="text" wire:model="referenceNo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('referenceNo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Notes</label>
                <textarea wire:model="notes" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"></textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>
