<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { text-align: center; font-size: 16px; margin-bottom: 5px; }
        .date { text-align: center; color: #666; margin-bottom: 20px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="date">Generated: {{ $date }}</div>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th class="text-right">Amount (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Opening Balance</td><td class="text-right">{{ number_format($data['opening_balance'] ?? 0, 2) }}</td></tr>
            <tr><td>Inflows</td><td class="text-right">{{ number_format($data['inflows'] ?? 0, 2) }}</td></tr>
            <tr><td>Outflows</td><td class="text-right">{{ number_format($data['outflows'] ?? 0, 2) }}</td></tr>
            <tr class="total"><td>Net Cash Flow</td><td class="text-right">{{ number_format($data['net_cash_flow'] ?? 0, 2) }}</td></tr>
            <tr class="total"><td>Closing Balance</td><td class="text-right">{{ number_format($data['closing_balance'] ?? 0, 2) }}</td></tr>
        </tbody>
    </table>
</body>
</html>
