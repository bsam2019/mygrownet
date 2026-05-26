@extends('emails.layout')

@section('content')
    <h2 class="email-title">{{ $title ?? 'Transaction Notification' }}</h2>
    
    <p class="email-text">
        {{ $greeting ?? 'Hello!' }}
    </p>

    <p class="email-text">
        {{ $message ?? 'A transaction has been processed on your account.' }}
    </p>

    @if(isset($transactionDetails))
    <table class="details-table">
        @foreach($transactionDetails as $label => $value)
        <tr>
            <td class="details-label">{{ $label }}</td>
            <td class="details-value">{{ $value }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    @if(isset($status))
    <div class="info-box {{ $status === 'success' ? 'info-box-success' : ($status === 'pending' ? 'info-box-warning' : '') }}">
        <p><strong>Status:</strong> {{ ucfirst($status) }}</p>
        @if(isset($statusMessage))
        <p style="margin-top: 8px;">{{ $statusMessage }}</p>
        @endif
    </div>
    @endif

    @if(isset($actionUrl))
    <div class="button-container">
        <a href="{{ $actionUrl }}" class="button">
            {{ $actionText ?? 'View Transaction' }}
        </a>
    </div>
    @endif

    <div class="divider"></div>

    <p class="email-text" style="font-size: 14px; color: #6b7280;">
        If you have any questions about this transaction, please contact our support team or visit your <a href="{{ config('app.url') }}/dashboard" style="color: #2563eb;">dashboard</a>.
    </p>
@endsection
