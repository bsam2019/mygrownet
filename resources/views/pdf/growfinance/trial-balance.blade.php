<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }
        .container { padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0891b2; padding-bottom: 20px; }
        .header h1 { font-size: 24px; color: #0891b2; margin-bottom: 5px; }
        .header h2 { font-size: 16px; color: #374151; font-weight: normal; }
        .header .period { font-size: 12px; color: #6b7280; margin-top: 10px; }
        .status-box { padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; }
        .status-balanced { background-color: #d1fae5; border: 1px solid #059669; color: #065f46; }
        .status-unbalanced { background-color: #fee2e2; border: 1px solid #dc2626; color: #991b1b; }
        .section { margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f3f4f6; font-weight: 600; }
        .amount { text-align: right; font-family: monospace; }
        .total-row { font-weight: bold; background-color: #f0fdfa; border-top: 2px solid #0891b2; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
        .row-alt { background-color: #f9fafb; }
        .type-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; text-transform: uppercase; }
        .type-asset { background-color: #dbeafe; color: #1e40af; }
        .type-liability { background-color: #fce7f3; color: #9d174d; }
        .type-equity { background-color: #d1fae5; color: #065f46; }
        .type-revenue { background-color: #dcfce7; color: #166534; }
        .type-expense { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Trial Balance</h1>
            <h2>{{ $user->name }}</h2>
            <div class="period">
                As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}
            </div>
        </div>

        <!-- Balance Status -->
        <div class="status-box {{ $data['isBalanced'] ? 'status-balanced' : 'status-unbalanced' }}">
            @if($data['isBalanced'])
                ✓ Trial Balance is in balance - Debits equal Credits
            @else
                ⚠ Trial Balance is NOT in balance - Please review entries
            @endif
        </div>

        <!-- Accounts Table -->
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Code</th>
                        <th style="width: 38%;">Account Name</th>
                        <th style="width: 12%;">Type</th>
                        <th class="amount" style="width: 19%;">Debit (K)</th>
                        <th class="amount" style="width: 19%;">Credit (K)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['accounts'] as $index => $account)
                    <tr class="{{ $index % 2 ? 'row-alt' : '' }}">
                        <td>{{ $account->code }}</td>
                        <td>{{ $account->name }}</td>
                        <td>
                            <span class="type-badge type-{{ $account->type }}">{{ ucfirst($account->type) }}</span>
                        </td>
                        <td class="amount">
                            @if($account->total_debit > 0)
                                {{ number_format($account->total_debit, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="amount">
                            @if($account->total_credit > 0)
                                {{ number_format($account->total_credit, 2) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #9ca3af; padding: 30px;">
                            No transactions recorded
                        </td>
                    </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="3"><strong>TOTALS</strong></td>
                        <td class="amount"><strong>K{{ number_format($data['totalDebits'], 2) }}</strong></td>
                        <td class="amount"><strong>K{{ number_format($data['totalCredits'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Difference (if unbalanced) -->
        @if(!$data['isBalanced'])
        <div class="section" style="background-color: #fef2f2; padding: 15px; border-radius: 8px;">
            <strong style="color: #991b1b;">Difference:</strong>
            <span style="font-family: monospace; color: #991b1b;">
                K{{ number_format(abs($data['totalDebits'] - $data['totalCredits']), 2) }}
            </span>
        </div>
        @endif

        <div class="footer">
            Generated on {{ $generatedAt }} | GrowFinance by MyGrowNet
        </div>
    </div>
</body>
</html>
