@extends('auth.layout')

@section('title', 'Forgot Password')
@section('header-title', 'Reset Password')
@section('header-subtitle', 'Enter your email to receive a reset link')

@section('content')
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
                <p class="alert-message">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">
        Forgot your password? No problem. Enter your email address and we'll send you a link to reset it.
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
