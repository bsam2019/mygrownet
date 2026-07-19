<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
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
        .status-accepted {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">{{ $quotation->company->name }}</div>
                <div class="company-details">
                    @if($quotation->company->address)
                        {{ $quotation->company->address }}<br>
                    @endif
                    @if($quotation->company->phone)
                        Phone: {{ $quotation->company->phone }}<br>
                    @endif
                    @if($quotation->company->email)
                        Email: {{ $quotation->company->email }}<br>
                    @endif
                    @if($quotation->company->tax_number)
                        Tax ID: {{ $quotation->company->tax_number }}
                    @endif
                </div>
            </div>
            <div class="document-info">
                <div class="document-title">QUOTATION</div>
                <div class="document-number">{{ $quotation->quotation_number }}</div>
                <div class="status-badge status-{{ $quotation->status }}">{{ ucfirst($quotation->status) }}</div>
                <div style="margin-top: 10px; font-size: 9pt;">
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}<br>
                    @if($quotation->expiry_date)
                        <strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($quotation->expiry_date)->format('d M Y') }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="customer-section">
            <div class="section-title">Bill To</div>
            <div class="customer-box">
                <div class="customer-name">{{ $quotation->customer->name }}</div>
                @if($quotation->customer->email)
                    Email: {{ $quotation->customer->email }}<br>
                @endif
                @if($quotation->customer->phone)
                    Phone: {{ $quotation->customer->phone }}<br>
                @endif
                @if($quotation->customer->address)
                    {{ $quotation->customer->address }}
                @endif
            </div>
        </div>

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
                @foreach($quotation->items as $item)
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
                <span>K{{ number_format($quotation->subtotal, 2) }}</span>
            </div>
            @if($quotation->discount_amount > 0)
            <div class="totals-row">
                <span>Discount:</span>
                <span>-K{{ number_format($quotation->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="totals-row">
                <span>Tax:</span>
                <span>K{{ number_format($quotation->tax_amount, 2) }}</span>
            </div>
            <div class="totals-row total">
                <span>Total:</span>
                <span>K{{ number_format($quotation->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Notes -->
        @if($quotation->notes || $quotation->terms)
        <div class="notes-section">
            @if($quotation->notes)
            <div class="section-title">Notes</div>
            <div class="notes-box">{{ $quotation->notes }}</div>
            @endif
            
            @if($quotation->terms)
            <div class="section-title" style="margin-top: 20px;">Terms & Conditions</div>
            <div class="notes-box">{{ $quotation->terms }}</div>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top: 5px;">This is a computer-generated quotation and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
