<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Flow Statement</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #7c3aed; padding-bottom: 20px; }
        .header h1 { font-size: 24px; color: #7c3aed; margin-bottom: 5px; }
        .header h2 { font-size: 16px; color: #374151; font-weight: normal; }
        .header .period { font-size: 12px; color: #6b7280; margin-top: 10px; }
        .summary-box { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); color: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; }
        .summary-title { font-size: 12px; opacity: 0.9; margin-bottom: 5px; }
        .summary-value { font-size: 28px; font-weight: bold; }
        .summary-grid { display: table; width: 100%; margin-top: 20px; }
        .summary-item { display: table-cell; text-align: center; padding: 10px; }
        .summary-item-value { font-size: 18px; font-weight: bold; }
        .summary-item-label { font-size: 10px; opacity: 0.8; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: bold; color: #7c3aed; border-bottom: 1px solid #d1d5db; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: 600; }
        .amount { text-align: right; font-family: monospace; font-size: 13px; }
        .positive { color: #059669; }
        .negative { color: #dc2626; }
        .row-highlight { background-color: #faf5ff; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cash Flow Statement</h1>
            <h2>{{ $user->name }}</h2>
            <div class="period">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </div>
        </div>

        <!-- Summary Box -->
        <div class="summary-box">
            <div class="summary-title">Net Cash Flow</div>
            <div class="summary-value">K{{ number_format($data['netCashFlow'], 2) }}</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-item-value">K{{ number_format($data['openingBalance'], 2) }}</div>
                    <div class="summary-item-label">Opening Balance</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-value">K{{ number_format($data['inflows'], 2) }}</div>
                    <div class="summary-item-label">Cash In</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-value">K{{ number_format($data['outflows'], 2) }}</div>
                    <div class="summary-item-label">Cash Out</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-value">K{{ number_format($data['closingBalance'], 2) }}</div>
                    <div class="summary-item-label">Closing Balance</div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Details -->
        <div class="section">
            <div class="section-title">Cash Flow Summary</div>
            <table>
                <tbody>
                    <tr>
                        <td><strong>Opening Cash Balance</strong></td>
                        <td class="amount">K{{ number_format($data['openingBalance'], 2) }}</td>
                    </tr>
                    <tr class="row-highlight">
                        <td style="padding-left: 30px;">Cash Inflows (Receipts)</td>
                        <td class="amount positive">+ K{{ number_format($data['inflows'], 2) }}</td>
                    </tr>
                    <tr class="row-highlight">
                        <td style="padding-left: 30px;">Cash Outflows (Payments)</td>
                        <td class="amount negative">- K{{ number_format($data['outflows'], 2) }}</td>
                    </tr>
                    <tr style="border-top: 2px solid #7c3aed;">
                        <td><strong>Net Cash Flow</strong></td>
                        <td class="amount {{ $data['netCashFlow'] >= 0 ? 'positive' : 'negative' }}">
                            <strong>K{{ number_format($data['netCashFlow'], 2) }}</strong>
                        </td>
                    </tr>
                    <tr style="background-color: #7c3aed; color: white;">
                        <td><strong>Closing Cash Balance</strong></td>
                        <td class="amount"><strong>K{{ number_format($data['closingBalance'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            Generated on {{ $generatedAt }} | GrowFinance by MyGrowNet
        </div>
    </div>
</body>
</html>
