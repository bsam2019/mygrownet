<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            padding: 32px 24px;
            text-align: center;
        }
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        .brand-text {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin: 0;
        }
        .email-content {
            padding: 32px 24px;
        }
        .email-footer {
            background-color: #f3f4f6;
            padding: 24px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 16px 0;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="MyGrowNet Logo" class="logo">
                <div>
                    <h1 class="brand-text">MyGrowNet</h1>
                    <p class="tagline">Growth Platform</p>
                </div>
            </div>
        </div>
        
        <div class="email-content">
            @yield('content')
        </div>
        
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} MyGrowNet. All rights reserved.</p>
            <p>This email was sent from {{ config('app.url') }}</p>
        </div>
    </div>
</body>
</html>