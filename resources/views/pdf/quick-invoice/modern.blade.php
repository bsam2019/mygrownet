{{-- Modern Template - Contemporary design with colored header --}}
@php
    $primaryColor = $document['colors']['primary'] ?? '#2563eb';
    $textColor = $document['colors']['text'] ?? '#1f2937';
    $symbol = match($document['currency']) {
        'ZMW' => 'K',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'ZAR' => 'R',
        default => $document['currency'],
    };
    $showDueDate = $document['type'] === 'invoice';
    $termsArray = !empty($document['terms']) ? array_filter(array_map('trim', preg_split('/[\r\n]+/', $document['terms']))) : [];
    $clientLabel = match($document['type']) {
        'invoice' => 'Bill To',
        'quotation' => 'Quote For',
        'delivery_note' => 'Deliver To',
        'receipt' => 'Received From',
        default => 'Client',
    };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document['type_label'] }} - {{ $document['document_number'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: {{ $textColor }}; }
        .header-bar { background: {{ $primaryColor }}; padding: 25px 30px; color: white; }
        .header { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 50%; vertical-align: middle; }
        .logo { max-width: 140px; max-height: 60px; background: white; padding: 8px; }
        .company-section { display: table-cell; width: 50%; text-align: right; vertical-align: middle; }
        .company-name { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .company-details { font-size: 10px; opacity: 0.9; line-height: 1.5; }
        .content { padding: 30px; }
        .document-badge { display: inline-block; background: {{ $primaryColor }}; color: white; padding: 8px 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; }
        .document-number { font-size: 24px; font-weight: bold; color: {{ $textColor }}; margin-bottom: 25px; }
        .info-grid { display: table; width: 100%; margin-bottom: 30px; }
        .info-card { display: table-cell; width: 50%; vertical-align: top; }
        .info-card-inner { background: #f8fafc; padding: 15px; margin-right: 10px; }
        .info-card.right .info-card-inner { margin-right: 0; margin-left: 10px; text-align: right; }
        .info-label { font-size: 9px; color: {{ $primaryColor }}; text-transform: uppercase; font-weight: bold; margin-bottom: 8px; }
        .client-name { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table.items th { background: {{ $textColor }}; color: white; padding: 12px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items th.qty { width: 70px; text-align: center; }
        table.items th.price, table.items th.amount { width: 90px; text-align: right; }
        table.items td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; }
        table.items td.qty { text-align: center; }
        table.items td.price, table.items td.amount { text-align: right; }
        .totals { width: 300px; margin-left: auto; background: #f8fafc; padding: 15px; }
        .totals-row { display: table; width: 100%; padding: 8px 0; }
        .totals-label { display: table-cell; width: 50%; color: #6b7280; }
        .totals-value { display: table-cell; width: 50%; text-align: right; font-weight: 500; }
        .totals-row.total { border-top: 2px solid {{ $primaryColor }}; margin-top: 8px; padding-top: 12px; }
        .totals-row.total .totals-label, .totals-row.total .totals-value { font-size: 16px; font-weight: bold; color: {{ $primaryColor }}; }
        .notes-section { margin-top: 25px; padding: 15px; background: #f8fafc; border-left: 4px solid {{ $primaryColor }}; }
        .notes-title { font-weight: bold; margin-bottom: 5px; color: {{ $primaryColor }}; font-size: 10px; text-transform: uppercase; }
        .notes-content { color: #6b7280; font-size: 10px; }
        .terms-list { margin: 0; padding-left: 15px; color: #6b7280; font-size: 10px; }
        .terms-list li { margin-bottom: 3px; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .signature-table td { border: 1px solid #d1d5db; padding: 8px 12px; vertical-align: top; }
        .signature-table .sig-header { background: #f8fafc; font-weight: bold; font-size: 10px; color: {{ $primaryColor }}; }
        .signature-table .sig-space { height: 50px; }
        .signature-table .sig-date { font-size: 10px; }
        .signature-image { max-height: 40px; max-width: 150px; }
        .footer { margin-top: 35px; text-align: center; color: #9ca3af; font-size: 9px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header-bar">
        <div class="header">
            <div class="logo-section">
                @if(!empty($document['business_info']['logo']))
                    <img src="{{ $document['business_info']['logo'] }}" class="logo" alt="Logo">
                @endif
            </div>
            <div class="company-section">
                <div class="company-name">{{ $document['business_info']['name'] }}</div>
                <div class="company-details">
                    @if(!empty($document['business_info']['address'])){{ $document['business_info']['address'] }}<br>@endif
                    @if(!empty($document['business_info']['phone'])){{ $document['business_info']['phone'] }} @endif
                    @if(!empty($document['business_info']['email']))| {{ $document['business_info']['email'] }}@endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="content">
        <div class="document-badge">{{ $document['type_label'] }}</div>
        <div class="document-number">#{{ $document['document_number'] }}</div>
        
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-inner">
                    <div class="info-label">{{ $clientLabel }}</div>
                    <div class="client-name">{{ $document['client_info']['name'] }}</div>
                    <div style="color: #6b7280; font-size: 10px;">
                        @if(!empty($document['client_info']['address'])){{ $document['client_info']['address'] }}<br>@endif
                        @if(!empty($document['client_info']['phone'])){{ $document['client_info']['phone'] }}<br>@endif
                        @if(!empty($document['client_info']['email'])){{ $document['client_info']['email'] }}@endif
                    </div>
                </div>
            </div>
            <div class="info-card right">
                <div class="info-card-inner">
                    <div class="info-label">Document Details</div>
                    <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($document['issue_date'])->format('d M Y') }}</div>
                    @if($showDueDate && !empty($document['due_date']))
                    <div><strong>Due:</strong> {{ \Carbon\Carbon::parse($document['due_date'])->format('d M Y') }}</div>
                    @endif
                    @if(!empty($document['business_info']['tax_number']))
                    <div><strong>TPIN:</strong> {{ $document['business_info']['tax_number'] }}</div>
                    @endif
                </div>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th class="qty">Qty</th>
                    <th>Description</th>
                    @if($document['type'] !== 'delivery_note')
                    <th class="price">Price</th>
                    <th class="amount">Amount</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($document['items'] as $item)
                <tr>
                    <td class="qty">{{ number_format($item['quantity'], $item['quantity'] == floor($item['quantity']) ? 0 : 2) }}{{ !empty($item['unit']) ? ' '.$item['unit'] : '' }}</td>
                    <td>{{ $item['description'] }}</td>
                    @if($document['type'] !== 'delivery_note')
                    <td class="price">{{ $symbol }} {{ number_format($item['unit_price'], 2) }}</td>
                    <td class="amount">{{ $symbol }} {{ number_format($item['amount'], 2) }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($document['type'] !== 'delivery_note')
        <div class="totals">
            <div class="totals-row">
                <div class="totals-label">Subtotal</div>
                <div class="totals-value">{{ $symbol }} {{ number_format($document['subtotal'], 2) }}</div>
            </div>
            @if($document['discount_rate'] > 0)
            <div class="totals-row">
                <div class="totals-label">Discount ({{ $document['discount_rate'] }}%)</div>
                <div class="totals-value">- {{ $symbol }} {{ number_format($document['discount_amount'], 2) }}</div>
            </div>
            @endif
            @if($document['tax_rate'] > 0)
            <div class="totals-row">
                <div class="totals-label">Tax ({{ $document['tax_rate'] }}%)</div>
                <div class="totals-value">{{ $symbol }} {{ number_format($document['tax_amount'], 2) }}</div>
            </div>
            @endif
            <div class="totals-row total">
                <div class="totals-label">TOTAL</div>
                <div class="totals-value">{{ $symbol }} {{ number_format($document['total'], 2) }}</div>
            </div>
        </div>
        @endif

        @if(!empty($document['notes']))
        <div class="notes-section">
            <div class="notes-title">Notes</div>
            <div class="notes-content">{{ $document['notes'] }}</div>
        </div>
        @endif

        @if(count($termsArray) > 0)
        <div class="notes-section" style="margin-top: 15px;">
            <div class="notes-title">Terms & Conditions:</div>
            <ul class="terms-list">
                @foreach($termsArray as $term)
                <li>{{ $term }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <table class="signature-table">
            <tr>
                <td class="sig-header" colspan="2">Prepared By:</td>
            </tr>
            <tr>
                <td class="sig-space" style="width: 50%;">
                    @if(!empty($document['prepared_by']))
                    <div style="font-weight: bold; font-size: 12px;">{{ $document['prepared_by'] }}</div>
                    @endif
                    <div class="sig-date" style="margin-top: 5px;">Date: {{ \Carbon\Carbon::parse($document['issue_date'])->format('d/m/Y') }}</div>
                </td>
                <td class="sig-space" style="width: 50%; text-align: right;">
                    @if(!empty($document['signature']))
                    <img src="{{ $document['signature'] }}" class="signature-image" alt="Signature">
                    @endif
                </td>
            </tr>
        </table>

        <div class="footer">Generated with MyGrowNet Quick Invoice | mygrownet.com/quick-invoice</div>
    </div>
</body>
</html>
