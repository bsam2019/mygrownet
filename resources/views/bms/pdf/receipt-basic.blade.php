<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->receipt_number ?? 'RCPT-' . $payment->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        .container {
            padding: 40px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 9pt;
            color: #666;
            line-height: 1.6;
        }
        .document-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .document-title {
            font-size: 24pt;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
        }
        .document-number {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .customer-section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .customer-box {
            background: #f0fdf4;
            padding: 15px;
            border-left: 3px solid #059669;
        }
        .customer-name {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .payment-details {
            background: #f0fdf4;
            border: 2px solid #059669;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #d1fae5;
        }
        .payment-row:last-child {
            border-bottom: none;
        }
        .payment-row.amount {
            font-size: 18pt;
            font-weight: bold;
            color: #059669;
            border-top: 2px solid #059669;
            margin-top: 10px;
            padding-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        thead {
            background: #059669;
            color: white;
        }
        th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
        }
        th.right {
            text-align: right;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        td.right {
            text-align: right;
        }
        .notes-section {
            margin-top: 30px;
        }
        .notes-box {
            background: #f9fafb;
            padding: 15px;
            border-left: 3px solid #6b7280;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        .paid-stamp {
            background: #059669;
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            font-size: 14pt;
            font-weight: bold;
            display: inline-block;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">{{ $payment->company->name }}</div>
                <div class="company-details">
                    @if($payment->company->address)
                        {{ $payment->company->address }}<br>
                    @endif
                    @if($payment->company->phone)
                        Phone: {{ $payment->company->phone }}<br>
                    @endif
                    @if($payment->company->email)
                        Email: {{ $payment->company->email }}<br>
                    @endif
                    @if($payment->company->tax_number)
                        Tax ID: {{ $payment->company->tax_number }}
                    @endif
                </div>
            </div>
            <div class="document-info">
                <div class="document-title">RECEIPT</div>
                <div class="document-number">{{ $payment->receipt_number ?? 'RCPT-' . $payment->id }}</div>
                <div class="paid-stamp">✓ PAID</div>
                <div style="margin-top: 10px; font-size: 9pt;">
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                </div>
            </div>
        </div>

        <!-- Customer Section -->
        @if($payment->customer)
        <div class="customer-section">
            <div class="section-title">Received From</div>
            <div class="customer-box">
                <div class="customer-name">{{ $payment->customer->name }}</div>
                @if($payment->customer->email)
                    Email: {{ $payment->customer->email }}<br>
                @endif
                @if($payment->customer->phone)
                    Phone: {{ $payment->customer->phone }}<br>
                @endif
                @if($payment->customer->address)
                    {{ $payment->customer->address }}
                @endif
            </div>
        </div>
        @endif

        <!-- Payment Details -->
        <div class="payment-details">
            <div class="payment-row">
                <span><strong>Payment Method:</strong></span>
                <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @if($payment->reference_number)
            <div class="payment-row">
                <span><strong>Reference Number:</strong></span>
                <span>{{ $payment->reference_number }}</span>
            </div>
            @endif
            <div class="payment-row amount">
                <span>Amount Received:</span>
                <span>K{{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        <!-- Payment Allocations -->
        @if($payment->allocations && $payment->allocations->count() > 0)
        <div class="section-title">Payment Applied To</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Invoice Number</th>
                    <th style="width: 30%;">Invoice Date</th>
                    <th class="right" style="width: 30%;">Amount Applied</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->allocations as $allocation)
                <tr>
                    <td>{{ $allocation->invoice->invoice_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($allocation->invoice->invoice_date)->format('d M Y') }}</td>
                    <td class="right">K{{ number_format($allocation->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Notes -->
        @if($payment->notes)
        <div class="notes-section">
            <div class="section-title">Notes</div>
            <div class="notes-box">{{ $payment->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your payment!</strong></p>
            <p style="margin-top: 5px;">This is a computer-generated receipt and does not require a signature.</p>
            <p style="margin-top: 10px;">Please retain this receipt for your records.</p>
        </div>
    </div>
</body>
</html>
