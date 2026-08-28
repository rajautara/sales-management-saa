<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Expense Categories</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="font-semibold text-lg text-gray-800 mb-4">{{ $categoryId ? 'Edit Category' : 'Add Category' }}</h3>
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block font-medium text-sm text-gray-700">Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Description</label>
                <input type="text" wire:model="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($categoryId)
                    <button type="button" wire:click="resetForm" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </button>
                @endif
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ $categoryId ? 'Update' : 'Save' }}
                </button>
            </div>
        </form>
    </div>

    <x-responsive-list class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-slot:cards>
            @forelse ($categories as $category)
                <div class="p-4 space-y-2">
                    <div class="text-sm font-semibold text-gray-900">{{ $category->name }}</div>
                    <p class="text-sm text-gray-500">{{ $category->description ?: '-' }}</p>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                        <button type="button" wire:click="edit({{ $category->id }})" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 py-1">Edit</button>
                        <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?" class="text-sm font-medium text-red-600 hover:text-red-900 py-1">Delete</button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-sm text-gray-500 text-center">No categories found.</div>
            @endforelse
        </x-slot:cards>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $category->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button type="button" wire:click="edit({{ $category->id }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category?" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-sm text-gray-500 text-center">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot:footer>
            {{ $categories->links() }}
        </x-slot:footer>
    </x-responsive-list>
</div>
