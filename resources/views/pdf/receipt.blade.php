<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $receipt->number }}</title>
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
            color: #16a34a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .document-meta {
            font-size: 11px;
            color: #334155;
            line-height: 1.5;
        }
        .receipt-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .receipt-card table {
            width: 100%;
        }
        .receipt-card td {
            padding: 8px 10px;
            font-size: 12px;
            vertical-align: top;
        }
        .receipt-card .label {
            color: #64748b;
            font-weight: bold;
            width: 30%;
        }
        .receipt-card .val {
            color: #1e293b;
            font-weight: 500;
        }
        .amount-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        .amount-title {
            font-size: 11px;
            font-weight: bold;
            color: #16a34a;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .amount-val {
            font-size: 24px;
            font-weight: bold;
            color: #15803d;
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
                    <span class="company-name">{{ $receipt->company->name }}</span>
                @endif
                <div class="company-details">
                    @if ($logo)
                        <strong>{{ $receipt->company->name }}</strong><br>
                    @endif
                    @if ($receipt->company->registration_no)
                        Reg No: {{ $receipt->company->registration_no }}<br>
                    @endif
                    {!! nl2br(e($receipt->company->address)) !!}<br>
                    @if ($receipt->company->phone)
                        Phone: {{ $receipt->company->phone }}<br>
                    @endif
                    @if ($receipt->company->email)
                        Email: {{ $receipt->company->email }}
                    @endif
                </div>
            </td>
            <td class="document-title-cell">
                <div class="document-title">Official Receipt</div>
                <div class="document-meta">
                    <strong>Receipt No:</strong> {{ $receipt->number }}<br>
                    <strong>Date:</strong> {{ $receipt->date->format('Y-m-d') }}<br>
                    <strong>Currency:</strong> {{ $receipt->company->currency ?? 'MYR' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Amount Acknowledgment Box -->
    <div class="amount-box">
        <div class="amount-title">Amount Received</div>
        <div class="amount-val">{{ number_format($receipt->amount, 2) }}</div>
    </div>

    <!-- Receipt Details Card -->
    <div class="receipt-card">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td class="label">Received From:</td>
                <td class="val">
                    <strong>{{ $receipt->payment->invoice->customer->name }}</strong>
                </td>
            </tr>
            <tr>
                <td class="label">Payment Date:</td>
                <td class="val">{{ $receipt->payment->date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td class="label">Payment Method:</td>
                <td class="val">{{ $receipt->payment->method->label() }}</td>
            </tr>
            @if ($receipt->payment->reference_no)
                <tr>
                    <td class="label">Reference No:</td>
                    <td class="val">{{ $receipt->payment->reference_no }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">For Invoice:</td>
                <td class="val">{{ $receipt->payment->invoice->number }}</td>
            </tr>
        </table>
    </div>

    @if ($receipt->notes)
        <div style="margin-top: 20px; font-size: 11px; color: #475569; line-height: 1.4;">
            <strong>Remarks:</strong> {!! nl2br(e($receipt->notes)) !!}
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated automatically by {{ config('app.name', 'Sales SaaS') }}
    </div>
</body>
</html>
