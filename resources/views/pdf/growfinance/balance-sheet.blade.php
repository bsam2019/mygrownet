<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 20px; }
        .header h1 { font-size: 24px; color: #2563eb; margin-bottom: 5px; }
        .header h2 { font-size: 16px; color: #374151; font-weight: normal; }
        .header .period { font-size: 12px; color: #6b7280; margin-top: 10px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: bold; color: #2563eb; border-bottom: 1px solid #d1d5db; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: 600; }
        .amount { text-align: right; font-family: monospace; }
        .total-row { font-weight: bold; background-color: #f9fafb; border-top: 2px solid #d1d5db; }
        .grand-total { font-size: 14px; background-color: #2563eb; color: white; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
        .row-alt { background-color: #f9fafb; }
        .two-column { display: table; width: 100%; }
        .column { display: table-cell; width: 50%; vertical-align: top; padding-right: 15px; }
        .column:last-child { padding-right: 0; padding-left: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Balance Sheet</h1>
            <h2>{{ $user->name }}</h2>
            <div class="period">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}
            </div>
        </div>

        <!-- Assets Section -->
        <div class="section">
            <div class="section-title">Assets</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 55%;">Account</th>
                        <th class="amount" style="width: 30%;">Balance (K)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['assets'] as $index => $account)
                    <tr class="{{ $index % 2 ? 'row-alt' : '' }}">
                        <td>{{ $account->code }}</td>
                        <td>{{ $account->name }}</td>
                        <td class="amount">{{ number_format($account->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #9ca3af;">No assets</td>
                    </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="2"><strong>Total Assets</strong></td>
                        <td class="amount"><strong>K{{ number_format($data['totalAssets'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Liabilities Section -->
        <div class="section">
            <div class="section-title">Liabilities</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 55%;">Account</th>
                        <th class="amount" style="width: 30%;">Balance (K)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['liabilities'] as $index => $account)
                    <tr class="{{ $index % 2 ? 'row-alt' : '' }}">
                        <td>{{ $account->code }}</td>
                        <td>{{ $account->name }}</td>
                        <td class="amount">{{ number_format($account->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #9ca3af;">No liabilities</td>
                    </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="2"><strong>Total Liabilities</strong></td>
                        <td class="amount"><strong>K{{ number_format($data['totalLiabilities'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Equity Section -->
        <div class="section">
            <div class="section-title">Equity</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th style="width: 55%;">Account</th>
                        <th class="amount" style="width: 30%;">Balance (K)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['equity'] as $index => $account)
                    <tr class="{{ $index % 2 ? 'row-alt' : '' }}">
                        <td>{{ $account->code }}</td>
                        <td>{{ $account->name }}</td>
                        <td class="amount">{{ number_format($account->balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #9ca3af;">No equity accounts</td>
                    </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="2"><strong>Total Equity</strong></td>
                        <td class="amount"><strong>K{{ number_format($data['totalEquity'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="section">
            <table>
                <tr class="grand-total">
                    <td style="width: 70%;"><strong>Total Liabilities + Equity</strong></td>
                    <td class="amount" style="width: 30%;">
                        <strong>K{{ number_format($data['totalLiabilities'] + $data['totalEquity'], 2) }}</strong>
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
