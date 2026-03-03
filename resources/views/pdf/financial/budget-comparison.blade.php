<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget Comparison Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #1e40af;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #f3f4f6;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #d1d5db;
            font-size: 9px;
        }
        table td {
            padding: 5px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-green {
            color: #059669;
        }
        .text-red {
            color: #dc2626;
        }
        .text-amber {
            color: #d97706;
        }
        .text-blue {
            color: #2563eb;
        }
        .summary-box {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-box {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .alert-red {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
        }
        .alert-amber {
            background-color: #fef3c7;
            border-left: 4px solid #d97706;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-expense {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-revenue {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-over {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-on-track {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-under {
            background-color: #d1fae5;
            color: #065f46;
        }
        .total-row {
            font-weight: bold;
            padding-top: 8px;
            border-top: 2px solid #d1d5db;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #999;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Budget Comparison Report</h1>
        @if($comparison['has_budget'])
        <p><strong>Period:</strong> {{ $comparison['dates']['start'] }} to {{ $comparison['dates']['end'] }}</p>
        <p><strong>Budget:</strong> {{ $comparison['budget']['name'] }}</p>
        @else
        <p><strong>Status:</strong> No Active Budget</p>
        @endif
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    </div>

    @if(!$comparison['has_budget'])
    <div class="alert-box alert-amber">
        <p style="margin: 0; font-weight: bold;">{{ $comparison['message'] }}</p>
    </div>
    @else

    <!-- Summary Section -->
    <div class="section">
        <div class="section-title">Budget Summary</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td><strong>Total Budgeted</strong></td>
                    <td class="text-right text-blue font-bold">K{{ number_format($comparison['summary']['total_budgeted'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Actual</strong></td>
                    <td class="text-right {{ $comparison['summary']['total_variance'] >= 0 ? 'text-red' : 'text-green' }} font-bold">
                        K{{ number_format($comparison['summary']['total_actual'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Variance</strong></td>
                    <td class="text-right {{ $comparison['summary']['total_variance'] >= 0 ? 'text-red' : 'text-green' }} font-bold">
                        K{{ number_format(abs($comparison['summary']['total_variance']), 2) }}
                        ({{ $comparison['summary']['total_variance'] >= 0 ? 'Over' : 'Under' }} Budget)
                    </td>
                </tr>
                <tr>
                    <td><strong>Budget Used</strong></td>
                    <td class="text-right font-bold">{{ number_format($comparison['summary']['percentage_used'], 1) }}%</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Performance Metrics -->
    @if($metrics['has_budget'])
    <div class="section">
        <div class="section-title">Performance Metrics</div>
        <table>
            <tr>
                <td><strong>Over Budget Categories</strong></td>
                <td class="text-right text-red font-bold">{{ $metrics['metrics']['over_budget_count'] }}</td>
            </tr>
            <tr>
                <td><strong>On Track Categories</strong></td>
                <td class="text-right text-blue font-bold">{{ $metrics['metrics']['on_track_count'] }}</td>
            </tr>
            <tr>
                <td><strong>Under Budget Categories</strong></td>
                <td class="text-right text-green font-bold">{{ $metrics['metrics']['under_budget_count'] }}</td>
            </tr>
            <tr>
                <td><strong>Unbudgeted Expenses</strong></td>
                <td class="text-right text-amber font-bold">{{ $metrics['metrics']['unbudgeted_expense_count'] }}</td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Critical Overages Alert -->
    @if($metrics['has_budget'] && count($metrics['metrics']['critical_overages']) > 0)
    <div class="alert-box alert-red">
        <p style="margin: 0 0 8px 0; font-weight: bold; color: #991b1b;">⚠ Critical Budget Overages (>120%)</p>
        <table style="margin: 0;">
            @foreach($metrics['metrics']['critical_overages'] as $overage)
            <tr>
                <td style="border: none; padding: 3px 0;"><strong>{{ $overage['category'] }}</strong></td>
                <td style="border: none; padding: 3px 0;" class="text-right text-red font-bold">
                    {{ number_format($overage['percentage_used'], 1) }}%
                    (K{{ number_format($overage['variance'], 2) }} over)
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <!-- Budget vs Actual Breakdown -->
    <div class="section page-break">
        <div class="section-title">Budget vs Actual Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Type</th>
                    <th class="text-right">Budgeted</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Variance</th>
                    <th class="text-right">Usage</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comparison['comparisons'] as $item)
                <tr>
                    <td>{{ $item['category'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $item['item_type'] === 'expense' ? 'badge-expense' : 'badge-revenue' }}">
                            {{ strtoupper($item['item_type']) }}
                        </span>
                    </td>
                    <td class="text-right">K{{ number_format($item['budgeted'], 2) }}</td>
                    <td class="text-right {{ $item['item_type'] === 'expense' && $item['status'] === 'over_budget' ? 'text-red' : '' }}">
                        K{{ number_format($item['actual'], 2) }}
                    </td>
                    <td class="text-right {{ $item['variance'] >= 0 ? 'text-red' : 'text-green' }}">
                        K{{ number_format(abs($item['variance']), 2) }}
                        {{ $item['variance'] >= 0 ? '↑' : '↓' }}
                    </td>
                    <td class="text-right font-bold">{{ number_format($item['percentage_used'], 1) }}%</td>
                    <td class="text-center">
                        <span class="badge 
                            @if($item['status'] === 'over_budget') badge-over
                            @elseif($item['status'] === 'on_track') badge-on-track
                            @else badge-under
                            @endif">
                            {{ strtoupper(str_replace('_', ' ', $item['status'])) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Unbudgeted Expenses -->
    @if(count($comparison['unbudgeted_expenses']) > 0)
    <div class="section">
        <div class="alert-box alert-amber">
            <p style="margin: 0 0 8px 0; font-weight: bold; color: #92400e;">⚠ Unbudgeted Expenses</p>
            <p style="margin: 0 0 8px 0; font-size: 9px; color: #78350f;">
                The following expenses were not included in the budget:
            </p>
            <table style="margin: 0;">
                @foreach($comparison['unbudgeted_expenses'] as $expense)
                <tr>
                    <td style="border: none; padding: 3px 0;"><strong>{{ $expense['category'] }}</strong></td>
                    <td style="border: none; padding: 3px 0;" class="text-right text-amber font-bold">
                        K{{ number_format($expense['amount'], 2) }}
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    @endif

    <div class="footer">
        <p>MyGrowNet Platform - Budget Comparison Report | Generated: {{ $generatedAt }}</p>
    </div>
</body>
</html>
