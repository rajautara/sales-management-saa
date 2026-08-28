<x-public-layout>
    <x-slot name="title">Delivery Order {{ $deliveryOrder->number }}</x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-8 border border-slate-100">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-3xl font-extrabold text-indigo-600 tracking-tight">DELIVERY ORDER</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">No: <span class="text-slate-800 font-semibold">{{ $deliveryOrder->number }}</span></p>
                <p class="text-sm text-slate-500">Date: <span class="text-slate-800 font-medium">{{ $deliveryOrder->date->format('Y-m-d') }}</span></p>
                <p class="text-sm text-slate-500">Sales Order No: <span class="text-slate-800 font-medium">{{ $deliveryOrder->salesOrder->number }}</span></p>
                <p class="text-sm text-slate-500">Status: <span class="text-indigo-600 font-semibold uppercase tracking-wider text-xs px-2 py-0.5 bg-indigo-50 rounded-full">{{ $deliveryOrder->status->label() }}</span></p>
            </div>
            <div class="text-left sm:text-right">
                <h2 class="text-lg font-bold text-slate-900">{{ $deliveryOrder->company->name }}</h2>
                @if ($deliveryOrder->company->registration_no)
                    <p class="text-xs text-slate-500">Reg No: {{ $deliveryOrder->company->registration_no }}</p>
                @endif
                <p class="text-sm text-slate-600 mt-1 whitespace-pre-line">{{ $deliveryOrder->company->address }}</p>
                @if ($deliveryOrder->company->phone)
                    <p class="text-sm text-slate-600">Tel: {{ $deliveryOrder->company->phone }}</p>
                @endif
                @if ($deliveryOrder->company->email)
                    <p class="text-sm text-slate-600">Email: {{ $deliveryOrder->company->email }}</p>
                @endif
            </div>
        </div>

        <!-- Addresses -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-6 text-sm">
            <div>
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-xs mb-2">DELIVER TO:</h3>
                <p class="font-semibold text-slate-800">{{ $deliveryOrder->salesOrder->customer->name }}</p>
                @if ($deliveryOrder->salesOrder->customer->phone)
                    <p class="text-slate-600">Tel: {{ $deliveryOrder->salesOrder->customer->phone }}</p>
                @endif
                @if ($deliveryOrder->salesOrder->customer->email)
                    <p class="text-slate-600">Email: {{ $deliveryOrder->salesOrder->customer->email }}</p>
                @endif
            </div>
            <div>
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-xs mb-2">SHIPPING ADDRESS:</h3>
                <p class="text-slate-600 whitespace-pre-line">{{ $deliveryOrder->salesOrder->customer->shipping_address ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mt-6">
            <table class="min-w-full divide-y divide-slate-200 border border-slate-100 rounded-lg">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Qty</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @foreach ($deliveryOrder->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-900 font-medium">{{ $item->salesOrderItem->description }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500 text-right font-semibold">{{ number_format($item->qty, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($deliveryOrder->notes)
            <div class="mt-8 text-sm text-slate-600 border-t border-slate-100 pt-6">
                <span class="font-bold text-slate-700 block mb-1">Notes:</span>
                <p class="whitespace-pre-line">{{ $deliveryOrder->notes }}</p>
            </div>
        @endif
    </div>
</x-public-layout>
