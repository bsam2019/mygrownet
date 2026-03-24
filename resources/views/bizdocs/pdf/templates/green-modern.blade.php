<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Green Modern</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #1f2937; font-size: 13px; }
        
        .top-bar { background: #059669; height: 8px; width: 100%; }
        .content-wrapper { padding: 40px; }
        
        .company-name { font-size: 26px; font-weight: bold; color: #059669; margin-bottom: 8px; }
        .company-info { font-size: 11px; color: #6b7280; line-height: 1.7; }
        
        .doc-badge { background: #059669; color: white; padding: 15px 30px; border-radius: 8px; text-align: center; }
        .doc-type { font-size: 22px; font-weight: bold; letter-spacing: 2px; }
        .doc-number { font-size: 12px; margin-top: 5px; opacity: 0.9; }
        
        @if(!isset($isPdf) || !$isPdf)
        /* Modern CSS for HTML preview */
        .info-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 35px; }
        .totals-box .total-line { display: flex; justify-content: space-between; padding: 10px 0; font-size: 13px; border-bottom: 1px dashed #d1d5db; }
        .totals-box .grand-total-line { display: flex; justify-content: space-between; padding: 15px 0; font-size: 20px; font-weight: bold; color: #059669; margin-top: 10px; border-top: 2px solid #059669; }
        @else
        /* Table-based CSS for PDF */
        .info-grid { width: 100%; margin-bottom: 35px; }
        .totals-box table { width: 100%; }
        .totals-box .total-line td { padding: 10px 0; font-size: 13px; border-bottom: 1px dashed #d1d5db; }
        .totals-box .grand-total-line td { padding: 15px 0; font-size: 20px; font-weight: bold; color: #059669; border-top: 2px solid #059669; }
        @endif
        
        .customer-card { background: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 4px solid #059669; }
        .date-card { background: #f9fafb; padding: 20px; border-radius: 8px; }
        .card-title { font-size: 10px; font-weight: bold; color: #059669; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .card-name { font-size: 16px; font-weight: bold; margin-bottom: 8px; color: #111827; }
        .card-details { font-size: 12px; color: #6b7280; line-height: 1.7; }
        
        .items-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 30px; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
        .items-table thead { background: #059669; }
        .items-table th { color: white; padding: 14px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .items-table td { padding: 14px 12px; border-bottom: 1px solid #e5e7eb; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        
        .totals-box { max-width: 380px; margin-left: auto; background: #f0fdf4; padding: 20px; border-radius: 8px; border: 2px solid #059669; }
        .total-line:last-child { border-bottom: none; }
        
        .notes { margin-top: 40px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #059669; }
        .notes-title { font-size: 11px; font-weight: bold; color: #059669; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="top-bar"></div>
    
    <div class="content-wrapper">
    <table style="width: 100%; margin-bottom: 40px;">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 120px; max-height: 80px; width: auto; height: auto; margin-bottom: 15px;">
                @else
                    <div style="width: 80px; height: 80px; background: #059669; color: white; border-radius: 8px; text-align: center; line-height: 80px; font-size: 32px; font-weight: bold; margin-bottom: 15px;">
                        {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                    </div>
                @endif
                <div class="company-name">{{ $businessProfile->businessName() }}</div>
                <div class="company-info">
                    {{ $businessProfile->address() }}<br>
                    Phone: {{ $businessProfile->phone() }}
                    @if($businessProfile->email())<br>Email: {{ $businessProfile->email() }}@endif
                    @if($businessProfile->website())<br>Web: {{ $businessProfile->website() }}@endif
                    @if($businessProfile->tpin())<br>TPIN: {{ $businessProfile->tpin() }}@endif
                </div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="doc-badge">
                    <div class="doc-type">{{ strtoupper($document->type()->value()) }}</div>
                    <div class="doc-number">#{{ $document->number()->value() }}</div>
                </div>
            </td>
        </tr>
    </table>
    
    @if(!isset($isPdf) || !$isPdf)
    {{-- Grid layout for HTML preview --}}
    <div class="info-grid">
        <div class="customer-card">
            <div class="card-title">Customer Information</div>
            <div class="card-name">{{ $customer->name() }}</div>
            <div class="card-details">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                @if($customer->email())Email: {{ $customer->email() }}@endif
            </div>
        </div>
        <div class="date-card">
            <div class="card-title">Document Dates</div>
            <div class="card-details">
                <strong>Issue Date:</strong><br>{{ $document->issueDate()->format('d M Y') }}
                @if($document->dueDate())
                <br><br><strong>Due Date:</strong><br>{{ $document->dueDate()->format('d M Y') }}
                @endif
            </div>
        </div>
    </div>
    @else
    {{-- Table layout for PDF --}}
    <table class="info-grid">
        <tr>
            <td style="width: 65%; vertical-align: top; padding-right: 15px;">
                <div class="customer-card">
                    <div class="card-title">Customer Information</div>
                    <div class="card-name">{{ $customer->name() }}</div>
                    <div class="card-details">
                        @if($customer->address()){{ $customer->address() }}<br>@endif
                        @if($customer->phone())Phone: {{ $customer->phone() }}<br>@endif
                        @if($customer->email())Email: {{ $customer->email() }}@endif
                    </div>
                </div>
            </td>
            <td style="width: 35%; vertical-align: top; padding-left: 15px;">
                <div class="date-card">
                    <div class="card-title">Document Dates</div>
                    <div class="card-details">
                        <strong>Issue Date:</strong><br>{{ $document->issueDate()->format('d M Y') }}
                        @if($document->dueDate())
                        <br><br><strong>Due Date:</strong><br>{{ $document->dueDate()->format('d M Y') }}
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Item Description</th>
                <th class="text-right" style="width: 100px;">Dimensions</th>
                <th class="text-right" style="width: 70px;">Qty</th>
                <th class="text-right" style="width: 110px;">Unit Price</th>
                <th class="text-right" style="width: 70px;">Tax</th>
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
    
    <div class="totals-box">
        @if(!isset($isPdf) || !$isPdf)
        {{-- Flexbox layout for HTML preview --}}
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
            <span>TOTAL:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
        </div>
        @else
        {{-- Table layout for PDF --}}
        <table>
            <tr class="total-line">
                <td style="text-align: left;">Subtotal:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
            </tr>
            @if($totals['discountTotal'] > 0)
            <tr class="total-line">
                <td style="text-align: left;">Discount:</td>
                <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <tr class="total-line">
                <td style="text-align: left;">Tax (16%):</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total-line">
                <td style="text-align: left;">TOTAL:</td>
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
    
    @if($signaturePath)
    <table style="width: 100%; margin-top: 60px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                <div style="text-align: center; margin-bottom: 10px;">
                    <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 200px; max-height: 80px; width: auto; height: auto;">
                </div>
                <div style="border-top: 2px solid #059669; padding-top: 8px; font-size: 11px; color: #6b7280; font-weight: bold;">
                    Authorized Signature
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    @if($businessProfile->website())
    <table style="width: 100%; margin-top: 60px;">
        <tr>
            <td style="text-align: center; padding-top: 20px; border-top: 2px solid #d1fae5; font-size: 12px; color: #6b7280;">
                Visit us: <span style="color: #059669; font-weight: bold;">{{ $businessProfile->website() }}</span>
            </td>
        </tr>
    </table>
    @endif
    </div>
</body>
</html>
