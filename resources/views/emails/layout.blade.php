<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        /* Reset styles */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        
        /* Base styles */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #111827;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
        }
        
        /* Container */
        .email-wrapper {
            background-color: #f9fafb;
            padding: 24px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            padding: 40px 32px;
            text-align: center;
        }
        .logo-container {
            margin-bottom: 16px;
        }
        .logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            display: inline-block;
        }
        .brand-text {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin: 12px 0 4px 0;
            letter-spacing: -0.5px;
        }
        .tagline {
            color: rgba(255, 255, 255, 0.95);
            font-size: 15px;
            margin: 0;
            font-weight: 500;
        }
        
        /* Content */
        .email-content {
            padding: 40px 32px;
        }
        .email-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 16px 0;
            line-height: 1.3;
        }
        .email-text {
            font-size: 16px;
            color: #374151;
            margin: 0 0 16px 0;
            line-height: 1.6;
        }
        .email-text strong {
            color: #111827;
            font-weight: 600;
        }
        
        /* Buttons */
        .button-container {
            margin: 32px 0;
            text-align: center;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #2563eb;
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .button-secondary {
            background-color: #059669;
        }
        .button-secondary:hover {
            background-color: #047857;
        }
        
        /* Info boxes */
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 16px 20px;
            margin: 24px 0;
            border-radius: 6px;
        }
        .info-box-success {
            background-color: #ecfdf5;
            border-left-color: #059669;
        }
        .info-box-warning {
            background-color: #fffbeb;
            border-left-color: #d97706;
        }
        .info-box p {
            margin: 0;
            color: #374151;
            font-size: 15px;
        }
        
        /* Stats/Details table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            background-color: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
        }
        .details-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            width: 40%;
        }
        .details-value {
            color: #111827;
            font-size: 15px;
            font-weight: 500;
        }
        
        /* Footer */
        .email-footer {
            background-color: #f3f4f6;
            padding: 32px 24px;
            text-align: center;
        }
        .footer-text {
            font-size: 14px;
            color: #6b7280;
            margin: 8px 0;
        }
        .footer-links {
            margin: 16px 0;
        }
        .footer-link {
            color: #2563eb;
            text-decoration: none;
            margin: 0 12px;
            font-size: 14px;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        .social-links {
            margin: 20px 0 16px 0;
        }
        .social-link {
            display: inline-block;
            margin: 0 8px;
            color: #6b7280;
            text-decoration: none;
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 32px 0;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                border-radius: 0 !important;
            }
            .email-header {
                padding: 32px 24px !important;
            }
            .email-content {
                padding: 32px 24px !important;
            }
            .email-title {
                font-size: 22px !important;
            }
            .button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo-container">
                    <img src="https://mygrownet.com/logo.png" alt="MyGrowNet Logo" class="logo">
                </div>
                <h1 class="brand-text">MyGrowNet</h1>
                <p class="tagline">Learn • Earn • Grow</p>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-links">
                    <a href="{{ config('app.url') }}" class="footer-link">Home</a>
                    <a href="{{ config('app.url') }}/support" class="footer-link">Support</a>
                    <a href="{{ config('app.url') }}/contact" class="footer-link">Contact</a>
                </div>
                
                <div class="divider" style="margin: 20px auto; width: 80%; max-width: 200px;"></div>
                
                <p class="footer-text">
                    <strong>MyGrowNet</strong> - Community Empowerment Platform
                </p>
                <p class="footer-text">
                    &copy; {{ date('Y') }} MyGrowNet. All rights reserved.
                </p>
                <p class="footer-text" style="font-size: 12px; color: #9ca3af; margin-top: 16px;">
                    This email was sent to you as a member of MyGrowNet.<br>
                    If you have questions, please contact our support team.
                </p>
            </div>
        </div>
    </div>
</body>
</html>