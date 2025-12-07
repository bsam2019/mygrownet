<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .logo-section {
            width: 50%;
        }
        .business-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .business-details {
            color: #666;
            font-size: 11px;
        }
        .invoice-title {
            text-align: right;
            width: 50%;
        }
        .invoice-title h1 {
            font-size: 32px;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .bill-to, .invoice-details {
            width: 48%;
        }
        .section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .customer-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .customer-details {
            color: #666;
            font-size: 11px;
        }
        .invoice-details {
            text-align: right;
        }
        .detail-row {
            margin-bottom: 5px;
        }
        .detail-label {
            color: #666;
        }
        .detail-value {
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #2563eb;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .totals-section {
            width: 300px;
            margin-left: auto;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .total-row.grand-total {
            border-top: 2px solid #2563eb;
            border-bottom: none;
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            padding-top: 12px;
        }
        .notes-section {
            margin-top: 40px;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        .notes-content {
            color: #666;
            font-size: 11px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #dbeafe; color: #2563eb; }
        .status-paid { background: #d1fae5; color: #059669; }
        .status-partial { background: #fef3c7; color: #d97706; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body>
    <table width="100%" style="margin-bottom: 40px; border-bottom: 2px solid #2563eb; padding-bottom: 20px;">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <div class="business-name">{{ $business['name'] }}</div>
                <div class="business-details">
                    @if($business['email']){{ $business['email'] }}<br>@endif
                    @if($business['phone']){{ $business['phone'] }}<br>@endif
                    @if($business['address']){{ $business['address'] }}<br>@endif
                    @if($business['tax_number'])Tax #: {{ $business['tax_number'] }}@endif
                </div>
            </td>
            <td width="50%" style="text-align: right; vertical-align: top;">
                <h1 style="font-size: 32px; color: #2563eb; margin-bottom: 10px;">INVOICE</h1>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ strtolower($invoice->status->value ?? $invoice->status) }}">
                        {{ ucfirst($invoice->status->value ?? $invoice->status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom: 30px;">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <div class="section-title">Bill To</div>
                @if($customer)
                    <div class="customer-name">{{ $customer->name }}</div>
                    <div class="customer-details">
                        @if($customer->email){{ $customer->email }}<br>@endif
                        @if($customer->phone){{ $customer->phone }}<br>@endif
                        @if($customer->address){{ $customer->address }}@endif
                    </div>
                @else
                    <div class="customer-details">Walk-in Customer</div>
                @endif
            </td>
            <td width="50%" style="text-align: right; vertical-align: top;">
                <div class="detail-row">
                    <span class="detail-label">Invoice Date:</span>
                    <span class="detail-value">{{ $invoice->invoice_date->format('F j, Y') }}</span>
                </div>
                @if($invoice->due_date)
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">{{ $invoice->due_date->format('F j, Y') }}</span>
                </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 15%;">Quantity</th>
                <th style="width: 15%;">Unit Price</th>
                <th style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>K{{ number_format($item->unit_price, 2) }}</td>
                <td>K{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table width="300" style="margin-left: auto;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;">Subtotal</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">K{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->tax_amount > 0)
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;">Tax</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">K{{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        @endif
        @if($invoice->discount_amount > 0)
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;">Discount</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">-K{{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 12px 0; border-top: 2px solid #2563eb; font-size: 16px; font-weight: bold; color: #2563eb;">Total</td>
            <td style="padding: 12px 0; border-top: 2px solid #2563eb; font-size: 16px; font-weight: bold; color: #2563eb; text-align: right;">K{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        @if($invoice->amount_paid > 0)
        <tr>
            <td style="padding: 8px 0; color: #059669;">Amount Paid</td>
            <td style="padding: 8px 0; color: #059669; text-align: right;">K{{ number_format($invoice->amount_paid, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold;">Balance Due</td>
            <td style="padding: 8px 0; font-weight: bold; text-align: right;">K{{ number_format($invoice->balance_due, 2) }}</td>
        </tr>
        @endif
    </table>

    @if($invoice->notes)
    <div class="notes-section">
        <div class="notes-title">Notes</div>
        <div class="notes-content">{{ $invoice->notes }}</div>
    </div>
    @endif

    @if($invoice->terms)
    <div class="notes-section" style="margin-top: 15px;">
        <div class="notes-title">Terms & Conditions</div>
        <div class="notes-content">{{ $invoice->terms }}</div>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p style="margin-top: 5px;">Generated on {{ $generated_at }} via GrowFinance</p>
    </div>
</body>
</html>
