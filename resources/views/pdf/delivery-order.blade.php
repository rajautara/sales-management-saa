<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Order {{ $deliveryOrder->number }}</title>
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
            padding: 10px;
            vertical-align: top;
        }
        .text-right {
            text-align: right !important;
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
        .signature-table {
            width: 100%;
            margin-top: 60px;
        }
        .signature-cell {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px dashed #64748b;
            margin: 40px auto 10px auto;
        }
        .signature-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
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
                    <span class="company-name">{{ $deliveryOrder->company->name }}</span>
                @endif
                <div class="company-details">
                    @if ($logo)
                        <strong>{{ $deliveryOrder->company->name }}</strong><br>
                    @endif
                    @if ($deliveryOrder->company->registration_no)
                        Reg No: {{ $deliveryOrder->company->registration_no }}<br>
                    @endif
                    {!! nl2br(e($deliveryOrder->company->address)) !!}<br>
                    @if ($deliveryOrder->company->phone)
                        Phone: {{ $deliveryOrder->company->phone }}<br>
                    @endif
                    @if ($deliveryOrder->company->email)
                        Email: {{ $deliveryOrder->company->email }}
                    @endif
                </div>
            </td>
            <td class="document-title-cell">
                <div class="document-title">Delivery Order</div>
                <div class="document-meta">
                    <strong>Delivery Order No:</strong> {{ $deliveryOrder->number }}<br>
                    <strong>Date:</strong> {{ $deliveryOrder->date->format('Y-m-d') }}<br>
                    <strong>Sales Order No:</strong> {{ $deliveryOrder->salesOrder->number }}<br>
                    <strong>Status:</strong> {{ $deliveryOrder->status->label() }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Billing Details -->
    <table class="details-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="details-cell">
                <div class="section-title">Deliver To:</div>
                <div class="client-name">{{ $deliveryOrder->salesOrder->customer->name }}</div>
                <div class="client-details">
                    @if ($deliveryOrder->salesOrder->customer->phone)
                        Phone: {{ $deliveryOrder->salesOrder->customer->phone }}<br>
                    @endif
                    @if ($deliveryOrder->salesOrder->customer->email)
                        Email: {{ $deliveryOrder->salesOrder->customer->email }}
                    @endif
                </div>
            </td>
            <td class="details-cell">
                <div class="section-title">Shipping Address:</div>
                <div class="client-details">
                    {!! nl2br(e($deliveryOrder->salesOrder->customer->shipping_address ?? 'N/A')) !!}
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right" style="width: 100px;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($deliveryOrder->items as $item)
                <tr>
                    <td>{{ $item->salesOrderItem->description }}</td>
                    <td class="text-right">{{ number_format($item->qty, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Notes / Terms -->
    @if ($deliveryOrder->notes)
        <div class="notes-section">
            <div class="notes-title">Notes / Delivery Instructions:</div>
            <div class="notes-content">{!! nl2br(e($deliveryOrder->notes)) !!}</div>
        </div>
    @endif

    <!-- Signature Fields -->
    <table class="signature-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-label">Issued By</div>
            </td>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-label">Received In Good Condition By</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Generated automatically by {{ config('app.name', 'Sales SaaS') }}
    </div>
</body>
</html>
