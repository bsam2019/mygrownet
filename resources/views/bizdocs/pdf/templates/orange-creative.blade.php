<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orange Creative</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Verdana', sans-serif; color: #1f2937; font-size: 13px; margin: 0; padding: 0; }
        
        .layout-table { width: 100%; border-collapse: collapse; height: 100%; min-height: 297mm; }
        .sidebar-cell { width: 180px; background: #ea580c; padding: 40px 20px; color: white; vertical-align: top; height: 100%; }
        .content-cell { padding: 40px; vertical-align: top; height: 100%; }
        
        .sidebar-logo { width: 100px; height: 100px; background: white; border-radius: 8px; text-align: center; margin: 0 auto 15px; padding: 5px; display: flex; align-items: center; justify-content: center; }
        .sidebar-logo img { max-width: 90px; max-height: 90px; width: auto; height: auto; }
        .sidebar-logo-text { font-size: 28px; font-weight: bold; color: #ea580c; }
        .sidebar-company { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 20px; line-height: 1.3; word-wrap: break-word; }
        .sidebar-info { font-size: 9px; text-align: center; line-height: 1.7; opacity: 0.95; word-wrap: break-word; }
        
        .doc-header { background: #fff7ed; padding: 25px; border-radius: 8px; border-left: 6px solid #ea580c; margin-bottom: 30px; }
        .doc-title { font-size: 32px; font-weight: bold; color: #ea580c; margin-bottom: 10px; }
        .doc-meta { font-size: 12px; color: #6b7280; }
        .doc-meta strong { color: #111827; }
        
        .customer-section { background: white; padding: 20px; border: 2px solid #fed7aa; border-radius: 8px; margin-bottom: 30px; }
        .customer-label { font-size: 10px; font-weight: bold; color: #ea580c; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .customer-name { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 8px; }
        .customer-details { font-size: 12px; color: #6b7280; line-height: 1.7; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table thead { background: #ea580c; color: white; }
        .items-table th { padding: 14px 12px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .items-table td { padding: 14px 12px; border-bottom: 1px solid #fed7aa; }
        .items-table tbody tr:nth-child(odd) { background: #fffbeb; }
        .text-right { text-align: right; }
        
        .totals { background: #fff7ed; padding: 25px; border-radius: 8px; border: 2px solid #ea580c; margin-top: 20px; }
        .total-row { padding: 10px 0; font-size: 14px; }
        .total-row table { width: 100%; }
        .grand-total { padding: 18px 0; font-size: 22px; font-weight: bold; color: #ea580c; margin-top: 15px; border-top: 3px solid #ea580c; }
        .grand-total table { width: 100%; }
        
        .footer-note { margin-top: 40px; text-align: center; font-size: 11px; color: #6b7280; font-style: italic; }
        
        .signature-section { margin-top: 60px; text-align: center; }
        .signature-img { max-width: 200px; max-height: 80px; }
        .signature-line { border-top: 2px solid #ea580c; width: 200px; margin: 10px auto 5px; padding-top: 5px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    <table class="layout-table">
        <tr>
            <td class="sidebar-cell">
                @if($logoPath)
                <div class="sidebar-logo">
                    <img src="{{ $logoPath }}" alt="Logo">
                </div>
                @else
                <div class="sidebar-logo">
                    <span class="sidebar-logo-text">{{ strtoupper(substr($businessProfile->businessName(), 0, 2)) }}</span>
                </div>
                @endif
                <div class="sidebar-company">{{ $businessProfile->businessName() }}</div>
                <div class="sidebar-info">
                    @if($businessProfile->address()){{ $businessProfile->address() }}<br><br>@endif
                    @if($businessProfile->phone()){{ $businessProfile->phone() }}<br><br>@endif
                    @if($businessProfile->email()){{ $businessProfile->email() }}@endif
                    @if($businessProfile->website())<br><br>{{ $businessProfile->website() }}@endif
                    @if($businessProfile->tpin())<br><br>TPIN: {{ $businessProfile->tpin() }}@endif
                </div>
            </td>
            <td class="content-cell">
                <div class="doc-header">
                    <div class="doc-title">{{ strtoupper($document->type()->value()) }}</div>
                    <div class="doc-meta">
                        <strong>Document Number:</strong> {{ $document->number()->value() }} &nbsp;|&nbsp;
                        <strong>Date:</strong> {{ $document->issueDate()->format('d M Y') }}
                        @if($document->dueDate()) &nbsp;|&nbsp; <strong>Due:</strong> {{ $document->dueDate()->format('d M Y') }}@endif
                    </div>
                </div>
                
                <div class="customer-section">
                    <div class="customer-label">Billed To</div>
                    <div class="customer-name">{{ $customer->name() }}</div>
                    <div class="customer-details">
                        @if($customer->address()){{ $customer->address() }}<br>@endif
                        @if($customer->phone())Tel: {{ $customer->phone() }}<br>@endif
                        @if($customer->email())Email: {{ $customer->email() }}@endif
                    </div>
                </div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-right">Dimensions</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Tax</th>
                            <th class="text-right">Amount</th>
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
                
                <div class="totals">
                    <div class="total-row">
                        <table>
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    @if($totals['discountTotal'] > 0)
                    <div class="total-row">
                        <table>
                            <tr>
                                <td>Discount:</td>
                                <td class="text-right">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    @endif
                    @if($document->collectTax() && $totals['taxTotal'] > 0)
                    <div class="total-row">
                        <table>
                            <tr>
                                <td>Tax:</td>
                                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    @endif
                    <div class="grand-total">
                        <table>
                            <tr>
                                <td>TOTAL DUE:</td>
                                <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($signaturePath)
                <div class="signature-section">
                    <img src="{{ $signaturePath }}" alt="Signature" class="signature-img">
                    <div class="signature-line">Authorized Signature</div>
                </div>
                @else
                <div class="signature-section">
                    <div class="signature-line">Authorized Signature</div>
                </div>
                @endif
                
                <div class="footer-note">
                    Thank you for choosing {{ $businessProfile->businessName() }}. We appreciate your business!
                </div>
            </td>
        </tr>
    </table>
    
    @if($businessProfile->website())
    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #fed7aa; font-size: 12px; color: #6b7280;">
        Visit us: <span style="color: #ea580c; font-weight: bold;">{{ $businessProfile->website() }}</span>
    </div>
    @endif
</body>
</html>
