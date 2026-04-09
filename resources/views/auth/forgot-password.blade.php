@extends('auth.layout')

@section('title', 'Forgot Password')
@section('header-title', 'Reset Password')
@section('header-subtitle', 'We\'ll send you a reset link')

@section('content')
    {{-- Success Message --}}
    @if (session('status'))
        <div class="alert alert-success">
            <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div class="alert-content">
                <p class="alert-message">{{ session('status') }}</p>
                <p class="alert-message" style="margin-top: 0.5rem; font-size: 0.8125rem;">
                    If you don't receive an email within 5 minutes, please contact support:
                </p>
                <p style="margin-top: 0.25rem; font-size: 0.8125rem;">
                    📧 <a href="mailto:support@mygrownet.com" style="color: #065f46; text-decoration: underline;">support@mygrownet.com</a><br>
                    📱 WhatsApp: <a href="https://wa.me/260779872676" target="_blank" style="color: #065f46; text-decoration: underline;">+260 779 872 676</a>
                </p>
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
                <p class="alert-message">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">
        Enter your email address and we'll send you a link to reset your password.
    </p>

    <form method="POST" action="{{ route('password.email') }}" id="forgot-form">
        @csrf
        
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-input @error('email') error @enderror"
                value="{{ old('email') }}"
                placeholder="email@example.com"
                autocomplete="email"
                autofocus
                required
            >
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" id="submit-btn">
            <span id="btn-text">Send Reset Link</span>
            <span id="btn-loading" style="display: none;">
                <span class="btn-spinner"></span>
                Sending...
            </span>
        </button>
    </form>

    {{-- Support Information --}}
    <div style="margin-top: 1.5rem; padding: 1rem; background: #f9fafb; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
        <p style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 0.5rem;">
            <strong style="color: #374151;">Need help?</strong>
        </p>
        <p style="font-size: 0.8125rem; color: #6b7280; line-height: 1.6;">
            If you're unable to reset your password, contact our support team:<br>
            📧 <a href="mailto:support@mygrownet.com" style="color: #2563eb; text-decoration: none;">support@mygrownet.com</a><br>
            📱 WhatsApp: <a href="https://wa.me/260779872676" target="_blank" style="color: #2563eb; text-decoration: none;">+260 779 872 676</a>
        </p>
    </div>
@endsection

@section('footer')
    <p class="auth-footer-text">
        Remember your password? 
        <a href="{{ route('login') }}" class="form-link">Back to login</a>
    </p>
@endsection

@section('scripts')
<script>
    document.getElementById('forgot-form').addEventListener('submit', function() {
        var btn = document.getElementById('submit-btn');
        var btnText = document.getElementById('btn-text');
        var btnLoading = document.getElementById('btn-loading');
        
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
    });
</script>
@endsection
