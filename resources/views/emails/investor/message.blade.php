<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Message: {{ $subject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .message-box { background: #f9fafb; border-left: 4px solid #4f46e5; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .message-subject { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 10px; }
        .message-preview { color: #4b5563; }
        .cta-button { display: inline-block; background: #4f46e5; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Message</h1>
        </div>
        <div class="content">
            <p>Dear {{ $investorName }},</p>
            
            <p>You have received a new message from the MyGrowNet team:</p>
            
            <div class="message-box">
                <div class="message-subject">{{ $subject }}</div>
                <div class="message-preview">{{ Str::limit($preview, 200) }}</div>
            </div>
            
            <p>Log in to your investor portal to read the full message and reply.</p>
            
            <a href="{{ url('/investor/messages') }}" class="cta-button">View Message</a>
        </div>
        <div class="footer">
            <p>You're receiving this because you're an investor in MyGrowNet.</p>
            <p>To manage your email preferences, visit your <a href="{{ url('/investor/settings') }}">account settings</a>.</p>
            <p>&copy; {{ date('Y') }} MyGrowNet. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
