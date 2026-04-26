<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; font-size:12px; color:#1f2937; background:white; }
</style>
</head>
<body>
<table style="width:100%;border-collapse:collapse;background:#dc2626;color:white;">
  <tr>
    <td style="padding:22px 28px;width:65%;vertical-align:middle;">
      <table style="border-collapse:collapse;">
        <tr>
          <td style="vertical-align:middle;padding-right:14px;">
            @if($logoPath)
              <div style="width:65px;height:65px;background:white;border-radius:8px;text-align:center;line-height:65px;overflow:hidden;">
                <img src="{{ $logoPath }}" style="max-width:60px;max-height:60px;vertical-align:middle;" alt="Logo">
              </div>
            @else
              <div style="width:65px;height:65px;background:white;border-radius:8px;text-align:center;line-height:65px;font-size:26px;font-weight:bold;color:#dc2626;">
                {{ strtoupper(substr($businessProfile->businessName(),0,2)) }}
              </div>
            @endif
          </td>
          <td style="vertical-align:middle;">
            <div style="font-size:20px;font-weight:bold;margin-bottom:4px;">{{ $businessProfile->businessName() }}</div>
            <div style="font-size:10px;opacity:0.9;line-height:1.5;">
              {{ $businessProfile->address() }}<br>
              {{ $businessProfile->phone() }}@if($businessProfile->email()) &bull; {{ $businessProfile->email() }}@endif
            </div>
          </td>
        </tr>
      </table>
    </td>
    <td style="padding:22px 28px;text-align:right;vertical-align:middle;">
      <div style="font-size:28px;font-weight:bold;letter-spacing:2px;">{{ strtoupper($document->type()->value()) }}</div>
      <div style="font-size:11px;margin-top:4px;opacity:0.9;">{{ $document->number()->value() }}</div>
      <div style="font-size:11px;opacity:0.9;">{{ $document->issueDate()->format('d M Y') }}</div>
    </td>
  </tr>
</table>

<div style="padding:22px 28px;">

  {{-- BILL TO --}}
  <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
    <tr>
      <td style="width:55%;vertical-align:top;padding-right:20px;">
        <div style="background:#eff6ff;border-left:4px solid #dc2626;padding:14px;border-radius:0 6px 6px 0;">
          <div style="font-size:9px;font-weight:bold;color:#dc2626;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Bill To</div>
          <div style="font-size:14px;font-weight:bold;margin-bottom:4px;">{{ $customer->name() }}</div>
          <div style="font-size:11px;color:#4b5563;line-height:1.5;">
            @if($customer->address()){{ $customer->address() }}<br>@endif
            @if($customer->phone()){{ $customer->phone() }}<br>@endif
            @if($customer->email()){{ $customer->email() }}@endif
          </div>
        </div>
      </td>
      <td style="width:45%;vertical-align:top;text-align:right;">
        @if($document->dueDate())
        <div style="font-size:11px;color:#6b7280;">Due: <strong style="color:#1f2937;">{{ $document->dueDate()->format('d M Y') }}</strong></div>
        @endif
        <div style="font-size:11px;color:#6b7280;white-space:nowrap;">Currency: <strong style="color:#1f2937;">{{ $businessProfile->defaultCurrency() }}</strong></div>
        @if($businessProfile->tpin())
        <div style="font-size:11px;color:#6b7280;">TPIN: <strong style="color:#1f2937;">{{ $businessProfile->tpin() }}</strong></div>
        @endif
      </td>
    </tr>
  </table>

  {{-- ITEMS TABLE --}}
  <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
    <thead>
      <tr style="background:#1f2937;color:white;">
        <th style="padding:10px 8px;text-align:left;font-size:10px;text-transform:uppercase;font-weight:700;">Description</th>
        <th style="padding:10px 8px;text-align:center;font-size:10px;text-transform:uppercase;font-weight:700;width:16%;">Dimensions</th>
        <th style="padding:10px 8px;text-align:center;font-size:10px;text-transform:uppercase;font-weight:700;width:8%;">Qty</th>
        <th style="padding:10px 8px;text-align:right;font-size:10px;text-transform:uppercase;font-weight:700;width:18%;white-space:nowrap;">Unit Price</th>
        <th style="padding:10px 8px;text-align:right;font-size:10px;text-transform:uppercase;font-weight:700;width:18%;white-space:nowrap;">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($items as $index => $item)
      @php $lineTotal = $item->lineTotal / 100; @endphp
      <tr style="background:{{ $index % 2 == 0 ? 'white' : '#f9fafb' }};">
        <td style="padding:10px 8px;border-bottom:1px solid #f3f4f6;font-size:12px;">{{ $item->description }}</td>
        <td style="padding:10px 8px;border-bottom:1px solid #f3f4f6;font-size:11px;text-align:center;">
          @if($item->dimensions !== null && $item->dimensions !== ''){{ $item->dimensions }}@else-@endif
        </td>
        <td style="padding:10px 8px;border-bottom:1px solid #f3f4f6;font-size:12px;text-align:center;">
          @if($item->dimensionsValue != 1){{ number_format($item->dimensionsValue,2) }}@else{{ $item->quantity }}@endif
        </td>
        <td style="padding:10px 8px;border-bottom:1px solid #f3f4f6;font-size:12px;text-align:right;white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($item->unitPrice/100,2) }}</td>
        <td style="padding:10px 8px;border-bottom:1px solid #f3f4f6;font-size:12px;text-align:right;white-space:nowrap;font-weight:bold;">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal,2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- TOTALS + PAYMENT INFO --}}
  <table style="width:100%;border-collapse:collapse;table-layout:fixed;margin-bottom:20px;">
    <tr>
      <td style="width:55%;vertical-align:top;padding-right:20px;">
        <div style="font-size:11px;color:#6b7280;line-height:1.8;">
          <strong style="color:#1f2937;">Payment Information</strong><br>
          Currency: {{ $businessProfile->defaultCurrency() }}<br>
          @if($document->dueDate())<strong>Due Date:</strong> {{ $document->dueDate()->format('d M Y') }}@endif
        </div>
      </td>
      <td style="width:45%;vertical-align:top;">
        <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
          <tr>
            <td style="width:50%;padding:7px 10px;font-size:12px;border-bottom:1px solid #e5e7eb;">Subtotal:</td>
            <td style="width:50%;padding:7px 10px;font-size:12px;border-bottom:1px solid #e5e7eb;text-align:right;white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal']/100,2) }}</td>
          </tr>
          @if($totals['discountTotal'] > 0)
          <tr>
            <td style="width:50%;padding:7px 10px;font-size:12px;border-bottom:1px solid #e5e7eb;">Discount:</td>
            <td style="width:50%;padding:7px 10px;font-size:12px;border-bottom:1px solid #e5e7eb;text-align:right;white-space:nowrap;color:#dc2626;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal']/100,2) }}</td>
          </tr>
          @endif
          @if($totals['taxTotal'] > 0)
          <tr>
            <td style="width:50%;padding:7px 10px;font-size:12px;border-bottom:1px solid #e5e7eb;">Tax:</td>
            <td style="width:50%;padding:7px 10px;font-size:12px;border-bottom:1px solid #e5e7eb;text-align:right;white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal']/100,2) }}</td>
          </tr>
          @endif
          <tr>
            <td style="width:50%;padding:10px;font-size:14px;font-weight:bold;background:#dc2626;color:white;">TOTAL:</td>
            <td style="width:50%;padding:10px;font-size:14px;font-weight:bold;background:#dc2626;color:white;text-align:right;white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal']/100,2) }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- NOTES / TERMS --}}
  @if($document->notes())
  <div style="margin-bottom:12px;padding:12px 14px;background:#f0f9ff;border-left:4px solid #dc2626;border-radius:0 4px 4px 0;">
    <div style="font-size:9px;font-weight:bold;color:#dc2626;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Notes</div>
    <div style="font-size:11px;color:#4b5563;line-height:1.6;">{{ $document->notes() }}</div>
  </div>
  @endif
  @if($document->terms())
  <div style="margin-bottom:12px;padding:12px 14px;background:#f0f9ff;border-left:4px solid #dc2626;border-radius:0 4px 4px 0;">
    <div style="font-size:9px;font-weight:bold;color:#dc2626;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Terms &amp; Conditions</div>
    <div style="font-size:11px;color:#4b5563;line-height:1.6;">{{ $document->terms() }}</div>
  </div>
  @endif
  @if($document->paymentInstructions())
  <div style="margin-bottom:12px;padding:12px 14px;background:#f0f9ff;border-left:4px solid #dc2626;border-radius:0 4px 4px 0;">
    <div style="font-size:9px;font-weight:bold;color:#dc2626;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Payment Instructions</div>
    <div style="font-size:11px;color:#4b5563;line-height:1.6;">{{ $document->paymentInstructions() }}</div>
  </div>
  @endif

  {{-- SIGNATURE --}}
  <table style="width:100%;border-collapse:collapse;margin-top:25px;border-top:1px solid #e5e7eb;padding-top:15px;">
    <tr>
      <td style="vertical-align:bottom;font-size:10px;color:#9ca3af;padding-top:15px;">Thank you for your business.</td>
      <td style="width:200px;text-align:center;vertical-align:bottom;padding-top:15px;">
        @if($signaturePath)
        <img src="{{ $signaturePath }}" style="max-width:180px;max-height:55px;width:auto;height:auto;display:block;margin:0 auto 8px;" alt="Signature">
        @else
        <div style="height:50px;"></div>
        @endif
        <div style="border-top:2px solid #dc2626;padding-top:6px;font-size:10px;font-weight:bold;color:#4b5563;">Authorized Signature</div>
      </td>
    </tr>
  </table>

</div>
</body>
</html>
