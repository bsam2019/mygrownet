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
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="date">Generated: {{ $date }}</div>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th class="text-right">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>Total AR</td>
                    <td class="text-right">{{ number_format($row['total_ar'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Total AP</td>
                    <td class="text-right">{{ number_format($row['total_ap'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Customer Count</td>
                    <td class="text-right">{{ $row['customer_count'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Vendor Count</td>
                    <td class="text-right">{{ $row['vendor_count'] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
