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
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
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
            background: #fffbeb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #fde68a;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #fef3c7;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #92400e;
            font-weight: 500;
        }
        .value {
            color: #78350f;
            font-weight: 600;
        }
        .amount {
            font-size: 1.5rem;
            color: #d97706;
        }
        .warning-badge {
            display: inline-block;
            background: #d97706;
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
        <h1 style="margin: 0; font-size: 1.5rem;">⏰ Payment Reminder</h1>
        <div class="warning-badge">{{ strtoupper($reminderType) }}</div>
    </div>

    <div class="content">
        <p>Dear {{ $customer->name }},</p>

        @if($reminderType === 'upcoming')
        <p>This is a friendly reminder that you have an upcoming payment due soon.</p>
        @elseif($reminderType === 'due_today')
        <p>This is a reminder that your payment is <strong>due today</strong>.</p>
        @else
        <p>This is a reminder about your invoice payment.</p>
        @endif

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
                <span class="label">Amount Due:</span>
                <span class="value amount">K{{ number_format($invoice->balance_due, 2) }}</span>
            </div>
            @if($daysUntilDue !== null)
            <div class="detail-row">
                <span class="label">Days Until Due:</span>
                <span class="value">{{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }}</span>
            </div>
            @endif
        </div>

        <p>Please arrange payment at your earliest convenience to avoid any late fees or service interruptions.</p>

        <p><strong>Payment Methods:</strong></p>
        <ul style="background: #f9fafb; padding: 15px 15px 15px 35px; border-radius: 6px;">
            <li>Bank Transfer</li>
            <li>Mobile Money</li>
            <li>Cash Payment at our office</li>
        </ul>

        @if($invoice->notes)
        <p><strong>Invoice Notes:</strong><br>{{ $invoice->notes }}</p>
        @endif

        <p>If you have already made this payment, please disregard this reminder.</p>

        <p>If you have any questions, please don't hesitate to contact us.</p>

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
