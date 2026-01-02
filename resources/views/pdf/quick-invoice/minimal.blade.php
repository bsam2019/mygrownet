{{-- Minimal Template - Simple and elegant with whitespace --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document['type_label'] }} - {{ $document['document_number'] }}</title>
    <style>
        :root { --primary: {{ $document['colors']['primary'] ?? '#2563eb' }}; --text: {{ $document['colors']['text'] ?? '#1f2937' }}; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: var(--text); padding: 40px 50px; }
        .header { margin-bottom: 50px; }
        .logo { max-width: 120px; max-height: 50px; margin-bottom: 15px; }
        .company-name { font-size: 14px; font-weight: 600; letter-spacing: 1px; }
        .company-details { color: #9ca3af; font-size: 10px; margin-top: 5px; }
        .document-header { display: table; width: 100%; margin-bottom: 40px; }
        .document-title { display: table-cell; width: 50%; }
        .document-title h1 { font-size: 32px; font-weight: 300; color: var(--text); letter-spacing: -1px; }
        .document-meta { display: table-cell; width: 50%; text-align: right; vertical-align: bottom; }
        .document-number { font-size: 11px; color: #6b7280; }
        .info-section { display: table; width: 100%; margin-bottom: 40px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; }
        .info-box.right { text-align: right; }
        .info-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .client-name { font-size: 13px; font-weight: 600; }
        .info-value { color: #6b7280; font-size: 10px; line-height: 1.6; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { border-bottom: 1px solid var(--text); padding: 12px 0; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; font-weight: normal; }
        table.items th.qty { width: 60px; }
        table.items th.price, table.items th.amount { width: 100px; text-align: right; }
        table.items td { padding: 15px 0; border-bottom: 1px solid #f3f4f6; }
        table.items td.qty { color: #6b7280; }
        table.items td.price, table.items td.amount { text-align: right; }
        .totals { width: 250px; margin-left: auto; margin-bottom: 40px; }
        .totals-row { display: table; width: 100%; padding: 8px 0; }
        .totals-label { display: table-cell; width: 50%; color: #9ca3af; font-size: 10px; }
        .totals-value { display: table-cell; width: 50%; text-align: right; }
        .totals-row.total { border-top: 1px solid var(--text); margin-top: 10px; padding-top: 15px; }
        .totals-row.total .totals-label { color: var(--text); font-weight: 600; }
        .totals-row.total .totals-value { font-size: 18px; font-weight: 600; }
        .notes-section { margin-top: 40px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
        .notes-title { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .notes-content { color: #6b7280; font-size: 10px; line-height: 1.6; }
        .signature-section { margin-top: 50px; display: table; width: 100%; }
        .signature-box { display: table-cell; width: 33%; padding: 10px; text-align: center; }
        .signature-image { max-height: 40px; margin-bottom: 5px; }
        .signature-line { border-top: 1px solid #e5e7eb; margin-top: 50px; padding-top: 8px; font-size: 9px; color: #9ca3af; }
        .footer { margin-top: 50px; text-align: center; color: #d1d5db; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        @if($document['business_info']['logo'])
            <img src="{{ $document['business_info']['logo'] }}" class="logo" alt="Logo">
        @endif
        <div class="company-name">{{ $document['business_info']['name'] }}</div>
        <div class="company-details">
            {{ $document['business_info']['address'] ?? '' }}
            @if($document['business_info']['phone']) · {{ $document['business_info']['phone'] }}@endif
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

    @include('pdf.quick-invoice.partials.info-and-items')
</body>
</html>
