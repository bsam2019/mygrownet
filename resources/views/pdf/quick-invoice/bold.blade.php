{{-- Bold Template - Eye-catching design with strong colors --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document['type_label'] }} - {{ $document['document_number'] }}</title>
    <style>
        :root { --primary: {{ $document['colors']['primary'] ?? '#2563eb' }}; --secondary: {{ $document['colors']['secondary'] ?? '#1e40af' }}; --accent: {{ $document['colors']['accent'] ?? '#059669' }}; --text: {{ $document['colors']['text'] ?? '#1f2937' }}; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: var(--text); }
        .header-block { background: var(--primary); padding: 30px; position: relative; }
        .header-block::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 8px; background: var(--accent); }
        .header { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 50%; vertical-align: middle; }
        .logo { max-width: 140px; max-height: 60px; background: white; padding: 10px; border-radius: 8px; }
        .doc-section { display: table-cell; width: 50%; text-align: right; vertical-align: middle; color: white; }
        .doc-type { font-size: 32px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .doc-number { font-size: 14px; opacity: 0.9; margin-top: 5px; }
        .content { padding: 30px; }
        .company-strip { background: var(--text); color: white; padding: 15px 30px; margin: -30px -30px 30px -30px; }
        .company-name { font-size: 16px; font-weight: bold; }
        .company-details { font-size: 10px; opacity: 0.8; margin-top: 3px; }
        .info-blocks { display: table; width: 100%; margin-bottom: 30px; }
        .info-block { display: table-cell; width: 50%; vertical-align: top; }
        .info-card { background: white; border: 3px solid var(--primary); border-radius: 10px; padding: 20px; margin-right: 15px; }
        .info-block:last-child .info-card { margin-right: 0; margin-left: 15px; }
        .info-label { font-size: 10px; color: var(--primary); text-transform: uppercase; font-weight: bold; letter-spacing: 1px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid var(--primary); }
        .client-name { font-size: 16px; font-weight: bold; margin-bottom: 8px; }
        .info-value { color: #6b7280; font-size: 11px; line-height: 1.6; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 25px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table.items th { background: var(--primary); color: white; padding: 15px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        table.items th.qty { width: 60px; text-align: center; }
        table.items th.price, table.items th.amount { width: 100px; text-align: right; }
        table.items td { padding: 15px 12px; border-bottom: 2px solid #f3f4f6; }
        table.items td.qty { text-align: center; font-weight: bold; color: var(--primary); }
        table.items td.price, table.items td.amount { text-align: right; }
        table.items tr:last-child td { border-bottom: none; }
        .totals-box { background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 10px; padding: 20px; width: 320px; margin-left: auto; color: white; }
        .totals-row { display: table; width: 100%; padding: 8px 0; }
        .totals-label { display: table-cell; width: 50%; opacity: 0.9; }
        .totals-value { display: table-cell; width: 50%; text-align: right; font-weight: 500; }
        .totals-row.total { border-top: 2px solid rgba(255,255,255,0.3); margin-top: 10px; padding-top: 15px; }
        .totals-row.total .totals-label, .totals-row.total .totals-value { font-size: 18px; font-weight: bold; opacity: 1; }
        .notes-section { margin-top: 30px; background: #f8fafc; border-radius: 10px; padding: 20px; border-left: 5px solid var(--accent); }
        .notes-title { font-weight: bold; color: var(--accent); font-size: 11px; text-transform: uppercase; margin-bottom: 8px; }
        .notes-content { color: #6b7280; font-size: 11px; line-height: 1.6; }
        .signature-section { margin-top: 40px; display: table; width: 100%; }
        .signature-box { display: table-cell; width: 33%; padding: 15px; text-align: center; }
        .signature-image { max-height: 50px; margin-bottom: 8px; }
        .signature-line { background: var(--primary); color: white; margin-top: 45px; padding: 10px; border-radius: 5px; font-size: 10px; }
        .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header-block">
        <div class="header">
            <div class="logo-section">
                @if($document['business_info']['logo'])
                    <img src="{{ $document['business_info']['logo'] }}" class="logo" alt="Logo">
                @endif
            </div>
            <div class="doc-section">
                <div class="doc-type">{{ $document['type_label'] }}</div>
                <div class="doc-number">#{{ $document['document_number'] }}</div>
            </div>
        </div>
    </div>
    
    <div class="content">
        <div class="company-strip">
            <div class="company-name">{{ $document['business_info']['name'] }}</div>
            <div class="company-details">
                {{ $document['business_info']['address'] ?? '' }}
                @if($document['business_info']['phone']) · {{ $document['business_info']['phone'] }}@endif
                @if($document['business_info']['email']) · {{ $document['business_info']['email'] }}@endif
            </div>
        </div>

        <div class="info-blocks">
            <div class="info-block">
                <div class="info-card">
                    <div class="info-label">Bill To</div>
                    <div class="client-name">{{ $document['client_info']['name'] }}</div>
                    <div class="info-value">
                        @if($document['client_info']['address']){{ $document['client_info']['address'] }}<br>@endif
                        @if($document['client_info']['phone']){{ $document['client_info']['phone'] }}<br>@endif
                        @if($document['client_info']['email']){{ $document['client_info']['email'] }}@endif
                    </div>
                </div>
            </div>
            <div class="info-block">
                <div class="info-card">
                    <div class="info-label">Details</div>
                    <div class="info-value">
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($document['issue_date'])->format('d M Y') }}<br>
                        @if($document['due_date'])<strong>Due:</strong> {{ \Carbon\Carbon::parse($document['due_date'])->format('d M Y') }}<br>@endif
                        @if($document['business_info']['tax_number'])<strong>TPIN:</strong> {{ $document['business_info']['tax_number'] }}@endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $symbol = match($document['currency']) {
                'ZMW' => 'K', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ZAR' => 'R',
                default => $document['currency'],
            };
        @endphp

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
                    <td class="qty">{{ number_format($item['quantity'], $item['quantity'] == floor($item['quantity']) ? 0 : 2) }}</td>
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
        <div class="totals-box">
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

        @if($document['notes'] || $document['terms'])
        <div class="notes-section">
            @if($document['notes'])<div class="notes-title">Notes</div><div class="notes-content">{{ $document['notes'] }}</div>@endif
            @if($document['terms'])@if($document['notes'])<br><br>@endif<div class="notes-title">Terms</div><div class="notes-content">{{ $document['terms'] }}</div>@endif
        </div>
        @endif

        @if($document['type'] === 'delivery_note' || !empty($document['signature']))
        <div class="signature-section">
            @if(!empty($document['signature']))
            <div class="signature-box">
                <img src="{{ $document['signature'] }}" class="signature-image" alt="Signature">
                <div class="signature-line">Authorized Signature</div>
            </div>
            @endif
            @if($document['type'] === 'delivery_note')
            <div class="signature-box"><div class="signature-line">Prepared by</div></div>
            <div class="signature-box"><div class="signature-line">Delivered by</div></div>
            <div class="signature-box"><div class="signature-line">Received by</div></div>
            @endif
        </div>
        @endif

        <div class="footer">Generated with MyGrowNet Quick Invoice | mygrownet.com/quick-invoice</div>
    </div>
</body>
</html>
