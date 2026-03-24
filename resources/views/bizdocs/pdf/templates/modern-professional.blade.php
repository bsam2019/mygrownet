<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; color: #1f2937; background: white; }
        
        .content-wrapper { padding: 40px; }
        
        .logo { width: 80px; height: 80px; color: white; border-radius: 12px; text-align: center; line-height: 80px; font-size: 40px; overflow: hidden; }
        .logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }
        .business-name { font-size: 28px; font-weight: bold; color: #2563eb; margin-top: 10px; }
        .business-details { font-size: 11px; color: #6b7280; line-height: 1.8; margin-top: 8px; }
        
        .doc-title { font-size: 36px; font-weight: bold; color: #2563eb; letter-spacing: 3px; margin-bottom: 10px; }
        .doc-meta { font-size: 13px; color: #6b7280; line-height: 1.8; }
        
        @if(!isset($isPdf) || !$isPdf)
        /* Modern CSS for HTML preview */
        .header { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px solid #2563eb; }
        .totals .total-row { display: flex; justify-content: space-between; padding: 12px 20px; font-size: 14px; border-bottom: 1px solid #e5e7eb; }
        .totals .grand-total { background: linear-gradient(135deg, #2563eb, #2563ebdd); color: white; font-weight: bold; font-size: 20px; border-radius: 8px; margin-top: 10px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        @else
        /* Table-based CSS for PDF */
        .header { width: 100%; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px solid #2563eb; }
        .totals table { width: 100%; }
        .totals .total-row td { padding: 12px 20px; font-size: 14px; border-bottom: 1px solid #e5e7eb; }
        .totals .grand-total td { background: #2563eb; color: white; font-weight: bold; font-size: 20px; padding: 12px 20px; }
        @endif
        
        .customer-box { background: #2563eb15; border-left: 5px solid #2563eb; padding: 20px; margin-bottom: 30px; border-radius: 0 8px 8px 0; }
        .customer-label { font-size: 10px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        .customer-name { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        .customer-details { font-size: 12px; color: #4b5563; line-height: 1.8; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #2563eb; color: white; padding: 15px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 15px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .text-right { text-align: right; }
        
        .totals { margin-left: auto; width: 350px; }
        
        .footer { margin-top: 50px; padding-top: 30px; border-top: 2px solid #e5e7eb; }
        .footer-section { margin-bottom: 25px; }
        .footer-title { font-size: 11px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .footer-content { font-size: 12px; color: #6b7280; line-height: 1.8; }
        .sig-box { width: 200px; text-align: center; }
        .sig-line { border-top: 2px solid #000; margin-top: 50px; padding-top: 8px; font-size: 11px; font-weight: 600; }
        
        .notes { margin-top: 40px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #2563eb; }
        .notes-title { font-size: 11px; font-weight: bold; color: #2563eb; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="content-wrapper">
    @if(!isset($isPdf) || !$isPdf)
    {{-- Grid layout for HTML preview --}}
    <div class="header">
        <div class="logo-section">
            <div class="logo">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                @else
                    {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                @endif
            </div>
            <div class="business-name">{{ $businessProfile->businessName() }}</div>
            <div class="business-details">
                {{ $businessProfile->address() }}<br>
                {{ $businessProfile->phone() }}<br>
                @if($businessProfile->email()){{ $businessProfile->email() }}<br>@endif
                @if($businessProfile->website()){{ $businessProfile->website() }}<br>@endif
                @if($businessProfile->tpin())TPIN: {{ $businessProfile->tpin() }}@endif
            </div>
        </div>
        <div class="doc-info" style="text-align: right;">
            <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
            <div class="doc-meta">
                <strong>{{ $document->number()->value() }}</strong><br>
                Date: {{ $document->issueDate()->format('d M Y') }}<br>
                @if($document->dueDate())Due: {{ $document->dueDate()->format('d M Y') }}@endif
            </div>
        </div>
    </div>
    @else
    {{-- Table layout for PDF --}}
    <table class="header">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                @if($logoPath)
                    <div style="width: 80px; height: 80px; background: white; border-radius: 12px; position: relative; margin-bottom: 10px;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <img src="{{ $logoPath }}" alt="Logo" style="max-width: 70px; max-height: 70px; width: auto; height: auto;">
                        </div>
                    </div>
                @else
                    <div class="logo">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
                @endif
                <div class="business-name">{{ $businessProfile->businessName() }}</div>
                <div class="business-details">
                    {{ $businessProfile->address() }}<br>
                    {{ $businessProfile->phone() }}<br>
                    @if($businessProfile->email()){{ $businessProfile->email() }}<br>@endif
                    @if($businessProfile->website()){{ $businessProfile->website() }}<br>@endif
                    @if($businessProfile->tpin())TPIN: {{ $businessProfile->tpin() }}@endif
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
                <div class="doc-meta">
                    <strong>{{ $document->number()->value() }}</strong><br>
                    Date: {{ $document->issueDate()->format('d M Y') }}<br>
                    @if($document->dueDate())Due: {{ $document->dueDate()->format('d M Y') }}@endif
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    <div class="customer-box">
        <div class="customer-label">Bill To</div>
        <div class="customer-name">{{ $customer->name() }}</div>
        <div class="customer-details">
            @if($customer->address()){{ $customer->address() }}<br>@endif
            @if($customer->phone()){{ $customer->phone() }}<br>@endif
            @if($customer->email()){{ $customer->email() }}@endif
        </div>
    </div>
    
    <table>
        <thead><tr><th>Description</th><th class="text-right">Dimensions</th><th class="text-right">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            @php
                $effectiveQty = ($item->dimensionsValue ?? 1) * $item->quantity;
                $lineTotal = $effectiveQty * ($item->unitPrice / 100);
            @endphp
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">
                    @if($item->dimensions !== null && $item->dimensions !== '')
                        {{ $item->dimensions }} <small style="color: #666;">({{ number_format($item->dimensionsValue, 2) }})</small>
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($item->unitPrice / 100, 2) }}</td>
                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        @if(!isset($isPdf) || !$isPdf)
        {{-- Flexbox layout for HTML preview --}}
        <div class="total-row"><span>Subtotal:</span><span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span></div>
        @if($totals['discountTotal'] > 0)
        <div class="total-row"><span>Discount:</span><span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span></div>
        @endif
        @if($document->collectTax() && $totals['taxTotal'] > 0)
        <div class="total-row"><span>Tax:</span><span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span></div>
        @endif
        <div class="total-row grand-total"><span>Total:</span><span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span></div>
        @else
        {{-- Table layout for PDF --}}
        <table>
            <tr class="total-row"><td style="text-align: left;">Subtotal:</td><td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td></tr>
            @if($totals['discountTotal'] > 0)
            <tr class="total-row"><td style="text-align: left;">Discount:</td><td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td></tr>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <tr class="total-row"><td style="text-align: left;">Tax:</td><td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td></tr>
            @endif
            <tr class="grand-total"><td style="text-align: left;">Total:</td><td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td></tr>
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
    
    <div class="footer">
        @if(!isset($isPdf) || !$isPdf)
        <div class="signatures">
            @if($signaturePath)
            <div class="sig-box">
                <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 180px; max-height: 60px;">
                <div class="sig-line">Authorized Signature</div>
            </div>
            @else
            <div class="sig-box"><div class="sig-line">Authorized Signature</div></div>
            @endif
        </div>
        @else
        <table style="width: 100%; margin-top: 60px;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    @if($signaturePath)
                    <div style="text-align: center; margin-bottom: 10px;">
                        <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 180px; max-height: 60px; width: auto; height: auto;">
                    </div>
                    @endif
                    <div style="border-top: 2px solid #000; padding-top: 8px; font-size: 11px; font-weight: 600;">
                        Authorized Signature
                    </div>
                </td>
            </tr>
        </table>
        @endif
    </div>
    
    @if($businessProfile->website())
    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #e5e7eb; font-size: 12px; color: #6b7280;">
        Visit us: <span style="color: #2563eb; font-weight: bold;">{{ $businessProfile->website() }}</span>
    </div>
    @endif
    </div>
</body>
</html>
