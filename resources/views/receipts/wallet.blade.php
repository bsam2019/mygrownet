<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Wallet Transaction Receipt - MyGrowNet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f9fafb;
            padding: 40px 20px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .logo-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .tagline {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
        }
        .receipt-title {
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 20px;
            opacity: 0.95;
        }
        .content {
            padding: 40px 30px;
        }
        .receipt-meta {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .meta-item {
            text-align: center;
        }
        .meta-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .meta-value {
            font-size: 14px;
            color: #111827;
            font-weight: 600;
        }
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .info-item {
            padding: 12px 0;
        }
        .info-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 14px;
            color: #111827;
            font-weight: 600;
        }
        .amount-section {
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            border: 2px solid #4f46e5;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 40px 0;
        }
        .amount-section.credit {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #059669;
        }
        .amount-section.debit {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-color: #dc2626;
        }
        .amount-label {
            font-size: 13px;
            color: #4338ca;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .amount-section.credit .amount-label {
            color: #065f46;
        }
        .amount-section.debit .amount-label {
            color: #991b1b;
        }
        .amount-value {
            font-size: 48px;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: -1px;
        }
        .amount-section.credit .amount-value {
            color: #059669;
        }
        .amount-section.debit .amount-value {
            color: #dc2626;
        }
        .status-badge {
            display: inline-block;
            background: #4f46e5;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 12px;
        }
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
            margin: 30px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-message {
            font-size: 16px;
            color: #374151;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .footer-info {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
        }
        .footer-contact {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }
        .contact-item {
            display: inline-block;
            margin: 0 12px;
            font-size: 12px;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header with Logo -->
        <div class="header">
            <div class="logo-container">
                @if(file_exists(public_path('logo.png')))
                    <img src="{{ public_path('logo.png') }}" alt="MyGrowNet Logo" style="height: 60px; width: auto; margin-bottom: 12px;">
                @else
                    <div class="logo-icon">MG</div>
                    <div class="logo-text">MyGrowNet</div>
                @endif
            </div>
            <div class="tagline">Empowering Communities Through Growth</div>
            <div class="receipt-title">Wallet Transaction Receipt</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Receipt Meta Info -->
            <div class="receipt-meta">
                <div class="meta-item">
                    <div class="meta-label">Receipt Number</div>
                    <div class="meta-value">{{ $receipt_number }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Date</div>
                    <div class="meta-value">{{ $date }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Transaction ID</div>
                    <div class="meta-value">#{{ $transaction->id }}</div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="section">
                <div class="section-title">Account Information</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">{{ $user->phone ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member ID</div>
                        <div class="info-value">#{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Transaction Details -->
            <div class="section">
                <div class="section-title">Transaction Details</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Transaction Type</div>
                        <div class="info-value">{{ $type }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Description</div>
                        <div class="info-value">{{ $description }}</div>
                    </div>
                    @if(isset($transaction->reference))
                    <div class="info-item">
                        <div class="info-label">Reference</div>
                        <div class="info-value">{{ $transaction->reference }}</div>
                    </div>
                    @endif
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value" style="color: #059669;">Completed</div>
                    </div>
                </div>
            </div>

            <!-- Amount Section -->
            @php
                $isCredit = $transaction->amount > 0;
                $amountClass = $isCredit ? 'credit' : 'debit';
            @endphp
            <div class="amount-section {{ $amountClass }}">
                <div class="amount-label">{{ $isCredit ? 'Amount Credited' : 'Amount Debited' }}</div>
                <div class="amount-value">K{{ number_format($amount, 2) }}</div>
                <div class="status-badge">✓ COMPLETED</div>
            </div>

            <!-- Balance Information -->
            @if(isset($transaction->balance_after))
            <div class="section">
                <div class="section-title">Balance Information</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Balance Before</div>
                        <div class="info-value">K{{ number_format($transaction->balance_before ?? 0, 2) }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Balance After</div>
                        <div class="info-value">K{{ number_format($transaction->balance_after, 2) }}</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Information -->
            <div class="section">
                <div class="section-title">Important Information</div>
                <div class="footer-info" style="text-align: left;">
                    <p style="margin-bottom: 12px;">
                        • This receipt serves as proof of your wallet transaction.
                    </p>
                    <p style="margin-bottom: 12px;">
                        • Please keep this receipt for your records.
                    </p>
                    <p style="margin-bottom: 12px;">
                        • For any queries regarding this transaction, please contact our support team with your receipt number.
                    </p>
                    <p style="margin-bottom: 12px;">
                        • All wallet transactions are secure and encrypted.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-message">Thank you for using MyGrowNet Wallet!</div>
            <div class="footer-info">
                We're committed to empowering communities through growth, learning, and opportunity.
            </div>
            <div class="footer-contact">
                <span class="contact-item">Email: info@mygrownet.com</span>
                <span class="contact-item">Phone: +260 977 891 894</span>
                <span class="contact-item">Web: www.mygrownet.com</span>
            </div>
        </div>
    </div>
</body>
</html>
