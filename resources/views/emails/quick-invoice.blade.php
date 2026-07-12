@extends('emails.layout')

@section('content')
    <h2 class="email-title">{{ $document['type_label'] }}</h2>
    
    <p class="email-text">
        From <strong>{{ $document['business_info']['name'] }}</strong>
    </p>

    @if($customMessage)
    <div class="info-box info-box-warning">
        <p>{{ $customMessage }}</p>
    </div>
    @endif

    <table class="details-table">
        <tr>
            <td class="details-label">Document #</td>
            <td class="details-value">{{ $document['document_number'] }}</td>
        </tr>
        <tr>
            <td class="details-label">Date</td>
            <td class="details-value">{{ \Carbon\Carbon::parse($document['issue_date'])->format('d M Y') }}</td>
        </tr>
        @if(!empty($document['due_date']))
        <tr>
            <td class="details-label">Due Date</td>
            <td class="details-value">{{ \Carbon\Carbon::parse($document['due_date'])->format('d M Y') }}</td>
        </tr>
        @endif
        <tr>
            <td class="details-label">To</td>
            <td class="details-value">{{ $document['client_info']['name'] }}</td>
        </tr>
        <tr>
            <td class="details-label">Amount</td>
            <td class="details-value" style="font-size: 20px; font-weight: 700; color: #059669;">
                @php
                    $symbol = match($document['currency']) {
                        'ZMW' => 'K',
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        'ZAR' => 'R',
                        default => $document['currency'],
                    };
                @endphp
                {{ $symbol }} {{ number_format($document['total'], 2) }}
            </td>
        </tr>
    </table>

    <div class="info-box">
        <p><strong>📎 Attachment Included</strong></p>
        <p style="margin-top: 8px;">Your {{ strtolower($document['type_label']) }} is attached to this email as a PDF.</p>
    </div>

    @if(!empty($document['notes']))
    <p class="email-text">
        <strong>Notes:</strong><br>
        {{ $document['notes'] }}
    </p>
    @endif

    <div class="button-container">
        <a href="{{ config('app.url') }}/quick-invoice" class="button">
            Create Your Own Invoice
        </a>
    </div>

    <div class="divider"></div>

    <p class="email-text" style="font-size: 14px; color: #6b7280;">
        This {{ strtolower($document['type_label']) }} was sent via <a href="{{ config('app.url') }}" style="color: #2563eb;">MyGrowNet</a>.
    </p>
@endsection
