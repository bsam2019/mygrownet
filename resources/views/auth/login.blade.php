@extends('auth.layout')

@section('title', 'Log In')
@section('header-title', 'Welcome Back')
@section('header-subtitle', 'Sign in to your account')

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
                <p class="alert-title">Unable to sign in</p>
                <p class="alert-message">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf
        
        <div class="form-group">
            <label for="email" class="form-label">Email or Phone Number</label>
            <input 
                type="text" 
                id="email" 
                name="email" 
                class="form-input @error('email') error @enderror"
                value="{{ old('email') }}"
                placeholder="email@example.com or 0977123456"
                autocomplete="username"
                autofocus
                required
            >
            <p class="form-hint">Enter your email address or phone number</p>
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input @error('password') error @enderror"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-row">
            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="checkbox-label">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="form-link">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary" id="submit-btn">
            <span id="btn-text">Sign in to your account</span>
            <span id="btn-loading" style="display: none;">
                <span class="btn-spinner"></span>
                Signing in...
            </span>
        </button>
    </form>
@endsection

@section('footer')
    <p class="auth-footer-text">
        Don't have an account? 
        <a href="{{ route('register') }}" class="form-link">Create an account</a>
    </p>
@endsection

@section('scripts')
<script>
    document.getElementById('login-form').addEventListener('submit', function() {
        var btn = document.getElementById('submit-btn');
        var btnText = document.getElementById('btn-text');
        var btnLoading = document.getElementById('btn-loading');
        
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
    });
</script>
@endsection
