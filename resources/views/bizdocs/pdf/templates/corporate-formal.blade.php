<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Corporate Formal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; color: #1f2937; background: white; font-size: 13px; }
        
        .content-wrapper { padding: 50px; }
        .letterhead { border-bottom: 4px solid #4f46e5; padding-bottom: 25px; margin-bottom: 40px; }
        .logo-formal { width: 80px; height: 80px; color: white; text-align: center; line-height: 80px; font-size: 36px; border: 3px solid #111827; overflow: hidden; }
        .logo-formal img { width: 100%; height: 100%; object-fit: cover; }
        .company-name { font-size: 26px; font-weight: bold; color: #111827; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 2px; }
        .company-tagline { font-size: 11px; color: #6b7280; font-style: italic; margin-bottom: 10px; }
        .company-details { font-size: 11px; color: #4b5563; line-height: 1.7; }
        .registration-info { text-align: right; font-size: 10px; color: #6b7280; line-height: 1.8; }
        .document-header { background: #4f46e510; padding: 25px; margin-bottom: 35px; border-left: 6px solid #4f46e5; }
        .doc-title-formal { font-size: 32px; font-weight: bold; color: #4f46e5; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 3px; }
        
        @if(!isset($isPdf) || !$isPdf)
        .letterhead-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .logo-section { display: flex; align-items: center; gap: 15px; }
        .doc-reference { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; font-size: 12px; }
        .parties-section { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px; }
        .summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
        .summary-grand { display: flex; justify-content: space-between; padding: 18px 0; font-size: 20px; font-weight: bold; margin-top: 15px; border-top: 3px double #111827; color: #4f46e5; }
        .signature-formal { margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 50px; }
        @else
        .letterhead-top { width: 100%; margin-bottom: 15px; }
        .doc-reference { width: 100%; font-size: 12px; margin-bottom: 0; }
        .parties-section { width: 100%; margin-bottom: 40px; }
        .financial-summary table { width: 100%; }
        .summary-row td { padding: 10px 0; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
        .summary-grand td { padding: 18px 0; font-size: 20px; font-weight: bold; border-top: 3px double #111827; color: #4f46e5; }
        @endif
        
        .ref-item { padding: 10px; background: white; border-radius: 4px; }
        .ref-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .ref-value { font-weight: bold; color: #111827; }
        .party-box { padding: 20px; border: 2px solid #e5e7eb; border-radius: 6px; border-top: 4px solid #4f46e5; }
        .party-label { font-size: 11px; font-weight: bold; color: #4f46e5; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .party-name { font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #111827; }
        .party-details { font-size: 12px; color: #6b7280; line-height: 1.8; }
        .items-table-formal { width: 100%; border-collapse: collapse; margin-bottom: 35px; border: 2px solid #111827; }
        .items-table-formal thead { background: #111827; color: white; }
        .items-table-formal th { padding: 14px 12px; text-align: left; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; border-right: 1px solid #4b5563; }
        .items-table-formal th:last-child { border-right: none; }
        .items-table-formal td { padding: 14px 12px; border: 1px solid #d1d5db; font-size: 13px; }
        .items-table-formal tbody tr:nth-child(odd) { background: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .financial-summary { max-width: 450px; margin-left: auto; border: 3px double #111827; padding: 20px; margin-bottom: 40px; }
        .summary-title { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; color: #111827; text-align: center; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb; }
        .summary-row:last-child { border-bottom: none; }
        .terms-formal { margin-top: 40px; padding: 20px; background: #f9fafb; border-left: 4px solid #4f46e5; border-radius: 4px; }
        .terms-title { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; color: #111827; }
        .terms-content { font-size: 11px; color: #4b5563; line-height: 1.9; }
        .notes { margin-top: 20px; padding: 20px; background: #f5f3ff; border-radius: 8px; border-left: 4px solid #4f46e5; }
        .notes-title { font-size: 11px; font-weight: bold; color: #4f46e5; text-transform: uppercase; margin-bottom: 10px; }
        .notes-text { font-size: 12px; color: #6b7280; line-height: 1.8; }
        .sig-block { text-align: center; }
        .sig-space { height: 60px; border-bottom: 2px solid #111827; margin-bottom: 10px; }
        .sig-label { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #4b5563; }
        .sig-sublabel { font-size: 10px; color: #6b7280; margin-top: 5px; }
        .document-footer { margin-top: 50px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="content-wrapper">
    <div class="letterhead">
        @if(!isset($isPdf) || !$isPdf)
        <div class="letterhead-top">
            <div class="logo-section">
                <div class="logo-formal">
                    @if($logoPath)
                        <img src="{{ $logoPath }}" alt="Logo" style="max-width: 100%; max-height: 100%; width: auto; height: auto;">
                    @else
                        {{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}
                    @endif
                </div>
                <div class="company-info">
                    <div class="company-name">{{ $businessProfile->businessName() }}</div>
                    <div class="company-tagline">Professional Business Solutions</div>
                    <div class="company-details">
                        {{ $businessProfile->address() }}<br>
                        Tel: {{ $businessProfile->phone() }}
                        @if($businessProfile->email()) | Email: {{ $businessProfile->email() }}@endif
                    </div>
                </div>
            </div>
            <div class="registration-info">
                @if($businessProfile->tpin())<strong>TPIN:</strong> {{ $businessProfile->tpin() }}<br>@endif
                <strong>Reg. No:</strong> 123456789<br>
                <strong>VAT Reg:</strong> Yes
            </div>
        </div>
        @else
        <table class="letterhead-top">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <table><tr>
                        <td style="width: 100px; vertical-align: top;">
                            @if($logoPath)
                                <div style="width: 80px; height: 80px; border: 3px solid #111827; position: relative;">
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                        <img src="{{ $logoPath }}" alt="Logo" style="max-width: 70px; max-height: 70px; width: auto; height: auto;">
                                    </div>
                                </div>
                            @else
                                <div class="logo-formal">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</div>
                            @endif
                        </td>
                        <td style="vertical-align: top; padding-left: 15px;">
                            <div class="company-name">{{ $businessProfile->businessName() }}</div>
                            <div class="company-tagline">Professional Business Solutions</div>
                            <div class="company-details">
                                {{ $businessProfile->address() }}<br>
                                Tel: {{ $businessProfile->phone() }}
                                @if($businessProfile->email()) | Email: {{ $businessProfile->email() }}@endif
                            </div>
                        </td>
                    </tr></table>
                </td>
                <td style="width: 30%; vertical-align: top;">
                    <div class="registration-info">
                        @if($businessProfile->tpin())<strong>TPIN:</strong> {{ $businessProfile->tpin() }}<br>@endif
                        <strong>Reg. No:</strong> 123456789<br><strong>VAT Reg:</strong> Yes
                    </div>
                </td>
            </tr>
        </table>
        @endif
    </div>
    
    <div class="document-header">
        <div class="doc-title-formal">{{ strtoupper($document->type()->value()) }}</div>
        @if(!isset($isPdf) || !$isPdf)
        <div class="doc-reference">
            <div class="ref-item">
                <div class="ref-label">Reference No.</div>
                <div class="ref-value">{{ $document->number()->value() }}</div>
            </div>
            <div class="ref-item">
                <div class="ref-label">Issue Date</div>
                <div class="ref-value">{{ $document->issueDate()->format('d M Y') }}</div>
            </div>
            @if($document->dueDate())
            <div class="ref-item">
                <div class="ref-label">Valid Until</div>
                <div class="ref-value">{{ $document->dueDate()->format('d M Y') }}</div>
            </div>
            @endif
        </div>
        @else
        <table class="doc-reference">
            <tr>
                <td style="width: 33%; padding: 10px; background: white; border-radius: 4px;">
                    <div class="ref-label">Reference No.</div>
                    <div class="ref-value">{{ $document->number()->value() }}</div>
                </td>
                <td style="width: 2%;"></td>
                <td style="width: 33%; padding: 10px; background: white; border-radius: 4px;">
                    <div class="ref-label">Issue Date</div>
                    <div class="ref-value">{{ $document->issueDate()->format('d M Y') }}</div>
                </td>
                @if($document->dueDate())
                <td style="width: 2%;"></td>
                <td style="width: 30%; padding: 10px; background: white; border-radius: 4px;">
                    <div class="ref-label">Valid Until</div>
                    <div class="ref-value">{{ $document->dueDate()->format('d M Y') }}</div>
                </td>
                @endif
            </tr>
        </table>
        @endif
    </div>
    
    @if(!isset($isPdf) || !$isPdf)
    <div class="parties-section">
        <div class="party-box">
            <div class="party-label">Customer</div>
            <div class="party-name">{{ $customer->name() }}</div>
            <div class="party-details">
                @if($customer->address()){{ $customer->address() }}<br>@endif
                @if($customer->phone())<strong>Phone:</strong> {{ $customer->phone() }}<br>@endif
                @if($customer->email())<strong>Email:</strong> {{ $customer->email() }}@endif
            </div>
        </div>
        <div class="party-box">
            <div class="party-label">Prepared By</div>
            <div class="party-name">{{ $businessProfile->businessName() }}</div>
            <div class="party-details">
                <strong>Contact Person:</strong> Sales Department<br>
                <strong>Phone:</strong> {{ $businessProfile->phone() }}<br>
                @if($businessProfile->email())<strong>Email:</strong> {{ $businessProfile->email() }}@endif
            </div>
        </div>
    </div>
    @else
    <table class="parties-section">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                <div class="party-box">
                    <div class="party-label">Customer</div>
                    <div class="party-name">{{ $customer->name() }}</div>
                    <div class="party-details">
                        @if($customer->address()){{ $customer->address() }}<br>@endif
                        @if($customer->phone())<strong>Phone:</strong> {{ $customer->phone() }}<br>@endif
                        @if($customer->email())<strong>Email:</strong> {{ $customer->email() }}@endif
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top; padding-left: 15px;">
                <div class="party-box">
                    <div class="party-label">Prepared By</div>
                    <div class="party-name">{{ $businessProfile->businessName() }}</div>
                    <div class="party-details">
                        <strong>Contact Person:</strong> Sales Department<br>
                        <strong>Phone:</strong> {{ $businessProfile->phone() }}<br>
                        @if($businessProfile->email())<strong>Email:</strong> {{ $businessProfile->email() }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
    @endif
    
    <table class="items-table-formal">
        <thead>
            <tr>
                <th style="width: 50px;">No.</th>
                <th>Description of Goods/Services</th>
                <th class="text-center" style="width: 100px;">Dimensions</th>
                <th class="text-center" style="width: 70px;">Quantity</th>
                <th class="text-right" style="width: 110px;">Unit Price</th>
                <th class="text-right" style="width: 70px;">Tax %</th>
                <th class="text-right" style="width: 120px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            @php
                $effectiveQty = ($item->dimensionsValue ?? 1) * $item->quantity;
                $lineTotal = $effectiveQty * ($item->unitPrice / 100);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">
                    @if($item->dimensions !== null && $item->dimensions !== '')
                        {{ $item->dimensions }} <small style="color: #666; font-size: 10px;">({{ number_format($item->dimensionsValue, 2) }})</small>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($item->unitPrice / 100, 2) }}</td>
                <td class="text-right">{{ $item->taxRate }}%</td>
                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="financial-summary">
        <div class="summary-title">Financial Summary</div>
        @if(!isset($isPdf) || !$isPdf)
        <div class="summary-row">
            <span>Subtotal:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
        </div>
        @if($totals['discountTotal'] > 0)
        <div class="summary-row">
            <span>Discount:</span>
            <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
        </div>
        @endif
        @if($document->collectTax() && $totals['taxTotal'] > 0)
        <div class="summary-row">
            <span>Tax (16% VAT):</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
        </div>
        @endif
        <div class="summary-grand">
            <span>TOTAL AMOUNT:</span>
            <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
        </div>
        @else
        <table>
            <tr class="summary-row">
                <td>Subtotal:</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
            </tr>
            @if($totals['discountTotal'] > 0)
            <tr class="summary-row">
                <td>Discount:</td>
                <td style="text-align: right;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            @if($document->collectTax() && $totals['taxTotal'] > 0)
            <tr class="summary-row">
                <td>Tax (16% VAT):</td>
                <td style="text-align: right;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
            </tr>
            @endif
            <tr class="summary-grand">
                <td>TOTAL AMOUNT:</td>
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
    
    <div class="terms-formal">
        <div class="terms-title">Terms & Conditions</div>
        <div class="terms-content">
            1. This document is valid for 30 days from the date of issue.<br>
            2. Payment terms: Net 30 days from invoice date.<br>
            3. Prices are exclusive of VAT unless otherwise stated.<br>
            4. Delivery terms and conditions apply as per agreement.
        </div>
    </div>
    
    @if(!isset($isPdf) || !$isPdf)
    <div class="signature-formal">
        <div class="sig-block">
            @if($signaturePath)
                <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 180px; max-height: 60px; margin-bottom: 10px;">
            @else
                <div class="sig-space"></div>
            @endif
            <div class="sig-label">Authorized Signature</div>
            <div class="sig-sublabel">{{ $businessProfile->businessName() }}</div>
        </div>
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
                @else
                <div style="height: 60px; border-bottom: 2px solid #111827; margin-bottom: 10px;"></div>
                @endif
                <div class="sig-label">Authorized Signature</div>
                <div class="sig-sublabel">{{ $businessProfile->businessName() }}</div>
            </td>
        </tr>
    </table>
    @endif
    
    <div class="document-footer">
        This is a computer-generated document. No signature is required for validity.
    </div>
    </div>
</body>
</html>
