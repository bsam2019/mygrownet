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
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
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
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #6b7280;
            font-weight: 500;
        }
        .value {
            color: #111827;
            font-weight: 600;
        }
        .total {
            font-size: 1.25rem;
            color: #2563eb;
        }
        .button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
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
        <h1 style="margin: 0; font-size: 1.5rem;">Invoice from {{ $company->name }}</h1>
    </div>

    <div class="content">
        <p>Dear {{ $customer->name }},</p>

        <p>Thank you for your business! Please find your invoice details below.</p>

        <div class="invoice-details">
            <div class="detail-row">
                <span class="label">Invoice Number:</span>
                <span class="value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Invoice Date:</span>
                <span class="value">{{ $invoice->invoice_date->format('F j, Y') }}</span>
            </div>
            @if($invoice->due_date)
            <div class="detail-row">
                <span class="label">Due Date:</span>
                <span class="value">{{ $invoice->due_date->format('F j, Y') }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="label">Total Amount:</span>
                <span class="value total">K{{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            @if($invoice->amount_paid > 0)
            <div class="detail-row">
                <span class="label">Amount Paid:</span>
                <span class="value">K{{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Balance Due:</span>
                <span class="value total">K{{ number_format($invoice->balance_due, 2) }}</span>
            </div>
            @endif
        </div>

        @if($invoice->notes)
        <p><strong>Notes:</strong><br>{{ $invoice->notes }}</p>
        @endif

        <p>The invoice PDF is attached to this email for your records.</p>

        <p>If you have any questions about this invoice, please don't hesitate to contact us.</p>

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
