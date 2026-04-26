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
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }
        .container {
            padding: 40px;
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
            color: #2563eb;
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
            color: #2563eb;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .customer-box {
            background: #f9fafb;
            padding: 15px;
            border-left: 3px solid #2563eb;
        }
        .customer-name {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        thead {
            background: #2563eb;
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
        tbody tr:hover {
            background: #f9fafb;
        }
        .totals-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-row.total {
            font-size: 14pt;
            font-weight: bold;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
            margin-top: 10px;
            padding-top: 10px;
        }
        .totals-row.paid {
            color: #059669;
        }
        .totals-row.due {
            color: #d97706;
            font-weight: bold;
        }
        .notes-section {
            margin-top: 60px;
            clear: both;
        }
        .notes-box {
            background: #f9fafb;
            padding: 15px;
            border-left: 3px solid #059669;
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
            color: #374151;
        }
        .status-sent {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-partial {
            background: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .payment-notice {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">{{ $invoice->company->name }}</div>
                <div class="company-details">
                    @if($invoice->company->address)
                        {{ $invoice->company->address }}<br>
                    @endif
                    @if($invoice->company->phone)
                        Phone: {{ $invoice->company->phone }}<br>
                    @endif
                    @if($invoice->company->email)
                        Email: {{ $invoice->company->email }}<br>
                    @endif
                    @if($invoice->company->tax_number)
                        Tax ID: {{ $invoice->company->tax_number }}
                    @endif
                </div>
            </div>
            <div class="document-info">
                <div class="document-title">INVOICE</div>
                <div class="document-number">{{ $invoice->invoice_number }}</div>
                <div class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</div>
                <div style="margin-top: 10px; font-size: 9pt;">
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}<br>
                    @if($invoice->due_date)
                        <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="customer-section">
            <div class="section-title">Bill To</div>
            <div class="customer-box">
                <div class="customer-name">{{ $invoice->customer->name }}</div>
                @if($invoice->customer->email)
                    Email: {{ $invoice->customer->email }}<br>
                @endif
                @if($invoice->customer->phone)
                    Phone: {{ $invoice->customer->phone }}<br>
                @endif
                @if($invoice->customer->address)
                    {{ $invoice->customer->address }}
                @endif
            </div>
        </div>

        <!-- Payment Notice for Overdue -->
        @if($invoice->due_date && \Carbon\Carbon::parse($invoice->due_date)->isPast() && $invoice->status !== 'paid')
        <div class="payment-notice">
            ⚠ PAYMENT OVERDUE - This invoice was due on {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
        </div>
        @endif

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="right" style="width: 15%;">Quantity</th>
                    <th class="right" style="width: 15%;">Unit Price</th>
                    <th class="right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="right">K{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">K{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>K{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="totals-row total">
                <span>Total:</span>
                <span>K{{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            @if($invoice->amount_paid > 0)
            <div class="totals-row paid">
                <span>Amount Paid:</span>
                <span>K{{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            @endif
            @if($invoice->total_amount - $invoice->amount_paid > 0)
            <div class="totals-row due">
                <span>Balance Due:</span>
                <span>K{{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</span>
            </div>
            @endif
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes-section">
            <div class="section-title">Notes</div>
            <div class="notes-box">{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Payment Instructions -->
        @if($invoice->company->invoice_footer)
        <div class="notes-section">
            <div class="section-title">Payment Instructions</div>
            <div class="notes-box">{{ $invoice->company->invoice_footer }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top: 5px;">This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
