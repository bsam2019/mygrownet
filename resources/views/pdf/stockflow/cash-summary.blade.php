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
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>Cash Summary Report</p>
        <p>{{ $from }} — {{ $to }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th class="text-right">Sales</th>
                <th class="text-right">Expenses</th>
                <th class="text-right">Banking</th>
                <th class="text-right">Variance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registers as $reg)
            <tr>
                <td>{{ $reg['register_date'] }}</td>
                <td>{{ ucfirst($reg['status']) }}</td>
                <td class="text-right">{{ number_format($reg['total_sales'], 2) }}</td>
                <td class="text-right">{{ number_format($reg['total_expenses'], 2) }}</td>
                <td class="text-right">{{ number_format($reg['total_banking'], 2) }}</td>
                <td class="text-right">{{ $reg['variance'] !== null ? number_format($reg['variance'], 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td class="summary-label">Total Sales</td><td class="summary-value">{{ number_format($totals['total_sales'], 2) }}</td></tr>
        <tr><td class="summary-label">Total Expenses</td><td class="summary-value">{{ number_format($totals['total_expenses'], 2) }}</td></tr>
        <tr><td class="summary-label">Total Banking</td><td class="summary-value">{{ number_format($totals['total_banking'], 2) }}</td></tr>
        <tr><td class="summary-label">Total Variance</td><td class="summary-value">{{ number_format($totals['total_variance'], 2) }}</td></tr>
    </table>

    <div class="footer">Generated on {{ date('Y-m-d H:i') }} — StockFlow</div>
</body>
</html>
