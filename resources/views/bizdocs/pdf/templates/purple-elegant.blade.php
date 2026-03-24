<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purple Elegant</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; color: #1f2937; font-size: 13px; }
        
        .content-wrapper { padding: 40px; }
        .ornament-top { height: 4px; background: #7c3aed; width: 100%; }
        
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid #e9d5ff; }
        .company-name-elegant { font-size: 32px; font-weight: bold; color: #7c3aed; margin-bottom: 10px; letter-spacing: 1px; }
        .company-tagline { font-size: 13px; color: #6b7280; font-style: italic; margin-bottom: 15px; }
        .company-contact { font-size: 11px; color: #6b7280; line-height: 1.8; }
        
        .doc-info-bar { background: #f5f3ff; padding: 20px 30px; border-radius: 8px; border: 1px solid #e9d5ff; margin-bottom: 35px; }
        .doc-type-elegant { font-size: 28px; font-weight: bold; color: #7c3aed; letter-spacing: 2px; }
        .doc-details-elegant { text-align: right; font-size: 12px; color: #6b7280; line-height: 1.8; }
        
        @if(!isset($isPdf) || !$isPdf)
        /* Modern CSS for HTML preview */
        .logo-elegant { width: 100px; height: 100px; background: linear-gradient(135deg, #7c3aed, #a78bfa); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 48px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(124, 58, 237, 0.3); }
        .doc-info-bar { display: flex; justify-content: space-between; align-items: center; }
        .parties-elegant { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px; }
        .total-line-elegant { display: flex; justify-content: space-between; padding: 12px 0; font-size: 14px; border-bottom: 1px dashed #e9d5ff; }
        .grand-total-elegant { display: flex; justify-content: space-between; padding: 20px 25px; font-size: 22px; font-weight: bold; color: #7c3aed; background: white; border-top: 3px solid #7c3aed; }
        @else
        /* Table-based CSS for PDF */
        .logo-elegant { width: 100px; height: 100px; background: #7c3aed; color: white; border-radius: 50%; text-align: center; line-height: 100px; font-size: 48px; margin: 0 auto 20px; }
        .parties-elegant { width: 100%; margin-bottom: 40px; }
        .totals-elegant table { width: 100%; }
        .total-line-elegant td { padding: 12px 0; font-size: 14px; border-bottom: 1px dashed #e9d5ff; }
        .grand-total-elegant td { padding: 20px 25px; font-size: 22px; font-weight: bold; color: #7c3aed; background: white; border-top: 3px solid #7c3aed; }
        @endif
        
        .party-card-elegant { background: white; padding: 25px; border: 2px solid #e9d5ff; border-radius: 8px; border-top: 4px solid #7c3aed; }
        .party-label-elegant { font-size: 11px; font-weight: bold; color: #7c3aed; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; }
        .party-name-elegant { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 10px; }
        .party-info-elegant { font-size: 12px; color: #6b7280; line-height: 1.9; }
        
        .items-table-elegant { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 35px; border: 2px solid #e9d5ff; border-radius: 8px; overflow: hidden; }
        .items-table-elegant thead { background: #7c3aed; }
        .items-table-elegant th { color: white; padding: 16px 14px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .items-table-elegant td { padding: 16px 14px; border-bottom: 1px solid #f5f3ff; }
        .items-table-elegant tbody tr:nth-child(even) { background: #faf5ff; }
        .items-table-elegant tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        
        .totals-elegant { max-width: 420px; margin-left: auto; border: 2px solid #7c3aed; border-radius: 8px; overflow: hidden; }
        .totals-header-elegant { background: #7c3aed; color: white; padding: 15px 25px; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .totals-body-elegant { padding: 20px 25px; background: #faf5ff; }
        .total-line-elegant:last-child { border-bottom: none; }
        
        .notes { margin-top: 40px; padding: 20px; background: #f5f3ff; border-radius: 8px; border-left: 4px solid #7c3aed; }
        .notes-title { font-size: 11px; font-weight: bold; color: #7c3aed; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
        
        .footer-elegant { margin-top: 60px; text-align: center; padding-top: 30px; border-top: 2px solid #e9d5ff; }
        .signature-elegant { display: inline-block; width: 250px; border-top: 2px solid #7c3aed; padding-top: 10px; margin-top: 50px; font-size: 11px; font-weight: bold; color: #6b7280; }
        .thank-you-elegant { font-size: 14px; color: #7c3aed; font-style: italic; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="ornament-top"></div>
    
    <div class="content-wrapper">
    <div class="header">
        @if($logoPath)
            @if(!isset($isPdf) || !$isPdf)
                <img src="{{ $logoPath }}" alt="Logo" style="max-width: 150px; max-height: 100px; width: auto; height: auto; margin-bottom: 20px;">
            @else
                <div style="width: 100px; height: 100px; background: white; border-radius: 50%; margin: 0 auto 20px; position: relative;">
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <img src="{{ $logoPath }}" alt="Logo" style="max-width: 90px; max-height: 90px; width: auto; height: auto;">
                    </div>
                </div>
            @endif
        @else
            <div class="logo-elegant">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
        @endif
        <div class="company-name-elegant">{{ $businessProfile->businessName() }}</div>
        <div class="company-tagline">Excellence in Every Transaction</div>
        <div class="company-contact">
            {{ $businessProfile->address() }} | {{ $businessProfile->phone() }}
            @if($businessProfile->email()) | {{ $businessProfile->email() }}@endif
            @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
            @if($businessProfile->tpin())<br>TPIN: {{ $businessProfile->tpin() }}@endif
        </div>
    </div>
    
    @if(!isset($isPdf) || !$isPdf)
    {{-- Flexbox layout for HTML preview --}}
    <div class="doc-info-bar">
        <div class="doc-type-elegant">{{ strtoupper($document->type()->value()) }}</div>
        <div class="doc-details-elegant">
            <strong>Number:</strong> {{ $document->number()->value() }}<br>
            <strong>Issue Date:</strong> {{ $document->issueDate()->format('d M Y') }}
            @if($document->dueDate())<br><strong>Due Date:</strong> {{ $document->dueDate()->format('d M Y') }}@endif
        </div>
    </div>
    @else
    {{-- Table layout for PDF --}}
    <div class="doc-info-bar">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="doc-type-elegant">{{ strtoupper($document->type()->value()) }}</div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <div class="doc-details-elegant">
                        <strong>Number:</strong> {{ $document->number()->value() }}<br>
                        <strong>Issue Date:</strong> {{ $document->issueDate()->format('d M Y') }}
                        @if($document->dueDate())<br><strong>Due Date:</strong> {{ $document->dueDate()->format('d M Y') }}@endif
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @endif
    
    @if(!isset($isPdf) || !$isPdf)
    {{-- Grid layout for HTML preview --}}
    <div class="parties-elegant">
        <div class="party-card-elegant">
            <div class="party-label-elegant">Client Information</div>
            <div class="party-name-elegant">{{ $customer->name() }}</div>
            <div class="party-info-elegant">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                @if($customer->email())Email: {{ $customer->email() }}@endif
            </div>
        </div>
        <div class="party-card-elegant">
            <div class="party-label-elegant">Payment Information</div>
            <div class="party-info-elegant">
                <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                <strong>Payment Terms:</strong> Net 30 Days<br>
                @if($businessProfile->tpin())<strong>Tax ID:</strong> {{ $businessProfile->tpin() }}@endif
            </div>
        </div>
    </div>
    @else
    {{-- Table layout for PDF --}}
    <table class="parties-elegant">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                <div class="party-card-elegant">
                    <div class="party-label-elegant">Client Information</div>
                    <div class="party-name-elegant">{{ $customer->name() }}</div>
                    <div class="party-info-elegant">
                        @if($customer->address()){{ $customer->address() }}<br>@endif
                        @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                        @if($customer->email())Email: {{ $customer->email() }}@endif
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top; padding-left: 15px;">
                <div class="party-card-elegant">
                    <div class="party-label-elegant">Payment Information</div>
                    <div class="party-info-elegant">
                        <strong>Currency:</strong> {{ $businessProfile->defaultCurrency() }}<br>
                        <strong>Payment Terms:</strong> Net 30 Days<br>
                        @if($businessProfile->tpin())<strong>Tax ID:</strong> {{ $businessProfile->tpin() }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    <table class="items-table-elegant">
        <thead>
            <tr>
                <th>Item Description</th>
                <th class="text-right" style="width: 100px;">Dimensions</th>
                <th class="text-right" style="width: 70px;">Qty</th>
                <th class="text-right" style="width: 110px;">Unit Price</th>
                <th class="text-right" style="width: 60px;">Tax</th>
                <th class="text-right" style="width: 120px;">Total</th>
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
    
    <div class="totals-elegant">
        <div class="totals-header-elegant">Payment Summary</div>
        <div class="totals-body-elegant">
            @if(!isset($isPdf) || !$isPdf)
            {{-- Flexbox layout for HTML preview --}}
            <div class="total-line-elegant">
                <span>Subtotal:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
            </div>
            @if($totals['discountTotal'] > 0)
            <div class="total-line-elegant">
                <span>Discount:</span>
                <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
            </div>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <div class="total-line-elegant">
                <span>Tax (16%):</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
            </div>
            @endif
            @else
            {{-- Table layout for PDF --}}
            <table>
                <tr class="total-line-elegant">
                    <td style="text-align: left;">Subtotal:</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
                </tr>
                @if($totals['discountTotal'] > 0)
                <tr class="total-line-elegant">
                    <td style="text-align: left;">Discount:</td>
                    <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
                </tr>
                @endif
                @if($document->collectTax() && $totals['taxTotal'] > 0)
                <tr class="total-line-elegant">
                    <td style="text-align: left;">Tax (16%):</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
                </tr>
                @endif
            </table>
            @endif
        </div>
        @if(!isset($isPdf) || !$isPdf)
        <div class="grand-total-elegant">
            <span>TOTAL AMOUNT:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
        </div>
        @else
        <table>
            <tr class="grand-total-elegant">
                <td style="text-align: left;">TOTAL AMOUNT:</td>
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
    
    <div class="footer-elegant">
        @if($signaturePath)
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 250px; max-height: 80px; width: auto; height: auto;">
            </div>
            <div class="signature-elegant" style="margin-top: 10px;">Authorized Signature</div>
        @else
            <div class="signature-elegant">Authorized Signature</div>
        @endif
        <div class="thank-you-elegant">Thank you for your valued business</div>
    </div>
    </div>
    
    @if($businessProfile->website())
    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #e9d5ff; font-size: 12px; color: #6b7280;">
        Visit us: <span style="color: #7c3aed; font-weight: bold;">{{ $businessProfile->website() }}</span>
    </div>
    @endif
</body>
</html>
