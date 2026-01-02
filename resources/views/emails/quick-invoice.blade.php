<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document['type_label'] }} from {{ $document['business_info']['name'] }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2563eb;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .document-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .document-info p {
            margin: 5px 0;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #059669;
            text-align: center;
            margin: 20px 0;
        }
        .custom-message {
            background: #fef3c7;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #d97706;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 0;
        }
        .btn:hover {
            background: #1d4ed8;
        }
        .cta {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .footer a {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $document['type_label'] }}</h1>
            <p style="color: #6b7280; margin-top: 5px;">from {{ $document['business_info']['name'] }}</p>
        </div>

        @if($customMessage)
        <div class="custom-message">
            <p style="margin: 0;">{{ $customMessage }}</p>
        </div>
        @endif

        <div class="document-info">
            <p><strong>Document #:</strong> {{ $document['document_number'] }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($document['issue_date'])->format('d M Y') }}</p>
            @if($document['due_date'])
            <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($document['due_date'])->format('d M Y') }}</p>
            @endif
            <p><strong>To:</strong> {{ $document['client_info']['name'] }}</p>
        </div>

        <div class="amount">
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
        </div>

        <div class="cta">
            <a href="{{ $pdfUrl }}" class="btn">Download {{ $document['type_label'] }}</a>
        </div>

        @if($document['notes'])
        <div style="margin-top: 20px;">
            <strong>Notes:</strong>
            <p style="color: #6b7280;">{{ $document['notes'] }}</p>
        </div>
        @endif

        <div class="footer">
            <p>This {{ strtolower($document['type_label']) }} was sent via <a href="{{ config('app.url') }}">MyGrowNet</a></p>
            <p style="margin-top: 10px;">
                <a href="{{ config('app.url') }}/quick-invoice">Create your own invoices for free</a>
            </p>
        </div>
    </div>
</body>
</html>
