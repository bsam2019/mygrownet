<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; font-size:12px; color:#1f2937; background:white; max-width:500px; margin:0 auto; }
</style>
</head>
<body>
{{-- HEADER --}}
<table style="width:100%;border-collapse:collapse;background:#059669;color:white;">
  <tr>
    <td style="padding:18px 20px;text-align:center;">
      @if($logoPath)
      <div style="width:60px;height:60px;background:white;border-radius:50%;text-align:center;line-height:60px;margin:0 auto 8px;overflow:hidden;">
        <img src="{{ $logoPath }}" style="max-width:55px;max-height:55px;vertical-align:middle;" alt="Logo">
      </div>
      @else
      <div style="width:60px;height:60px;background:white;border-radius:50%;text-align:center;line-height:60px;font-size:24px;font-weight:bold;color:#059669;margin:0 auto 8px;">
        {{ strtoupper(substr($businessProfile->businessName(),0,2)) }}
      </div>
      @endif
      <div style="font-size:16px;font-weight:bold;margin-bottom:4px;">{{ $businessProfile->businessName() }}</div>
      <div style="font-size:10px;opacity:0.9;line-height:1.5;">
        {{ $businessProfile->address() }}<br>
        {{ $businessProfile->phone() }}@if($businessProfile->email()) &bull; {{ $businessProfile->email() }}@endif
      </div>
    </td>
  </tr>
</table>

{{-- RECEIPT BANNER --}}
<div style="background:#1f2937;color:white;text-align:center;padding:12px;font-size:20px;font-weight:bold;letter-spacing:3px;border-bottom:4px solid #059669;">
  {{ strtoupper($document->type()->value()) }}
</div>

{{-- DOC DETAILS --}}
<table style="width:100%;border-collapse:collapse;background:#f9fafb;padding:12px;">
  <tr>
    <td style="padding:6px 12px;font-size:11px;font-weight:bold;color:#6b7280;border-bottom:1px dashed #d1d5db;">Number:</td>
    <td style="padding:6px 12px;font-size:11px;text-align:right;border-bottom:1px dashed #d1d5db;">{{ $document->number()->value() }}</td>
  </tr>
  <tr>
    <td style="padding:6px 12px;font-size:11px;font-weight:bold;color:#6b7280;border-bottom:1px dashed #d1d5db;">Date:</td>
    <td style="padding:6px 12px;font-size:11px;text-align:right;border-bottom:1px dashed #d1d5db;">{{ $document->issueDate()->format('d M Y') }}</td>
  </tr>
  @if($businessProfile->tpin())
  <tr>
    <td style="padding:6px 12px;font-size:11px;font-weight:bold;color:#6b7280;">TPIN:</td>
    <td style="padding:6px 12px;font-size:11px;text-align:right;">{{ $businessProfile->tpin() }}</td>
  </tr>
  @endif
</table>

{{-- CUSTOMER --}}
<div style="margin:12px;padding:12px;border:2px dashed #059669;border-radius:6px;">
  <div style="font-size:9px;font-weight:bold;color:#059669;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Customer</div>
  <div style="font-size:13px;font-weight:bold;margin-bottom:3px;">{{ $customer->name() }}</div>
  <div style="font-size:11px;color:#6b7280;">
    @if($customer->phone()){{ $customer->phone() }}@endif
    @if($customer->email() && $customer->phone()) &bull; @endif
    @if($customer->email()){{ $customer->email() }}@endif
  </div>
</div>

{{-- ITEMS --}}
<div style="margin:0 12px;">
  <div style="background:#1f2937;color:white;padding:8px 10px;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">Items</div>
  <table style="width:100%;border-collapse:collapse;">
    @foreach($items as $item)
    @php $lineTotal = $item->lineTotal / 100; @endphp
    <tr style="background:{{ $loop->even ? '#f9fafb' : 'white' }};">
      <td style="padding:9px 10px;font-size:12px;border-bottom:1px solid #e5e7eb;">{{ $item->description }}</td>
      <td style="padding:9px 10px;font-size:11px;text-align:center;border-bottom:1px solid #e5e7eb;color:#6b7280;">
        @if($item->dimensions !== null && $item->dimensions !== ''){{ $item->dimensions }}@endif
      </td>
      <td style="padding:9px 10px;font-size:11px;text-align:center;border-bottom:1px solid #e5e7eb;color:#6b7280;">
        @if($item->dimensionsValue != 1){{ number_format($item->dimensionsValue,2) }}@else&times;{{ $item->quantity }}@endif
      </td>
      <td style="padding:9px 10px;font-size:12px;text-align:right;border-bottom:1px solid #e5e7eb;font-weight:600;white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($lineTotal,2) }}</td>
    </tr>
    @endforeach
  </table>
</div>

{{-- TOTALS --}}
<div style="margin:0 12px;background:#1f2937;color:white;padding:16px;">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="padding:5px 0;font-size:12px;border-bottom:1px dashed rgba(255,255,255,0.2);">Subtotal:</td>
      <td style="padding:5px 0;font-size:12px;text-align:right;border-bottom:1px dashed rgba(255,255,255,0.2);white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal']/100,2) }}</td>
    </tr>
    @if($totals['discountTotal'] > 0)
    <tr>
      <td style="padding:5px 0;font-size:12px;border-bottom:1px dashed rgba(255,255,255,0.2);">Discount:</td>
      <td style="padding:5px 0;font-size:12px;text-align:right;border-bottom:1px dashed rgba(255,255,255,0.2);white-space:nowrap;color:#fca5a5;">-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal']/100,2) }}</td>
    </tr>
    @endif
    @if($totals['taxTotal'] > 0)
    <tr>
      <td style="padding:5px 0;font-size:12px;border-bottom:1px dashed rgba(255,255,255,0.2);">Tax:</td>
      <td style="padding:5px 0;font-size:12px;text-align:right;border-bottom:1px dashed rgba(255,255,255,0.2);white-space:nowrap;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal']/100,2) }}</td>
    </tr>
    @endif
    <tr>
      <td style="padding:10px 0 5px;font-size:16px;font-weight:bold;color:#6ee7b7;">TOTAL PAID:</td>
      <td style="padding:10px 0 5px;font-size:16px;font-weight:bold;text-align:right;white-space:nowrap;color:#6ee7b7;">{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal']/100,2) }}</td>
    </tr>
  </table>
</div>

{{-- NOTES / TERMS --}}
@if($document->notes())
<div style="margin:12px;padding:10px 12px;background:#f0fdf4;border-left:4px solid #059669;border-radius:0 4px 4px 0;">
  <div style="font-size:9px;font-weight:bold;color:#059669;text-transform:uppercase;margin-bottom:4px;">Notes</div>
  <div style="font-size:11px;color:#4b5563;line-height:1.5;">{{ $document->notes() }}</div>
</div>
@endif
@if($document->terms())
<div style="margin:12px;padding:10px 12px;background:#f0fdf4;border-left:4px solid #059669;border-radius:0 4px 4px 0;">
  <div style="font-size:9px;font-weight:bold;color:#059669;text-transform:uppercase;margin-bottom:4px;">Terms &amp; Conditions</div>
  <div style="font-size:11px;color:#4b5563;line-height:1.5;">{{ $document->terms() }}</div>
</div>
@endif

{{-- FOOTER + SIGNATURE --}}
<div style="margin:12px;text-align:center;">
  <div style="font-size:14px;font-weight:bold;color:#059669;letter-spacing:2px;margin-bottom:8px;">* THANK YOU *</div>
  @if($signaturePath)
  <img src="{{ $signaturePath }}" style="max-width:160px;max-height:50px;width:auto;height:auto;display:block;margin:0 auto 6px;" alt="Signature">
  @else
  <div style="height:40px;"></div>
  @endif
  <div style="border-top:2px solid #059669;padding-top:6px;font-size:10px;font-weight:bold;color:#4b5563;width:160px;margin:0 auto;">AUTHORIZED SIGNATURE</div>
</div>

</body>
</html>
