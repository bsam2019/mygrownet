{{-- Minimal Template - Simple and elegant with whitespace --}}
@php
    $primaryColor = $document['colors']['primary'] ?? '#2563eb';
    $textColor = $document['colors']['text'] ?? '#1f2937';
    $symbol = match($document['currency']) {
        'ZMW' => 'K', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ZAR' => 'R',
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
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: {{ $textColor }}; padding: 40px 50px; }
        .header { margin-bottom: 50px; }
        .logo { max-width: 120px; max-height: 50px; margin-bottom: 15px; }
        .company-name { font-size: 14px; font-weight: 600; letter-spacing: 1px; }
        .company-details { color: #9ca3af; font-size: 10px; margin-top: 5px; }
        .document-header { display: table; width: 100%; margin-bottom: 40px; }
        .document-title { display: table-cell; width: 50%; }
        .document-title h1 { font-size: 32px; font-weight: 300; color: {{ $textColor }}; letter-spacing: -1px; }
        .document-meta { display: table-cell; width: 50%; text-align: right; vertical-align: bottom; }
        .document-number { font-size: 11px; color: #6b7280; }
        .info-section { display: table; width: 100%; margin-bottom: 40px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; }
        .info-box.right { text-align: right; }
        .info-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .client-name { font-size: 13px; font-weight: 600; }
        .info-value { color: #6b7280; font-size: 10px; line-height: 1.6; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { border-bottom: 1px solid {{ $textColor }}; padding: 12px 0; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; font-weight: normal; }
        table.items th.qty { width: 80px; }
        table.items th.price, table.items th.amount { width: 100px; text-align: right; }
        table.items td { padding: 15px 0; border-bottom: 1px solid #f3f4f6; }
        table.items td.qty { color: #6b7280; }
        table.items td.price, table.items td.amount { text-align: right; }
        .totals { width: 250px; margin-left: auto; margin-bottom: 40px; }
        .totals-row { display: table; width: 100%; padding: 8px 0; }
        .totals-label { display: table-cell; width: 50%; color: #9ca3af; font-size: 10px; }
        .totals-value { display: table-cell; width: 50%; text-align: right; }
        .totals-row.total { border-top: 1px solid {{ $textColor }}; margin-top: 10px; padding-top: 15px; }
        .totals-row.total .totals-label { color: {{ $textColor }}; font-weight: 600; }
        .totals-row.total .totals-value { font-size: 18px; font-weight: 600; }
        .notes-section { margin-top: 40px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
        .notes-title { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .notes-content { color: #6b7280; font-size: 10px; line-height: 1.6; }
        .terms-list { margin: 0; padding-left: 15px; color: #6b7280; font-size: 10px; }
        .terms-list li { margin-bottom: 3px; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 50px; }
        .signature-table td { border: 1px solid #e5e7eb; padding: 8px 12px; vertical-align: top; }
        .signature-table .sig-header { background: #f9fafb; font-weight: 600; font-size: 10px; }
        .signature-table .sig-space { height: 50px; }
        .signature-table .sig-date { font-size: 10px; color: #9ca3af; }
        .signature-image { max-height: 40px; max-width: 150px; }
        .footer { margin-top: 50px; text-align: center; color: #d1d5db; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($document['business_info']['logo']))
            <img src="{{ $document['business_info']['logo'] }}" class="logo" alt="Logo">
        @endif
        <div class="company-name">{{ $document['business_info']['name'] }}</div>
        <div class="company-details">
            {{ $document['business_info']['address'] ?? '' }}
            @if(!empty($document['business_info']['phone'])) · {{ $document['business_info']['phone'] }}@endif
        </div>
    </div>

    <div class="document-header">
        <div class="document-title">
            <h1>{{ $document['type_label'] }}</h1>
        </div>
        <div class="document-meta">
            <div class="document-number">{{ $document['document_number'] }}</div>
            <div style="margin-top: 5px; color: #6b7280;">{{ \Carbon\Carbon::parse($document['issue_date'])->format('d M Y') }}</div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-box">
            <div class="info-label">{{ $clientLabel }}</div>
            <div class="client-name">{{ $document['client_info']['name'] }}</div>
            <div class="info-value">
                @if(!empty($document['client_info']['address'])){{ $document['client_info']['address'] }}<br>@endif
                @if(!empty($document['client_info']['phone'])){{ $document['client_info']['phone'] }}<br>@endif
                @if(!empty($document['client_info']['email'])){{ $document['client_info']['email'] }}@endif
            </div>
        </div>
        <div class="info-box right">
            @if($showDueDate && !empty($document['due_date']))
            <div class="info-label">Due Date</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($document['due_date'])->format('d M Y') }}</div>
            @endif
            @if(!empty($document['business_info']['tax_number']))
            <br><div class="info-label">TPIN</div>
            <div class="info-value">{{ $document['business_info']['tax_number'] }}</div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
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

    <!-- Totals -->
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
            <div class="totals-label">Total</div>
            <div class="totals-value">{{ $symbol }} {{ number_format($document['total'], 2) }}</div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if(!empty($document['notes']))
    <div class="notes-section">
        <div class="notes-title">Notes</div>
        <div class="notes-content">{{ $document['notes'] }}</div>
    </div>
    @endif

    <!-- Terms & Conditions -->
    @if(count($termsArray) > 0)
    <div class="notes-section" style="margin-top: 20px; border-top: none;">
        <div class="notes-title">Terms & Conditions:</div>
        <ul class="terms-list">
            @foreach($termsArray as $term)
            <li>{{ $term }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Signature Section -->
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
