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
        .summary { margin-top: 8px; }
        .summary td { border: none; padding: 3px 8px; }
        .summary-label { font-weight: bold; }
        .summary-value { text-align: right; font-weight: bold; }
        .total-row td { border-top: 2px solid #059669; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 5px; }
        .status { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8px; }
        .status-received { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-partial { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>Purchase Orders Report</p>
        <p>{{ $from }} — {{ $to }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $po)
            <tr>
                <td>{{ $po['order_number'] }}</td>
                <td>{{ $po['order_date'] }}</td>
                <td>{{ $po['supplier_name'] ?? 'N/A' }}</td>
                <td>{{ ucfirst($po['status']) }}</td>
                <td class="text-right">{{ number_format($po['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td class="summary-label">Total Orders</td><td class="summary-value">{{ $summary['total_orders'] }}</td></tr>
        <tr><td class="summary-label">Total Cost</td><td class="summary-value">{{ number_format($summary['total_cost'], 2) }}</td></tr>
        <tr><td class="summary-label">Received</td><td class="summary-value">{{ $summary['received'] }}</td></tr>
        <tr><td class="summary-label">Pending</td><td class="summary-value">{{ $summary['pending'] }}</td></tr>
        <tr class="total-row">
            <td class="summary-label">Grand Total</td>
            <td class="summary-value">{{ number_format($summary['total_cost'], 2) }}</td>
        </tr>
    </table>

    @if (!empty($summary['by_supplier']))
    <table class="summary" style="margin-top:15px">
        <tr><th colspan="2">By Supplier</th></tr>
        @foreach ($summary['by_supplier'] as $supplier => $total)
        <tr><td>{{ $supplier }}</td><td class="text-right">{{ number_format($total, 2) }}</td></tr>
        @endforeach
    </table>
    @endif

    <div class="footer">Generated on {{ date('Y-m-d H:i') }} — StockFlow</div>
</body>
</html>
