<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .report-badge { display: inline-block; background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-bottom: 15px; }
        .report-title { font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 10px; }
        .report-period { color: #6b7280; margin-bottom: 20px; }
        .highlights { background: #f9fafb; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .highlights h3 { margin: 0 0 15px 0; font-size: 14px; color: #374151; text-transform: uppercase; }
        .highlight-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .highlight-item:last-child { border-bottom: none; }
        .highlight-label { color: #6b7280; }
        .highlight-value { font-weight: 600; color: #111827; }
        .cta-button { display: inline-block; background: #059669; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Financial Report Available</h1>
        </div>
        <div class="content">
            <p>Dear {{ $investorName }},</p>
            
            <span class="report-badge">{{ $reportType }} Report</span>
            
            <h2 class="report-title">{{ $title }}</h2>
            <p class="report-period">Period: {{ $reportPeriod }}</p>
            
            @if(!empty($highlights))
            <div class="highlights">
                <h3>Key Highlights</h3>
                @foreach($highlights as $label => $value)
                <div class="highlight-item">
                    <span class="highlight-label">{{ $label }}</span>
                    <span class="highlight-value">{{ $value }}</span>
                </div>
                @endforeach
            </div>
            @endif
            
            <p>A new financial report is now available in your investor portal. Log in to view the complete report with detailed metrics and analysis.</p>
            
            <a href="{{ url('/investor/reports') }}" class="cta-button">View Full Report</a>
        </div>
        <div class="footer">
            <p>You're receiving this because you're an investor in MyGrowNet.</p>
            <p>To manage your email preferences, visit your <a href="{{ url('/investor/settings') }}">account settings</a>.</p>
            <p>&copy; {{ date('Y') }} MyGrowNet. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
