<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #059669; padding-bottom: 20px; }
        .header h1 { font-size: 24px; color: #059669; margin-bottom: 5px; }
        .header h2 { font-size: 16px; color: #374151; font-weight: normal; }
        .header .period { font-size: 12px; color: #6b7280; margin-top: 10px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: bold; color: #059669; border-bottom: 1px solid #d1d5db; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: 600; }
        .amount { text-align: right; font-family: monospace; }
        .total-row { font-weight: bold; background-color: #f9fafb; border-top: 2px solid #d1d5db; }
        .grand-total { font-size: 14px; background-color: #059669; color: white; }
        .positive { color: #059669; }
        .negative { color: #dc2626; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
        .row-alt { background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Profit & Loss Statement</h1>
            <h2>{{ $user->name }}</h2>
            <div class="period">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </div>
        </div>

        <!-- Revenue Section -->
        <div class="section">
            <div class="section-title">Revenue</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 55%;">Account</th>
                        <th class="amount" style="width: 30%;">Amount (K)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['revenue'] as $index => $account)
                    <tr class="{{ $index % 2 ? 'row-alt' : '' }}">
                        <td>{{ $account->code }}</td>
                        <td>{{ $account->name }}</td>
                        <td class="amount">{{ number_format($account->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #9ca3af;">No revenue recorded</td>
                    </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="2"><strong>Total Revenue</strong></td>
                        <td class="amount positive"><strong>K{{ number_format($data['totalRevenue'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Expenses Section -->
        <div class="section">
            <div class="section-title">Expenses</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 55%;">Account</th>
                        <th class="amount" style="width: 30%;">Amount (K)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['expenses'] as $index => $account)
                    <tr class="{{ $index % 2 ? 'row-alt' : '' }}">
                        <td>{{ $account->code }}</td>
                        <td>{{ $account->name }}</td>
                        <td class="amount">{{ number_format($account->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #9ca3af;">No expenses recorded</td>
                    </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="2"><strong>Total Expenses</strong></td>
                        <td class="amount negative"><strong>K{{ number_format($data['totalExpenses'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Net Income -->
        <div class="section">
            <table>
                <tr class="grand-total">
                    <td style="width: 70%;"><strong>Net Income</strong></td>
                    <td class="amount" style="width: 30%;">
                        <strong>K{{ number_format($data['netIncome'], 2) }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Generated on {{ $generatedAt }} | GrowFinance by MyGrowNet
        </div>
    </div>
</body>
</html>
