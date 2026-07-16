<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; color: #059669; }
        .header h2 { font-size: 14px; margin: 4px 0; color: #374151; }
        .header p { font-size: 11px; color: #666; margin: 4px 0; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 8px; border: none; font-size: 10px; }
        .info-label { font-weight: bold; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #059669; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .summary { margin-top: 8px; }
        .summary td { border: none; padding: 3px 8px; }
        .summary-label { font-weight: bold; }
        .summary-value { text-align: right; font-weight: bold; }
        .variance-negative { color: #dc2626; }
        .variance-positive { color: #059669; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 5px; }
        .section-title { font-size: 12px; font-weight: bold; color: #374151; margin-top: 15px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>{{ $audit['title'] }}</h2>
        <p>Reference: {{ $audit['report_reference'] }} | Date: {{ $audit['audit_date'] }}</p>
    </div>

    <table class="info-table">
        @if (!empty($audit['prepared_for']))<tr><td class="info-label">Prepared For:</td><td>{{ $audit['prepared_for'] }}</td></tr>@endif
        @if (!empty($audit['prepared_by']))<tr><td class="info-label">Prepared By:</td><td>{{ $audit['prepared_by'] }}</td></tr>@endif
    </table>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">System Qty</th>
                <th class="text-right">Physical Qty</th>
                <th class="text-right">Gap</th>
                <th class="text-right">System Value</th>
                <th class="text-right">Physical Value</th>
                <th class="text-right">Gap Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
            <tr>
                <td>{{ $item['item_name'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                <td class="text-right">{{ $item['system_qty'] ?? 0 }}</td>
                <td class="text-right">{{ $item['physical_qty'] ?? 0 }}</td>
                <td class="text-right">{{ $item['gap_qty'] ?? 0 }}</td>
                <td class="text-right">{{ number_format($item['system_value'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item['physical_value'] ?? 0, 2) }}</td>
                <td class="text-right {{ ($item['gap_value'] ?? 0) < 0 ? 'variance-negative' : (($item['gap_value'] ?? 0) > 0 ? 'variance-positive' : '') }}">{{ number_format($item['gap_value'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Summary</div>
    <table class="summary">
        <tr><td class="summary-label">Total System Value</td><td class="summary-value">{{ number_format($audit['total_system_value'], 2) }}</td></tr>
        <tr><td class="summary-label">Total Physical Value</td><td class="summary-value">{{ number_format($audit['total_physical_value'], 2) }}</td></tr>
        <tr><td class="summary-label">Total Variance</td><td class="summary-value {{ ($audit['total_variance'] ?? 0) < 0 ? 'variance-negative' : 'variance-positive' }}">{{ number_format($audit['total_variance'], 2) }}</td></tr>
        <tr><td class="summary-label">Recorded Sales</td><td class="summary-value">{{ number_format($audit['total_recorded_sales'] ?? 0, 2) }}</td></tr>
        <tr><td class="summary-label">Unaccounted Value</td><td class="summary-value {{ ($audit['unaccounted_value'] ?? 0) != 0 ? 'variance-negative' : '' }}">{{ number_format($audit['unaccounted_value'] ?? 0, 2) }}</td></tr>
    </table>

    @if (!empty($audit['executive_summary']))
    <div class="section-title">Executive Summary</div>
    <p>{{ $audit['executive_summary'] }}</p>
    @endif

    @if (!empty($audit['recommendations']))
    <div class="section-title">Recommendations</div>
    <p>{{ $audit['recommendations'] }}</p>
    @endif

    @if (!empty($audit['conclusion']))
    <div class="section-title">Conclusion</div>
    <p>{{ $audit['conclusion'] }}</p>
    @endif

    <div class="footer">Generated on {{ date('Y-m-d H:i') }} — StockFlow</div>
</body>
</html>
