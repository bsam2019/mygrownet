<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; line-height: 1.4; }
        .container { padding: 25px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #4f46e5; padding-bottom: 15px; }
        .header h1 { font-size: 22px; color: #4f46e5; margin-bottom: 5px; }
        .header h2 { font-size: 14px; color: #374151; font-weight: normal; }
        .header .period { font-size: 11px; color: #6b7280; margin-top: 8px; }
        .account-section { margin-bottom: 30px; page-break-inside: avoid; }
        .account-header { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; padding: 12px 15px; border-radius: 6px 6px 0 0; }
        .account-name { font-size: 13px; font-weight: bold; }
        .account-code { font-size: 10px; opacity: 0.9; }
        .account-balances { display: table; width: 100%; margin-top: 8px; }
        .balance-item { display: table-cell; }
        .balance-label { font-size: 9px; opacity: 0.8; }
        .balance-value { font-size: 12px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f3f4f6; font-weight: 600; font-size: 9px; text-transform: uppercase; }
        .amount { text-align: right; font-family: monospace; }
        .row-alt { background-color: #f9fafb; }
        .opening-row { background-color: #eef2ff; font-style: italic; }
        .closing-row { background-color: #4f46e5; color: white; font-weight: bold; }
        .positive { color: #059669; }
        .negative { color: #dc2626; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #9ca3af; text-align: center; }
        .no-transactions { text-align: center; color: #9ca3af; padding: 20px; background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>General Ledger</h1>
            <h2>{{ $user->name }}</h2>
            <div class="period">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </div>
        </div>

        @forelse($data['ledger'] as $entry)
        <div class="account-section">
            <!-- Account Header -->
            <div class="account-header">
                <div class="account-name">{{ $entry['account']->name }}</div>
                <div class="account-code">Account Code: {{ $entry['account']->code }}</div>
                <div class="account-balances">
                    <div class="balance-item">
                        <div class="balance-label">Opening Balance</div>
                        <div class="balance-value">K{{ number_format($entry['openingBalance'], 2) }}</div>
                    </div>
                    <div class="balance-item" style="text-align: right;">
                        <div class="balance-label">Closing Balance</div>
                        <div class="balance-value">K{{ number_format($entry['closingBalance'], 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 12%;">Reference</th>
                        <th style="width: 32%;">Description</th>
                        <th class="amount" style="width: 14%;">Debit (K)</th>
                        <th class="amount" style="width: 14%;">Credit (K)</th>
                        <th class="amount" style="width: 16%;">Balance (K)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Balance Row -->
                    <tr class="opening-row">
                        <td>{{ \Carbon\Carbon::parse($startDate)->format('M d') }}</td>
                        <td>-</td>
                        <td>Opening Balance</td>
                        <td class="amount">-</td>
                        <td class="amount">-</td>
                        <td class="amount">{{ number_format($entry['openingBalance'], 2) }}</td>
                    </tr>

                    @forelse($entry['transactions'] as $index => $tx)
                    <tr class="{{ $index % 2 ? 'row-alt' : '' }}">
                        <td>{{ \Carbon\Carbon::parse($tx->entry_date)->format('M d') }}</td>
                        <td>{{ $tx->reference ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($tx->description, 40) }}</td>
                        <td class="amount">
                            @if($tx->debit > 0)
                                {{ number_format($tx->debit, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="amount">
                            @if($tx->credit > 0)
                                {{ number_format($tx->credit, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="amount {{ $tx->balance >= 0 ? 'positive' : 'negative' }}">
                            {{ number_format($tx->balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="no-transactions">
                            No transactions in this period
                        </td>
                    </tr>
                    @endforelse

                    <!-- Closing Balance Row -->
                    <tr class="closing-row">
                        <td colspan="5">Closing Balance</td>
                        <td class="amount">K{{ number_format($entry['closingBalance'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @empty
        <div class="no-transactions" style="padding: 50px;">
            <p style="font-size: 14px; margin-bottom: 10px;">No accounts found</p>
            <p>Create accounts and record transactions to generate the general ledger.</p>
        </div>
        @endforelse

        <div class="footer">
            Generated on {{ $generatedAt }} | GrowFinance by MyGrowNet
        </div>
    </div>
</body>
</html>
