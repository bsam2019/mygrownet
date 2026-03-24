<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; color: #1f2937; background: white; padding: 0; }
        
        .header-bar { background: #2563eb; color: white; padding: 40px 50px; margin-bottom: 0; }
        .logo { width: 80px; height: 80px; background: white; color: #2563eb; border-radius: 12px; text-align: center; line-height: 80px; font-size: 40px; }
        .business-name { font-size: 28px; font-weight: bold; margin-bottom: 8px; }
        .business-details { font-size: 12px; opacity: 0.9; line-height: 1.6; }
        .doc-title { font-size: 36px; font-weight: bold; margin-bottom: 10px; letter-spacing: 2px; }
        .doc-meta { font-size: 14px; opacity: 0.9; }
        
        @if(!isset($isPdf) || !$isPdf)
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo-business { display: flex; align-items: center; gap: 20px; }
        .totals-grid { display: grid; grid-template-columns: 1fr 300px; gap: 30px; align-items: end; }
        .total-row { display: flex; justify-content: space-between; padding: 14px 20px; font-size: 14px; }
        .grand-total { background: #2563eb; color: white; font-weight: bold; font-size: 22px; padding: 20px; }
        @else
        .header-content { width: 100%; }
        .totals-grid { width: 100%; }
        .totals table { width: 100%; }
        .total-row td { padding: 14px 20px; font-size: 14px; }
        .grand-total td { background: #2563eb; color: white; font-weight: bold; font-size: 22px; padding: 20px; }
        @endif
        
        .content { padding: 40px 50px; }
        .customer-box { background: #2563eb10; border-left: 6px solid #2563eb; padding: 25px; margin-bottom: 35px; }
        .customer-label { font-size: 11px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; }
        .customer-name { font-size: 20px; font-weight: bold; margin-bottom: 8px; }
        .customer-details { font-size: 13px; color: #4b5563; line-height: 1.8; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        thead { background: #1f2937; color: white; }
        th { padding: 16px 15px; text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 16px 15px; border-bottom: 2px solid #f3f4f6; font-size: 14px; }
        .text-right { text-align: right; }
        
        .totals-section { background: #f9fafb; padding: 30px; border-radius: 12px; margin-bottom: 40px; }
        .payment-info { font-size: 12px; color: #6b7280; line-height: 1.8; }
        .payment-info strong { color: #1f2937; }
        .totals { background: white; border-radius: 8px; overflow: hidden; }
        .total-row:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
        
        .footer { background: #f9fafb; padding: 30px 50px; margin-top: 40px; }
        .footer-content { font-size: 11px; color: #6b7280; line-height: 1.8; text-align: center; }
        
        .notes { margin-top: 20px; padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #2563eb; }
        .notes-title { font-size: 11px; font-weight: bold; color: #2563eb; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="header-bar">
        @if(!isset($isPdf) || !$isPdf)
        <div class="header-content">
            <div class="logo-business">
                <div class="logo">
                    @if($logoPath)
                        <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                    @else
                        {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                    @endif
                </div>
                <div class="business-info">
                    <div class="business-name">{{ $businessProfile->businessName() }}</div>
                    <div class="business-details">
                        {{ $businessProfile->address() }}<br>
                        {{ $businessProfile->phone() }}
                        @if($businessProfile->email()) • {{ $businessProfile->email() }}@endif
                    </div>
                </div>
            </div>
            <div class="doc-info" style="text-align: right;">
                <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
                <div class="doc-meta">
                    {{ $document->number()->value() }}<br>
                    {{ $document->issueDate()->format('F d, Y') }}
                </div>
            </div>
        </div>
        @else
        <table class="header-content">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <table><tr>
                        <td style="width: 100px; vertical-align: top;">
                            @if($logoPath)
                                <div style="width: 80px; height: 80px; background: white; border-radius: 12px; position: relative;">
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                        <img src="{{ $logoPath }}" alt="Logo" style="max-width: 70px; max-height: 70px; width: auto; height: auto;">
                                    </div>
                                </div>
                            @else
                                <div class="logo">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
                            @endif
                        </td>
                        <td style="vertical-align: top; padding-left: 20px;">
                            <div class="business-name">{{ $businessProfile->businessName() }}</div>
                            <div class="business-details">
                                {{ $businessProfile->address() }}<br>
                                {{ $businessProfile->phone() }}
                                @if($businessProfile->email()) • {{ $businessProfile->email() }}@endif
                            </div>
                        </td>
                    </tr></table>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
                    <div class="doc-meta">
                        {{ $document->number()->value() }}<br>
                        {{ $document->issueDate()->format('F d, Y') }}
                    </div>
                </td>
            </tr>
        </table>
        @endif
    </div>
    
    <div class="content">
        <div class="customer-box">
            <div class="customer-label">Bill To</div>
            <div class="customer-name">{{ $customer->name() }}</div>
            <div class="customer-details">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                @if($customer->email())Email: {{ $customer->email() }}@endif
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 35%;">Description</th>
                    <th class="text-right" style="width: 15%;">Dimensions</th>
                    <th class="text-right" style="width: 12%;">Quantity</th>
                    <th class="text-right" style="width: 18%;">Unit Price</th>
                    <th class="text-right" style="width: 20%;">Amount</th>
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
                    <td class="text-right"><strong>{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals-section">
            @if(!isset($isPdf) || !$isPdf)
            <div class="totals-grid">
                <div class="payment-info">
                    <strong>Payment Information:</strong><br>
                    Currency: {{ $businessProfile->defaultCurrency() }}<br>
                    @if($document->dueDate())<strong>Due Date:</strong> {{ $document->dueDate()->format('F d, Y') }}@endif
                </div>
                <div class="totals">
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
                        <span>Tax:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
                    </div>
                    @endif
                    <div class="total-row grand-total">
                        <span>TOTAL:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
                    </div>
                </div>
            </div>
            @else
            <table class="totals-grid">
                <tr>
                    <td style="width: 60%; vertical-align: bottom;">
                        <div class="payment-info">
                            <strong>Payment Information:</strong><br>
                            Currency: {{ $businessProfile->defaultCurrency() }}<br>
                            @if($document->dueDate())<strong>Due Date:</strong> {{ $document->dueDate()->format('F d, Y') }}@endif
                        </div>
                    </td>
                    <td style="width: 40%; vertical-align: bottom;">
                        <div class="totals">
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
                                    <td>Tax:</td>
                                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="grand-total">
                                    <td>TOTAL:</td>
                                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>
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
    </div>
    
    <div class="footer">
        <div class="footer-content">
            Thank you for your business! Payment is due within 30 days.<br>
            Please include invoice number {{ $document->number()->value() }} with your payment.
        </div>
    </div>
</body>
</html>
