<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suppliers</h2>
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
                <input type="text" wire:model.live="search" placeholder="Name, email, phone..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-64 mt-1">
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Status</label>
                <select wire:model.live="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-40 mt-1">
                    <option value="">All</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <a href="{{ route('suppliers.create') }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full md:w-auto">
            Add Supplier
        </a>
    </div>

    <x-responsive-list class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <x-slot:cards>
            @forelse ($suppliers as $supplier)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-900">{{ $supplier->name }}</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full shrink-0 {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="text-gray-900 break-all">{{ $supplier->email ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="text-gray-900">{{ $supplier->phone ?: '-' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Tax No</dt>
                            <dd class="text-gray-900">{{ $supplier->tax_no ?: '-' }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1">
                        <a href="{{ route('suppliers.edit', $supplier) }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-900 py-1">Edit</a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-sm text-gray-500 text-center">No suppliers found.</div>
            @endforelse
        </x-slot:cards>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supplier->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supplier->phone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supplier->tax_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('suppliers.edit', $supplier) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No suppliers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <x-slot:footer>
            {{ $suppliers->links() }}
        </x-slot:footer>
    </x-responsive-list>
</div>
