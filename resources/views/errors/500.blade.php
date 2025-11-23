<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyGrowNet - Server Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .logo {
            width: 180px;
            height: auto;
            margin-bottom: 30px;
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2d3748;
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        p {
            color: #718096;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .error-code {
            background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
        }
        
        .error-details {
            background: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: left;
        }
        
        .error-details h3 {
            color: #c53030;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .error-details p {
            color: #742a2a;
            font-size: 14px;
            margin-bottom: 0;
            font-family: 'Courier New', monospace;
        }
        
        .contact {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }
        
        .contact p {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .contact a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .contact a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 640px) {
            .container {
                padding: 40px 25px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            p {
                font-size: 16px;
            }
            
            .icon {
                font-size: 60px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="/logo.png" alt="MyGrowNet" class="logo" onerror="this.style.display='none'">
        
        <div class="icon">⚠️</div>
        
        <div class="error-code">Error 500 - Internal Server Error</div>
        
        <h1>Oops! Something Went Wrong</h1>
        
        <p>
            {{ $message ?? 'We encountered an unexpected error while processing your request. Our team has been notified and is working to fix the issue.' }}
        </p>
        
        @if(isset($error) && $error)
        <div class="error-details">
            <h3>Technical Details:</h3>
            <p>{{ $error }}</p>
        </div>
        @endif
        
        <div class="actions">
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                ← Back to Dashboard
            </a>
            <a href="javascript:window.location.reload()" class="btn btn-secondary">
                🔄 Refresh Page
            </a>
        </div>
        
        <div class="contact">
            <p><strong>Need immediate assistance?</strong></p>
            <p>
                📧 Email: <a href="mailto:support@mygrownet.com">support@mygrownet.com</a><br>
                📱 WhatsApp: <a href="https://wa.me/260123456789">+260 123 456 789</a>
            </p>
        </div>
    </div>
</body>
</html>
