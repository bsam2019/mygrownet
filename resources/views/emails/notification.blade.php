@extends('emails.layout')

@section('content')
    @if(!empty($greeting))
        <h2 style="color: #111827; font-size: 20px; font-weight: 600; margin-bottom: 16px;">
            {{ $greeting }}
        </h2>
    @endif

    @if(!empty($introLines))
        @foreach($introLines as $line)
            <p style="margin-bottom: 16px; line-height: 1.6;">{{ $line }}</p>
        @endforeach
    @endif

    @if(isset($actionText) && isset($actionUrl))
        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $actionUrl }}" class="button" style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                {{ $actionText }}
            </a>
        </div>
    @endif

    @if(!empty($outroLines))
        @foreach($outroLines as $line)
            <p style="margin-bottom: 16px; line-height: 1.6;">{{ $line }}</p>
        @endforeach
    @endif

    @if(isset($actionText) && isset($actionUrl))
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">
        <p style="font-size: 14px; color: #6b7280;">
            If you're having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser:
            <br>
            <a href="{{ $actionUrl }}" style="color: #2563eb; word-break: break-all;">{{ $actionUrl }}</a>
        </p>
    @endif
@endsection