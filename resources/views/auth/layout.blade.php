<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'MyGrowNet') }}</title>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <style>
        /* Reset & Base */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        html { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.5;
            color: #1f2937;
        }
        
        /* Card Container */
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        
        /* Header */
        .auth-header {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        
        .auth-logo {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
        }
        
        .auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        /* Form Body */
        .auth-body {
            padding: 2rem;
        }
        
        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        .alert-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .alert-content { flex: 1; }
        .alert-title { font-weight: 600; font-size: 0.875rem; }
        .alert-message { font-size: 0.875rem; margin-top: 0.25rem; }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.2s;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-input.error {
            border-color: #ef4444;
        }
        
        .form-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.375rem;
        }
        
        .form-error {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.375rem;
        }
        
        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-input {
            width: 1rem;
            height: 1rem;
            accent-color: #2563eb;
        }
        
        .checkbox-label {
            font-size: 0.875rem;
            color: #374151;
        }
        
        /* Links */
        .form-link {
            font-size: 0.875rem;
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        
        .form-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-spinner {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Footer */
        .auth-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        .auth-footer-text {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            body { padding: 0.5rem; }
            .auth-card { border-radius: 0.75rem; }
            .auth-header { padding: 1.5rem; }
            .auth-body { padding: 1.5rem; }
            .auth-footer { padding: 1rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">🌱</div>
            <h1 class="auth-title">@yield('header-title', config('app.name', 'MyGrowNet'))</h1>
            <p class="auth-subtitle">@yield('header-subtitle')</p>
        </div>
        
        <div class="auth-body">
            @yield('content')
        </div>
        
        <div class="auth-footer">
            @yield('footer')
        </div>
    </div>
    
    @yield('scripts')
</body>
</html>
