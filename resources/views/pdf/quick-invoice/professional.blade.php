{{-- Professional Template - Corporate style with structured layout --}}
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
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: {{ $textColor }}; }
        .page-border { border: 2px solid {{ $primaryColor }}; margin: 15px; padding: 25px; min-height: calc(100vh - 30px); }
        .header { display: table; width: 100%; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid {{ $primaryColor }}; }
        .logo-section { display: table-cell; width: 40%; vertical-align: middle; }
        .logo { max-width: 150px; max-height: 60px; }
        .title-section { display: table-cell; width: 60%; text-align: right; vertical-align: middle; }
        .document-type { font-size: 28px; font-weight: bold; color: {{ $primaryColor }}; text-transform: uppercase; }
        .document-number { font-size: 12px; color: #dc2626; margin-top: 5px; }
        .company-bar { background: {{ $primaryColor }}; color: white; padding: 12px 15px; margin-bottom: 20px; }
        .company-bar .name { font-size: 14px; font-weight: bold; }
        .company-bar .details { font-size: 9px; opacity: 0.9; margin-top: 3px; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 8px; vertical-align: top; }
        .info-table .label-cell { width: 15%; background: #f3f4f6; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .info-table .value-cell { width: 35%; }
        .client-box { background: #f8fafc; border: 1px solid #e5e7eb; padding: 12px; margin-bottom: 20px; }
        .client-label { font-size: 9px; color: {{ $primaryColor }}; text-transform: uppercase; font-weight: bold; margin-bottom: 8px; }
        .client-name { font-size: 13px; font-weight: bold; margin-bottom: 5px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: {{ $primaryColor }}; color: white; padding: 10px 8px; text-align: left; font-size: 9px; text-transform: uppercase; border: 1px solid {{ $primaryColor }}; }
        table.items th.qty { width: 70px; text-align: center; }
        table.items th.price, table.items th.amount { width: 85px; text-align: right; }
        table.items td { padding: 10px 8px; border: 1px solid #e5e7eb; }
        table.items td.qty { text-align: center; background: #f9fafb; }
        table.items td.price, table.items td.amount { text-align: right; }
        table.items tr.even td { background: #fafafa; }
        table.items tr.even td.qty { background: #f3f4f6; }
        .totals-wrapper { display: table; width: 100%; }
        .totals-spacer { display: table-cell; width: 60%; }
        .totals { display: table-cell; width: 40%; }
        .totals-row { display: table; width: 100%; border: 1px solid #e5e7eb; border-top: none; }
        .totals-row.first { border-top: 1px solid #e5e7eb; }
        .totals-label { display: table-cell; width: 50%; padding: 8px; background: #f9fafb; font-size: 9px; }
        .totals-value { display: table-cell; width: 50%; padding: 8px; text-align: right; }
        .totals-row.total { background: {{ $primaryColor }}; }
        .totals-row.total .totals-label, .totals-row.total .totals-value { color: white; font-weight: bold; font-size: 12px; background: transparent; }
        .notes-section { margin-top: 20px; border: 1px solid #e5e7eb; }
        .notes-header { background: #f3f4f6; padding: 8px 12px; font-weight: bold; font-size: 9px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        .notes-content { padding: 12px; color: #6b7280; font-size: 10px; }
        .terms-list { margin: 0; padding-left: 15px; }
        .terms-list li { margin-bottom: 3px; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .signature-table td { border: 1px solid #d1d5db; padding: 8px 12px; vertical-align: top; }
        .signature-table .sig-header { background: {{ $primaryColor }}; color: white; font-weight: bold; font-size: 10px; }
        .signature-table .sig-space { height: 50px; background: #fafafa; }
        .signature-table .sig-date { font-size: 10px; background: #f9fafb; }
        .signature-image { max-height: 40px; max-width: 150px; }
        .footer { margin-top: 25px; text-align: center; padding-top: 15px; border-top: 2px solid {{ $primaryColor }}; color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
    <div class="page-border">
        <div class="header">
            <div class="logo-section">
                @if(!empty($document['business_info']['logo']))
                    <img src="{{ $document['business_info']['logo'] }}" class="logo" alt="Logo">
                @endif
            </div>
            <div class="title-section">
                <div class="document-type">{{ $document['type_label'] }}</div>
                <div class="document-number">#{{ $document['document_number'] }}</div>
            </div>
        </div>

        <div class="company-bar">
            <div class="name">{{ $document['business_info']['name'] }}</div>
            <div class="details">
                {{ $document['business_info']['address'] ?? '' }}
                @if(!empty($document['business_info']['phone'])) | Tel: {{ $document['business_info']['phone'] }}@endif
                @if(!empty($document['business_info']['email'])) | {{ $document['business_info']['email'] }}@endif
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label-cell">Date</td>
                <td class="value-cell">{{ \Carbon\Carbon::parse($document['issue_date'])->format('d/m/Y') }}</td>
                @if($showDueDate && !empty($document['due_date']))
                <td class="label-cell">Due Date</td>
                <td class="value-cell">{{ \Carbon\Carbon::parse($document['due_date'])->format('d/m/Y') }}</td>
                @else
                <td></td><td></td>
                @endif
            </tr>
            @if(!empty($document['business_info']['tax_number']))
            <tr>
                <td class="label-cell">TPIN</td>
                <td class="value-cell">{{ $document['business_info']['tax_number'] }}</td>
                <td></td><td></td>
            </tr>
            @endif
        </table>

        <div class="client-box">
            <div class="client-label">{{ $clientLabel }}</div>
            <div class="client-name">{{ $document['client_info']['name'] }}</div>
            <div style="color: #6b7280;">
                @if(!empty($document['client_info']['address'])){{ $document['client_info']['address'] }}<br>@endif
                @if(!empty($document['client_info']['phone'])){{ $document['client_info']['phone'] }}@endif
                @if(!empty($document['client_info']['email'])) | {{ $document['client_info']['email'] }}@endif
            </div>
        </div>

        <!-- Items Table -->
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
        <div class="totals-wrapper">
            <div class="totals-spacer"></div>
            <div class="totals">
                <div class="totals-row first">
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
        </div>
        @endif

        <!-- Notes -->
        @if(!empty($document['notes']))
        <div class="notes-section">
            <div class="notes-header">Notes</div>
            <div class="notes-content">{{ $document['notes'] }}</div>
        </div>
        @endif

        <!-- Terms & Conditions -->
        @if(count($termsArray) > 0)
        <div class="notes-section" style="margin-top: 15px;">
            <div class="notes-header">Terms & Conditions</div>
            <div class="notes-content">
                <ul class="terms-list">
                    @foreach($termsArray as $term)
                    <li>{{ $term }}</li>
                    @endforeach
                </ul>
            </div>
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
    </div>
</body>
</html>
