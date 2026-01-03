{{-- Classic Template - Traditional business style --}}
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
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: {{ $textColor }}; padding: 30px; }
        .header { display: table; width: 100%; margin-bottom: 30px; border-bottom: 3px solid {{ $primaryColor }}; padding-bottom: 20px; }
        .logo-section { display: table-cell; width: 50%; vertical-align: top; }
        .logo { max-width: 160px; max-height: 70px; }
        .company-section { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .company-name { font-size: 18px; font-weight: bold; color: {{ $textColor }}; margin-bottom: 5px; }
        .company-details { color: #6b7280; font-size: 10px; line-height: 1.5; }
        .document-title { text-align: center; margin: 25px 0; }
        .document-title h1 { font-size: 26px; color: {{ $primaryColor }}; text-transform: uppercase; letter-spacing: 3px; }
        .document-number { font-size: 13px; color: #dc2626; font-weight: bold; margin-top: 5px; }
        .info-section { display: table; width: 100%; margin-bottom: 25px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; padding: 10px; }
        .info-box.right { text-align: right; }
        .info-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; }
        .client-name { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: {{ $primaryColor }}; color: white; padding: 10px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items th.qty { width: 70px; text-align: center; }
        table.items th.price, table.items th.amount { width: 90px; text-align: right; }
        table.items td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; }
        table.items td.qty { text-align: center; }
        table.items td.price, table.items td.amount { text-align: right; }
        table.items tr.even { background: #f9fafb; }
        .totals { width: 280px; margin-left: auto; margin-bottom: 25px; }
        .totals-row { display: table; width: 100%; padding: 6px 0; border-bottom: 1px solid #e5e7eb; }
        .totals-label { display: table-cell; width: 50%; color: #6b7280; }
        .totals-value { display: table-cell; width: 50%; text-align: right; font-weight: 500; }
        .totals-row.total { border-bottom: none; border-top: 2px solid {{ $primaryColor }}; margin-top: 5px; padding-top: 10px; }
        .totals-row.total .totals-label, .totals-row.total .totals-value { font-size: 14px; font-weight: bold; color: {{ $primaryColor }}; }
        .notes-section { margin-top: 25px; padding: 12px; background: #f9fafb; }
        .notes-title { font-weight: bold; margin-bottom: 4px; color: #374151; font-size: 10px; }
        .notes-content { color: #6b7280; font-size: 10px; }
        .terms-list { margin: 0; padding-left: 15px; }
        .terms-list li { margin-bottom: 3px; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .signature-table td { border: 1px solid #d1d5db; padding: 8px 12px; vertical-align: top; }
        .signature-table .sig-header { background: #f9fafb; font-weight: bold; font-size: 10px; }
        .signature-table .sig-space { height: 50px; }
        .signature-table .sig-date { font-size: 10px; }
        .signature-image { max-height: 40px; max-width: 150px; }
        .footer { margin-top: 35px; text-align: center; color: #9ca3af; font-size: 9px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
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
                @if(!empty($document['business_info']['phone']))Tel: {{ $document['business_info']['phone'] }}<br>@endif
                @if(!empty($document['business_info']['email'])){{ $document['business_info']['email'] }}@endif
            </div>
        </div>
    </div>

    <div class="document-title">
        <h1>{{ $document['type_label'] }}</h1>
        <div class="document-number">#{{ $document['document_number'] }}</div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-label">{{ $clientLabel }}</div>
            <div class="client-name">{{ $document['client_info']['name'] }}</div>
            <div style="color: #6b7280; font-size: 10px;">
                @if(!empty($document['client_info']['address'])){{ $document['client_info']['address'] }}<br>@endif
                @if(!empty($document['client_info']['phone'])){{ $document['client_info']['phone'] }}<br>@endif
                @if(!empty($document['client_info']['email'])){{ $document['client_info']['email'] }}@endif
            </div>
        </div>
        <div class="info-box right">
            <div class="info-label">Date</div>
            <div>{{ \Carbon\Carbon::parse($document['issue_date'])->format('d/m/Y') }}</div>
            @if($showDueDate && !empty($document['due_date']))
                <br><div class="info-label">Due Date</div>
                <div>{{ \Carbon\Carbon::parse($document['due_date'])->format('d/m/Y') }}</div>
            @endif
            @if(!empty($document['business_info']['tax_number']))
                <br><div class="info-label">Tax Number (TPIN)</div>
                <div>{{ $document['business_info']['tax_number'] }}</div>
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th class="qty">Qty</th>
                <th>Description</th>
                @if($document['type'] !== 'delivery_note')
                <th class="price">Unit Price</th>
                <th class="amount">Amount</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($document['items'] as $index => $item)
            <tr class="{{ $index % 2 == 1 ? 'even' : '' }}">
                <td class="qty">{{ number_format($item['quantity'], $item['quantity'] == floor($item['quantity']) ? 0 : 2) }}{{ !empty($item['unit']) ? ' ' . $item['unit'] : '' }}</td>
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
</body>
</html>
