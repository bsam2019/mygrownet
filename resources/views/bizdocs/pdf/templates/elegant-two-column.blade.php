<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Elegant Two Column</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; color: #1f2937; background: white; font-size: 13px; }
        
        .content-wrapper { padding: 40px; }
        .logo-circle { width: 70px; height: 70px; border-radius: 50%; background: #2563eb; color: white; text-align: center; line-height: 70px; font-size: 32px; }
        .business-name { font-size: 20px; font-weight: bold; color: #111827; margin-bottom: 5px; }
        .business-details { font-size: 11px; color: #6b7280; line-height: 1.7; }
        .document-info-box { background: #2563eb15; padding: 20px; border-radius: 8px; border-left: 4px solid #2563eb; }
        .doc-title { font-size: 28px; font-weight: bold; color: #2563eb; margin-bottom: 15px; letter-spacing: 1px; }
        .doc-meta { font-size: 12px; color: #4b5563; line-height: 1.8; }
        .doc-meta strong { color: #111827; }
        
        @if(!isset($isPdf) || !$isPdf)
        .header-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px double #2563eb; }
        .logo-business { display: flex; align-items: center; gap: 15px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 35px; }
        .totals-row { display: flex; justify-content: space-between; padding: 12px 20px; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
        .totals-row.grand-total { background: #2563eb; color: white; font-size: 18px; font-weight: bold; padding: 18px 20px; }
        .footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 40px; padding-top: 25px; border-top: 2px solid #e5e7eb; }
        @else
        .header-grid { width: 100%; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px double #2563eb; }
        .info-grid { width: 100%; margin-bottom: 35px; }
        .totals-container table { width: 100%; }
        .totals-row td { padding: 12px 20px; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
        .totals-row.grand-total td { background: #2563eb; color: white; font-size: 18px; font-weight: bold; padding: 18px 20px; }
        .footer-grid { width: 100%; margin-top: 40px; padding-top: 25px; border-top: 2px solid #e5e7eb; }
        @endif
        
        .info-box { padding: 20px; background: #f9fafb; border-radius: 8px; border-top: 3px solid #2563eb; }
        .info-label { font-size: 10px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; }
        .info-name { font-size: 16px; font-weight: bold; margin-bottom: 8px; color: #111827; }
        .info-details { font-size: 12px; color: #6b7280; line-height: 1.7; }
        .items-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 30px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .items-table thead { background: #2563eb; }
        .items-table th { color: white; padding: 14px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; }
        .items-table td { padding: 14px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .totals-container { max-width: 400px; margin-left: auto; border: 2px solid #2563eb; border-radius: 8px; overflow: hidden; margin-bottom: 40px; }
        .totals-row:last-child { border-bottom: none; }
        .footer-box { padding: 15px; background: #fafafa; border-radius: 6px; border-left: 3px solid #2563eb; }
        .footer-title { font-size: 11px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .footer-content { font-size: 12px; color: #6b7280; line-height: 1.7; }
        .signature-area { margin-top: 50px; text-align: center; }
        .sig-line { display: inline-block; width: 250px; border-top: 2px solid #111827; padding-top: 8px; font-size: 11px; font-weight: 600; margin-top: 50px; }
        
        .notes { margin-top: 20px; padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #2563eb; }
        .notes-title { font-size: 11px; font-weight: bold; color: #2563eb; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="content-wrapper">
    @if(!isset($isPdf) || !$isPdf)
    <div class="header-grid">
        <div class="logo-business">
            <div class="logo-circle">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                @else
                    {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                @endif
            </div>
            <div>
                <div class="business-name">{{ $businessProfile->businessName() }}</div>
                <div class="business-details">
                    {{ $businessProfile->address() }}<br>
                    {{ $businessProfile->phone() }}
                    @if($businessProfile->email())<br>{{ $businessProfile->email() }}@endif
                    @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
                </div>
            </div>
        </div>
        <div class="document-info-box">
            <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
            <div class="doc-meta">
                <strong>Number:</strong> {{ $document->number()->value() }}<br>
                <strong>Date:</strong> {{ $document->issueDate()->format('d M Y') }}
                @if($document->dueDate())<br><strong>Due:</strong> {{ $document->dueDate()->format('d M Y') }}@endif
            </div>
        </div>
    </div>
    @else
    <table class="header-grid">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table><tr>
                    <td style="width: 90px; vertical-align: top;">
                        @if($logoPath)
                            <div style="width: 70px; height: 70px; border-radius: 50%; background: white; position: relative;">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 65px; max-height: 65px; width: auto; height: auto;">
                                </div>
                            </div>
                        @else
                            <div class="logo-circle">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
                        @endif
                    </td>
                    <td style="vertical-align: top; padding-left: 15px;">
                        <div class="business-name">{{ $businessProfile->businessName() }}</div>
                        <div class="business-details">
                            {{ $businessProfile->address() }}<br>
                            {{ $businessProfile->phone() }}
                            @if($businessProfile->email())<br>{{ $businessProfile->email() }}@endif
                            @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
                        </div>
                    </td>
                </tr></table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <div class="document-info-box">
                    <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
                    <div class="doc-meta">
                        <strong>Number:</strong> {{ $document->number()->value() }}<br>
                        <strong>Date:</strong> {{ $document->issueDate()->format('d M Y') }}
                        @if($document->dueDate())<br><strong>Due:</strong> {{ $document->dueDate()->format('d M Y') }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    @if(!isset($isPdf) || !$isPdf)
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Bill To</div>
            <div class="info-name">{{ $customer->name() }}</div>
            <div class="info-details">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone()){{ $customer->phone() }}<br>@endif
                @if($customer->email()){{ $customer->email() }}@endif
            </div>
        </div>
        <div class="info-box">
            <div class="info-label">Payment Information</div>
            <div class="info-details">
                <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                <strong>Payment Terms:</strong> Net 30<br>
                @if($businessProfile->tpin())<strong>TPIN:</strong> {{ $businessProfile->tpin() }}@endif
            </div>
        </div>
    </div>
    @else
    <table class="info-grid">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                <div class="info-box">
                    <div class="info-label">Bill To</div>
                    <div class="info-name">{{ $customer->name() }}</div>
                    <div class="info-details">
                        @if($customer->address()){{ $customer->address() }}<br>@endif
                        @if($customer->phone()){{ $customer->phone() }}<br>@endif
                        @if($customer->email()){{ $customer->email() }}@endif
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top; padding-left: 15px;">
                <div class="info-box">
                    <div class="info-label">Payment Information</div>
                    <div class="info-details">
                        <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                        <strong>Payment Terms:</strong> Net 30<br>
                        @if($businessProfile->tpin())<strong>TPIN:</strong> {{ $businessProfile->tpin() }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Dimensions</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Total</th>
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
    
    <div class="totals-container">
        @if(!isset($isPdf) || !$isPdf)
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
        </div>
        @if($totals['discountTotal'] > 0)
        <div class="totals-row">
            <span>Discount:</span>
            <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
        </div>
        @endif
        @if($document->collectTax() && $totals['taxTotal'] > 0)
        <div class="totals-row">
            <span>Tax:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
        </div>
        @endif
        <div class="totals-row grand-total">
            <span>TOTAL:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
        </div>
        @else
        <table>
            <tr class="totals-row">
                <td>Subtotal:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
            </tr>
            @if($totals['discountTotal'] > 0)
            <tr class="totals-row">
                <td>Discount:</td>
                <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <tr class="totals-row">
                <td>Tax:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            <tr class="totals-row grand-total">
                <td>TOTAL:</td>
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
    
    @if(!isset($isPdf) || !$isPdf)
    <div class="footer-grid">
        <div class="footer-box">
            <div class="footer-title">Notes</div>
            <div class="footer-content">Thank you for your business. We appreciate your continued partnership.</div>
        </div>
        <div class="footer-box">
            <div class="footer-title">Terms & Conditions</div>
            <div class="footer-content">Payment due within 30 days. Late fees may apply.</div>
        </div>
    </div>
    @else
    <table class="footer-grid">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 12px;">
                <div class="footer-box">
                    <div class="footer-title">Notes</div>
                    <div class="footer-content">Thank you for your business. We appreciate your continued partnership.</div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top; padding-left: 12px;">
                <div class="footer-box">
                    <div class="footer-title">Terms & Conditions</div>
                    <div class="footer-content">Payment due within 30 days. Late fees may apply.</div>
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
</body>
</html>
