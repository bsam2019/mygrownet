{{-- Modern Template - Contemporary design with accent colors --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document['type_label'] }} - {{ $document['document_number'] }}</title>
    <style>
        :root {
            --primary: {{ $document['colors']['primary'] ?? '#2563eb' }};
            --secondary: {{ $document['colors']['secondary'] ?? '#1e40af' }};
            --accent: {{ $document['colors']['accent'] ?? '#059669' }};
            --text: {{ $document['colors']['text'] ?? '#1f2937' }};
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: var(--text); }
        .header-bar { background: linear-gradient(135deg, var(--primary), var(--secondary)); padding: 25px 30px; color: white; }
        .header { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 50%; vertical-align: middle; }
        .logo { max-width: 140px; max-height: 60px; background: white; padding: 8px; border-radius: 6px; }
        .company-section { display: table-cell; width: 50%; text-align: right; vertical-align: middle; }
        .company-name { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .company-details { font-size: 10px; opacity: 0.9; line-height: 1.5; }
        .content { padding: 30px; }
        .document-badge { display: inline-block; background: var(--primary); color: white; padding: 8px 20px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; }
        .document-number { font-size: 24px; font-weight: bold; color: var(--text); margin-bottom: 25px; }
        .info-grid { display: table; width: 100%; margin-bottom: 30px; }
        .info-card { display: table-cell; width: 50%; vertical-align: top; }
        .info-card-inner { background: #f8fafc; border-radius: 8px; padding: 15px; margin-right: 10px; }
        .info-card.right .info-card-inner { margin-right: 0; margin-left: 10px; text-align: right; }
        .info-label { font-size: 9px; color: var(--primary); text-transform: uppercase; font-weight: bold; margin-bottom: 8px; }
        .client-name { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 25px; border-radius: 8px; overflow: hidden; }
        table.items th { background: var(--text); color: white; padding: 12px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items th.qty { width: 50px; text-align: center; }
        table.items th.price, table.items th.amount { width: 90px; text-align: right; }
        table.items td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; }
        table.items td.qty { text-align: center; }
        table.items td.price, table.items td.amount { text-align: right; }
        table.items tr:hover { background: #f8fafc; }
        .totals { width: 300px; margin-left: auto; background: #f8fafc; border-radius: 8px; padding: 15px; }
        .totals-row { display: table; width: 100%; padding: 8px 0; }
        .totals-label { display: table-cell; width: 50%; color: #6b7280; }
        .totals-value { display: table-cell; width: 50%; text-align: right; font-weight: 500; }
        .totals-row.total { border-top: 2px solid var(--primary); margin-top: 8px; padding-top: 12px; }
        .totals-row.total .totals-label, .totals-row.total .totals-value { font-size: 16px; font-weight: bold; color: var(--primary); }
        .notes-section { margin-top: 25px; padding: 15px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 8px; border-left: 4px solid var(--primary); }
        .notes-title { font-weight: bold; margin-bottom: 5px; color: var(--primary); font-size: 10px; text-transform: uppercase; }
        .notes-content { color: #6b7280; font-size: 10px; }
        .signature-section { margin-top: 35px; display: table; width: 100%; }
        .signature-box { display: table-cell; width: 33%; padding: 10px; text-align: center; }
        .signature-image { max-height: 50px; margin-bottom: 5px; }
        .signature-line { border-top: 2px solid var(--primary); margin-top: 40px; padding-top: 8px; font-size: 9px; color: #6b7280; }
        .footer { margin-top: 35px; text-align: center; color: #9ca3af; font-size: 9px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="header-bar">
        <div class="header">
            <div class="logo-section">
                @if($document['business_info']['logo'])
                    <img src="{{ $document['business_info']['logo'] }}" class="logo" alt="Logo">
                @endif
            </div>
            <div class="company-section">
                <div class="company-name">{{ $document['business_info']['name'] }}</div>
                <div class="company-details">
                    @if($document['business_info']['address']){{ $document['business_info']['address'] }}<br>@endif
                    @if($document['business_info']['phone']){{ $document['business_info']['phone'] }} @endif
                    @if($document['business_info']['email'])| {{ $document['business_info']['email'] }}@endif
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="document-badge">{{ $document['type_label'] }}</div>
        <div class="document-number">#{{ $document['document_number'] }}</div>
        
        @include('pdf.quick-invoice.partials.info-and-items')
    </div>
</body>
</html>
