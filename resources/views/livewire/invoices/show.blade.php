<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Invoice {{ $invoice->number }}</h2>
</x-slot>

<div class="max-w-7xl mx-auto space-y-6">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col md:flex-row justify-between md:items-start gap-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div>
                    <span class="text-gray-500">Customer:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ $invoice->customer->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Date:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ $invoice->date->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Due Date:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ $invoice->due_date->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ $invoice->status->label() }}</span>
                </div>
                @if ($invoice->salesOrder)
                    <div>
                        <span class="text-gray-500">Sales Order:</span>
                        <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-900 ml-1">{{ $invoice->salesOrder->number }}</a>
                    </div>
                @endif
                <div>
                    <span class="text-gray-500">Amount Paid:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Amount Due:</span>
                    <span class="font-medium text-gray-900 ml-1">{{ number_format($amountDue, 2) }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ $this->getWhatsappUrl('Invoice', $invoice->number, $invoice->customer, (float) $invoice->total, URL::temporarySignedRoute('public.invoices.show', now()->addDays(7), $invoice)) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.498 1.45 5.441 1.451 5.432 0 9.851-4.416 9.854-9.85.002-2.632-1.023-5.105-2.887-6.97C17.19 1.92 14.717.893 12.01.893c-5.436 0-9.857 4.418-9.86 9.852-.001 1.925.501 3.805 1.458 5.41l-.96 3.508 3.596-.943zm12.383-6.527c-.33-.165-1.951-.963-2.248-1.072-.296-.108-.51-.163-.727.162-.217.324-.836 1.072-1.025 1.288-.19.216-.379.243-.709.078-.33-.165-1.393-.513-2.653-1.637-1-.893-1.676-1.997-1.873-2.33-.197-.33-.021-.508.143-.672.149-.148.33-.385.495-.578.165-.192.22-.324.33-.54.11-.217.055-.405-.028-.57-.082-.164-.727-1.753-.996-2.4-.262-.63-.529-.544-.726-.554l-.62-.01c-.217 0-.57.081-.869.405-.299.324-1.14 1.114-1.14 2.72 0 1.605 1.169 3.159 1.332 3.376.163.217 2.299 3.511 5.568 4.919.778.335 1.385.535 1.859.686.782.249 1.494.213 2.057.129.628-.094 1.951-.798 2.224-1.57.272-.77.272-1.43.19-1.57-.081-.14-.298-.222-.628-.387z"/>
                    </svg>
                    Send WhatsApp
                </a>
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export PDF
                </a>
                @if ($invoice->status->value === 'draft')
                    <button type="button" wire:click="send" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">Send</button>
                @endif
                @if (! in_array($invoice->status->value, ['void']))
                    <a href="{{ route('payments.create', $invoice) }}" wire:navigate class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">Record Payment</a>
                    <button type="button" wire:click="markVoid" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">Void</button>
                @endif
                @if ($invoice->status === \App\Enums\InvoiceStatus::PAID)
                    <a href="{{ route('rebates.create', $invoice) }}" wire:navigate class="inline-flex items-center px-3 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">Beri Rebate</a>
                @endif
            </div>
        </div>

        @if ($invoice->notes)
            <div class="mb-6 text-sm text-gray-700">
                <span class="text-gray-500">Notes:</span> {{ $invoice->notes }}
            </div>
        @endif

        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg mb-6">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Discount</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Tax</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item->description }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($item->qty, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($item->discount, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($item->tax, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right font-medium text-gray-700">Subtotal</td>
                    <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right font-medium text-gray-700">Tax</td>
                    <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($invoice->tax, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right font-medium text-gray-700">Discount</td>
                    <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($invoice->discount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right font-bold text-gray-900">Total</td>
                    <td class="px-4 py-2 text-right font-bold text-gray-900">{{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @php
            $bankName = \App\Models\Setting::get('bank_name');
            $bankAccountName = \App\Models\Setting::get('bank_account_name');
            $bankAccountNo = \App\Models\Setting::get('bank_account_no');
        @endphp
        @if ($bankName || $bankAccountNo)
            <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm">
                <h3 class="font-medium text-gray-700 mb-1">Payment Details</h3>
                <div class="text-gray-600 space-y-0.5">
                    @if ($bankName)<div><span class="text-gray-500">Bank:</span> <span class="font-medium text-gray-900">{{ $bankName }}</span></div>@endif
                    @if ($bankAccountName)<div><span class="text-gray-500">Account Name:</span> <span class="font-medium text-gray-900">{{ $bankAccountName }}</span></div>@endif
                    @if ($bankAccountNo)<div><span class="text-gray-500">Account No:</span> <span class="font-medium text-gray-900">{{ $bankAccountNo }}</span></div>@endif
                </div>
            </div>
        @endif

        <h3 class="font-medium text-gray-700 mb-2">Payment History</h3>
        @if ($invoice->payments->count())
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($invoice->payments as $payment)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $payment->method->label() }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $payment->reference_no }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                @if ($payment->receipt)
                                    <a href="{{ route('receipts.show', $payment->receipt) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">{{ $payment->receipt->number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500">No payments recorded.</p>
        @endif

        @if ($invoice->rebates->count())
            <h3 class="font-medium text-gray-700 mb-2 mt-6">Rebate History</h3>
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($invoice->rebates as $rebate)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $rebate->date->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $rebate->method->label() }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500 text-right">{{ number_format($rebate->amount, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $rebate->reference_no ?: '-' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $rebate->notes ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if (config('myinvois.enabled'))
        @php
            $ei = $invoice->eInvoice;
            $badge = match ($ei?->status?->color()) {
                'green' => 'bg-green-100 text-green-800',
                'red' => 'bg-red-100 text-red-800',
                'blue' => 'bg-blue-100 text-blue-800',
                'yellow' => 'bg-yellow-100 text-yellow-800',
                default => 'bg-gray-100 text-gray-800',
            };
        @endphp
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">e-Invoice (MyInvois / LHDN)</h3>

            @if (! $ei)
                <p class="text-sm text-gray-500 mb-4">This invoice has not been submitted to MyInvois yet.</p>
                <button type="button" wire:click="submitEInvoice" wire:loading.attr="disabled" wire:target="submitEInvoice"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="submitEInvoice">Submit to MyInvois</span>
                    <span wire:loading wire:target="submitEInvoice">Submitting…</span>
                </button>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm mb-4">
                    <div>
                        <span class="text-gray-500">Status:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ml-1 {{ $badge }}">{{ $ei->status->label() }}</span>
                    </div>
                    @if ($ei->uuid)
                        <div>
                            <span class="text-gray-500">UUID:</span>
                            <span class="font-mono text-xs text-gray-900 ml-1">{{ $ei->uuid }}</span>
                        </div>
                    @endif
                    @if ($ei->validation_link)
                        <div class="md:col-span-2">
                            <span class="text-gray-500">Validation:</span>
                            <a href="{{ $ei->validation_link }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 ml-1 break-all">{{ $ei->validation_link }}</a>
                        </div>
                    @endif
                </div>

                @if ($ei->error_log)
                    <pre class="bg-red-50 border border-red-200 text-red-700 text-xs rounded p-3 mb-4 overflow-x-auto">{{ json_encode($ei->error_log, JSON_PRETTY_PRINT) }}</pre>
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    @if ($ei->status->value === 'invalid')
                        <button type="button" wire:click="submitEInvoice" wire:loading.attr="disabled" wire:target="submitEInvoice"
                            class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="submitEInvoice">Resubmit to MyInvois</span>
                            <span wire:loading wire:target="submitEInvoice">Submitting…</span>
                        </button>
                    @endif
                    <button type="button" wire:click="refreshEInvoiceStatus" wire:loading.attr="disabled" wire:target="refreshEInvoiceStatus"
                        class="inline-flex items-center px-3 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition disabled:opacity-50">
                        Check Status
                    </button>

                    @if ($ei->isCancellable())
                        <input type="text" wire:model="cancelReason" placeholder="Cancellation reason"
                            class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-sm">
                        <button type="button" wire:click="cancelEInvoice" wire:confirm="Cancel this e-Invoice? This cannot be undone."
                            class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                            Cancel e-Invoice
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
