@extends('emails.layout')

@section('content')
    <h2 class="email-title">Reset Your Password</h2>
    
    <p class="email-text">
        {{ $greeting ?? 'Hello!' }}
    </p>

    <p class="email-text">
        You are receiving this email because we received a password reset request for your account.
    </p>

    <div class="button-container">
        <a href="{{ $resetUrl }}" class="button">
            Reset Password
        </a>
    </div>

    <div class="info-box info-box-warning">
        <p><strong>Security Notice:</strong></p>
        <p style="margin-top: 8px;">This password reset link will expire in <strong>{{ $expiresIn ?? '60 minutes' }}</strong>.</p>
    </div>

    <p class="email-text">
        If you did not request a password reset, no further action is required. Your account remains secure.
    </p>

    <div class="divider"></div>

    <p class="email-text" style="font-size: 14px; color: #6b7280;">
        <strong>Having trouble clicking the button?</strong><br>
        Copy and paste this URL into your browser:<br>
        <span style="color: #2563eb; word-break: break-all;">{{ $resetUrl }}</span>
    </p>
@endsection
