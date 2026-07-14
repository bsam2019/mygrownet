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
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>Sales Report</p>
        <p>{{ $from }} — {{ $to }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Receipt</th>
                <th>Date</th>
                <th>Method</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
            <tr>
                <td>{{ $sale['receipt_number'] }}</td>
                <td>{{ $sale['sale_date'] }}</td>
                <td>{{ str_replace('_', ' ', $sale['payment_method']) }}</td>
                <td class="text-right">{{ number_format($sale['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td class="summary-label">Total Transactions</td><td class="summary-value">{{ $summary['total_transactions'] }}</td></tr>
        <tr><td class="summary-label">Total Sales</td><td class="summary-value">{{ number_format($summary['total_sales'], 2) }}</td></tr>
        <tr><td class="summary-label">Cash Sales</td><td class="summary-value">{{ number_format($summary['cash_sales'], 2) }}</td></tr>
        <tr><td class="summary-label">Average per Transaction</td><td class="summary-value">{{ $summary['total_transactions'] > 0 ? number_format($summary['total_sales'] / $summary['total_transactions'], 2) : '0.00' }}</td></tr>
        <tr class="total-row">
            <td class="summary-label">Grand Total</td>
            <td class="summary-value">{{ number_format($summary['total_sales'], 2) }}</td>
        </tr>
    </table>

    @if (!empty($summary['by_method']))
    <table class="summary" style="margin-top:15px">
        <tr><th colspan="2">Payment Method Breakdown</th></tr>
        @foreach ($summary['by_method'] as $method => $total)
        <tr><td>{{ ucfirst(str_replace('_', ' ', $method)) }}</td><td class="text-right">{{ number_format($total, 2) }}</td></tr>
        @endforeach
    </table>
    @endif

    <div class="footer">Generated on {{ date('Y-m-d H:i') }} — StockFlow</div>
</body>
</html>
