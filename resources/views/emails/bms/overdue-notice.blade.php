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
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
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
        .invoice-details {
            background: #fef2f2;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #fecaca;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #fee2e2;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #991b1b;
            font-weight: 500;
        }
        .value {
            color: #7f1d1d;
            font-weight: 600;
        }
        .amount {
            font-size: 1.5rem;
            color: #dc2626;
        }
        .overdue-badge {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin: 10px 0;
        }
        .urgent-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
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
        <h1 style="margin: 0; font-size: 1.5rem;">⚠️ Overdue Payment Notice</h1>
        <div class="overdue-badge">PAYMENT OVERDUE</div>
    </div>

    <div class="content">
        <p>Dear {{ $customer->name }},</p>

        <div class="urgent-box">
            <strong>⚠️ URGENT:</strong> This invoice is now <strong>{{ $daysOverdue }} {{ $daysOverdue === 1 ? 'day' : 'days' }} overdue</strong>. 
            Immediate payment is required to avoid additional late fees and potential service suspension.
        </div>

        <p>Our records show that the following invoice remains unpaid past its due date:</p>

        <div class="invoice-details">
            <div class="detail-row">
                <span class="label">Invoice Number:</span>
                <span class="value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Invoice Date:</span>
                <span class="value">{{ $invoice->invoice_date->format('F j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Due Date:</span>
                <span class="value">{{ $invoice->due_date->format('F j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Days Overdue:</span>
                <span class="value" style="color: #dc2626;">{{ $daysOverdue }} {{ $daysOverdue === 1 ? 'day' : 'days' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Amount Due:</span>
                <span class="value amount">K{{ number_format($invoice->balance_due, 2) }}</span>
            </div>
        </div>

        <p><strong>Please take immediate action:</strong></p>
        <ol style="background: #f9fafb; padding: 15px 15px 15px 35px; border-radius: 6px;">
            <li>Make payment immediately using one of our payment methods</li>
            <li>Contact us if you need to arrange a payment plan</li>
            <li>Provide proof of payment if already made</li>
        </ol>

        <p><strong>Payment Methods:</strong></p>
        <ul style="background: #f9fafb; padding: 15px 15px 15px 35px; border-radius: 6px;">
            <li>Bank Transfer</li>
            <li>Mobile Money</li>
            <li>Cash Payment at our office</li>
        </ul>

        <p><strong>Consequences of Non-Payment:</strong></p>
        <ul style="background: #fef2f2; padding: 15px 15px 15px 35px; border-radius: 6px; border-left: 3px solid #dc2626;">
            <li>Late payment fees may apply</li>
            <li>Service suspension or termination</li>
            <li>Negative impact on credit standing</li>
            <li>Potential legal action for debt recovery</li>
        </ul>

        <p>If you have already made this payment, please send us proof of payment immediately so we can update our records.</p>

        <p>If you are experiencing financial difficulties, please contact us to discuss payment arrangements.</p>

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
        <p style="margin-top: 15px; font-size: 0.75rem; color: #9ca3af;">
            <a href="{{ route('cms.email.unsubscribe', ['company' => $company->id, 'email' => $customer->email, 'type' => 'reminders']) }}" style="color: #6b7280;">
                Unsubscribe from payment reminders
            </a>
        </p>
    </div>
</body>
</html>
