<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #2563eb; }
        .receipt-title { font-size: 20px; margin-top: 10px; }
        .info-section { margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; }
        .amount-box { background: #f3f4f6; padding: 20px; text-align: center; margin: 30px 0; border-radius: 8px; }
        .amount { font-size: 32px; font-weight: bold; color: #059669; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">MyGrowNet</div>
        <div style="color: #666;">Growth Platform</div>
        <div class="receipt-title">PAYMENT RECEIPT</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="label">Receipt Number:</span>
            <span>{{ $receipt_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date:</span>
            <span>{{ $date }}</span>
        </div>
        <div class="info-row">
            <span class="label">Transaction ID:</span>
            <span>{{ $transaction_id }}</span>
        </div>
    </div>

    <div class="info-section">
        <h3>Customer Information</h3>
        <div class="info-row">
            <span class="label">Name:</span>
            <span>{{ $user->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Email:</span>
            <span>{{ $user->email }}</span>
        </div>
        <div class="info-row">
            <span class="label">Phone:</span>
            <span>{{ $user->phone ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="info-section">
        <h3>Payment Details</h3>
        <div class="info-row">
            <span class="label">Description:</span>
            <span>{{ $description }}</span>
        </div>
        <div class="info-row">
            <span class="label">Payment Method:</span>
            <span>{{ ucfirst($payment_method) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Status:</span>
            <span style="color: #059669; font-weight: bold;">PAID</span>
        </div>
    </div>

    <div class="amount-box">
        <div style="color: #666; margin-bottom: 10px;">Amount Paid</div>
        <div class="amount">K {{ number_format($amount, 2) }}</div>
    </div>

    <div class="footer">
        <p>Thank you for your payment!</p>
        <p>MyGrowNet - Empowering Communities Through Growth</p>
        <p>For inquiries, contact: support@mygrownet.com</p>
    </div>
</body>
</html>
