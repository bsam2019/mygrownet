<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .urgent-badge { display: inline-block; background: #dc2626; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-bottom: 15px; }
        .type-badge { display: inline-block; background: #e5e7eb; color: #374151; padding: 4px 12px; border-radius: 4px; font-size: 12px; margin-bottom: 15px; }
        .announcement-title { font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 15px; }
        .announcement-content { color: #4b5563; margin-bottom: 25px; }
        .cta-button { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MyGrowNet Investor Portal</h1>
        </div>
        <div class="content">
            <p>Dear {{ $investorName }},</p>
            
            @if($isUrgent)
                <span class="urgent-badge">URGENT</span>
            @endif
            <span class="type-badge">{{ ucfirst($type) }}</span>
            
            <h2 class="announcement-title">{{ $title }}</h2>
            
            <div class="announcement-content">
                {!! nl2br(e($content)) !!}
            </div>
            
            <a href="{{ url('/investor/announcements') }}" class="cta-button">View in Portal</a>
        </div>
        <div class="footer">
            <p>You're receiving this because you're an investor in MyGrowNet.</p>
            <p>To manage your email preferences, visit your <a href="{{ url('/investor/settings') }}">account settings</a>.</p>
            <p>&copy; {{ date('Y') }} MyGrowNet. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
