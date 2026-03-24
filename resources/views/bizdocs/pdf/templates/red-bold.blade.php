<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Red Bold</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @if(!isset($isPdf) || !$isPdf)
        body { font-family: 'Arial Black', sans-serif; color: #1f2937; font-size: 13px; }
        @else
        body { font-family: Arial, sans-serif; color: #1f2937; font-size: 13px; font-weight: bold; }
        @endif
        
        @if(!isset($isPdf) || !$isPdf)
        /* Modern CSS for HTML preview */
        .header-banner { background: linear-gradient(135deg, #dc2626, #ef4444); color: white; padding: 50px 40px; clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .company-info-bold { flex: 1; }
        .logo-bold { width: 100px; height: 100px; background: white; color: #dc2626; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 48px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); transform: rotate(-5deg); }
        .doc-title-section { background: #111827; color: white; padding: 20px 30px; margin: -20px 0 30px 0; transform: skewY(-2deg); }
        .doc-title-content { transform: skewY(2deg); display: flex; justify-content: space-between; align-items: center; }
        .info-boxes { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
        .total-row-bold { display: flex; justify-content: space-between; padding: 12px 0; font-size: 15px; font-weight: 700; border-bottom: 1px solid #374151; }
        .grand-total-bold { display: flex; justify-content: space-between; padding: 20px 0; font-size: 26px; font-weight: 900; color: #dc2626; background: white; margin: 20px -30px -30px -30px; padding: 25px 30px; border-radius: 0 0 8px 8px; }
        @else
        /* Table-based CSS for PDF */
        .header-banner { background: #dc2626; color: white; padding: 50px 40px; clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
        .header-content { width: 100%; }
        .logo-bold { width: 100px; height: 100px; background: white; color: #dc2626; border-radius: 12px; text-align: center; line-height: 100px; font-size: 48px; }
        .doc-title-section { background: #111827; color: white; padding: 20px 30px; margin: -20px 0 30px 0; }
        .doc-title-content { width: 100%; }
        .info-boxes { width: 100%; margin-bottom: 30px; }
        .totals-bold table { width: 100%; }
        .total-row-bold td { padding: 12px 0; font-size: 15px; font-weight: 700; border-bottom: 1px solid #374151; }
        .grand-total-bold td { padding: 20px 0; font-size: 26px; font-weight: 900; color: #dc2626; background: white; }
        @endif
        
        .company-name-bold { font-size: 36px; font-weight: 900; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .company-details-bold { font-size: 13px; line-height: 1.8; opacity: 0.95; font-weight: bold; }
        
        .doc-type-bold { font-size: 32px; font-weight: 900; letter-spacing: 3px; }
        .doc-number-bold { font-size: 16px; font-weight: bold; }
        
        .info-box-bold { background: #fef2f2; padding: 20px; border-left: 6px solid #dc2626; }
        .box-label-bold { font-size: 11px; font-weight: 900; color: #dc2626; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; }
        .box-content-bold { font-size: 12px; color: #374151; line-height: 1.8; }
        .box-name-bold { font-size: 18px; font-weight: 900; color: #111827; margin-bottom: 8px; }
        
        .content { padding: 40px; }
        
        .totals-bold { background: #111827; color: white; padding: 30px; max-width: 450px; margin-left: auto; border-radius: 8px; }
        .total-row-bold:last-child { border-bottom: none; }
        
        .items-table-bold { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table-bold thead { background: #111827; }
        .items-table-bold th { color: white; padding: 16px 14px; text-align: left; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
        .items-table-bold td { padding: 16px 14px; border-bottom: 2px solid #fee2e2; font-weight: 600; }
        .items-table-bold tbody tr:nth-child(odd) { background: #fef2f2; }
        .text-right { text-align: right; }
        
        .footer-bold { margin-top: 50px; text-align: center; }
    </style>
</head>
<body>
    <div class="header-banner">
        @if(!isset($isPdf) || !$isPdf)
        {{-- Flexbox layout for HTML preview with clip-path --}}
        <div class="header-content">
            <div class="company-info-bold">
                <div class="company-name-bold">{{ $businessProfile->businessName() }}</div>
                <div class="company-details-bold">
                    {{ $businessProfile->address() }}<br>
                    {{ $businessProfile->phone() }}
                    @if($businessProfile->email())<br>{{ $businessProfile->email() }}@endif
                    @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
                    @if($businessProfile->tpin())<br>TPIN: {{ $businessProfile->tpin() }}@endif
                </div>
            </div>
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100px; max-height: 100px; width: auto; height: auto; background: white; border-radius: 12px; padding: 10px;">
            @else
                <div class="logo-bold">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
            @endif
        </div>
        @else
        {{-- Table layout for PDF --}}
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <div class="company-name-bold">{{ $businessProfile->businessName() }}</div>
                    <div class="company-details-bold">
                        {{ $businessProfile->address() }}<br>
                        {{ $businessProfile->phone() }}
                        @if($businessProfile->email())<br>{{ $businessProfile->email() }}@endif
                        @if($businessProfile->website())<br>{{ $businessProfile->website() }}@endif
                        @if($businessProfile->tpin())<br>TPIN: {{ $businessProfile->tpin() }}@endif
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;">
                    @if($logoPath)
                        <div style="width: 100px; height: 100px; background: white; border-radius: 12px; padding: 5px; float: right; position: relative;">
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                <img src="{{ $logoPath }}" alt="Logo" style="max-width: 90px; max-height: 90px; width: auto; height: auto;">
                            </div>
                        </div>
                    @else
                        <div class="logo-bold" style="display: inline-block; float: right;">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
                    @endif
                </td>
            </tr>
        </table>
        @endif
    </div>
    
    <div class="content">
        @if(!isset($isPdf) || !$isPdf)
        {{-- Flexbox layout for HTML preview --}}
        <div class="doc-title-section">
            <div class="doc-title-content">
                <div class="doc-type-bold">{{ strtoupper($document->type()->value()) }}</div>
                <div class="doc-number-bold">#{{ $document->number()->value() }}</div>
            </div>
        </div>
        @else
        {{-- Table layout for PDF --}}
        <div class="doc-title-section">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 70%;">
                        <div class="doc-type-bold">{{ strtoupper($document->type()->value()) }}</div>
                    </td>
                    <td style="width: 30%; text-align: right;">
                        <div class="doc-number-bold">#{{ $document->number()->value() }}</div>
                    </td>
                </tr>
            </table>
        </div>
        @endif
        
        @if(!isset($isPdf) || !$isPdf)
        {{-- Grid layout for HTML preview --}}
        <div class="info-boxes">
            <div class="info-box-bold">
                <div class="box-label-bold">Customer Details</div>
                <div class="box-name-bold">{{ $customer->name() }}</div>
                <div class="box-content-bold">
                    @if($customer->address()){{ $customer->address() }}<br>@endif
                    @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                    @if($customer->email())Email: {{ $customer->email() }}@endif
                </div>
            </div>
            <div class="info-box-bold">
                <div class="box-label-bold">Document Info</div>
                <div class="box-content-bold">
                    <strong>Date:</strong><br>{{ $document->issueDate()->format('d M Y') }}
                    @if($document->dueDate())<br><br><strong>Due:</strong><br>{{ $document->dueDate()->format('d M Y') }}@endif
                </div>
            </div>
        </div>
        @else
        {{-- Table layout for PDF --}}
        <table class="info-boxes">
            <tr>
                <td style="width: 65%; vertical-align: top; padding-right: 10px;">
                    <div class="info-box-bold">
                        <div class="box-label-bold">Customer Details</div>
                        <div class="box-name-bold">{{ $customer->name() }}</div>
                        <div class="box-content-bold">
                            @if($customer->address()){{ $customer->address() }}<br>@endif
                            @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                            @if($customer->email())Email: {{ $customer->email() }}@endif
                        </div>
                    </div>
                </td>
                <td style="width: 35%; vertical-align: top; padding-left: 10px;">
                    <div class="info-box-bold">
                        <div class="box-label-bold">Document Info</div>
                        <div class="box-content-bold">
                            <strong>Date:</strong><br>{{ $document->issueDate()->format('d M Y') }}
                            @if($document->dueDate())<br><br><strong>Due:</strong><br>{{ $document->dueDate()->format('d M Y') }}@endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        @endif
        
        <table class="items-table-bold">
            <thead>
                <tr>
                    <th>DESCRIPTION</th>
                    <th class="text-right" style="width: 100px;">DIMENSIONS</th>
                    <th class="text-right" style="width: 70px;">QTY</th>
                    <th class="text-right" style="width: 110px;">PRICE</th>
                    <th class="text-right" style="width: 60px;">TAX</th>
                    <th class="text-right" style="width: 120px;">TOTAL</th>
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
        
        <div class="totals-bold">
            @if(!isset($isPdf) || !$isPdf)
            {{-- Flexbox layout for HTML preview --}}
            <div class="total-row-bold">
                <span>SUBTOTAL:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
            </div>
            @if($totals['discountTotal'] > 0)
            <div class="total-row-bold">
                <span>DISCOUNT:</span>
                <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
            </div>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <div class="total-row-bold">
                <span>TAX:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
            </div>
            @endif
            <div class="grand-total-bold">
                <span>TOTAL:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
            </div>
            @else
            {{-- Table layout for PDF --}}
            <table>
                <tr class="total-row-bold">
                    <td>SUBTOTAL:</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
                </tr>
                @if($totals['discountTotal'] > 0)
                <tr class="total-row-bold">
                    <td>DISCOUNT:</td>
                    <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
                </tr>
                @endif
                @if($document->collectTax() && $totals['taxTotal'] > 0)
                <tr class="total-row-bold">
                    <td>TAX:</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
                </tr>
                @endif
            </table>
            <table class="grand-total-bold" style="width: 100%; background: white; margin-top: 20px; border-radius: 0 0 8px 8px;">
                <tr>
                    <td>TOTAL:</td>
                    <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td>
                </tr>
            </table>
            @endif
        </div>
        
        <div class="footer-bold">
            @if($signaturePath)
            <table style="width: 100%; margin-bottom: 30px;">
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 50%; text-align: center;">
                        <img src="{{ $signaturePath }}" alt="Signature" style="display:block;max-width: 200px; max-height: 80px; margin: 0 auto 10px;">
                        <div style="border-top: 3px solid #dc2626; padding-top: 8px; font-size: 12px; color: #374151; font-weight: 900;">
                            AUTHORIZED SIGNATURE
                        </div>
                        @if($businessProfile->preparedBy())
                        <div style="font-size: 11px; color: #6b7280; margin-top: 5px; font-weight: 600;">
                            Prepared by: {{ $businessProfile->preparedBy() }}
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
            @endif
        </div>
        
        @if($businessProfile->website())
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #fee2e2; font-size: 12px; color: #6b7280;">
            Visit us: <span style="color: #dc2626; font-weight: bold;">{{ $businessProfile->website() }}</span>
        </div>
        @endif
    </div>
</body>
</html>
