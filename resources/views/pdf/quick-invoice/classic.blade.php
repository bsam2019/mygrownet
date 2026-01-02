{{-- Classic Template - Traditional business style --}}
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
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: var(--text); padding: 30px; }
        .header { display: table; width: 100%; margin-bottom: 30px; border-bottom: 3px solid var(--primary); padding-bottom: 20px; }
        .logo-section { display: table-cell; width: 50%; vertical-align: top; }
        .logo { max-width: 160px; max-height: 70px; }
        .company-section { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .company-name { font-size: 18px; font-weight: bold; color: var(--text); margin-bottom: 5px; }
        .company-details { color: #6b7280; font-size: 10px; line-height: 1.5; }
        .document-title { text-align: center; margin: 25px 0; }
        .document-title h1 { font-size: 26px; color: var(--primary); text-transform: uppercase; letter-spacing: 3px; }
        .document-number { font-size: 13px; color: #dc2626; font-weight: bold; margin-top: 5px; }
        .info-section { display: table; width: 100%; margin-bottom: 25px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; padding: 10px; }
        .info-box.right { text-align: right; }
        .info-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; }
        .client-name { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: var(--primary); color: white; padding: 10px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items th.qty { width: 50px; text-align: center; }
        table.items th.price, table.items th.amount { width: 90px; text-align: right; }
        table.items td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; }
        table.items td.qty { text-align: center; }
        table.items td.price, table.items td.amount { text-align: right; }
        table.items tr:nth-child(even) { background: #f9fafb; }
        .totals { width: 280px; margin-left: auto; margin-bottom: 25px; }
        .totals-row { display: table; width: 100%; padding: 6px 0; border-bottom: 1px solid #e5e7eb; }
        .totals-label { display: table-cell; width: 50%; color: #6b7280; }
        .totals-value { display: table-cell; width: 50%; text-align: right; font-weight: 500; }
        .totals-row.total { border-bottom: none; border-top: 2px solid var(--primary); margin-top: 5px; padding-top: 10px; }
        .totals-row.total .totals-label, .totals-row.total .totals-value { font-size: 14px; font-weight: bold; }
        .notes-section { margin-top: 25px; padding: 12px; background: #f9fafb; border-radius: 4px; }
        .notes-title { font-weight: bold; margin-bottom: 4px; color: #374151; font-size: 10px; }
        .notes-content { color: #6b7280; font-size: 10px; }
        .signature-section { margin-top: 35px; display: table; width: 100%; }
        .signature-box { display: table-cell; width: 33%; padding: 10px; text-align: center; }
        .signature-image { max-height: 50px; margin-bottom: 5px; }
        .signature-line { border-top: 1px solid #9ca3af; margin-top: 40px; padding-top: 5px; font-size: 9px; color: #6b7280; }
        .footer { margin-top: 35px; text-align: center; color: #9ca3af; font-size: 9px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
    @include('pdf.quick-invoice.partials.content')
</body>
</html>
