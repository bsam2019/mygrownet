<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Receipt - MyGrowNet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }
        .receipt-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 50px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            max-height: 80px;
            width: auto;
        }
        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .company-subtitle {
            font-size: 13px;
            color: #059669;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .company-contact {
            font-size: 12px;
            color: #666;
            line-height: 1.8;
        }
        .receipt-title {
            font-size: 24px;
            font-weight: 700;
            color: #2563eb;
            text-align: center;
            margin: 30px 0;
            letter-spacing: 2px;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-left, .info-right {
            flex: 1;
        }
        .info-right {
            text-align: right;
        }
        .info-row {
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.6;
        }
        .info-label {
            font-weight: 600;
            color: #333;
        }
        .info-value {
            color: #666;
        }
        .divider {
            border-top: 2px solid #e0e0e0;
            margin: 25px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        .items-table thead {
            background: #f0f9ff;
            border-bottom: 2px solid #2563eb;
        }
        .items-table th {
            padding: 12px 8px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            text-transform: uppercase;
        }
        .items-table th:nth-child(2),
        .items-table th:nth-child(3),
        .items-table th:nth-child(4) {
            text-align: right;
        }
        .items-table td {
            padding: 12px 8px;
            font-size: 14px;
            color: #666;
            border-bottom: 1px solid #e0e0e0;
        }
        .items-table td:nth-child(2),
        .items-table td:nth-child(3),
        .items-table td:nth-child(4) {
            text-align: right;
        }
        .total-row {
            font-weight: 700;
            font-size: 16px;
            color: #2563eb;
            background: #f0f9ff;
        }
        .total-row td {
            padding: 15px 8px;
            border-bottom: 2px solid #2563eb;
        }
        .payment-details {
            margin: 25px 0;
        }
        .payment-row {
            margin-bottom: 10px;
            font-size: 14px;
        }
        .amount-words {
            background: #f0f9ff;
            padding: 15px;
            margin: 25px 0;
            border-left: 4px solid #2563eb;
            font-size: 14px;
            color: #333;
        }
        .footer-message {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin: 30px 0;
            line-height: 1.6;
        }
        .signature-section {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 280px;
        }
        .digital-signature {
            height: 70px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .signature-stamp {
            border: 3px solid #2563eb;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%);
            margin: 0 auto;
        }
        .stamp-text {
            font-size: 10px;
            font-weight: 700;
            color: #2563eb;
            text-align: center;
            line-height: 1.3;
            text-transform: uppercase;
        }
        .stamp-date {
            font-size: 8px;
            color: #059669;
            margin-top: 3px;
        }
        .signature-line {
            border-top: 2px solid #333;
            padding-top: 8px;
            font-size: 13px;
            color: #333;
            font-weight: 600;
        }
        .signature-title {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .signature-note {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                @if(file_exists(public_path('logo.png')))
                    <img src="{{ public_path('logo.png') }}" alt="MyGrowNet Logo">
                @endif
            </div>
            <div class="company-name">MYGROWNET</div>
            <div class="company-subtitle">(Operated by Rockshield Investments Limited)</div>
            <div style="font-size: 11px; color: #999; margin-bottom: 12px;">Community Empowerment Platform</div>
            <div class="company-contact">
                Email: info@mygrownet.com<br>
                Phone: +260 977 563 730<br>
                Web: www.mygrownet.com
            </div>
        </div>

        <div class="receipt-title">OFFICIAL RECEIPT</div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="info-left">
                <div class="info-row">
                    <span class="info-label">Received From:</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name:</span> <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Member ID:</span> <span class="info-value">MGN-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span> <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span> <span class="info-value">{{ $user->phone ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="info-right">
                <div class="info-row">
                    <span class="info-value">{{ $receipt_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-value">{{ $date }}</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit (ZMW)</th>
                    <th>Total (ZMW)</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($items) && count($items) > 0)
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>1</td>
                        <td>{{ number_format($item['price'], 2) }}</td>
                        <td>{{ number_format($item['price'], 2) }}</td>
                    </tr>
                    @endforeach
                @endif
                
                @if(isset($show_discount) && $show_discount)
                    <tr style="border-top: 2px solid #e0e0e0;">
                        <td colspan="3" style="text-align: right; padding-top: 15px; font-weight: 600;">Subtotal:</td>
                        <td style="padding-top: 15px;">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: 600; color: #059669;">Member Bundle Discount ({{ number_format(($discount / $subtotal) * 100, 0) }}%):</td>
                        <td style="color: #059669;">-{{ number_format($discount, 2) }}</td>
                    </tr>
                @endif
                
                <tr class="total-row">
                    <td colspan="3">Amount Paid</td>
                    <td>{{ number_format($amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Details -->
        <div class="payment-details">
            <div class="payment-row">
                <span class="info-label">Payment Method:</span> <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payment_method)) }}</span>
            </div>
            @if(isset($transaction_id) && $transaction_id)
            <div class="payment-row">
                <span class="info-label">Transaction ID:</span> <span class="info-value">{{ $transaction_id }}</span>
            </div>
            @endif
            <div class="payment-row">
                <span class="info-label">Received By:</span> <span class="info-value">Rockshield Investments Limited</span>
            </div>
        </div>

        <!-- Amount in Words -->
        <div class="amount-words">
            <span class="info-label">Amount in Words:</span> 
            @php
                $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                $numericAmount = is_numeric($amount) ? (float)$amount : (float)((string)$amount);
                $amountInWords = ucwords($formatter->format($numericAmount));
            @endphp
            {{ $amountInWords }} Kwacha Only (ZMW {{ number_format($amount, 2) }})
        </div>

        <!-- Footer Message -->
        <div class="footer-message">
            Thank you for growing with MyGrowNet — where<br>
            every member matters.
        </div>

        <!-- Signature -->
        <div class="signature-section">
            <div style="text-align: center;">
                <div class="signature-box">
                    <div class="digital-signature">
                        <div class="signature-stamp">
                            <div class="stamp-text">
                                DIGITALLY<br>VERIFIED<br>✓
                            </div>
                            <div class="stamp-date">{{ date('d/m/Y') }}</div>
                        </div>
                    </div>
                    <div class="signature-line">Authorized by MyGrowNet System</div>
                    <div class="signature-title">Rockshield Investments Limited</div>
                    <div class="signature-title" style="font-size: 10px; color: #999; margin-top: 3px;">
                        (MyGrowNet Platform)
                    </div>
                </div>
            </div>
            <div class="signature-note">
                This is a digitally generated receipt. No physical signature required.<br>
                For verification, contact us at info@mygrownet.com or +260 977 563 730
            </div>
        </div>
    </div>
</body>
</html>
