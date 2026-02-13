<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .payment-details {
            background: #f0fdf4;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #86efac;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #d1fae5;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #065f46;
            font-weight: 500;
        }
        .value {
            color: #064e3b;
            font-weight: 600;
        }
        .amount {
            font-size: 1.5rem;
            color: #059669;
        }
        .success-badge {
            display: inline-block;
            background: #059669;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin: 10px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 1.5rem;">✓ Payment Received</h1>
        <div class="success-badge">PAID</div>
    </div>

    <div class="content">
        <p>Dear {{ $customer->name }},</p>

        <p>Thank you! We have successfully received your payment.</p>

        <div class="payment-details">
            <div class="detail-row">
                <span class="label">Receipt Number:</span>
                <span class="value">{{ $payment->receipt_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Payment Date:</span>
                <span class="value">{{ $payment->payment_date->format('F j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Payment Method:</span>
                <span class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Amount Paid:</span>
                <span class="value amount">K{{ number_format($payment->amount, 2) }}</span>
            </div>
            @if($payment->reference_number)
            <div class="detail-row">
                <span class="label">Reference:</span>
                <span class="value">{{ $payment->reference_number }}</span>
            </div>
            @endif
        </div>

        @if($payment->allocations && $payment->allocations->count() > 0)
        <p><strong>Payment Applied To:</strong></p>
        <ul style="background: #f9fafb; padding: 15px 15px 15px 35px; border-radius: 6px;">
            @foreach($payment->allocations as $allocation)
            <li>Invoice {{ $allocation->invoice->invoice_number }}: K{{ number_format($allocation->amount, 2) }}</li>
            @endforeach
        </ul>
        @endif

        @if($payment->notes)
        <p><strong>Notes:</strong><br>{{ $payment->notes }}</p>
        @endif

        <p>Your receipt is attached to this email for your records.</p>

        <p>Thank you for your business!</p>

        <p>Best regards,<br>{{ $company->name }}</p>
    </div>

    <div class="footer">
        <p>{{ $company->name }}</p>
        @if($company->address)
        <p>{{ $company->address }}</p>
        @endif
        <p>
            @if($company->phone)Phone: {{ $company->phone }} | @endif
            @if($company->email)Email: {{ $company->email }}@endif
        </p>
    </div>
</body>
</html>
