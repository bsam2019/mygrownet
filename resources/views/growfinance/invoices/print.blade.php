<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: {{ $template?->colors['text'] ?? '#1f2937' }};
            background: white;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        
        /* Header */
        .invoice-header {
            background: {{ $template?->colors['primary'] ?? '#2563eb' }};
            color: white;
            padding: 30px;
            margin: -40px -40px 30px -40px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .company-info h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info p {
            opacity: 0.9;
            font-size: 13px;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h2 {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .invoice-title .invoice-number {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        /* Header Text */
        .header-text {
            text-align: center;
            font-style: italic;
            color: #6b7280;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Details Section */
        .details-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .bill-to, .invoice-details {
            width: 48%;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: {{ $template?->colors['secondary'] ?? '#1e40af' }};
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .customer-name {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
        }
        
        .customer-details {
            color: #6b7280;
            font-size: 13px;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .detail-row {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-bottom: 5px;
        }
        
        .detail-label {
            color: #6b7280;
        }
        
        .detail-value {
            font-weight: 500;
            min-width: 100px;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background: {{ $template?->colors['accent'] ?? '#3b82f6' }}20;
            color: {{ $template?->colors['secondary'] ?? '#1e40af' }};
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid {{ $template?->colors['accent'] ?? '#3b82f6' }}40;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }
        
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .item-description {
            font-weight: 500;
        }
        
        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        
        .totals-table {
            width: 300px;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .totals-row.total {
            border-bottom: none;
            border-top: 2px solid {{ $template?->colors['primary'] ?? '#2563eb' }};
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: {{ $template?->colors['primary'] ?? '#2563eb' }};
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #d1fae5; color: #059669; }
        .status-partial { background: #fef3c7; color: #d97706; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        
        /* Footer */
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-text {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .terms {
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
        }
        
        .notes {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .notes-title {
            font-weight: 600;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        /* Print Styles */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .invoice-container {
                padding: 20px;
            }
            
            .invoice-header {
                margin: -20px -20px 20px -20px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* Print Button */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        
        .print-btn {
            padding: 10px 20px;
            background: {{ $template?->colors['primary'] ?? '#2563eb' }};
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .print-btn:hover {
            opacity: 0.9;
        }
        
        .back-btn {
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Print Actions -->
    <div class="print-actions no-print">
        <a href="{{ url()->previous() }}" class="back-btn">← Back</a>
        <button onclick="window.print()" class="print-btn">🖨️ Print Invoice</button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="company-info">
                    <h1>{{ $profile?->business_name ?? 'Your Business' }}</h1>
                    @if($profile)
                        <p>{{ $profile->address }}</p>
                        <p>{{ $profile->phone }} • {{ $profile->email }}</p>
                    @endif
                </div>
                <div class="invoice-title">
                    <h2>INVOICE</h2>
                    <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                </div>
            </div>
        </div>

        <!-- Header Text -->
        @if($template?->header_text)
            <div class="header-text">{{ $template->header_text }}</div>
        @endif

        <!-- Details Section -->
        <div class="details-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                @if($invoice->customer)
                    <div class="customer-name">{{ $invoice->customer->name }}</div>
                    <div class="customer-details">
                        @if($invoice->customer->address)
                            {{ $invoice->customer->address }}<br>
                        @endif
                        @if($invoice->customer->email)
                            {{ $invoice->customer->email }}<br>
                        @endif
                        @if($invoice->customer->phone)
                            {{ $invoice->customer->phone }}
                        @endif
                    </div>
                @else
                    <div class="customer-name">Walk-in Customer</div>
                @endif
            </div>
            <div class="invoice-details">
                <div class="detail-row">
                    <span class="detail-label">Invoice Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</span>
                </div>
                @if($invoice->due_date)
                    <div class="detail-row">
                        <span class="detail-label">Due Date:</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ strtolower($invoice->status) }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th style="width: 15%">Quantity</th>
                    <th style="width: 15%">Unit Price</th>
                    <th style="width: 20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td class="item-description">{{ $item->description }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>K{{ number_format($item->unit_price, 2) }}</td>
                        <td>K{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>K{{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                    <div class="totals-row">
                        <span>Tax</span>
                        <span>K{{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                @endif
                @if($invoice->discount_amount > 0)
                    <div class="totals-row">
                        <span>Discount</span>
                        <span>-K{{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="totals-row total">
                    <span>Total</span>
                    <span>K{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                @if($invoice->amount_paid > 0)
                    <div class="totals-row">
                        <span>Amount Paid</span>
                        <span>K{{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="totals-row" style="font-weight: 600;">
                        <span>Balance Due</span>
                        <span>K{{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
            <div class="notes">
                <div class="notes-title">Notes</div>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            @if($template?->footer_text)
                <div class="footer-text">{{ $template->footer_text }}</div>
            @else
                <div class="footer-text">Thank you for your business!</div>
            @endif
            
            @if($template?->terms_text)
                <div class="terms">
                    <strong>Terms & Conditions:</strong> {{ $template->terms_text }}
                </div>
            @endif
        </div>
    </div>

    <script>
        // Auto-print if requested via URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === '1') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
