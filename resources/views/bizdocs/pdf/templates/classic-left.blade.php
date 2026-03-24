<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; color: #1f2937; background: white; }
        
        .content-wrapper { padding: 40px; max-width: 800px; margin: 0 auto; }
        .logo { width: 100px; height: 100px; background: #2563eb; color: white; border-radius: 50%; text-align: center; line-height: 100px; font-size: 50px; margin-bottom: 20px; }
        .business-name { font-size: 24px; font-weight: bold; color: #1f2937; margin-bottom: 10px; }
        .business-details { font-size: 11px; color: #6b7280; line-height: 1.8; }
        .right-section { text-align: right; background: #2563eb; color: white; padding: 30px; border-radius: 10px; min-width: 280px; }
        .doc-title { font-size: 32px; font-weight: bold; margin-bottom: 15px; letter-spacing: 2px; }
        .doc-number { font-size: 16px; margin-bottom: 5px; opacity: 0.9; }
        .doc-date { font-size: 13px; opacity: 0.8; }
        
        @if(!isset($isPdf) || !$isPdf)
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 50px; }
        .left-section { flex: 1; }
        .info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px; }
        .total-row { display: flex; justify-content: space-between; padding: 12px 20px; font-size: 14px; background: white; }
        .grand-total { background: #2563eb; color: white; font-weight: bold; font-size: 18px; padding: 18px 20px; }
        .footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px; }
        @else
        .header { width: 100%; margin-bottom: 50px; }
        .info-row { width: 100%; margin-bottom: 40px; }
        .totals-box table { width: 100%; }
        .total-row td { padding: 12px 20px; font-size: 14px; background: white; }
        .grand-total td { background: #2563eb; color: white; font-weight: bold; font-size: 18px; padding: 18px 20px; }
        .footer-grid { width: 100%; margin-bottom: 40px; }
        @endif
        
        .info-box { border: 2px solid #e5e7eb; padding: 20px; border-radius: 8px; }
        .info-label { font-size: 10px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; }
        .info-name { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
        .info-details { font-size: 12px; color: #6b7280; line-height: 1.8; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        thead { background: #f9fafb; border-top: 3px solid #2563eb; border-bottom: 3px solid #2563eb; }
        th { padding: 15px 12px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #374151; letter-spacing: 1px; }
        td { padding: 15px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .text-right { text-align: right; }
        
        .totals-box { margin-left: auto; width: 350px; border: 2px solid #2563eb; border-radius: 10px; overflow: hidden; }
        .total-row:not(:last-child) { border-bottom: 1px solid #e5e7eb; }
        
        .footer { margin-top: 60px; padding-top: 30px; border-top: 3px double #e5e7eb; }
        .footer-section { padding: 15px; background: #f9fafb; border-radius: 8px; }
        .footer-title { font-size: 12px; font-weight: bold; color: #2563eb; margin-bottom: 10px; }
        .footer-content { font-size: 11px; color: #6b7280; line-height: 1.8; }
        .signature-area { text-align: center; margin-top: 50px; }
        .sig-line { display: inline-block; width: 250px; border-top: 2px solid #000; padding-top: 10px; font-size: 11px; font-weight: 600; }
        
        .notes { margin-top: 20px; padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #2563eb; }
        .notes-title { font-size: 11px; font-weight: bold; color: #2563eb; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="content-wrapper">
    @if(!isset($isPdf) || !$isPdf)
    <div class="header">
        <div class="left-section">
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
                Phone: {{ $businessProfile->phone() }}<br>
                @if($businessProfile->email())Email: {{ $businessProfile->email() }}<br>@endif
                @if($businessProfile->website())Web: {{ $businessProfile->website() }}<br>@endif
                @if($businessProfile->tpin())TPIN: {{ $businessProfile->tpin() }}@endif
            </div>
        </div>
        <div class="right-section">
            <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
            <div class="doc-number">#{{ $document->number()->value() }}</div>
            <div class="doc-date">{{ $document->issueDate()->format('F d, Y') }}</div>
            @if($document->dueDate())
            <div class="doc-date" style="margin-top: 10px;">Due: {{ $document->dueDate()->format('F d, Y') }}</div>
            @endif
        </div>
    </div>
    @else
    <table class="header">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                @if($logoPath)
                    <div style="width: 100px; height: 100px; background: white; border-radius: 50%; position: relative; margin-bottom: 20px;">
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <img src="{{ $logoPath }}" alt="Logo" style="max-width: 90px; max-height: 90px; width: auto; height: auto;">
                        </div>
                    </div>
                @else
                    <div class="logo">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
                @endif
                <div class="business-name">{{ $businessProfile->businessName() }}</div>
                <div class="business-details">
                    {{ $businessProfile->address() }}<br>
                    Phone: {{ $businessProfile->phone() }}<br>
                    @if($businessProfile->email())Email: {{ $businessProfile->email() }}<br>@endif
                    @if($businessProfile->website())Web: {{ $businessProfile->website() }}<br>@endif
                    @if($businessProfile->tpin())TPIN: {{ $businessProfile->tpin() }}@endif
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <div class="right-section">
                    <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
                    <div class="doc-number">#{{ $document->number()->value() }}</div>
                    <div class="doc-date">{{ $document->issueDate()->format('F d, Y') }}</div>
                    @if($document->dueDate())
                    <div class="doc-date" style="margin-top: 10px;">Due: {{ $document->dueDate()->format('F d, Y') }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    @if(!isset($isPdf) || !$isPdf)
    <div class="info-row">
        <div class="info-box">
            <div class="info-label">Bill To</div>
            <div class="info-name">{{ $customer->name() }}</div>
            <div class="info-details">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                @if($customer->email())Email: {{ $customer->email() }}@endif
            </div>
        </div>
        <div class="info-box">
            <div class="info-label">Payment Details</div>
            <div class="info-details">
                <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                @if($businessProfile->tpin())<strong>TPIN:</strong> {{ $businessProfile->tpin() }}@endif
            </div>
        </div>
    </div>
    @else
    <table class="info-row">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                <div class="info-box">
                    <div class="info-label">Bill To</div>
                    <div class="info-name">{{ $customer->name() }}</div>
                    <div class="info-details">
                        @if($customer->address()){{ $customer->address() }}<br>@endif
                        @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                        @if($customer->email())Email: {{ $customer->email() }}@endif
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top; padding-left: 15px;">
                <div class="info-box">
                    <div class="info-label">Payment Details</div>
                    <div class="info-details">
                        <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                        @if($businessProfile->tpin())<strong>TPIN:</strong> {{ $businessProfile->tpin() }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Description</th>
                <th class="text-right" style="width: 15%;">Dimensions</th>
                <th class="text-right" style="width: 12%;">Quantity</th>
                <th class="text-right" style="width: 18%;">Unit Price</th>
                <th class="text-right" style="width: 15%;">Amount</th>
            </tr>
        </thead>
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
                        {{ $item->dimensions }} <small style="color: #666; font-size: 9px;">({{ number_format($item->dimensionsValue, 2) }})</small>
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
    
    <div class="totals-box">
        @if(!isset($isPdf) || !$isPdf)
        <div class="total-row">
            <span>Subtotal:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
        </div>
        @if($totals['discountTotal'] > 0)
        <div class="total-row">
            <span>Discount:</span>
            <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
        </div>
        @endif
        @if($document->collectTax() && $totals['taxTotal'] > 0)
        <div class="total-row">
            <span>Tax (16%):</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span>TOTAL DUE:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
        </div>
        @else
        <table>
            <tr class="total-row">
                <td>Subtotal:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
            </tr>
            @if($totals['discountTotal'] > 0)
            <tr class="total-row">
                <td>Discount:</td>
                <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <tr class="total-row">
                <td>Tax (16%):</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>TOTAL DUE:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td>
            </tr>
        </table>
        @endif
    </div>
    
    @if($document->paymentInstructions() || $document->notes())
    <div class="notes">
        <div class="notes-title">Payment Information</div>
        <div class="notes-text">
            {{ $document->paymentInstructions() ?: $document->notes() }}
        </div>
    </div>
    @endif
    
    <div class="footer">
        @if(!isset($isPdf) || !$isPdf)
        <div class="footer-grid">
            <div class="footer-section">
                <div class="footer-title">Payment Terms</div>
                <div class="footer-content">
                    Payment is due within 30 days of invoice date. Late payments may incur additional charges.
                </div>
            </div>
            <div class="footer-section">
                <div class="footer-title">Notes</div>
                <div class="footer-content">
                    Thank you for your business. We appreciate your continued partnership.
                </div>
            </div>
        </div>
        @else
        <table class="footer-grid">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                    <div class="footer-section">
                        <div class="footer-title">Payment Terms</div>
                        <div class="footer-content">
                            Payment is due within 30 days of invoice date. Late payments may incur additional charges.
                        </div>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top; padding-left: 15px;">
                    <div class="footer-section">
                        <div class="footer-title">Notes</div>
                        <div class="footer-content">
                            Thank you for your business. We appreciate your continued partnership.
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        @endif
        <div class="signature-area">
            @if($signaturePath)
                <div style="text-align: center; margin-bottom: 10px;">
                    <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 180px; max-height: 60px; width: auto; height: auto;">
                </div>
            @endif
            <div class="sig-line">Authorized Signature</div>
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
