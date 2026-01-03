{{-- Bold Template - High contrast, impactful design --}}
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
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: {{ $textColor }}; }
        .header-bar { background: {{ $primaryColor }}; color: white; padding: 25px 30px; }
        .header-content { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 50%; vertical-align: middle; }
        .logo { max-width: 140px; max-height: 55px; background: white; padding: 5px; }
        .title-section { display: table-cell; width: 50%; text-align: right; vertical-align: middle; }
        .document-type { font-size: 32px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .document-number { font-size: 14px; opacity: 0.9; margin-top: 5px; }
        .content { padding: 25px 30px; }
        .company-info { background: #1f2937; color: white; padding: 12px 20px; margin-bottom: 20px; font-size: 10px; }
        .company-name { font-weight: bold; font-size: 12px; }
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; }
        .info-box { background: #f8fafc; border-left: 4px solid {{ $primaryColor }}; padding: 15px; margin-bottom: 10px; }
        .info-label { font-size: 9px; color: {{ $primaryColor }}; text-transform: uppercase; font-weight: bold; margin-bottom: 8px; }
        .info-value { font-size: 11px; }
        .client-name { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        .date-box { background: {{ $primaryColor }}; color: white; padding: 12px 15px; text-align: center; margin-left: 15px; }
        .date-label { font-size: 9px; text-transform: uppercase; opacity: 0.8; }
        .date-value { font-size: 14px; font-weight: bold; margin-top: 3px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #1f2937; color: white; padding: 12px 10px; text-align: left; font-size: 10px; text-transform: uppercase; font-weight: bold; }
        table.items th.qty { width: 70px; text-align: center; }
        table.items th.price, table.items th.amount { width: 90px; text-align: right; }
        table.items td { padding: 12px 10px; border-bottom: 2px solid #e5e7eb; }
        table.items td.qty { text-align: center; font-weight: bold; background: #f9fafb; }
        table.items td.price, table.items td.amount { text-align: right; }
        table.items tr:hover td { background: #f8fafc; }
        table.items tr:hover td.qty { background: #f3f4f6; }
        .totals-wrapper { display: table; width: 100%; margin-bottom: 20px; }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals { display: table-cell; width: 45%; }
        .totals-row { display: table; width: 100%; background: #f8fafc; margin-bottom: 2px; }
        .totals-label { display: table-cell; width: 50%; padding: 10px 15px; font-weight: 500; }
        .totals-value { display: table-cell; width: 50%; padding: 10px 15px; text-align: right; font-weight: bold; }
        .totals-row.total { background: {{ $primaryColor }}; }
        .totals-row.total .totals-label, .totals-row.total .totals-value { color: white; font-size: 14px; }
        .notes-section { background: #f8fafc; border-left: 4px solid {{ $primaryColor }}; padding: 15px; margin-bottom: 15px; }
        .notes-title { font-weight: bold; font-size: 10px; text-transform: uppercase; color: {{ $primaryColor }}; margin-bottom: 8px; }
        .notes-content { color: #6b7280; font-size: 10px; }
        .terms-list { margin: 0; padding-left: 15px; }
        .terms-list li { margin-bottom: 3px; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .signature-table td { border: 2px solid #1f2937; padding: 10px 15px; vertical-align: top; }
        .signature-table .sig-header { background: #1f2937; color: white; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .signature-table .sig-space { height: 55px; background: #fafafa; }
        .signature-table .sig-date { font-size: 10px; background: #f3f4f6; }
        .signature-image { max-height: 45px; max-width: 150px; }
        .footer { background: #1f2937; color: white; padding: 12px 30px; text-align: center; font-size: 9px; margin-top: 25px; }
    </style>
</head>
<body>
    <!-- Header Bar -->
    <div class="header-bar">
        <div class="header-content">
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
    </div>

    <!-- Company Info Bar -->
    <div class="company-info">
        <span class="company-name">{{ $document['business_info']['name'] }}</span>
        @if(!empty($document['business_info']['address'])) | {{ $document['business_info']['address'] }}@endif
        @if(!empty($document['business_info']['phone'])) | Tel: {{ $document['business_info']['phone'] }}@endif
        @if(!empty($document['business_info']['email'])) | {{ $document['business_info']['email'] }}@endif
    </div>

    <div class="content">
        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-col">
                <div class="info-box">
                    <div class="info-label">{{ $clientLabel }}</div>
                    <div class="client-name">{{ $document['client_info']['name'] }}</div>
                    <div class="info-value" style="color: #6b7280;">
                        @if(!empty($document['client_info']['address'])){{ $document['client_info']['address'] }}<br>@endif
                        @if(!empty($document['client_info']['phone'])){{ $document['client_info']['phone'] }}<br>@endif
                        @if(!empty($document['client_info']['email'])){{ $document['client_info']['email'] }}@endif
                    </div>
                </div>
                @if(!empty($document['business_info']['tax_number']))
                <div class="info-box">
                    <div class="info-label">Tax Number (TPIN)</div>
                    <div class="info-value">{{ $document['business_info']['tax_number'] }}</div>
                </div>
                @endif
            </div>
            <div class="info-col">
                <div class="date-box">
                    <div class="date-label">Date</div>
                    <div class="date-value">{{ \Carbon\Carbon::parse($document['issue_date'])->format('d/m/Y') }}</div>
                </div>
                @if($showDueDate && !empty($document['due_date']))
                <div class="date-box" style="margin-top: 10px; background: #dc2626;">
                    <div class="date-label">Due Date</div>
                    <div class="date-value">{{ \Carbon\Carbon::parse($document['due_date'])->format('d/m/Y') }}</div>
                </div>
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
                    <th class="price">Unit Price</th>
                    <th class="amount">Amount</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($document['items'] as $index => $item)
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
        <div class="totals-wrapper">
            <div class="totals-spacer"></div>
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
        <div class="notes-section">
            <div class="notes-title">Terms & Conditions</div>
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
    </div>

    <!-- Footer -->
    <div class="footer">Generated with MyGrowNet Quick Invoice | mygrownet.com/quick-invoice</div>
</body>
</html>
