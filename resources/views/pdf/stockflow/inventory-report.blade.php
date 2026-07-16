<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; color: #059669; }
        .header p { font-size: 11px; color: #666; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #059669; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 8px; }
        .summary td { border: none; padding: 3px 8px; }
        .summary-label { font-weight: bold; }
        .summary-value { text-align: right; font-weight: bold; }
        .total-row td { border-top: 2px solid #059669; font-weight: bold; }
        .low-stock { background: #fef2f2; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>Inventory Report</p>
        <p>As of {{ $reportDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>SKU</th>
                <th>Category</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
            <tr @if(($item['system_quantity'] ?? 0) <= ($item['reorder_level'] ?? 0)) class="low-stock" @endif>
                <td>{{ $item['name'] ?? $item['item_name'] ?? 'N/A' }}</td>
                <td>{{ $item['sku'] ?? '-' }}</td>
                <td>{{ $item['category'] ?? '-' }}</td>
                <td class="text-right">{{ $item['system_quantity'] ?? 0 }}</td>
                <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format(($item['unit_price'] ?? 0) * ($item['system_quantity'] ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td class="summary-label">Total Items</td><td class="summary-value">{{ $summary['total_items'] }}</td></tr>
        <tr><td class="summary-label">Total Stock Value</td><td class="summary-value">{{ number_format($summary['total_value'], 2) }}</td></tr>
        <tr><td class="summary-label">Low Stock Items</td><td class="summary-value">{{ $summary['low_stock'] }}</td></tr>
        <tr><td class="summary-label">Out of Stock</td><td class="summary-value">{{ $summary['out_of_stock'] }}</td></tr>
    </table>

    <div class="footer">Generated on {{ date('Y-m-d H:i') }} — StockFlow</div>
</body>
</html>
