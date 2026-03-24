<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #1f2937; background: white; }
        
        .content-wrapper { padding: 60px 40px; max-width: 700px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 50px; padding-bottom: 30px; border-bottom: 1px solid #e5e7eb; }
        .logo { width: 70px; height: 70px; background: #2563eb; color: white; border-radius: 8px; text-align: center; line-height: 70px; font-size: 35px; margin: 0 auto 15px; }
        .business-name { font-size: 26px; font-weight: 300; color: #1f2937; margin-bottom: 8px; letter-spacing: 1px; }
        .business-details { font-size: 11px; color: #9ca3af; line-height: 1.6; }
        .doc-header { text-align: center; margin-bottom: 40px; }
        .doc-title { font-size: 28px; font-weight: 300; color: #2563eb; letter-spacing: 4px; margin-bottom: 15px; }
        .doc-meta { font-size: 12px; color: #6b7280; }
        .customer-section { text-align: center; padding: 25px; background: #f9fafb; border-radius: 8px; margin-bottom: 40px; }
        .customer-label { font-size: 10px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; }
        .customer-name { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        .customer-details { font-size: 12px; color: #6b7280; line-height: 1.8; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { padding: 12px 15px; text-align: left; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #9ca3af; letter-spacing: 1.5px; border-bottom: 2px solid #e5e7eb; }
        td { padding: 18px 15px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; }
        .text-right { text-align: right; }
        
        @if(!isset($isPdf) || !$isPdf)
        .totals .total-row { display: flex; justify-content: space-between; padding: 10px 20px; font-size: 13px; color: #6b7280; }
        .totals .grand-total { background: #2563eb; color: white; font-weight: 600; font-size: 20px; padding: 20px; border-radius: 8px; margin-top: 15px; }
        @else
        .totals table { width: 100%; }
        .totals .total-row td { padding: 10px 20px; font-size: 13px; color: #6b7280; }
        .totals .grand-total td { background: #2563eb; color: white; font-weight: 600; font-size: 20px; padding: 20px; }
        @endif
        
        .totals { max-width: 400px; margin: 0 auto 40px; }
        .total-row:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
        .footer { text-align: center; margin-top: 60px; padding-top: 30px; border-top: 1px solid #e5e7eb; }
        .footer-text { font-size: 11px; color: #9ca3af; line-height: 1.8; max-width: 500px; margin: 0 auto 30px; }
        .signature { margin-top: 50px; }
        .sig-line { display: inline-block; width: 200px; border-top: 1px solid #000; padding-top: 8px; font-size: 10px; font-weight: 600; color: #6b7280; }
        
        .notes { margin-top: 20px; padding: 20px; background: #f9fafb; border-radius: 8px; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto; }
        .notes-title { font-size: 10px; font-weight: bold; color: #2563eb; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="header">
            @if($logoPath)
                <div style="width: 70px; height: 70px; margin: 0 auto 15px; position: relative;">
                    @if(!isset($isPdf) || !$isPdf)
                        <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                    @else
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <img src="{{ $logoPath }}" alt="Logo" style="max-width: 65px; max-height: 65px; width: auto; height: auto;">
                        </div>
                    @endif
                </div>
            @else
                <div class="logo">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
            @endif
            <div class="business-name">{{ $businessProfile->businessName() }}</div>
            <div class="business-details">
                {{ $businessProfile->address() }} • {{ $businessProfile->phone() }}
                @if($businessProfile->email()) • {{ $businessProfile->email() }}@endif
                @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
            </div>
        </div>
        
        <div class="doc-header">
            <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
            <div class="doc-meta">
                <strong>{{ $document->number()->value() }}</strong> • 
                {{ $document->issueDate()->format('F d, Y') }}
                @if($document->dueDate()) • Due: {{ $document->dueDate()->format('M d, Y') }}@endif
            </div>
        </div>
        
        <div class="customer-section">
            <div class="customer-label">Bill To</div>
            <div class="customer-name">{{ $customer->name() }}</div>
            <div class="customer-details">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone()){{ $customer->phone() }}@endif
                @if($customer->email()) • {{ $customer->email() }}@endif
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Item Description</th>
                    <th class="text-right" style="width: 15%;">Dimensions</th>
                    <th class="text-right" style="width: 12%;">Qty</th>
                    <th class="text-right" style="width: 18%;">Price</th>
                    <th class="text-right" style="width: 15%;">Total</th>
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
        
        <div class="totals">
            @if(!isset($isPdf) || !$isPdf)
            <div class="total-row">
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
                <span>Tax</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>Total</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
            </div>
            @else
            <table>
                <tr class="total-row">
                    <td>Subtotal</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
                </tr>
                @if($totals['discountTotal'] > 0)
                <tr class="total-row">
                    <td>Discount</td>
                    <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
                </tr>
                @endif
                @if($document->collectTax() && $totals['taxTotal'] > 0)
                <tr class="total-row">
                    <td>Tax</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>Total</td>
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
        
        <div class="footer">
            <div class="footer-text">
                Thank you for your business. Payment is due within 30 days. 
                Please include the invoice number with your payment.
            </div>
            <div class="signature">
                @if($signaturePath)
                    <div style="text-align: center; margin-bottom: 10px;">
                        <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 180px; max-height: 60px; width: auto; height: auto;">
                    </div>
                @endif
                <div class="sig-line">Authorized Signature</div>
            </div>
        </div>
    </div>
</body>
</html>
