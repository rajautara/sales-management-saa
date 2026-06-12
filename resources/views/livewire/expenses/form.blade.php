<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $expenseId ? 'Edit Expense' : 'Create Expense' }}</h2>
</x-slot>

<div class="max-w-3xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block font-medium text-sm text-gray-700">Date <span class="text-red-500">*</span></label>
                <input type="date" wire:model="date" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Category <span class="text-red-500">*</span></label>
                <select wire:model="expenseCategoryId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option value="">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('expenseCategoryId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Supplier</label>
                <select wire:model="supplierId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option value="">None</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplierId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Amount <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" wire:model="amount" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block font-medium text-sm text-gray-700">Description <span class="text-red-500">*</span></label>
            <input type="text" wire:model="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block font-medium text-sm text-gray-700">Receipt Attachment</label>
            <input type="file" wire:model="receipt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <div wire:loading wire:target="receipt" class="text-sm text-gray-500 mt-1">Uploading...</div>
            @error('receipt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

            @if ($existingReceipt)
                <p class="mt-2 text-sm text-gray-600">
                    Current receipt:
                    <a href="{{ Storage::url($existingReceipt) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">View</a>
                </p>
            @endif
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('expenses.index') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save
            </button>
        </div>
    </form>
</div>
