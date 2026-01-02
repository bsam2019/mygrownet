{{-- Professional Template - Corporate style with structured layout --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document['type_label'] }} - {{ $document['document_number'] }}</title>
    <style>
        :root { --primary: {{ $document['colors']['primary'] ?? '#2563eb' }}; --secondary: {{ $document['colors']['secondary'] ?? '#1e40af' }}; --text: {{ $document['colors']['text'] ?? '#1f2937' }}; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: var(--text); }
        .page-border { border: 2px solid var(--primary); margin: 15px; padding: 25px; min-height: calc(100vh - 30px); }
        .header { display: table; width: 100%; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--primary); }
        .logo-section { display: table-cell; width: 40%; vertical-align: middle; }
        .logo { max-width: 150px; max-height: 60px; }
        .title-section { display: table-cell; width: 60%; text-align: right; vertical-align: middle; }
        .document-type { font-size: 28px; font-weight: bold; color: var(--primary); text-transform: uppercase; }
        .document-number { font-size: 12px; color: #dc2626; margin-top: 5px; }
        .company-bar { background: var(--primary); color: white; padding: 12px 15px; margin-bottom: 20px; }
        .company-bar .name { font-size: 14px; font-weight: bold; }
        .company-bar .details { font-size: 9px; opacity: 0.9; margin-top: 3px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 8px; vertical-align: top; }
        .info-table .label-cell { width: 15%; background: #f3f4f6; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .info-table .value-cell { width: 35%; }
        .client-box { background: #f8fafc; border: 1px solid #e5e7eb; padding: 12px; margin-bottom: 20px; }
        .client-label { font-size: 9px; color: var(--primary); text-transform: uppercase; font-weight: bold; margin-bottom: 8px; }
        .client-name { font-size: 13px; font-weight: bold; margin-bottom: 5px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: var(--primary); color: white; padding: 10px 8px; text-align: left; font-size: 9px; text-transform: uppercase; border: 1px solid var(--secondary); }
        table.items th.qty { width: 50px; text-align: center; }
        table.items th.price, table.items th.amount { width: 85px; text-align: right; }
        table.items td { padding: 10px 8px; border: 1px solid #e5e7eb; }
        table.items td.qty { text-align: center; background: #f9fafb; }
        table.items td.price, table.items td.amount { text-align: right; }
        table.items tr:nth-child(even) td { background: #fafafa; }
        table.items tr:nth-child(even) td.qty { background: #f3f4f6; }
        .totals-wrapper { display: table; width: 100%; }
        .totals-spacer { display: table-cell; width: 60%; }
        .totals { display: table-cell; width: 40%; }
        .totals-row { display: table; width: 100%; border: 1px solid #e5e7eb; border-top: none; }
        .totals-row:first-child { border-top: 1px solid #e5e7eb; }
        .totals-label { display: table-cell; width: 50%; padding: 8px; background: #f9fafb; font-size: 9px; }
        .totals-value { display: table-cell; width: 50%; padding: 8px; text-align: right; }
        .totals-row.total { background: var(--primary); }
        .totals-row.total .totals-label, .totals-row.total .totals-value { color: white; font-weight: bold; font-size: 12px; background: transparent; }
        .notes-section { margin-top: 20px; border: 1px solid #e5e7eb; }
        .notes-header { background: #f3f4f6; padding: 8px 12px; font-weight: bold; font-size: 9px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; }
        .notes-content { padding: 12px; color: #6b7280; font-size: 10px; }
        .signature-section { margin-top: 30px; display: table; width: 100%; }
        .signature-box { display: table-cell; width: 33%; padding: 10px; text-align: center; }
        .signature-image { max-height: 45px; margin-bottom: 5px; }
        .signature-line { border: 1px solid #e5e7eb; border-top: 2px solid var(--primary); margin-top: 35px; padding: 8px; font-size: 9px; color: #6b7280; background: #f9fafb; }
        .footer { margin-top: 25px; text-align: center; padding-top: 15px; border-top: 2px solid var(--primary); color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
    <div class="page-border">
        <div class="header">
            <div class="logo-section">
                @if($document['business_info']['logo'])
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
                @if($document['business_info']['phone']) | Tel: {{ $document['business_info']['phone'] }}@endif
                @if($document['business_info']['email']) | {{ $document['business_info']['email'] }}@endif
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label-cell">Date</td>
                <td class="value-cell">{{ \Carbon\Carbon::parse($document['issue_date'])->format('d/m/Y') }}</td>
                @if($document['due_date'])
                <td class="label-cell">Due Date</td>
                <td class="value-cell">{{ \Carbon\Carbon::parse($document['due_date'])->format('d/m/Y') }}</td>
                @endif
            </tr>
            @if($document['business_info']['tax_number'])
            <tr>
                <td class="label-cell">TPIN</td>
                <td class="value-cell">{{ $document['business_info']['tax_number'] }}</td>
                <td></td><td></td>
            </tr>
            @endif
        </table>

        <div class="client-box">
            <div class="client-label">Bill To / Deliver To</div>
            <div class="client-name">{{ $document['client_info']['name'] }}</div>
            <div style="color: #6b7280;">
                @if($document['client_info']['address']){{ $document['client_info']['address'] }}<br>@endif
                @if($document['client_info']['phone']){{ $document['client_info']['phone'] }}@endif
                @if($document['client_info']['email']) | {{ $document['client_info']['email'] }}@endif
            </div>
        </div>

        @include('pdf.quick-invoice.partials.items-only')
    </div>
</body>
</html>
