<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #374151; font-size: 14px; line-height: 1.6; margin: 0; padding: 0; background: #f9fafb; }
        .wrapper { max-width: 600px; margin: 30px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #2563eb; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0 0; opacity: 0.85; font-size: 13px; }
        .body { padding: 30px; }
        .greeting { font-size: 16px; margin-bottom: 16px; }
        .custom-message { background: #f0f9ff; border-left: 3px solid #2563eb; padding: 12px 16px; margin: 16px 0; border-radius: 0 6px 6px 0; font-style: italic; color: #1e40af; }
        .summary { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .summary-row:last-child { border-bottom: none; font-weight: bold; font-size: 15px; color: #111827; }
        .cta { text-align: center; margin: 24px 0; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ $company->name }}</h1>
            <p>Quotation {{ $quotation->quotation_number }}</p>
        </div>
        <div class="body">
            <p class="greeting">Dear {{ $recipient_name }},</p>

            @if(!empty($custom_message))
            <div class="custom-message">{{ $custom_message }}</div>
            @else
            <p>Please find attached your quotation from <strong>{{ $company->name }}</strong>. We look forward to working with you.</p>
            @endif

            <div class="summary">
                <div class="summary-row"><span>Quotation Number</span><span>{{ $quotation->quotation_number }}</span></div>
                <div class="summary-row"><span>Date</span><span>{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}</span></div>
                @if($quotation->expiry_date)
                <div class="summary-row"><span>Valid Until</span><span>{{ \Carbon\Carbon::parse($quotation->expiry_date)->format('d M Y') }}</span></div>
                @endif
                <div class="summary-row"><span>Total Amount</span><span>K{{ number_format($quotation->total_amount, 2) }}</span></div>
            </div>

            <p style="font-size: 13px; color: #6b7280;">The quotation PDF is attached to this email. If you have any questions, please don't hesitate to contact us.</p>
        </div>
        <div class="footer">
            <p>{{ $company->name }}
                @if($company->phone) · {{ $company->phone }}@endif
                @if($company->email) · {{ $company->email }}@endif
            </p>
            <p style="margin-top: 8px;">This email was sent by {{ $company->name }} via their business management system.</p>
        </div>
    </div>
</body>
</html>
