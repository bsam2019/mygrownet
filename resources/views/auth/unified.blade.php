<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $activeTab === 'register' ? 'Create Account' : 'Sign In' }} - MyGrowNet</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    
    {{-- Disable Inertia for this page --}}
    <meta name="inertia" content="false">
    
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --success: #10b981;
            --error: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        html { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }
    </style>
</head>
<body>

    <style>
        /* Background Effects */
        .bg-decoration {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        
        .bg-decoration::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-2%, 2%) rotate(1deg); }
        }
        
        /* Main Container */
        .auth-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }
        
        /* Card */
        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4),
                        0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            backdrop-filter: blur(20px);
        }
        
        /* Header */
        .auth-header {
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            background: linear-gradient(180deg, var(--gray-50) 0%, white 100%);
            border-bottom: 1px solid var(--gray-100);
        }
        
        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            text-decoration: none;
            transition: transform 0.2s ease;
        }
        
        .logo-wrapper:hover {
            transform: scale(1.05);
        }
        
        .logo-img {
            height: 56px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
        
        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            letter-spacing: -0.025em;
        }
        
        .brand-tagline {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }
    </style>

    <style>
        /* Tabs */
        .tabs-container {
            display: flex;
            padding: 0.5rem;
            background: var(--gray-100);
            margin: 1.5rem 2rem 0;
            border-radius: 0.75rem;
            gap: 0.25rem;
        }
        
        .tab-btn {
            flex: 1;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: transparent;
            color: var(--gray-500);
        }
        
        .tab-btn:hover {
            color: var(--gray-700);
            background: rgba(255, 255, 255, 0.5);
        }
        
        .tab-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Form Body */
        .auth-body {
            padding: 1.5rem 2rem 2rem;
        }
        
        /* Tab Panels */
        .tab-panel {
            display: none;
        }
        
        .tab-panel.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .alert-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .alert-content { flex: 1; }
        .alert-title { font-weight: 600; font-size: 0.875rem; margin-bottom: 0.125rem; }
        .alert-message { font-size: 0.8125rem; opacity: 0.9; }
    </style>

    <style>
        /* Form Elements */
        .form-group { margin-bottom: 1.25rem; }
        
        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }
        
        .form-label .optional {
            font-weight: 400;
            color: var(--gray-400);
            font-size: 0.75rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 0.9375rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            background: white;
            color: var(--gray-900);
        }
        
        .form-input::placeholder { color: var(--gray-400); }
        .form-input:hover { border-color: var(--gray-300); }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        
        .form-input.error {
            border-color: var(--error);
            background: #fef2f2;
        }
        
        .form-hint {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.375rem;
        }
        
        .form-error {
            font-size: 0.75rem;
            color: var(--error);
            margin-top: 0.375rem;
            font-weight: 500;
        }
        
        /* Checkbox & Links */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-input {
            width: 1.125rem;
            height: 1.125rem;
            accent-color: var(--primary);
            cursor: pointer;
        }
        
        .checkbox-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            cursor: pointer;
        }
        
        .form-link {
            font-size: 0.875rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .form-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
    </style>

    <style>
        /* Button */
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
        }
        
        .btn-primary:active { transform: translateY(0); }
        
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-spinner {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Password Toggle */
        .password-wrapper { position: relative; }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.25rem;
        }
        
        .password-toggle:hover { color: var(--gray-600); }
        .password-toggle svg { width: 1.25rem; height: 1.25rem; }
        
        /* Footer */
        .auth-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-100);
        }
        
        .footer-text {
            font-size: 0.8125rem;
            color: var(--gray-500);
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 0.75rem;
        }
        
        .footer-link {
            font-size: 0.75rem;
            color: var(--gray-400);
            text-decoration: none;
        }
        
        .footer-link:hover { color: var(--gray-600); }
        
        /* Responsive */
        @media (max-width: 480px) {
            body { padding: 0.75rem; }
            .auth-card { border-radius: 1.25rem; }
            .auth-header { padding: 1.5rem 1.5rem 1rem; }
            .tabs-container { margin: 1rem 1.5rem 0; }
            .auth-body { padding: 1.25rem 1.5rem 1.5rem; }
            .auth-footer { padding: 1.25rem 1.5rem; }
            .logo-img { height: 48px; }
            .brand-name { font-size: 1.25rem; }
        }
    </style>

    <!-- Background decoration -->
    <div class="bg-decoration"></div>
    
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header with Logo -->
            <div class="auth-header">
                <a href="/" class="logo-wrapper">
                    <img src="/logo.png" alt="MyGrowNet" class="logo-img" onerror="this.style.display='none'">
                </a>
                <h1 class="brand-name">MyGrowNet</h1>
                <p class="brand-tagline">Grow Together, Succeed Together</p>
                
                <!-- Tabs (JavaScript controlled) -->
                <div class="tabs-container">
                    <button type="button" class="tab-btn {{ $activeTab === 'login' ? 'active' : '' }}" onclick="switchTab('login')" id="tab-login">
                        Sign In
                    </button>
                    <button type="button" class="tab-btn {{ $activeTab === 'register' ? 'active' : '' }}" onclick="switchTab('register')" id="tab-register">
                        Create Account
                    </button>
                </div>
            </div>
            
            <div class="auth-body">
                {{-- Success Message --}}
                @if (session('status'))
                    <div class="alert alert-success">
                        <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div class="alert-content">
                            <p class="alert-message">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Error Messages --}}
                @if ($errors->any())
                    <div class="alert alert-error">
                        <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="alert-content">
                            <p class="alert-title" id="error-title">{{ $activeTab === 'register' ? 'Registration failed' : 'Unable to sign in' }}</p>
                            <p class="alert-message">{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <!-- LOGIN FORM -->
                <div class="tab-panel {{ $activeTab === 'login' ? 'active' : '' }}" id="panel-login">
                    <form method="POST" action="{{ route('login') }}" id="login-form" data-turbo="false">
                        @csrf
                        
                        <div class="form-group">
                            <label for="login_email" class="form-label">Email or Phone Number</label>
                            <input 
                                type="text" 
                                id="login_email" 
                                name="email" 
                                class="form-input @error('email') error @enderror"
                                value="{{ old('email') }}"
                                placeholder="email@example.com or 0977123456"
                                autocomplete="username"
                            >
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="login_password" class="form-label">Password</label>
                            <div class="password-wrapper">
                                <input 
                                    type="password" 
                                    id="login_password" 
                                    name="password" 
                                    class="form-input @error('password') error @enderror"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('login_password')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-options">
                            <div class="checkbox-group">
                                <input type="checkbox" id="remember" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember" class="checkbox-label">Remember me</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="form-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary" id="login-btn">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-loading" style="display: none;">
                                <span class="btn-spinner"></span>
                                Signing in...
                            </span>
                        </button>
                    </form>
                </div>

                <!-- REGISTER FORM -->
                <div class="tab-panel {{ $activeTab === 'register' ? 'active' : '' }}" id="panel-register">
                    <form method="POST" action="{{ route('register') }}" id="register-form" data-turbo="false">
                        @csrf
                        
                        {{-- Hidden field to preserve referral code --}}
                        <input type="hidden" id="stored_referral" name="stored_referral" value="{{ $referralCode ?? old('referral_code') ?? request('ref') }}">
                        
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-input @error('name') error @enderror"
                                value="{{ old('name') }}"
                                placeholder="Enter your full name"
                                autocomplete="name"
                            >
                            @error('name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reg_email" class="form-label">Email Address <span class="optional">(optional)</span></label>
                            <input 
                                type="email" 
                                id="reg_email" 
                                name="email" 
                                class="form-input @error('email') error @enderror"
                                value="{{ old('email') }}"
                                placeholder="email@example.com"
                                autocomplete="email"
                            >
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number <span class="optional">(optional)</span></label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                class="form-input @error('phone') error @enderror"
                                value="{{ old('phone') }}"
                                placeholder="0977123456"
                                autocomplete="tel"
                            >
                            <p class="form-hint">Provide either email or phone number</p>
                            @error('phone')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reg_password" class="form-label">Password</label>
                            <div class="password-wrapper">
                                <input 
                                    type="password" 
                                    id="reg_password" 
                                    name="password" 
                                    class="form-input @error('password') error @enderror"
                                    placeholder="Create a strong password"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('reg_password')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="password-wrapper">
                                <input 
                                    type="password" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    class="form-input"
                                    placeholder="Confirm your password"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="referral_code" class="form-label">Referral Code <span class="optional">(optional)</span></label>
                            <input 
                                type="text" 
                                id="referral_code" 
                                name="referral_code" 
                                class="form-input @error('referral_code') error @enderror"
                                value="{{ old('referral_code') ?? $referralCode ?? request('ref') }}"
                                placeholder="Enter referral code if you have one"
                            >
                            @error('referral_code')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" id="register-btn" style="margin-top: 0.5rem;">
                            <span class="btn-text">Create Account</span>
                            <span class="btn-loading" style="display: none;">
                                <span class="btn-spinner"></span>
                                Creating account...
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="footer-text">
                    &copy; {{ date('Y') }} MyGrowNet. All rights reserved.
                </p>
                <div class="footer-links">
                    <a href="/" class="footer-link">Home</a>
                    <a href="/about" class="footer-link">About</a>
                    <a href="/how-we-operate" class="footer-link">How We Operate</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching without page refresh
        function switchTab(tab) {
            // Update tab buttons
            document.getElementById('tab-login').classList.toggle('active', tab === 'login');
            document.getElementById('tab-register').classList.toggle('active', tab === 'register');
            
            // Update panels
            document.getElementById('panel-login').classList.toggle('active', tab === 'login');
            document.getElementById('panel-register').classList.toggle('active', tab === 'register');
            
            // Update URL without reload (for bookmarking)
            var newUrl = tab === 'register' ? '{{ route("register") }}' : '{{ route("login") }}';
            window.history.pushState({tab: tab}, '', newUrl);
            
            // Update page title
            document.title = (tab === 'register' ? 'Create Account' : 'Sign In') + ' - MyGrowNet';
            
            // Preserve referral code when switching tabs
            var refCode = document.getElementById('referral_code')?.value || 
                          document.getElementById('stored_referral')?.value ||
                          new URLSearchParams(window.location.search).get('ref');
            if (refCode && document.getElementById('referral_code')) {
                document.getElementById('referral_code').value = refCode;
            }
            
            // Focus first input
            setTimeout(function() {
                var panel = document.getElementById('panel-' + tab);
                var firstInput = panel.querySelector('input:not([type="hidden"])');
                if (firstInput) firstInput.focus();
            }, 100);
        }
        
        // Handle browser back/forward
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.tab) {
                switchTab(e.state.tab);
            }
        });
        
        // Form submission loading state
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var btn = this.querySelector('.btn-primary');
                var btnText = btn.querySelector('.btn-text');
                var btnLoading = btn.querySelector('.btn-loading');
                
                btn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
            });
        });
        
        // Password visibility toggle
        function togglePassword(inputId) {
            var input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        
        // Store referral code in sessionStorage for persistence
        (function() {
            var refCode = new URLSearchParams(window.location.search).get('ref') || 
                          document.getElementById('referral_code')?.value;
            if (refCode) {
                sessionStorage.setItem('mygrownet_ref', refCode);
            } else {
                var stored = sessionStorage.getItem('mygrownet_ref');
                if (stored && document.getElementById('referral_code')) {
                    document.getElementById('referral_code').value = stored;
                }
            }
        })();
    </script>
</body>
</html>
