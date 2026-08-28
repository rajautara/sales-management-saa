<x-public-layout>
    <x-slot name="title">Receipt {{ $receipt->number }}</x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 sm:p-8 border border-slate-100 max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-3xl font-extrabold text-indigo-600 tracking-tight">RECEIPT</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">No: <span class="text-slate-800 font-semibold">{{ $receipt->number }}</span></p>
                <p class="text-sm text-slate-500">Date: <span class="text-slate-800 font-medium">{{ $receipt->date->format('Y-m-d') }}</span></p>
                <p class="text-sm text-slate-500">Invoice: <span class="text-slate-800 font-medium">{{ $receipt->payment->invoice->number }}</span></p>
            </div>
            <div class="text-left sm:text-right">
                <h2 class="text-lg font-bold text-slate-900">{{ $receipt->company->name }}</h2>
                @if ($receipt->company->registration_no)
                    <p class="text-xs text-slate-500">Reg No: {{ $receipt->company->registration_no }}</p>
                @endif
                <p class="text-sm text-slate-600 mt-1 whitespace-pre-line">{{ $receipt->company->address }}</p>
            </div>
        </div>

        <!-- Details -->
        <div class="py-6 space-y-4 text-sm border-b border-slate-100">
            <div class="grid grid-cols-3">
                <span class="text-slate-500 font-medium">Received From:</span>
                <span class="col-span-2 text-slate-900 font-bold text-base">{{ $receipt->payment->invoice->customer->name }}</span>
            </div>
            <div class="grid grid-cols-3">
                <span class="text-slate-500 font-medium">Payment Method:</span>
                <span class="col-span-2 text-slate-900 font-semibold">{{ $receipt->payment->method->label() }}</span>
            </div>
            @if ($receipt->payment->reference_no)
                <div class="grid grid-cols-3">
                    <span class="text-slate-500 font-medium">Reference No:</span>
                    <span class="col-span-2 text-slate-900 font-semibold">{{ $receipt->payment->reference_no }}</span>
                </div>
            @endif
            <div class="grid grid-cols-3 pt-2">
                <span class="text-slate-500 font-medium text-lg self-center">Amount Paid:</span>
                <span class="col-span-2 text-2xl font-extrabold text-indigo-600">{{ $receipt->company->currency ?? 'RM' }} {{ number_format($receipt->payment->amount, 2) }}</span>
            </div>
        </div>

        @if ($receipt->payment->notes)
            <div class="mt-6 text-sm text-slate-600">
                <span class="font-bold text-slate-700 block mb-1">Notes:</span>
                <p class="whitespace-pre-line">{{ $receipt->payment->notes }}</p>
            </div>
        @endif

        <div class="mt-8 text-center text-xs text-slate-400">
            Thank you for your payment!
        </div>
    </div>
</x-public-layout>
