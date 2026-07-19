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
        .contract-details {
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
        .button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 14px 36px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
            font-size: 1rem;
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
        <h1 style="margin: 0; font-size: 1.5rem;">Contract Ready for Signing</h1>
        <p style="margin: 8px 0 0; opacity: 0.9;">{{ $company->name }}</p>
    </div>

    <div class="content">
        <p>Dear {{ $customer->name }},</p>

        <p>A contract has been prepared for you by <strong>{{ $company->name }}</strong>. Please review and sign it electronically.</p>

        <div class="contract-details">
            <div class="detail-row">
                <span class="label">Contract:</span>
                <span class="value">{{ $contract->title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Reference:</span>
                <span class="value">{{ $contract->contract_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Value:</span>
                <span class="value">K{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Start Date:</span>
                <span class="value">{{ $contract->start_date->format('F j, Y') }}</span>
            </div>
            @if($contract->end_date)
            <div class="detail-row">
                <span class="label">End Date:</span>
                <span class="value">{{ $contract->end_date->format('F j, Y') }}</span>
            </div>
            @endif
        </div>

        <div style="text-align: center;">
            <a href="{{ $signingUrl }}" class="button">Review &amp; Sign Contract</a>
        </div>

        <p style="font-size: 0.875rem; color: #6b7280;">This link is unique to you. Do not share it with anyone. The link will expire after the contract has been signed.</p>

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
