@extends('auth.layout')

@section('title', 'Create Account')
@section('header-title', 'Join MyGrowNet')
@section('header-subtitle', 'Create your free account today')

@section('content')
    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="alert alert-error">
            <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="alert-content">
                <p class="alert-title">Registration failed</p>
                <p class="alert-message">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" id="register-form">
        @csrf
        
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
                autofocus
                required
            >
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address <span style="color: #6b7280; font-weight: 400;">(optional)</span></label>
            <input 
                type="email" 
                id="email" 
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
            <label for="phone" class="form-label">Phone Number <span style="color: #6b7280; font-weight: 400;">(optional)</span></label>
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
            <label for="password" class="form-label">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input @error('password') error @enderror"
                placeholder="Create a strong password"
                autocomplete="new-password"
                required
            >
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                class="form-input"
                placeholder="Confirm your password"
                autocomplete="new-password"
                required
            >
        </div>

        <div class="form-group">
            <label for="referral_code" class="form-label">Referral Code <span style="color: #6b7280; font-weight: 400;">(optional)</span></label>
            <input 
                type="text" 
                id="referral_code" 
                name="referral_code" 
                class="form-input @error('referral_code') error @enderror"
                value="{{ old('referral_code', request('ref')) }}"
                placeholder="Enter referral code if you have one"
            >
            @error('referral_code')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" id="submit-btn" style="margin-top: 0.5rem;">
            <span id="btn-text">Create Account</span>
            <span id="btn-loading" style="display: none;">
                <span class="btn-spinner"></span>
                Creating account...
            </span>
        </button>
    </form>
@endsection

@section('footer')
    <p class="auth-footer-text">
        Already have an account? 
        <a href="{{ route('login') }}" class="form-link">Sign in</a>
    </p>
@endsection

@section('scripts')
<script>
    document.getElementById('register-form').addEventListener('submit', function() {
        var btn = document.getElementById('submit-btn');
        var btnText = document.getElementById('btn-text');
        var btnLoading = document.getElementById('btn-loading');
        
        btn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
    });
</script>
@endsection
