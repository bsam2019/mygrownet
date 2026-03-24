<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blue Lines Professional</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #1f2937; padding: 50px; background: white; font-size: 13px; }
        
        /* Header with logo and business info side by side */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            margin-bottom: 40px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-box {
            width: 80px;
            height: 80px;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            flex-shrink: 0;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 3px;
        }
        
        .company-tagline {
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
            margin-bottom: 8px;
        }
        
        .business-details {
            text-align: right;
            font-size: 11px;
            color: #4b5563;
            line-height: 1.7;
        }
        
        /* Document title section */
        .doc-title-section {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .doc-title-main {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 8px;
            margin-bottom: 8px;
        }
        
        .doc-number {
            font-size: 14px;
            color: #dc2626;
            font-weight: 600;
        }
        
        /* Customer and meta info */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px;
        }
        
        .customer-info {
            flex: 1;
        }
        
        .info-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .customer-name {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }
        
        .customer-details {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        .meta-info {
            text-align: right;
            font-size: 11px;
            color: #4b5563;
            line-height: 1.8;
        }
        
        .meta-label {
            color: #6b7280;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        
        .meta-value {
            font-weight: 600;
            color: #111827;
        }
        
        /* Items table with blue header */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table thead {
            background: #2563eb;
            color: white;
        }
        
        .items-table th {
            padding: 14px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .items-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        
        .items-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Totals section */
        .totals-section {
            max-width: 400px;
            margin-left: auto;
            margin-bottom: 40px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            font-size: 13px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .total-row.subtotal {
            background: #f9fafb;
        }
        
        .total-row.grand-total {
            background: #2563eb;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border-bottom: none;
            margin-top: 5px;
        }
        
        /* Footer */
        .footer-section {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 2px solid #e5e7eb;
        }
        
        .notes-box {
            padding: 20px;
            background: #f9fafb;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        
        .notes-title {
            font-size: 11px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .notes-content {
            font-size: 12px;
            color: #4b5563;
            line-height: 1.8;
        }
        
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 2px solid #2563eb;
            margin-top: 60px;
            padding-top: 10px;
            font-size: 11px;
            font-weight: 600;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <div class="logo-box">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                @else
                    {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                @endif
            </div>
            <div class="company-info">
                <div class="company-name">{{ $businessProfile->businessName() }}</div>
                <div class="company-tagline">Professional Business Solutions</div>
            </div>
        </div>
        <div class="business-details">
            {{ $businessProfile->address() }}<br>
            Tel: {{ $businessProfile->phone() }}
            @if($businessProfile->email())
            <br>{{ $businessProfile->email() }}
            @endif
            @if($businessProfile->website())
            <br>{{ $businessProfile->website() }}
            @endif
        </div>
    </div>
    
    <!-- Document Title -->
    <div class="doc-title-section">
        <div class="doc-title-main">{{ $template->template_structure['document_title']['text'] ?? 'QUOTATION' }}</div>
        <div class="doc-number">#{{ $document->number()->value() }}</div>
    </div>
    
    <!-- Customer and Meta Info -->
    <div class="info-section">
        <div class="customer-info">
            <div class="info-label">{{ $template->template_structure['customer_block']['label_text'] ?? 'Quote For' }}</div>
            <div class="customer-name">{{ $customer->name() }}</div>
            <div class="customer-details">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone()){{ $customer->phone() }}@endif
            </div>
        </div>
        <div class="meta-info">
            <div><span class="meta-label">Date:</span> <span class="meta-value">{{ $document->issueDate()->format('d/m/Y') }}</span></div>
            @if($document->dueDate())
            <div><span class="meta-label">Valid Until:</span> <span class="meta-value">{{ $document->dueDate()->format('d/m/Y') }}</span></div>
            @endif
            @if($businessProfile->tpin())
            <div><span class="meta-label">Tax Number (TPIN):</span> <span class="meta-value">{{ $businessProfile->tpin() }}</span></div>
            @endif
        </div>
    </div>
    
    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 100px;">DIMENSIONS</th>
                <th style="width: 70px;">QTY</th>
                <th>DESCRIPTION</th>
                <th class="text-right" style="width: 110px;">UNIT PRICE</th>
                <th class="text-right" style="width: 120px;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
                $effectiveQty = ($item->dimensionsValue ?? 1) * $item->quantity;
                $lineTotal = $effectiveQty * ($item->unitPrice / 100);
            @endphp
            <tr>
                <td class="text-center">
                    @if($item->dimensions !== null && $item->dimensions !== '')
                        {{ $item->dimensions }} <small style="color: #666; font-size: 9px;">({{ number_format($item->dimensionsValue, 2) }})</small>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($item->unitPrice / 100, 2) }}</td>
                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Totals -->
    <div class="totals-section">
        <div class="total-row subtotal">
            <span>Subtotal</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
        </div>
        @if($totals['discountTotal'] > 0)
        <div class="total-row">
            <span>Discount</span>
            <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
        </div>
        @endif
        @if($document->collectTax() && $totals['taxTotal'] > 0)
        <div class="total-row">
            <span>Tax (16%)</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span>TOTAL</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer-section">
        <div class="notes-box">
            <div class="notes-title">Terms & Conditions</div>
            <div class="notes-content">
                This quotation is valid for 30 days from the date of issue. Payment terms and conditions apply as per agreement. All prices are subject to change without prior notice.
            </div>
        </div>
        
        <div class="signature-area">
            <div class="signature-box">
                @if($signaturePath)
                    <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 180px; max-height: 60px; margin: 0 auto 10px; display: block;">
                @endif
                <div class="signature-line">Authorized Signature</div>
                @if($businessProfile->preparedBy())
                <div style="font-size: 10px; color: #9ca3af; margin-top: 5px; text-align: center;">
                    Prepared by: {{ $businessProfile->preparedBy() }}
                </div>
                @endif
            </div>
        </div>
    </div>
    
    @if($businessProfile->website())
    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #e5e7eb; font-size: 12px; color: #6b7280;">
        Visit us: <span style="color: #2563eb; font-weight: bold;">{{ $businessProfile->website() }}</span>
    </div>
    @endif
</body>
</html>
