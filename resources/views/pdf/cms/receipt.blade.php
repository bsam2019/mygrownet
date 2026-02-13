<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->payment_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
        }
        .container {
            padding: 40px;
        }
        .header {
            margin-bottom: 40px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 10pt;
            color: #666;
        }
        .receipt-title {
            text-align: center;
            font-size: 20pt;
            font-weight: bold;
            margin: 30px 0;
            color: #059669;
        }
        .receipt-number {
            text-align: center;
            font-size: 12pt;
            color: #666;
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-box {
            width: 48%;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 9pt;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 11pt;
            color: #333;
        }
        .payment-details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .payment-amount {
            text-align: center;
            padding: 20px;
            background: #059669;
            color: white;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .payment-amount-label {
            font-size: 10pt;
            margin-bottom: 5px;
        }
        .payment-amount-value {
            font-size: 28pt;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            color: #666;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
            font-size: 10pt;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $company->name }}</div>
            <div class="company-details">
                @if($company->address)
                    {{ $company->address }}<br>
                @endif
                @if($company->phone)
                    Phone: {{ $company->phone }}<br>
                @endif
                @if($company->email)
                    Email: {{ $company->email }}
                @endif
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">PAYMENT RECEIPT</div>
        <div class="receipt-number">{{ $payment->payment_number }}</div>

        <!-- Payment Amount (Prominent) -->
        <div class="payment-amount">
            <div class="payment-amount-label">AMOUNT RECEIVED</div>
            <div class="payment-amount-value">K {{ number_format($payment->amount, 2) }}</div>
        </div>

        <!-- Customer and Payment Info -->
        <div class="info-section">
            <div class="info-row">
                <div class="info-box">
                    <div class="info-label">Received From</div>
                    <div class="info-value">
                        <strong>{{ $customer->name }}</strong><br>
                        {{ $customer->customer_number }}<br>
                        @if($customer->phone)
                            {{ $customer->phone }}<br>
                        @endif
                        @if($customer->email)
                            {{ $customer->email }}
                        @endif
                    </div>
                </div>
                <div class="info-box">
                    <div class="info-label">Payment Details</div>
                    <div class="info-value">
                        <strong>Date:</strong> {{ $payment->payment_date->format('d M Y') }}<br>
                        <strong>Method:</strong> {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}<br>
                        @if($payment->reference_number)
                            <strong>Reference:</strong> {{ $payment->reference_number }}<br>
                        @endif
                        <strong>Received By:</strong> {{ $payment->receivedBy->user->name ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Allocation -->
        @if($allocations->count() > 0)
            <div class="info-label" style="margin-bottom: 10px;">PAYMENT ALLOCATION</div>
            <table>
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>Invoice Date</th>
                        <th>Invoice Amount</th>
                        <th class="text-right">Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allocations as $allocation)
                        <tr>
                            <td>{{ $allocation->invoice->invoice_number }}</td>
                            <td>{{ $allocation->invoice->invoice_date->format('d M Y') }}</td>
                            <td>K {{ number_format($allocation->invoice->total_amount, 2) }}</td>
                            <td class="text-right">K {{ number_format($allocation->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Unallocated Amount -->
        @if($payment->unallocated_amount > 0)
            <div class="payment-details">
                <strong>Unallocated Amount:</strong> K {{ number_format($payment->unallocated_amount, 2) }}<br>
                <small style="color: #666;">This amount is available as credit for future invoices.</small>
            </div>
        @endif

        <!-- Notes -->
        @if($payment->notes)
            <div class="payment-details">
                <div class="info-label">Notes</div>
                <div>{{ $payment->notes }}</div>
            </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    Received By<br>
                    <strong>{{ $payment->receivedBy->user->name ?? 'N/A' }}</strong>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    Customer Signature
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>Thank you for your payment!</strong><br>
            This is a computer-generated receipt and is valid without signature.<br>
            Generated on {{ now()->format('d M Y H:i') }}
        </div>
    </div>
</body>
</html>
