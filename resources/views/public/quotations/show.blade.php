<x-public-layout>
    <x-slot name="title">Quotation {{ $quotation->number }}</x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-8 border border-slate-100">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-3xl font-extrabold text-indigo-600 tracking-tight">QUOTATION</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">No: <span class="text-slate-800 font-semibold">{{ $quotation->number }}</span></p>
                <p class="text-sm text-slate-500">Date: <span class="text-slate-800 font-medium">{{ $quotation->date->format('Y-m-d') }}</span></p>
                @if ($quotation->valid_until)
                    <p class="text-sm text-slate-500">Valid Until: <span class="text-slate-800 font-medium">{{ $quotation->valid_until->format('Y-m-d') }}</span></p>
                @endif
                <p class="text-sm text-slate-500">Status: <span class="text-indigo-600 font-semibold uppercase tracking-wider text-xs px-2 py-0.5 bg-indigo-50 rounded-full">{{ $quotation->status->label() }}</span></p>
            </div>
            <div class="text-left sm:text-right">
                <h2 class="text-lg font-bold text-slate-900">{{ $quotation->company->name }}</h2>
                @if ($quotation->company->registration_no)
                    <p class="text-xs text-slate-500">Reg No: {{ $quotation->company->registration_no }}</p>
                @endif
                <p class="text-sm text-slate-600 mt-1 whitespace-pre-line">{{ $quotation->company->address }}</p>
                @if ($quotation->company->phone)
                    <p class="text-sm text-slate-600">Tel: {{ $quotation->company->phone }}</p>
                @endif
                @if ($quotation->company->email)
                    <p class="text-sm text-slate-600">Email: {{ $quotation->company->email }}</p>
                @endif
            </div>
        </div>

        <!-- Addresses -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-6 text-sm">
            <div>
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-xs mb-2">QUOTATION FOR:</h3>
                <p class="font-semibold text-slate-800">{{ $quotation->customer->name }}</p>
                @if ($quotation->customer->phone)
                    <p class="text-slate-600">Tel: {{ $quotation->customer->phone }}</p>
                @endif
                @if ($quotation->customer->email)
                    <p class="text-slate-600">Email: {{ $quotation->customer->email }}</p>
                @endif
            </div>
            <div>
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-xs mb-2">BILLING ADDRESS:</h3>
                <p class="text-slate-600 whitespace-pre-line">{{ $quotation->customer->billing_address ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mt-6">
            <table class="min-w-full divide-y divide-slate-200 border border-slate-100 rounded-lg">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Unit Price</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Discount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Tax</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @foreach ($quotation->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-900 font-medium">{{ $item->description }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500 text-right font-medium">{{ number_format($item->qty, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500 text-right font-medium">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500 text-right font-medium">{{ number_format($item->discount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500 text-right font-medium">{{ number_format($item->tax, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900 text-right font-semibold">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-right font-medium text-slate-600">Subtotal</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-900">{{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-right font-medium text-slate-600">Tax</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-900">{{ number_format($quotation->tax, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-right font-medium text-slate-600">Discount</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-900">{{ number_format($quotation->discount, 2) }}</td>
                    </tr>
                    <tr class="border-t-2 border-slate-200">
                        <td colspan="5" class="px-4 py-3 text-right font-bold text-slate-900 text-lg">Total</td>
                        <td class="px-4 py-3 text-right font-extrabold text-slate-900 text-lg">{{ $quotation->company->currency ?? 'RM' }} {{ number_format($quotation->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($quotation->notes)
            <div class="mt-8 text-sm text-slate-600 border-t border-slate-100 pt-6">
                <span class="font-bold text-slate-700 block mb-1">Notes:</span>
                <p class="whitespace-pre-line">{{ $quotation->notes }}</p>
            </div>
        @endif
    </div>
</x-public-layout>
