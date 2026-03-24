<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Compact Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; color: #111827; background: white; font-size: 12px; max-width: 500px; margin: 0 auto; }
        
        .content-wrapper { padding: 30px; }
        
        /* Compact centered header */
        .header { text-align: center; padding: 20px; background: #059669; color: white; border-radius: 8px 8px 0 0; margin-bottom: 0; }
        .logo-icon { width: 60px; height: 60px; margin: 0 auto 10px; background: white; color: #059669; border-radius: 50%; text-align: center; line-height: 60px; font-size: 30px; overflow: hidden; position: relative; }
        .logo-icon img { max-width: 50px; max-height: 50px; width: auto; height: auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .business-name { font-size: 18px; font-weight: bold; margin-bottom: 8px; letter-spacing: 1px; }
        .business-info { font-size: 10px; line-height: 1.6; opacity: 0.95; }
        
        /* Receipt title banner */
        .receipt-banner { background: #111827; color: white; text-align: center; padding: 15px; font-size: 24px; font-weight: bold; letter-spacing: 3px; border-bottom: 4px solid #059669; }
        
        /* Document details */
        .doc-details { background: #f9fafb; padding: 15px; border-left: 4px solid #059669; border-right: 4px solid #059669; font-size: 11px; }
        
        @if(!isset($isPdf) || !$isPdf)
        /* Flexbox for HTML preview */
        .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #d1d5db; }
        .item-row { display: flex; justify-content: space-between; padding: 12px 15px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .total-line { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px dashed rgba(255,255,255,0.2); }
        .grand-total-line { display: flex; justify-content: space-between; padding: 15px 0; font-size: 20px; font-weight: bold; margin-top: 10px; border-top: 2px solid #059669; color: #059669; }
        @else
        /* Table-based for PDF */
        .doc-details table { width: 100%; }
        .detail-row td { padding: 6px 0; border-bottom: 1px dashed #d1d5db; }
        .items-section table { width: 100%; }
        .item-row td { padding: 12px 15px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .totals-box table { width: 100%; }
        .total-line td { padding: 8px 0; font-size: 13px; border-bottom: 1px dashed rgba(255,255,255,0.2); }
        .grand-total-line td { padding: 15px 0; font-size: 20px; font-weight: bold; border-top: 2px solid #059669; color: #059669; }
        @endif
        
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: bold; color: #6b7280; }
        .detail-value { color: #111827; }
        
        /* Customer box */
        .customer-box { margin: 20px 0; padding: 15px; background: white; border: 2px dashed #059669; border-radius: 6px; }
        .customer-label { font-size: 10px; font-weight: bold; color: #059669; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .customer-name { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        .customer-info { font-size: 11px; color: #6b7280; line-height: 1.6; }
        
        /* Simple items list */
        .items-section { margin: 20px 0; }
        .section-header { background: #111827; color: white; padding: 10px 15px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .item-row:nth-child(even) { background: #f9fafb; }
        .item-desc { flex: 1; }
        .item-dim { width: 80px; text-align: center; color: #6b7280; font-size: 10px; }
        .item-qty { width: 60px; text-align: center; color: #6b7280; }
        .item-price { width: 100px; text-align: right; font-weight: 600; }
        
        /* Totals box */
        .totals-box { background: #111827; color: white; padding: 20px; border-radius: 0 0 8px 8px; margin-top: 0; }
        .total-line:last-child { border-bottom: none; }
        
        /* Footer */
        .footer-note { text-align: center; margin-top: 30px; padding: 15px; background: #f9fafb; border-radius: 6px; font-size: 11px; color: #6b7280; line-height: 1.7; }
        .thank-you { text-align: center; margin-top: 20px; font-size: 16px; font-weight: bold; color: #059669; letter-spacing: 2px; }
        .signature-line { margin-top: 40px; text-align: center; }
        .sig-box { display: inline-block; width: 200px; border-top: 2px solid #111827; padding-top: 8px; font-size: 10px; font-weight: bold; margin-top: 10px; }
        
        .notes { margin-top: 20px; padding: 15px; background: #f0fdf4; border-radius: 6px; border-left: 4px solid #059669; }
        .notes-title { font-size: 10px; font-weight: bold; color: #059669; text-transform: uppercase; margin-bottom: 8px; }
        .notes-text { font-size: 11px; color: #6b7280; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="content-wrapper">
    <!-- Header -->
    <div class="header">
        <div class="logo-icon">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
            @else
                {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
            @endif
        </div>
        <div class="business-name">{{ $businessProfile->businessName() }}</div>
        <div class="business-info">
            {{ $businessProfile->address() }}<br>
            {{ $businessProfile->phone() }}
            @if($businessProfile->email())
            | {{ $businessProfile->email() }}
            @endif
            @if($businessProfile->website())
            <br>{{ $businessProfile->website() }}
            @endif
        </div>
    </div>
    
    <!-- Receipt Banner -->
    <div class="receipt-banner">{{ strtoupper($document->type()->value()) }}</div>
    
    <!-- Document Details -->
    <div class="doc-details">
        @if(!isset($isPdf) || !$isPdf)
        <div class="detail-row">
            <span class="detail-label">Number:</span>
            <span class="detail-value">{{ $document->number()->value() }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date:</span>
            <span class="detail-value">{{ $document->issueDate()->format('d M Y, h:i A') }}</span>
        </div>
        @if($businessProfile->tpin())
        <div class="detail-row">
            <span class="detail-label">TPIN:</span>
            <span class="detail-value">{{ $businessProfile->tpin() }}</span>
        </div>
        @endif
        @else
        <table>
            <tr class="detail-row">
                <td class="detail-label">Number:</td>
                <td class="detail-value" style="text-align: right;">{{ $document->number()->value() }}</td>
            </tr>
            <tr class="detail-row">
                <td class="detail-label">Date:</td>
                <td class="detail-value" style="text-align: right;">{{ $document->issueDate()->format('d M Y, h:i A') }}</td>
            </tr>
            @if($businessProfile->tpin())
            <tr class="detail-row">
                <td class="detail-label">TPIN:</td>
                <td class="detail-value" style="text-align: right;">{{ $businessProfile->tpin() }}</td>
            </tr>
            @endif
        </table>
        @endif
    </div>
    
    <!-- Customer -->
    <div class="customer-box">
        <div class="customer-label">Customer</div>
        <div class="customer-name">{{ $customer->name() }}</div>
        <div class="customer-info">
            @if($customer->phone()){{ $customer->phone() }}@endif
            @if($customer->email() && $customer->phone()) | @endif
            @if($customer->email()){{ $customer->email() }}@endif
        </div>
    </div>
    
    <!-- Items -->
    <div class="items-section">
        <div class="section-header">Items</div>
        @if(!isset($isPdf) || !$isPdf)
        @foreach($items as $item)
        @php
            $effectiveQty = ($item->dimensionsValue ?? 1) * $item->quantity;
            $lineTotal = $effectiveQty * ($item->unitPrice / 100);
        @endphp
        <div class="item-row">
            <div class="item-desc">{{ $item->description }}</div>
            <div class="item-dim">
                @if($item->dimensions !== null && $item->dimensions !== '')
                    {{ $item->dimensions }} <small style="color: #999;">({{ number_format($item->dimensionsValue, 2) }})</small>
                @else
                    -
                @endif
            </div>
            <div class="item-qty">x{{ $item->quantity }}</div>
            <div class="item-price">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal, 2) }}</div>
        </div>
        @endforeach
        @else
        <table>
            @foreach($items as $item)
            @php
                $effectiveQty = ($item->dimensionsValue ?? 1) * $item->quantity;
                $lineTotal = $effectiveQty * ($item->unitPrice / 100);
            @endphp
            <tr class="item-row">
                <td style="width: 40%;">{{ $item->description }}</td>
                <td style="width: 20%; text-align: center; color: #6b7280; font-size: 10px;">
                    @if($item->dimensions !== null && $item->dimensions !== '')
                        {{ $item->dimensions }} <small style="color: #999;">({{ number_format($item->dimensionsValue, 2) }})</small>
                    @else
                        -
                    @endif
                </td>
                <td style="width: 15%; text-align: center; color: #6b7280;">x{{ $item->quantity }}</td>
                <td style="width: 25%; text-align: right; font-weight: 600;">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal, 2) }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    </div>
    
    <!-- Totals -->
    <div class="totals-box">
        @if(!isset($isPdf) || !$isPdf)
        <div class="total-line">
            <span>Subtotal:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
        </div>
        @if($totals['discountTotal'] > 0)
        <div class="total-line">
            <span>Discount:</span>
            <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
        </div>
        @endif
        @if($document->collectTax() && $totals['taxTotal'] > 0)
        <div class="total-line">
            <span>Tax (16%):</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
        </div>
        @endif
        <div class="grand-total-line">
            <span>TOTAL PAID:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
        </div>
        @else
        <table>
            <tr class="total-line">
                <td>Subtotal:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
            </tr>
            @if($totals['discountTotal'] > 0)
            <tr class="total-line">
                <td>Discount:</td>
                <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <tr class="total-line">
                <td>Tax (16%):</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total-line">
                <td>TOTAL PAID:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td>
            </tr>
        </table>
        @endif
    </div>
    
    @if($document->notes())
    <div class="notes">
        <div class="notes-title">Notes</div>
        <div class="notes-text">{{ $document->notes() }}</div>
    </div>
    @endif
    
    @if($document->paymentInstructions())
    <div class="notes">
        <div class="notes-title">Payment Instructions</div>
        <div class="notes-text">{{ $document->paymentInstructions() }}</div>
    </div>
    @endif
    
    @if($document->terms())
    <div class="notes">
        <div class="notes-title">Terms & Conditions</div>
        <div class="notes-text">{{ $document->terms() }}</div>
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer-note">
        This is an official receipt for payment received. Please keep for your records.
    </div>
    
    <div class="thank-you">* THANK YOU *</div>
    
    <div class="signature-line">
        @if($signaturePath)
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 180px; max-height: 60px; width: auto; height: auto;">
            </div>
        @endif
        <div class="sig-box">AUTHORIZED SIGNATURE</div>
    </div>
    
    @if($businessProfile->website())
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #d1d5db; font-size: 11px; color: #6b7280;">
        Visit us: <span style="color: #059669; font-weight: bold;">{{ $businessProfile->website() }}</span>
    </div>
    @endif
    </div>
</body>
</html>
