<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blue Lines Professional</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #1f2937; background: white; font-size: 12px; }
        .page { padding: 35px 40px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #2563eb; }
        .logo-box { width: 70px; height: 70px; background: #eff6ff; border-radius: 6px; text-align: center; line-height: 70px; font-size: 28px; color: #2563eb; overflow: hidden; }
        .logo-box img { max-width: 70px; max-height: 70px; width: auto; height: auto; vertical-align: middle; }
        .company-name { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 2px; }
        .company-sub  { font-size: 10px; color: #6b7280; line-height: 1.5; }
        .doc-title-section { text-align: center; margin-bottom: 25px; }
        .doc-title-main { font-size: 30px; font-weight: bold; color: #2563eb; letter-spacing: 6px; margin-bottom: 4px; }
        .doc-number { font-size: 12px; color: #dc2626; font-weight: 600; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .info-name  { font-size: 14px; font-weight: bold; color: #111827; margin-bottom: 3px; }
        .info-detail { font-size: 11px; color: #6b7280; line-height: 1.5; }
        .meta-label { color: #6b7280; text-transform: uppercase; font-size: 9px; }
        .meta-value { font-weight: 600; color: #111827; font-size: 11px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table thead { background: #2563eb; color: white; }
        .items-table th { padding: 10px 8px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .items-table td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        .items-table tbody tr:nth-child(even) { background: #f9fafb; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .totals-table { width: 300px; border-collapse: collapse; margin-left: auto; margin-bottom: 25px; }
        .totals-table td { padding: 7px 10px; font-size: 12px; border-bottom: 1px solid #e5e7eb; }
        .totals-table .lbl { text-align: left; }
        .totals-table .val { text-align: right; white-space: nowrap; }
        .totals-table .grand td { background: #2563eb; color: white; font-size: 14px; font-weight: bold; border-bottom: none; }
        .totals-table .disc .val { color: #dc2626; }
        .notes-box { padding: 12px 14px; background: #f9fafb; border-left: 4px solid #2563eb; border-radius: 4px; margin-bottom: 14px; }
        .notes-title { font-size: 10px; font-weight: bold; color: #2563eb; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .notes-content { font-size: 11px; color: #4b5563; line-height: 1.6; }
        .footer-section { margin-top: 25px; padding-top: 20px; border-top: 2px solid #e5e7eb; }
        .sig-line { border-top: 2px solid #2563eb; padding-top: 8px; font-size: 10px; font-weight: 600; color: #4b5563; text-align: center; width: 180px; }
    </style>
</head>
<body>
<div class="page">

<table class="header-table"><tr>
    <td style="width:75px;vertical-align:top;padding-right:12px;">
        <div class="logo-box">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="Logo">
            @else
                {{ strtoupper(substr($businessProfile->businessName(),0,2)) }}
            @endif
        </div>
    </td>
    <td style="vertical-align:top;">
        <div class="company-name">{{ $businessProfile->businessName() }}</div>
        <div class="company-sub">
            {{ $businessProfile->address() }}<br>
            {{ $businessProfile->phone() }}
            @if($businessProfile->email())
                &bull; {{ $businessProfile->email() }}
            @endif
        </div>
    </td>
    <td style="vertical-align:top;text-align:right;">
        <div class="company-sub">
            @if($businessProfile->tpin())
                <strong>TPIN:</strong> {{ $businessProfile->tpin() }}<br>
            @endif
            @if($businessProfile->website())
                {{ $businessProfile->website() }}
            @endif
        </div>
    </td>
</tr></table>

<div class="doc-title-section">
    <div class="doc-title-main">{{ strtoupper($document->type()->value()) }}</div>
    <div class="doc-number">{{ $document->number()->value() }} &nbsp;&bull;&nbsp; {{ $document->issueDate()->format('d M Y') }}</div>
</div>

<table class="info-table"><tr>
    <td style="width:55%;vertical-align:top;padding-right:20px;">
        <div class="info-label">Bill To</div>
        <div class="info-name">{{ $customer->name() }}</div>
        <div class="info-detail">
            @if($customer->address())
                {{ $customer->address() }}<br>
            @endif
            @if($customer->phone())
                {{ $customer->phone() }}
            @endif
            @if($customer->email())
                <br>{{ $customer->email() }}
            @endif
        </div>
    </td>
    <td style="width:45%;vertical-align:top;text-align:right;">
        @if($document->dueDate())
            <div><span class="meta-label">Valid Until: </span><span class="meta-value">{{ $document->dueDate()->format('d M Y') }}</span></div>
        @endif
        <div><span class="meta-label">Currency: </span><span class="meta-value" style="white-space:nowrap;">{{ $businessProfile->defaultCurrency() }}</span></div>
    </td>
</tr></table>

<table class="items-table">
    <thead><tr>
        <th style="width:18%;">DIMENSIONS</th>
        <th style="width:8%;" class="text-center">QTY</th>
        <th>DESCRIPTION</th>
        <th class="text-right" style="width:20%;white-space:nowrap;">UNIT PRICE</th>
        <th class="text-right" style="width:18%;white-space:nowrap;">AMOUNT</th>
    </tr></thead>
    <tbody>
        @foreach($items as $item)
        @php $lineTotal = $item->lineTotal / 100; @endphp
        <tr>
            <td>
                @if($item->dimensions !== null && $item->dimensions !== '')
                    {{ $item->dimensions }}
                @else
                    -
                @endif
            </td>
            <td class="text-center">
                @if($item->dimensionsValue != 1)
                    {{ number_format($item->dimensionsValue,2) }}
                @else
                    {{ $item->quantity }}
                @endif
            </td>
            <td>{{ $item->description }}</td>
            <td class="text-right" style="white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($item->unitPrice/100,2) }}</td>
            <td class="text-right" style="white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals-table">
    <tr><td class="lbl">Subtotal:</td><td class="val">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal']/100,2) }}</td></tr>
    @if($totals['discountTotal'] > 0)
    <tr class="disc"><td class="lbl">Discount:</td><td class="val">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal']/100,2) }}</td></tr>
    @endif
    @if($totals['taxTotal'] > 0)
    <tr><td class="lbl">Tax:</td><td class="val">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal']/100,2) }}</td></tr>
    @endif
    <tr class="grand"><td class="lbl">TOTAL:</td><td class="val">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal']/100,2) }}</td></tr>
</table>

<div class="footer-section">
    @if($document->notes())
    <div class="notes-box"><div class="notes-title">Notes</div><div class="notes-content">{{ $document->notes() }}</div></div>
    @endif
    @if($document->terms())
    <div class="notes-box"><div class="notes-title">Terms &amp; Conditions</div><div class="notes-content">{{ $document->terms() }}</div></div>
    @endif
    @if($document->paymentInstructions())
    <div class="notes-box"><div class="notes-title">Payment Instructions</div><div class="notes-content">{{ $document->paymentInstructions() }}</div></div>
    @endif

    <table style="width:100%;border-collapse:collapse;margin-top:25px;">
        <tr>
            <td style="vertical-align:bottom;">
                <div style="font-size:10px;color:#6b7280;">Thank you for your business.</div>
            </td>
            <td style="width:200px;text-align:center;vertical-align:bottom;">
                @if($signaturePath)
                <div style="margin-bottom:8px;"><img src="{{ $signaturePath }}" alt="Signature" style="max-width:180px;max-height:55px;width:auto;height:auto;"></div>
                @else
                <div style="height:50px;"></div>
                @endif
                <div class="sig-line">Authorized Signature</div>
            </td>
        </tr>
    </table>
</div>

</div>
</body>
</html>
