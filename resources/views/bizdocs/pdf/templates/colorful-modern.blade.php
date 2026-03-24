<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Colorful Modern</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #1f2937; padding: 0; background: white; font-size: 13px; }
        
        .top-banner { background: #f59e0b; color: white; padding: 30px 40px; }
        .logo-badge { width: 90px; height: 90px; background: white; color: #f59e0b; border-radius: 50%; text-align: center; line-height: 90px; font-size: 42px; margin-bottom: 15px; }
        .banner-info h1 { font-size: 28px; font-weight: bold; margin-bottom: 8px; }
        .banner-info p { font-size: 12px; opacity: 0.95; line-height: 1.6; }
        .doc-type-badge { background: white; color: #f59e0b; padding: 12px 25px; border-radius: 25px; font-size: 22px; font-weight: bold; display: inline-block; margin-bottom: 10px; }
        .doc-number { font-size: 14px; font-weight: 600; }
        .content-area { padding: 40px; }
        
        @if(!isset($isPdf) || !$isPdf)
        .top-banner { display: flex; justify-content: space-between; align-items: center; }
        .banner-left { display: flex; align-items: center; gap: 20px; }
        .info-cards { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 35px; }
        .total-row { display: flex; justify-content: space-between; padding: 12px 0; font-size: 14px; border-bottom: 1px solid #e5e7eb; }
        .grand-total-row { display: flex; justify-content: space-between; padding: 20px; font-size: 22px; font-weight: bold; background: #f59e0b15; color: #f59e0b; border-radius: 8px; margin-top: 15px; }
        @else
        .top-banner table { width: 100%; }
        .info-cards { width: 100%; margin-bottom: 35px; }
        .totals-body table { width: 100%; }
        .total-row td { padding: 12px 0; font-size: 14px; border-bottom: 1px solid #e5e7eb; }
        .grand-total-row td { padding: 20px; font-size: 22px; font-weight: bold; background: #f59e0b15; color: #f59e0b; }
        @endif
        
        .info-card { background: #f9fafb; padding: 20px; border-radius: 12px; border-left: 4px solid #f59e0b; }
        .card-icon { font-size: 24px; margin-bottom: 10px; }
        .card-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; margin-bottom: 8px; }
        .card-value { font-size: 14px; font-weight: bold; color: #111827; margin-bottom: 5px; }
        .card-details { font-size: 11px; color: #6b7280; line-height: 1.6; }
        
        .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 30px; border-radius: 12px; overflow: hidden; }
        .modern-table thead { background: #f59e0b; }
        .modern-table th { color: white; padding: 16px 14px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; }
        .modern-table td { padding: 16px 14px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .modern-table tbody tr:nth-child(even) { background: #fafafa; }
        .modern-table tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        
        .totals-card { max-width: 450px; margin-left: auto; background: #f9fafb; border-radius: 12px; overflow: hidden; margin-bottom: 35px; }
        .totals-header { background: #f59e0b; color: white; padding: 15px 20px; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .totals-body { padding: 20px; }
        .total-row:last-child { border-bottom: none; }
        
        .notes-modern { background: #fef3c7; padding: 20px; border-radius: 12px; border-left: 4px solid #f59e0b; margin-bottom: 25px; }
        .notes-icon { font-size: 20px; margin-bottom: 10px; }
        .notes-title { font-size: 12px; font-weight: bold; color: #92400e; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #78350f; line-height: 1.8; }
        
        .footer-signature { margin-top: 50px; padding: 30px; background: #f9fafb; border-radius: 12px; text-align: center; }
        .sig-line-modern { display: inline-block; width: 250px; border-top: 3px solid #f59e0b; padding-top: 10px; font-size: 12px; font-weight: bold; color: #4b5563; margin-top: 50px; }
        .thank-you-badge { display: inline-block; background: #f59e0b; color: white; padding: 10px 30px; border-radius: 25px; font-size: 14px; font-weight: bold; margin-top: 20px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="top-banner">
        @if(!isset($isPdf) || !$isPdf)
        <div class="banner-left">
            <div class="logo-badge">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                @else
                    {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                @endif
            </div>
            <div class="banner-info">
                <h1>{{ $businessProfile->businessName() }}</h1>
                <p>
                    {{ $businessProfile->address() }}<br>
                    {{ $businessProfile->phone() }}
                    @if($businessProfile->email()) | {{ $businessProfile->email() }}@endif
                    @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
                </p>
            </div>
        </div>
        <div class="banner-right" style="text-align: right;">
            <div class="doc-type-badge">{{ strtoupper($document->type()->value()) }}</div>
            <div class="doc-number">#{{ $document->number()->value() }}</div>
        </div>
        @else
        <table>
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <table><tr>
                        <td style="width: 110px; vertical-align: top;">
                            @if($logoPath)
                                <div style="width: 90px; height: 90px; background: white; border-radius: 50%; position: relative;">
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                        <img src="{{ $logoPath }}" alt="Logo" style="max-width: 80px; max-height: 80px; width: auto; height: auto;">
                                    </div>
                                </div>
                            @else
                                <div class="logo-badge">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
                            @endif
                        </td>
                        <td style="vertical-align: top; padding-left: 15px;">
                            <div class="banner-info">
                                <h1>{{ $businessProfile->businessName() }}</h1>
                                <p>
                                    {{ $businessProfile->address() }}<br>
                                    {{ $businessProfile->phone() }}
                                    @if($businessProfile->email()) | {{ $businessProfile->email() }}@endif
                                    @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
                                </p>
                            </div>
                        </td>
                    </tr></table>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;">
                    <div class="doc-type-badge">{{ strtoupper($document->type()->value()) }}</div>
                    <div class="doc-number">#{{ $document->number()->value() }}</div>
                </td>
            </tr>
        </table>
        @endif
    </div>
    
    <div class="content-area">
        @if(!isset($isPdf) || !$isPdf)
        <div class="info-cards">
            <div class="info-card">
                <div class="card-icon">👤</div>
                <div class="card-label">Customer</div>
                <div class="card-value">{{ $customer->name() }}</div>
                <div class="card-details">
                    @if($customer->phone()){{ $customer->phone() }}<br>@endif
                    @if($customer->email()){{ $customer->email() }}@endif
                </div>
            </div>
            <div class="info-card">
                <div class="card-icon">📅</div>
                <div class="card-label">Issue Date</div>
                <div class="card-value">{{ $document->issueDate()->format('d M Y') }}</div>
                <div class="card-details">
                    @if($document->dueDate())Due: {{ $document->dueDate()->format('d M Y') }}@else Payment on receipt@endif
                </div>
            </div>
            <div class="info-card">
                <div class="card-icon">💰</div>
                <div class="card-label">Currency</div>
                <div class="card-value">{{ $businessProfile->defaultCurrency() }}</div>
                <div class="card-details">
                    @if($businessProfile->tpin())TPIN: {{ $businessProfile->tpin() }}@endif
                </div>
            </div>
        </div>
        @else
        <table class="info-cards">
            <tr>
                <td style="width: 32%; vertical-align: top; padding-right: 10px;">
                    <div class="info-card">
                        <div class="card-icon">👤</div>
                        <div class="card-label">Customer</div>
                        <div class="card-value">{{ $customer->name() }}</div>
                        <div class="card-details">
                            @if($customer->phone()){{ $customer->phone() }}<br>@endif
                            @if($customer->email()){{ $customer->email() }}@endif
                        </div>
                    </div>
                </td>
                <td style="width: 2%;"></td>
                <td style="width: 32%; vertical-align: top; padding: 0 5px;">
                    <div class="info-card">
                        <div class="card-icon">📅</div>
                        <div class="card-label">Issue Date</div>
                        <div class="card-value">{{ $document->issueDate()->format('d M Y') }}</div>
                        <div class="card-details">
                            @if($document->dueDate())Due: {{ $document->dueDate()->format('d M Y') }}@else Payment on receipt@endif
                        </div>
                    </div>
                </td>
                <td style="width: 2%;"></td>
                <td style="width: 32%; vertical-align: top; padding-left: 10px;">
                    <div class="info-card">
                        <div class="card-icon">💰</div>
                        <div class="card-label">Currency</div>
                        <div class="card-value">{{ $businessProfile->defaultCurrency() }}</div>
                        <div class="card-details">
                            @if($businessProfile->tpin())TPIN: {{ $businessProfile->tpin() }}@endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        @endif
        
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="text-right" style="width: 100px;">Dimensions</th>
                    <th class="text-right" style="width: 70px;">Qty</th>
                    <th class="text-right" style="width: 110px;">Unit Price</th>
                    <th class="text-right" style="width: 70px;">Tax</th>
                    <th class="text-right" style="width: 120px;">Amount</th>
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
                    <td class="text-right">{{ $item->taxRate }}%</td>
                    <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals-card">
            <div class="totals-header">Payment Summary</div>
            <div class="totals-body">
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
                <div class="grand-total-row">
                    <span>TOTAL:</span>
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
                    <tr class="grand-total-row">
                        <td>TOTAL:</td>
                        <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td>
                    </tr>
                </table>
                @endif
            </div>
        </div>
        
        @if($document->notes())
        <div class="notes-modern">
            <div class="notes-icon">📝</div>
            <div class="notes-title">Notes</div>
            <div class="notes-text">{{ $document->notes() }}</div>
        </div>
        @endif
        
        @if($document->paymentInstructions())
        <div class="notes-modern">
            <div class="notes-icon">💳</div>
            <div class="notes-title">Payment Instructions</div>
            <div class="notes-text">{{ $document->paymentInstructions() }}</div>
        </div>
        @endif
        
        @if($document->terms())
        <div class="notes-modern">
            <div class="notes-icon">📋</div>
            <div class="notes-title">Terms & Conditions</div>
            <div class="notes-text">{{ $document->terms() }}</div>
        </div>
        @endif
        
        <div class="footer-signature">
            @if($signaturePath)
                <div style="text-align: center; margin-bottom: 10px;">
                    <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 200px; max-height: 70px; width: auto; height: auto;">
                </div>
            @endif
            <div class="sig-line-modern">Authorized Signature</div>
            <div class="thank-you-badge">★ THANK YOU FOR YOUR BUSINESS ★</div>
        </div>
    </div>
</body>
</html>
