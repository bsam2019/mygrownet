<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blue Professional</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; color: #1f2937; font-size: 13px; }
        
        .header { background: #1e40af; color: white; padding: 40px; }
        @if(!isset($isPdf) || !$isPdf)
        /* Modern CSS for HTML preview */
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { width: 80px; height: 80px; color: #1e40af; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 36px; padding: 8px; background: white; }
        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 35px; }
        .total-row { display: flex; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; }
        .grand-total { display: flex; justify-content: space-between; background: #1e40af; color: white; font-size: 18px; font-weight: bold; padding: 18px 20px; border-radius: 8px; margin-top: 10px; }
        @else
        /* Table-based CSS for PDF */
        .header-content { width: 100%; }
        .logo { width: 80px; height: 80px; color: #1e40af; border-radius: 8px; text-align: center; font-size: 36px; padding: 8px; background: white; line-height: 64px; }
        .parties { width: 100%; margin-bottom: 35px; }
        .totals table { width: 100%; }
        .total-row td { padding: 12px 20px; border-bottom: 1px solid #e5e7eb; }
        .grand-total { background: #1e40af; color: white; font-size: 18px; font-weight: bold; }
        .grand-total td { padding: 18px 20px; }
        @endif
        
        .company-name { font-size: 28px; font-weight: bold; margin-bottom: 8px; }
        .company-details { font-size: 12px; opacity: 0.9; line-height: 1.6; }
        
        .content { padding: 40px; }
        .doc-header { width: 100%; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #1e40af; }
        .doc-title { font-size: 32px; font-weight: bold; color: #1e40af; }
        .doc-meta { font-size: 12px; color: #6b7280; line-height: 1.8; }
        
        .party-box { padding: 20px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #1e40af; }
        .party-label { font-size: 10px; font-weight: bold; color: #1e40af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .party-name { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
        .party-details { font-size: 12px; color: #6b7280; line-height: 1.7; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table thead { background: #1e40af; color: white; }
        .items-table th { padding: 14px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .items-table td { padding: 14px 12px; border-bottom: 1px solid #e5e7eb; }
        .items-table .even-row { background: #f9fafb; }
        .text-right { text-align: right; }
        
        .totals { width: 400px; margin-left: auto; margin-right: 0; }
        
        .footer { margin-top: 50px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; }
        .signature { display: inline-block; width: 200px; border-top: 2px solid #1e40af; padding-top: 10px; margin-top: 10px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if(!isset($isPdf) || !$isPdf)
        {{-- Modern layout for HTML preview --}}
        <div class="header-content">
            <div>
                <div class="company-name">{{ $businessProfile->businessName() }}</div>
                <div class="company-details">
                    {{ $businessProfile->address() }}<br>
                    Tel: {{ $businessProfile->phone() }}
                    @if($businessProfile->email()) | Email: {{ $businessProfile->email() }}@endif
                    @if($businessProfile->website())<br>Web: {{ $businessProfile->website() }}@endif
                </div>
            </div>
            <div class="logo">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                @else
                    {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                @endif
            </div>
        </div>
        @else
        {{-- Table layout for PDF --}}
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; vertical-align: top; padding-right: 20px;">
                    <div class="company-name">{{ $businessProfile->businessName() }}</div>
                    <div class="company-details">
                        {{ $businessProfile->address() }}<br>
                        Tel: {{ $businessProfile->phone() }}
                        @if($businessProfile->email()) | Email: {{ $businessProfile->email() }}@endif
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;">
                    <table style="float: right; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">
                                @if($logoPath)
                                    <div style="width: 80px; height: 80px; background: white; border-radius: 8px; position: relative;">
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                            <img src="{{ $logoPath }}" alt="Logo" style="max-width: 70px; max-height: 70px; width: auto; height: auto;">
                                        </div>
                                    </div>
                                @else
                                    <div style="width: 80px; height: 80px; background: white; color: #1e40af; border-radius: 8px; text-align: center; line-height: 80px; font-size: 36px;">
                                        {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        @endif
    </div>
    
    <div class="content">
        <div class="doc-header">
            <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
            <div class="doc-meta">
                <strong>Number:</strong> {{ $document->number()->value() }}<br>
                <strong>Date:</strong> {{ $document->issueDate()->format('d M Y') }}
                @if($document->dueDate())<br><strong>Due:</strong> {{ $document->dueDate()->format('d M Y') }}@endif
            </div>
        </div>
        
        @if(!isset($isPdf) || !$isPdf)
        {{-- Grid layout for HTML preview --}}
        <div class="parties">
            <div class="party-box">
                <div class="party-label">Bill To</div>
                <div class="party-name">{{ $customer->name() }}</div>
                <div class="party-details">
                    @if($customer->address()){{ $customer->address() }}<br>@endif
                    @if($customer->phone()){{ $customer->phone() }}<br>@endif
                    @if($customer->email()){{ $customer->email() }}@endif
                </div>
            </div>
            <div class="party-box">
                <div class="party-label">Payment Details</div>
                <div class="party-details">
                    <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                    <strong>Terms:</strong> Net 30<br>
                    @if($businessProfile->tpin())<strong>TPIN:</strong> {{ $businessProfile->tpin() }}@endif
                </div>
            </div>
        </div>
        @else
        {{-- Table layout for PDF --}}
        <table class="parties">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                    <div class="party-box">
                        <div class="party-label">Bill To</div>
                        <div class="party-name">{{ $customer->name() }}</div>
                        <div class="party-details">
                            @if($customer->address()){{ $customer->address() }}<br>@endif
                            @if($customer->phone()){{ $customer->phone() }}<br>@endif
                            @if($customer->email()){{ $customer->email() }}@endif
                        </div>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top; padding-left: 15px;">
                    <div class="party-box">
                        <div class="party-label">Payment Details</div>
                        <div class="party-details">
                            <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                            <strong>Terms:</strong> Net 30<br>
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
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                @php
                    $effectiveQty = ($item->dimensionsValue ?? 1) * $item->quantity;
                    $lineTotal = $effectiveQty * ($item->unitPrice / 100);
                    $rowClass = ($index % 2 == 1) ? 'even-row' : '';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $item->description }}</td>
                    <td class="text-right">
                        @if($item->dimensions !== null && $item->dimensions !== '')
                            {{ $item->dimensions }}
                            @if($item->dimensionsValue != 1)
                                <small style="color: #666;"> ({{ number_format($item->dimensionsValue, 2) }})</small>
                            @endif
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
        
        <div class="totals">
            @if(!isset($isPdf) || !$isPdf)
            {{-- Flexbox layout for HTML preview --}}
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
            <div class="grand-total">
                <span>TOTAL:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
            </div>
            @else
            {{-- Table layout for PDF --}}
            <table>
                <tr class="total-row">
                    <td style="text-align: left;">Subtotal:</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
                </tr>
                @if($totals['discountTotal'] > 0)
                <tr class="total-row">
                    <td style="text-align: left;">Discount:</td>
                    <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
                </tr>
                @endif
                @if($document->collectTax() && $totals['taxTotal'] > 0)
                <tr class="total-row">
                    <td style="text-align: left;">Tax:</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td style="text-align: left;">TOTAL:</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td>
                </tr>
            </table>
            @endif
        </div>
        
        @if($document->notes())
        <div style="margin-top: 30px; padding: 20px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #1e40af;">
            <div style="font-size: 11px; font-weight: bold; color: #1e40af; text-transform: uppercase; margin-bottom: 10px;">Notes</div>
            <div style="font-size: 12px; color: #6b7280; line-height: 1.8;">{{ $document->notes() }}</div>
        </div>
        @endif
        
        @if($document->paymentInstructions())
        <div style="margin-top: 20px; padding: 20px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #1e40af;">
            <div style="font-size: 11px; font-weight: bold; color: #1e40af; text-transform: uppercase; margin-bottom: 10px;">Payment Instructions</div>
            <div style="font-size: 12px; color: #6b7280; line-height: 1.8;">{{ $document->paymentInstructions() }}</div>
        </div>
        @endif
        
        @if($document->terms())
        <div style="margin-top: 20px; padding: 20px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #1e40af;">
            <div style="font-size: 11px; font-weight: bold; color: #1e40af; text-transform: uppercase; margin-bottom: 10px;">Terms & Conditions</div>
            <div style="font-size: 12px; color: #6b7280; line-height: 1.8;">{{ $document->terms() }}</div>
        </div>
        @endif
        
        <div class="footer">
            @if($signaturePath)
                <div style="text-align: center; margin-bottom: 10px;">
                    <img src="{{ $signaturePath }}" alt="Signature" style="max-width:180px;max-height:60px;width:auto;height:auto;">
                </div>
            @endif
            <div class="signature">Authorized Signature</div>
        </div>
        
        @if($businessProfile->website())
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #e5e7eb; font-size: 12px; color: #6b7280;">
            Visit us: <span style="color: #1e40af; font-weight: bold;">{{ $businessProfile->website() }}</span>
        </div>
        @endif
    </div>
</body>
</html>
