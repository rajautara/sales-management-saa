<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex space-x-6 border-b border-gray-200 overflow-x-auto">
        <a href="{{ route('settings.index') }}" wire:navigate class="border-b-2 border-indigo-600 pb-3 px-1 text-sm font-semibold text-indigo-600 transition-colors">
            General Settings
        </a>
        <a href="{{ route('settings.users') }}" wire:navigate class="border-b-2 border-transparent pb-3 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors">
            User Management
        </a>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Company Profile</h3>

            <div>
                <label class="block font-medium text-sm text-gray-700 mb-2">Company Logo</label>
                <div class="flex items-center space-x-4">
                    @if ($existingLogoPath)
                        <div class="relative group w-20 h-20 border border-gray-200 rounded-lg overflow-hidden shadow-sm bg-gray-55 flex items-center justify-center">
                            <img src="{{ asset('storage/' . $existingLogoPath) }}" class="max-w-full max-h-full object-contain" alt="Current Company Logo">
                        </div>
                        <button type="button" wire:click="removeLogo" wire:confirm="Are you sure you want to remove the logo?" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150">
                            Remove
                        </button>
                    @elseif ($logo)
                        <div class="w-20 h-20 border border-gray-200 rounded-lg overflow-hidden shadow-sm bg-gray-55 flex items-center justify-center">
                            <img src="{{ $logo->temporaryUrl() }}" class="max-w-full max-h-full object-contain" alt="Temporary Upload Preview">
                        </div>
                        <span class="text-xs text-green-600">Logo preview ready</span>
                    @else
                        <div class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 bg-gray-50">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                    
                    <div class="flex-1">
                        <input type="file" wire:model="logo" id="logo" class="sr-only">
                        <label for="logo" class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                            Choose Image...
                        </label>
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, or SVG up to 1MB.</p>
                        @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Company Name</label>
                <input type="text" wire:model="companyName" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('companyName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Registration No</label>
                <input type="text" wire:model="companyRegistrationNo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                @error('companyRegistrationNo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Address</label>
                <textarea wire:model="companyAddress" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"></textarea>
                @error('companyAddress') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Phone</label>
                    <input type="text" wire:model="companyPhone" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyPhone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Email</label>
                    <input type="email" wire:model="companyEmail" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyEmail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Currency</label>
                <input type="text" wire:model="companyCurrency" maxlength="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-32 mt-1">
                @error('companyCurrency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Default Settings</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Tax Default (%)</label>
                    <input type="number" step="0.01" wire:model="taxDefault" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('taxDefault') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Quotation Prefix</label>
                    <input type="text" wire:model="quotationPrefix" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('quotationPrefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Sales Order Prefix</label>
                    <input type="text" wire:model="salesOrderPrefix" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('salesOrderPrefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Delivery Order Prefix</label>
                    <input type="text" wire:model="deliveryOrderPrefix" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('deliveryOrderPrefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Invoice Prefix</label>
                    <input type="text" wire:model="invoicePrefix" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('invoicePrefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Receipt Prefix</label>
                    <input type="text" wire:model="receiptPrefix" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('receiptPrefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Purchase Order Prefix</label>
                    <input type="text" wire:model="purchaseOrderPrefix" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('purchaseOrderPrefix') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">Invoice Terms</label>
                <textarea wire:model="invoiceTerms" rows="4" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"></textarea>
                @error('invoiceTerms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-base font-medium text-gray-900">Bank / Payment Details</h3>
                <p class="mt-1 text-sm text-gray-500">Shown on invoices so customers know where to make payment.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Bank Name</label>
                        <input type="text" wire:model="bankName" placeholder="e.g. Maybank" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                        @error('bankName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700">Account Name</label>
                        <input type="text" wire:model="bankAccountName" placeholder="Account holder name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                        @error('bankAccountName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700">Account Number</label>
                        <input type="text" wire:model="bankAccountNo" placeholder="e.g. 5123 4567 8901" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                        @error('bankAccountNo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">e-Invoice (MyInvois / LHDN)</h3>
                <p class="mt-1 text-sm text-gray-500">Required to submit invoices to LHDN MyInvois. The TIN, MSIC code and structured address are mandatory for validation.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">TIN (Tax Identification No)</label>
                    <input type="text" wire:model="companyTin" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyTin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">SST Registration No</label>
                    <input type="text" wire:model="companySstRegistrationNo" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companySstRegistrationNo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">MSIC Code</label>
                    <input type="text" wire:model="companyMsicCode" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyMsicCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Business Activity Description</label>
                    <input type="text" wire:model="companyBusinessActivityDesc" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyBusinessActivityDesc') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">City</label>
                    <input type="text" wire:model="companyAddressCity" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyAddressCity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Postcode</label>
                    <input type="text" wire:model="companyAddressPostcode" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyAddressPostcode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">State Code</label>
                    <input type="text" wire:model="companyAddressStateCode" maxlength="2" placeholder="e.g. 14 = W.P. Kuala Lumpur" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyAddressStateCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Country Code</label>
                    <input type="text" wire:model="companyAddressCountryCode" maxlength="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                    @error('companyAddressCountryCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save Settings
            </button>
        </div>
    </form>
</div>
