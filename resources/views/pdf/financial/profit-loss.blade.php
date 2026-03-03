<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
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
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #d1d5db;
        }
        table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
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
        .text-blue {
            color: #2563eb;
        }
        .summary-box {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .total-row {
            font-weight: bold;
            font-size: 13px;
            padding-top: 10px;
            border-top: 2px solid #d1d5db;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
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
        <h1>Profit & Loss Statement</h1>
        <p><strong>Period:</strong> {{ $statement['date_range']['from'] }} to {{ $statement['date_range']['to'] }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    </div>

    <!-- Summary Section -->
    <div class="section">
        <div class="section-title">Financial Summary</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td><strong>Total Revenue</strong></td>
                    <td class="text-right text-green font-bold">K{{ number_format($statement['revenue']['total'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Expenses</strong></td>
                    <td class="text-right text-red font-bold">K{{ number_format($statement['expenses']['total'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Gross Profit</strong></td>
                    <td class="text-right {{ $statement['profitability']['gross_profit'] >= 0 ? 'text-green' : 'text-red' }} font-bold">
                        K{{ number_format($statement['profitability']['gross_profit'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Profit Margin</strong></td>
                    <td class="text-right {{ $statement['profitability']['profit_margin'] >= 0 ? 'text-green' : 'text-red' }}">
                        {{ number_format($statement['profitability']['profit_margin'], 2) }}%
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="section">
        <div class="section-title">Revenue Breakdown</div>
        @if(isset($statement['revenue']['breakdown']) && count($statement['revenue']['breakdown']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Count</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement['revenue']['breakdown'] as $item)
                <tr>
                    <td>{{ $item['label'] ?? 'Unknown' }}</td>
                    <td class="text-center">{{ $item['count'] ?? 0 }}</td>
                    <td class="text-right">K{{ number_format($item['amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($item['percentage'] ?? 0, 1) }}%</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2"><strong>Total Revenue</strong></td>
                    <td class="text-right text-green"><strong>K{{ number_format($statement['revenue']['total'], 2) }}</strong></td>
                    <td class="text-right"><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #999; padding: 20px;">No revenue data for this period</p>
        @endif
    </div>

    <!-- Expense Breakdown -->
    <div class="section">
        <div class="section-title">Expense Breakdown</div>
        @if(isset($statement['expenses']['breakdown']) && count($statement['expenses']['breakdown']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Count</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement['expenses']['breakdown'] as $item)
                <tr>
                    <td>{{ $item['label'] ?? 'Unknown' }}</td>
                    <td class="text-center">{{ $item['count'] ?? 0 }}</td>
                    <td class="text-right">K{{ number_format($item['amount'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($item['percentage'] ?? 0, 1) }}%</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2"><strong>Total Expenses</strong></td>
                    <td class="text-right text-red"><strong>K{{ number_format($statement['expenses']['total'], 2) }}</strong></td>
                    <td class="text-right"><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #999; padding: 20px;">No expense data for this period</p>
        @endif
    </div>

    <!-- Module Profitability -->
    @if(isset($statement['by_module']) && count($statement['by_module']) > 0)
    <div class="section page-break">
        <div class="section-title">Module Profitability</div>
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Expenses</th>
                    <th class="text-right">Profit</th>
                    <th class="text-right">Margin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement['by_module'] as $module)
                <tr>
                    <td>{{ $module['module_name'] }}</td>
                    <td class="text-right text-green">K{{ number_format($module['revenue'], 2) }}</td>
                    <td class="text-right text-red">K{{ number_format($module['expenses'], 2) }}</td>
                    <td class="text-right {{ $module['profit'] >= 0 ? 'text-green' : 'text-red' }} font-bold">
                        K{{ number_format($module['profit'], 2) }}
                    </td>
                    <td class="text-right {{ $module['profit_margin'] >= 0 ? 'text-green' : 'text-red' }}">
                        {{ number_format($module['profit_margin'], 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Commission Efficiency -->
    @if(isset($commissionEfficiency))
    <div class="section">
        <div class="section-title">Commission Efficiency</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td><strong>Total Commissions Paid</strong></td>
                    <td class="text-right">K{{ number_format($commissionEfficiency['total_commissions'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Revenue</strong></td>
                    <td class="text-right">K{{ number_format($commissionEfficiency['total_revenue'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Commission Ratio</strong></td>
                    <td class="text-right {{ $commissionEfficiency['is_compliant'] ? 'text-green' : 'text-red' }} font-bold">
                        {{ number_format($commissionEfficiency['commission_ratio'], 2) }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Commission Cap</strong></td>
                    <td class="text-right">{{ $commissionEfficiency['commission_cap'] }}%</td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td class="text-right {{ $commissionEfficiency['is_compliant'] ? 'text-green' : 'text-red' }} font-bold">
                        {{ $commissionEfficiency['is_compliant'] ? 'Compliant' : 'Non-Compliant' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <!-- Cash Flow Analysis -->
    @if(isset($cashFlow))
    <div class="section">
        <div class="section-title">Cash Flow Analysis</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td><strong>Cash Inflows</strong></td>
                    <td class="text-right text-green">K{{ number_format($cashFlow['cash_inflows'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Cash Outflows</strong></td>
                    <td class="text-right text-red">K{{ number_format($cashFlow['cash_outflows'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Net Cash Flow</strong></td>
                    <td class="text-right {{ $cashFlow['net_cash_flow'] >= 0 ? 'text-green' : 'text-red' }} font-bold">
                        K{{ number_format($cashFlow['net_cash_flow'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Cash Flow Ratio</strong></td>
                    <td class="text-right">{{ number_format($cashFlow['cash_flow_ratio'], 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>MyGrowNet Platform - Profit & Loss Statement | Generated: {{ $generatedAt }}</p>
    </div>
</body>
</html>
