<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Item Label</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 20px; }
        .label { border: 2px solid #059669; border-radius: 8px; padding: 16px; max-width: 300px; }
        .name { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 4px; }
        .sku { font-size: 12px; color: #6b7280; margin-bottom: 8px; }
        .details { font-size: 14px; color: #374151; }
        .barcode { margin-top: 12px; text-align: center; font-family: monospace; letter-spacing: 4px; font-size: 16px; padding: 8px; background: #f3f4f6; border-radius: 4px; }
        .price { font-size: 16px; font-weight: bold; color: #059669; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="label">
        <div class="name">{{ $item->name }}</div>
        @if($item->sku)
            <div class="sku">SKU: {{ $item->sku }}</div>
        @endif
        <div class="details">{{ $item->unit ?? '' }}</div>
        @if($item->unit_price > 0)
            <div class="price">{{ number_format($item->unit_price, 2) }}</div>
        @endif
        <div class="barcode">{{ $item->sku ?? $item->id }}</div>
    </div>
</body>
</html>
