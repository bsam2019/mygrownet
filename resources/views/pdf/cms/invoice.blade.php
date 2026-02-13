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
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
        }
        .container {
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .company-name {
            font-size: 20pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 9pt;
            color: #666;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .invoice-title {
            font-size: 24pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 12pt;
            color: #666;
            margin-bottom: 5px;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .customer-info {
            background: #f9fafb;
            padding: 15px;
            border-radius: 5px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            display: inline-block;
            width: 100px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        thead {
            background: #2563eb;
            color: white;
        }
        th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        th.right {
            text-align: right;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        td.right {
            text-align: right;
        }
        tbody tr:hover {
            background: #f9fafb;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-row.grand {
            font-size: 14pt;
            font-weight: bold;
            color: #2563eb;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
            margin-top: 10px;
        }
        .total-label {
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft {
            background: #f3f4f6;
            color: #6b7280;
        }
        .status-sent {
            background: #dbeafe;
            color: #2563eb;
        }
        .status-partial {
            background: #fef3c7;
            color: #d97706;
        }
        .status-paid {
            background: #d1fae5;
            color: #059669;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #f9fafb;
            border-left: 4px solid #2563eb;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-details">
                    @if($company->address)
                        {{ $company->address }}<br>
                    @endif
                    @if($company->phone)
                        Phone: {{ $company->phone }}<br>
                    @endif
                    @if($company->email)
                        Email: {{ $company->email }}<br>
                    @endif
                    @if($company->tax_number)
                        Tax ID: {{ $company->tax_number }}
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                <div class="info-row">
                    <strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}
                </div>
                <div class="info-row">
                    <strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}
                </div>
                <div class="info-row">
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="section">
            <div class="section-title">Bill To</div>
            <div class="customer-info">
                <div style="font-weight: bold; font-size: 12pt; margin-bottom: 5px;">
                    {{ $customer->name }}
                </div>
                @if($customer->address)
                    <div>{{ $customer->address }}</div>
                @endif
                @if($customer->phone)
                    <div>Phone: {{ $customer->phone }}</div>
                @endif
                @if($customer->email)
                    <div>Email: {{ $customer->email }}</div>
                @endif
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="section">
            <div class="section-title">Items</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Description</th>
                        <th class="right" style="width: 15%;">Quantity</th>
                        <th class="right" style="width: 17.5%;">Unit Price</th>
                        <th class="right" style="width: 17.5%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="right">{{ number_format($item->quantity, 2) }}</td>
                            <td class="right">K{{ number_format($item->unit_price, 2) }}</td>
                            <td class="right">K{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="clearfix">
            <div class="totals">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span>K{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                    <div class="total-row">
                        <span class="total-label">Tax:</span>
                        <span>K{{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                @endif
                <div class="total-row grand">
                    <span class="total-label">Total:</span>
                    <span>K{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                @if($invoice->amount_paid > 0)
                    <div class="total-row">
                        <span class="total-label">Amount Paid:</span>
                        <span style="color: #059669;">K{{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Balance Due:</span>
                        <span style="color: #d97706;">K{{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
            <div class="notes">
                <div class="notes-title">Notes:</div>
                <div>{{ $invoice->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
