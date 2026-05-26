@extends('emails.layout')

@section('content')
    <h2 class="email-title">🎉 Congratulations on Your Level Advancement!</h2>
    
    <p class="email-text">
        {{ $greeting ?? 'Hello!' }}
    </p>

    <p class="email-text">
        We're thrilled to inform you that you've advanced to a new professional level on MyGrowNet!
    </p>

    <div class="info-box info-box-success">
        <p style="font-size: 18px; margin-bottom: 8px;"><strong>New Level: {{ $newLevel ?? 'Professional' }}</strong></p>
        <p>You've unlocked new benefits and opportunities!</p>
    </div>

    @if(isset($benefits) && is_array($benefits))
    <p class="email-text">
        <strong>Your new benefits include:</strong>
    </p>
    <ul style="color: #374151; font-size: 16px; line-height: 1.8; margin: 16px 0;">
        @foreach($benefits as $benefit)
        <li>{{ $benefit }}</li>
        @endforeach
    </ul>
    @endif

    @if(isset($stats))
    <table class="details-table">
        @foreach($stats as $label => $value)
        <tr>
            <td class="details-label">{{ $label }}</td>
            <td class="details-value">{{ $value }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="button-container">
        <a href="{{ $dashboardUrl ?? config('app.url') . '/dashboard' }}" class="button">
            View Your Dashboard
        </a>
    </div>

    <div class="divider"></div>

    <p class="email-text" style="font-size: 14px; color: #6b7280;">
        Keep up the great work! Continue engaging with the platform to unlock even more opportunities.
    </p>
@endsection
