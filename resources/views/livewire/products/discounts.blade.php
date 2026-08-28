<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Discounts</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingId ? 'Edit Discount' : 'Create Discount' }}</h3>

        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block font-medium text-sm text-gray-700">Name</label>
                <input type="text" wire:model="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Type</label>
                <select wire:model="type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option value="percent">Percent</option>
                    <option value="fixed">Fixed</option>
                </select>
                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Value</label>
                <input type="number" step="0.01" wire:model="value" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Applies To</label>
                <select wire:model="appliesTo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option value="order">Order</option>
                    <option value="product">Product</option>
                </select>
                @error('appliesTo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Product</label>
                <select wire:model="productId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1" {{ $appliesTo !== 'product' ? 'disabled' : '' }}>
                    <option value="">All products</option>
                    @foreach ($products as $id => $productName)
                        <option value="{{ $id }}">{{ $productName }}</option>
                    @endforeach
                </select>
                @error('productId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Start Date</label>
                <input type="date" wire:model="startDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('startDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">End Date</label>
                <input type="date" wire:model="endDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('endDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center">
                <input id="isActive" type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="isActive" class="ml-2 block text-sm text-gray-700">Active</label>
            </div>

            <div class="md:col-span-3 flex flex-wrap gap-2">
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </button>
                @endif
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </div>

    <x-responsive-list class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-slot:cards>
            @forelse ($discounts as $discount)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-900">{{ $discount->name }}</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full shrink-0 {{ $discount->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $discount->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <div>
                            <dt class="text-gray-500">Type</dt>
                            <dd class="text-gray-900 capitalize">{{ $discount->type->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Value</dt>
                            <dd class="text-gray-900 font-medium">{{ number_format($discount->value, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Applies To</dt>
                            <dd class="text-gray-900 capitalize">{{ $discount->applies_to }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Product</dt>
                            <dd class="text-gray-900">{{ $discount->product?->name ?? '-' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Period</dt>
                            <dd class="text-gray-900">{{ $discount->start_date?->format('d/m/Y') ?? '-' }} - {{ $discount->end_date?->format('d/m/Y') ?? '-' }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                        <button type="button" wire:click="edit({{ $discount->id }})" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 py-1">Edit</button>
                        <button type="button" wire:click="delete({{ $discount->id }})" wire:confirm="Are you sure?" class="text-sm font-medium text-red-600 hover:text-red-900 py-1">Delete</button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-sm text-gray-500 text-center">No discounts found.</div>
            @endforelse
        </x-slot:cards>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applies To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($discounts as $discount)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $discount->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $discount->type->label() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($discount->value, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">{{ $discount->applies_to }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $discount->product?->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $discount->start_date?->format('d/m/Y') ?? '-' }} - {{ $discount->end_date?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $discount->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $discount->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button type="button" wire:click="edit({{ $discount->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button type="button" wire:click="delete({{ $discount->id }})" wire:confirm="Are you sure?" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">No discounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-responsive-list>
</div>
