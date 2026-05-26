@extends('emails.layout')

@section('content')
    <h2 class="email-title">{{ $greeting ?? 'Welcome to MyGrowNet!' }}</h2>
    
    <p class="email-text">
        We're excited to have you join our community empowerment platform. MyGrowNet is designed to help you <strong>Learn</strong>, <strong>Earn</strong>, and <strong>Grow</strong> through practical skills, mentorship, and income opportunities.
    </p>

    <div class="info-box info-box-success">
        <p><strong>Your account is now active!</strong></p>
        <p style="margin-top: 8px;">You can now access all platform features and start your growth journey.</p>
    </div>

    <p class="email-text">
        <strong>What's next?</strong>
    </p>
    <ul style="color: #374151; font-size: 16px; line-height: 1.8; margin: 16px 0;">
        <li>Complete your profile to personalize your experience</li>
        <li>Explore our learning resources and skill-building content</li>
        <li>Connect with mentors and community members</li>
        <li>Start earning through referrals and participation</li>
    </ul>

    @if(isset($actionUrl))
    <div class="button-container">
        <a href="{{ $actionUrl }}" class="button">
            {{ $actionText ?? 'Get Started' }}
        </a>
    </div>
    @endif

    <div class="divider"></div>

    <p class="email-text" style="font-size: 14px; color: #6b7280;">
        Need help getting started? Our support team is here to assist you. Visit our <a href="{{ config('app.url') }}/support" style="color: #2563eb;">Help Center</a> or reply to this email.
    </p>
@endsection
