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
            color: #1f2937;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
        }
        
        /* Container */
        .email-wrapper {
            background-color: #f3f4f6;
            padding: 40px 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        /* Header - Simple & Clean */
        .email-header {
            background-color: #ffffff;
            padding: 40px 40px 24px 40px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .logo-container {
            margin-bottom: 0;
        }
        .logo {
            width: 120px;
            height: auto;
            object-fit: contain;
        }
        
        /* Content */
        .email-content {
            padding: 40px;
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
            color: #4b5563;
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
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        
        /* Info boxes */
        .info-box {
            background-color: #f0f9ff;
            border-left: 3px solid #2563eb;
            padding: 16px 20px;
            margin: 24px 0;
            border-radius: 4px;
        }
        .info-box-success {
            background-color: #f0fdf4;
            border-left-color: #10b981;
        }
        .info-box-warning {
            background-color: #fffbeb;
            border-left-color: #f59e0b;
        }
        .info-box p {
            margin: 0;
            color: #374151;
            font-size: 15px;
        }
        
        /* Details table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            background-color: #f9fafb;
            border-radius: 6px;
            overflow: hidden;
        }
        .details-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            width: 45%;
        }
        .details-value {
            color: #111827;
            font-size: 15px;
            font-weight: 500;
        }
        
        /* Footer - Professional */
        .email-footer {
            background-color: #f9fafb;
            padding: 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-brand {
            font-size: 16px;
            font-weight: 600;
            color: #2563eb;
            margin: 0 0 8px 0;
        }
        .footer-tagline {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 24px 0;
        }
        .footer-links {
            margin: 0 0 24px 0;
        }
        .footer-link {
            color: #2563eb;
            text-decoration: none;
            margin: 0 16px;
            font-size: 14px;
            font-weight: 500;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        .footer-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 24px auto;
            width: 80%;
            max-width: 200px;
        }
        .footer-text {
            font-size: 13px;
            color: #9ca3af;
            margin: 8px 0;
            line-height: 1.5;
        }
        .footer-address {
            font-size: 12px;
            color: #9ca3af;
            margin: 16px 0 0 0;
            line-height: 1.6;
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 32px 0;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px !important;
            }
            .email-container {
                border-radius: 0 !important;
            }
            .email-header {
                padding: 32px 24px 20px 24px !important;
            }
            .email-content {
                padding: 32px 24px !important;
            }
            .email-footer {
                padding: 32px 24px !important;
            }
            .email-title {
                font-size: 22px !important;
            }
            .logo {
                width: 100px !important;
            }
            .button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box;
            }
            .footer-link {
                margin: 0 8px !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header - Clean & Minimal -->
            <div class="email-header">
                <div class="logo-container">
                    <img src="https://mygrownet.com/logo.png" alt="MyGrowNet" class="logo">
                </div>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                @yield('content')
            </div>
            
            <!-- Footer - Professional -->
            <div class="email-footer">
                <p class="footer-brand">MyGrowNet</p>
                <p class="footer-tagline">Learn • Earn • Grow</p>
                
                <div class="footer-links">
                    <a href="https://mygrownet.com" class="footer-link">Home</a>
                    <a href="https://mygrownet.com/about" class="footer-link">About</a>
                    <a href="https://mygrownet.com/support" class="footer-link">Support</a>
                    <a href="https://mygrownet.com/contact" class="footer-link">Contact</a>
                </div>
                
                <div class="footer-divider"></div>
                
                <p class="footer-text">
                    © {{ date('Y') }} MyGrowNet. All rights reserved.
                </p>
                <p class="footer-address">
                    MyGrowNet Platform<br>
                    Zambia<br>
                    Email: support@mygrownet.com
                </p>
                <p class="footer-text" style="margin-top: 20px;">
                    You're receiving this email because you are a member of MyGrowNet.<br>
                    Need help? Contact our <a href="https://mygrownet.com/support" style="color: #2563eb;">support team</a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>