<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #333; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { color: #1e40af; font-size: 18pt; margin: 0; }
        .header p { color: #6b7280; font-size: 9pt; margin: 5px 0 0; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 12pt; color: #1e40af; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 10px; }
        table.details { width: 100%; border-collapse: collapse; }
        table.details td { padding: 6px 10px; font-size: 10pt; }
        table.details td:first-child { font-weight: bold; color: #6b7280; width: 200px; }
        .terms { font-size: 10pt; white-space: pre-wrap; }
        .signatures { margin-top: 30px; }
        .signature-block { display: inline-block; width: 45%; margin: 0 2%; text-align: center; }
        .signature-block .line { border-top: 1px solid #333; margin-top: 50px; padding-top: 8px; }
        .signature-block img { max-height: 50px; margin-bottom: 5px; }
        .signature-block .name { font-weight: bold; font-size: 10pt; }
        .signature-block .date { font-size: 8pt; color: #6b7280; }
        .footer { text-align: center; font-size: 8pt; color: #9ca3af; margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .badge { display: inline-block; background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 10px; font-size: 8pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $contract->title }}</h1>
        <p>Contract Number: <strong>{{ $contract->contract_number }}</strong></p>
        <p><span class="badge">SIGNED</span> &middot; Signed on {{ $contract->signed_at ? $contract->signed_at->format('F j, Y') : 'N/A' }}</p>
    </div>

    <div class="section">
        <h2>Contract Details</h2>
        <table class="details">
            <tr><td>Customer</td><td>{{ $contract->customer?->name ?? 'N/A' }}</td></tr>
            <tr><td>Customer Email</td><td>{{ $contract->customer?->email ?? 'N/A' }}</td></tr>
            <tr><td>Template</td><td>{{ $contract->template?->name ?? 'N/A' }}</td></tr>
            <tr><td>Start Date</td><td>{{ $contract->start_date ? $contract->start_date->format('F j, Y') : 'N/A' }}</td></tr>
            <tr><td>End Date</td><td>{{ $contract->end_date ? $contract->end_date->format('F j, Y') : 'N/A' }}</td></tr>
            <tr><td>Total Value</td><td>{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</td></tr>
        </table>
    </div>

    @if ($contract->description)
    <div class="section">
        <h2>Description</h2>
        <p>{{ $contract->description }}</p>
    </div>
    @endif

    @if ($contract->terms)
    <div class="section">
        <h2>Terms &amp; Conditions</h2>
        <div class="terms">{{ $contract->terms }}</div>
    </div>
    @endif

    @if ($contract->notes)
    <div class="section">
        <h2>Notes</h2>
        <p>{{ $contract->notes }}</p>
    </div>
    @endif

    <div class="section signatures">
        <h2>Signatures</h2>
        <div style="text-align:center;">
            <div class="signature-block">
                @php $csig = $customerSignatures->first(); @endphp
                @if ($csig && $csig->signature_data)
                    <img src="{{ $csig->signature_data }}" alt="Customer Signature" />
                @endif
                <div class="line">
                    <div class="name">{{ $csig->signer_name ?? ($contract->customer?->name ?? 'Customer') }}</div>
                    <div class="date">{{ $csig ? $csig->signed_at->format('F j, Y \\a\\t H:i') : '' }}</div>
                    <div style="font-size:7pt;color:#9ca3af;">Customer Signature</div>
                </div>
            </div>
            <div class="signature-block">
                @php $cosig = $companySignatures->first(); @endphp
                @if ($cosig && $cosig->signature_data)
                    <img src="{{ $cosig->signature_data }}" alt="Company Signature" />
                @endif
                <div class="line">
                    <div class="name">{{ $cosig->signer_name ?? 'Company Representative' }}</div>
                    <div class="date">{{ $cosig ? $cosig->signed_at->format('F j, Y \\a\\t H:i') : '' }}</div>
                    <div style="font-size:7pt;color:#9ca3af;">Company Signature</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        This document was generated electronically. Signed copies are stored securely.
        <br />Contract #{{ $contract->contract_number }} &middot; {{ date('Y') }}
    </div>
</body>
</html>
