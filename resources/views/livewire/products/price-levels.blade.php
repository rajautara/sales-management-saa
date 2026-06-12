<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Price Levels</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingId ? 'Edit Price Level' : 'Create Price Level' }}</h3>

        <form wire:submit="savePriceLevel" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-1/3">
                <label class="block font-medium text-sm text-gray-700">Name</label>
                <input type="text" wire:model="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="w-full md:w-1/2">
                <label class="block font-medium text-sm text-gray-700">Description</label>
                <input type="text" wire:model="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                @if ($editingId)
                    <button type="button" wire:click="cancelPriceLevel" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </button>
                @endif
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </div>

    @foreach ($priceLevels as $priceLevel)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $priceLevel->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $priceLevel->description }}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="editPriceLevel({{ $priceLevel->id }})" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</button>
                    <button type="button" wire:click="deletePriceLevel({{ $priceLevel->id }})" wire:confirm="Are you sure?" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200 border rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($priceLevel->productPrices as $productPrice)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $productPrice->product->name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                <input type="number" step="0.01" wire:model="editPrices.{{ $productPrice->id }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-32">
                                @error("editPrices.{$productPrice->id}") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button" wire:click="updateProductPrice({{ $productPrice->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Save</button>
                                <button type="button" wire:click="deleteProductPrice({{ $productPrice->id }})" wire:confirm="Remove this product price?" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-50">
                        <td class="px-4 py-2">
                            <select wire:model="newProductId.{{ $priceLevel->id }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm">
                                <option value="">Select product</option>
                                @foreach ($products as $id => $productName)
                                    <option value="{{ $id }}">{{ $productName }}</option>
                                @endforeach
                            </select>
                            @error("newProductId.{$priceLevel->id}") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" step="0.01" wire:model="newPrice.{{ $priceLevel->id }}" placeholder="Price" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-32 text-sm">
                            @error("newPrice.{$priceLevel->id}") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-4 py-2 text-right">
                            <button type="button" wire:click="addProductPrice({{ $priceLevel->id }})" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Add
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    @if ($priceLevels->isEmpty())
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
            No price levels found.
        </div>
    @endif
</div>
