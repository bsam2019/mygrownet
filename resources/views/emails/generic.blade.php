@extends('emails.layout')

@section('content')
    @if(isset($greeting))
    <h2 class="email-title">{{ $greeting }}</h2>
    @endif

    @if(isset($message))
    <div class="email-text">
        {!! nl2br(e($message)) !!}
    </div>
    @endif

    @if(isset($details) && is_array($details))
    <table class="details-table">
        @foreach($details as $label => $value)
        <tr>
            <td class="details-label">{{ $label }}</td>
            <td class="details-value">{{ $value }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    @if(isset($infoBox))
    <div class="info-box {{ $infoBoxType ?? '' }}">
        <p>{!! nl2br(e($infoBox)) !!}</p>
    </div>
    @endif

    @if(isset($actionUrl))
    <div class="button-container">
        <a href="{{ $actionUrl }}" class="button {{ $buttonClass ?? '' }}">
            {{ $actionText ?? 'View Details' }}
        </a>
    </div>
    @endif

    @if(isset($secondaryActionUrl))
    <div class="button-container">
        <a href="{{ $secondaryActionUrl }}" class="button button-secondary">
            {{ $secondaryActionText ?? 'Learn More' }}
        </a>
    </div>
    @endif

    @if(isset($footerNote))
    <div class="divider"></div>
    <p class="email-text" style="font-size: 14px; color: #6b7280;">
        {{ $footerNote }}
    </p>
    @endif
@endsection
