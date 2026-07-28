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
        .total { font-weight: bold; background: #f9f9f9; }
        .section-header { font-weight: bold; background: #e3f2fd; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="date">Generated: {{ $date }}</div>
    <table>
        <thead>
            <tr>
                <th>Section</th>
                <th>Account</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr class="{{ !empty($row['Section']) ? '' : 'section-header' }}">
                    <td>{{ $row['Section'] ?? '' }}</td>
                    <td>{{ $row['Account'] ?? '' }}</td>
                    <td class="text-right">{{ $row['Amount'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
