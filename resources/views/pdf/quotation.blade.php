<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.5;
        }
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }
        .header-logo {
            width: 50%;
            vertical-align: top;
        }
        .header-logo img {
            max-height: 70px;
            max-width: 200px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
        }
        .company-details {
            color: #64748b;
            font-size: 10px;
            margin-top: 5px;
            line-height: 1.4;
        }
        .document-title-cell {
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .document-meta {
            font-size: 11px;
            color: #334155;
            line-height: 1.5;
        }
        .details-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .details-cell {
            width: 50%;
            vertical-align: top;
        }
        .section-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .client-name {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .client-details {
            color: #475569;
            font-size: 11px;
            line-height: 1.4;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
            color: #475569;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 10px;
            vertical-align: top;
        }
        .text-right {
            text-align: right !important;
        }
        .totals-table-container {
            width: 100%;
            margin-top: 15px;
        }
        .totals-table {
            width: 45%;
            margin-left: 55%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 10px;
            font-size: 11px;
        }
        .totals-table .label {
            color: #64748b;
            text-align: right;
        }
        .totals-table .val {
            text-align: right;
            font-weight: 500;
            color: #1e293b;
        }
        .totals-table .grand-total td {
            border-top: 2px solid #cbd5e1;
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            padding-top: 8px;
        }
        .notes-section {
            margin-top: 35px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .notes-title {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .notes-content {
            color: #475569;
            font-size: 10px;
            line-height: 1.4;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
        }
    </style>
</head>
<body>
    <!-- Header Block -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header-logo">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo">
                @else
                    <span class="company-name">{{ $quotation->company->name }}</span>
                @endif
                <div class="company-details">
                    @if ($logo)
                        <strong>{{ $quotation->company->name }}</strong><br>
                    @endif
                    @if ($quotation->company->registration_no)
                        Reg No: {{ $quotation->company->registration_no }}<br>
                    @endif
                    {!! nl2br(e($quotation->company->address)) !!}<br>
                    @if ($quotation->company->phone)
                        Phone: {{ $quotation->company->phone }}<br>
                    @endif
                    @if ($quotation->company->email)
                        Email: {{ $quotation->company->email }}
                    @endif
                </div>
            </td>
            <td class="document-title-cell">
                <div class="document-title">Quotation</div>
                <div class="document-meta">
                    <strong>Quotation No:</strong> {{ $quotation->number }}<br>
                    <strong>Date:</strong> {{ $quotation->date->format('Y-m-d') }}<br>
                    <strong>Valid Until:</strong> {{ $quotation->expiry_date->format('Y-m-d') }}<br>
                    <strong>Currency:</strong> {{ $quotation->company->currency ?? 'MYR' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Billing Details -->
    <table class="details-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="details-cell">
                <div class="section-title">Quotation For:</div>
                <div class="client-name">{{ $quotation->customer->name }}</div>
                <div class="client-details">
                    @if ($quotation->customer->phone)
                        Phone: {{ $quotation->customer->phone }}<br>
                    @endif
                    @if ($quotation->customer->email)
                        Email: {{ $quotation->customer->email }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right" style="width: 80px;">Qty</th>
                <th class="text-right" style="width: 100px;">Unit Price</th>
                <th class="text-right" style="width: 80px;">Discount</th>
                <th class="text-right" style="width: 80px;">Tax</th>
                <th class="text-right" style="width: 100px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->qty, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                    <td class="text-right">{{ number_format($item->tax, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Table -->
    <div class="totals-table-container">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="val">{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Tax</td>
                <td class="val">{{ number_format($quotation->tax, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Discount</td>
                <td class="val">{{ number_format($quotation->discount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td class="label">Total</td>
                <td class="val">{{ number_format($quotation->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Notes / Terms -->
    @if ($quotation->notes)
        <div class="notes-section">
            <div class="notes-title">Notes / Terms:</div>
            <div class="notes-content">{!! nl2br(e($quotation->notes)) !!}</div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated automatically by {{ config('app.name', 'Sales SaaS') }}
    </div>
</body>
</html>
