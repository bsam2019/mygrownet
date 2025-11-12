<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyGrowNet - Under Maintenance</title>
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
        
        .status {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }
        
        .info {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: left;
        }
        
        .info h3 {
            color: #2d3748;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .info ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info li {
            color: #4a5568;
            font-size: 14px;
            padding: 5px 0;
            padding-left: 25px;
            position: relative;
        }
        
        .info li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="/logo.png" alt="MyGrowNet" class="logo">
        
        <div class="icon">🔧</div>
        
        <div class="status">System Maintenance in Progress</div>
        
        <h1>We'll Be Right Back!</h1>
        
        <p>
            MyGrowNet is currently undergoing scheduled maintenance to improve your experience. 
            We're working hard to bring you enhanced features and better performance.
        </p>
        
        <div class="info">
            <h3>What's happening:</h3>
            <ul>
                <li>System upgrades and optimizations</li>
                <li>Performance improvements</li>
                <li>Security enhancements</li>
                <li>Bug fixes and stability updates</li>
            </ul>
        </div>
        
        <div class="contact">
            <p><strong>Expected completion:</strong> Shortly</p>
            <p>For urgent matters, please contact support:</p>
            <p>
                📧 Email: <a href="mailto:support@mygrownet.com">support@mygrownet.com</a><br>
                📱 WhatsApp: <a href="https://wa.me/260123456789">+260 123 456 789</a>
            </p>
        </div>
    </div>
</body>
</html>
