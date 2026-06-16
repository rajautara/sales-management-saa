<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $customerId ? 'Edit Customer' : 'Create Customer' }}</h2>
</x-slot>

<div class="max-w-3xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block font-medium text-sm text-gray-700">Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Email</label>
                <input type="email" wire:model="email" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Phone</label>
                <input type="text" wire:model="phone" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Tax No</label>
                <input type="text" wire:model="taxNo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('taxNo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Credit Limit</label>
                <input type="number" step="0.01" wire:model="creditLimit" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('creditLimit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Price Level</label>
                <select wire:model="priceLevelId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    <option value="">Default</option>
                    @foreach ($priceLevels as $id => $levelName)
                        <option value="{{ $id }}">{{ $levelName }}</option>
                    @endforeach
                </select>
                @error('priceLevelId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block font-medium text-sm text-gray-700">Billing Address</label>
            <textarea wire:model="billingAddress" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"></textarea>
            @error('billingAddress') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block font-medium text-sm text-gray-700">Shipping Address</label>
            <textarea wire:model="shippingAddress" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"></textarea>
            @error('shippingAddress') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">e-Invoice (MyInvois / LHDN)</h3>
            <p class="text-xs text-gray-500 mb-4">Required to submit e-Invoices for this customer to LHDN.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-sm text-gray-700">TIN (Tax Identification No)</label>
                    <input type="text" wire:model="tin" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('tin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Registration Type</label>
                    <select wire:model="registrationType" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                        <option value="">—</option>
                        <option value="BRN">BRN (Business Registration)</option>
                        <option value="NRIC">NRIC</option>
                        <option value="PASSPORT">Passport</option>
                        <option value="ARMY">Army</option>
                    </select>
                    @error('registrationType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Registration No (BRN/NRIC/Passport)</label>
                    <input type="text" wire:model="registrationNo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('registrationNo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">SST Registration No</label>
                    <input type="text" wire:model="sstRegistrationNo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('sstRegistrationNo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">City</label>
                    <input type="text" wire:model="addressCity" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('addressCity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Postcode</label>
                    <input type="text" wire:model="addressPostcode" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('addressPostcode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">State Code</label>
                    <input type="text" wire:model="addressStateCode" maxlength="2" placeholder="e.g. 10 = Selangor" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('addressStateCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Country Code</label>
                    <input type="text" wire:model="addressCountryCode" maxlength="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('addressCountryCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center">
            <input id="isActive" type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="isActive" class="ml-2 block text-sm text-gray-700">Active</label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('customers.index') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save
            </button>
        </div>
    </form>
</div>
