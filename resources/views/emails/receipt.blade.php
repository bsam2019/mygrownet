@extends('emails.layout')

@section('content')
    <h2 class="email-title">Payment Receipt</h2>
    
    <p class="email-text">
        Dear <strong>{{ $user->name }}</strong>,
    </p>

    <p class="email-text">
        Thank you for your payment! Your transaction has been processed successfully.
    </p>

    <div class="info-box info-box-success">
        <p><strong>✓ Payment Confirmed</strong></p>
        <p style="margin-top: 8px;">Your receipt is attached to this email for your records.</p>
    </div>

    <div class="button-container">
        <a href="{{ config('app.url') }}/dashboard/transactions" class="button">
            View Transactions
        </a>
    </div>

    <div class="divider"></div>

    <p class="email-text" style="font-size: 14px; color: #6b7280;">
        If you have any questions about this payment, please contact our <a href="{{ config('app.url') }}/support" style="color: #2563eb;">support team</a>.
    </p>
@endsection
