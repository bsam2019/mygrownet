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

<!-- Header -->
<div class="header">
    <div class="logo-section">
        @if($document['business_info']['logo'])
            <img src="{{ $document['business_info']['logo'] }}" class="logo" alt="Logo">
        @endif
    </div>
    <div class="company-section">
        <div class="company-name">{{ $document['business_info']['name'] }}</div>
        <div class="company-details">
            @if($document['business_info']['address']){{ $document['business_info']['address'] }}<br>@endif
            @if($document['business_info']['phone'])Tel: {{ $document['business_info']['phone'] }}<br>@endif
            @if($document['business_info']['email']){{ $document['business_info']['email'] }}<br>@endif
            @if($document['business_info']['website']){{ $document['business_info']['website'] }}@endif
        </div>
    </div>
</div>

<!-- Document Title -->
<div class="document-title">
    <h1>{{ $document['type_label'] }}</h1>
    <div class="document-number">#{{ $document['document_number'] }}</div>
</div>

<!-- Info Section -->
<div class="info-section">
    <div class="info-box">
        <div class="info-label">Bill To / Deliver To</div>
        <div class="client-name">{{ $document['client_info']['name'] }}</div>
        <div style="color: #6b7280; font-size: 10px;">
            @if($document['client_info']['address']){{ $document['client_info']['address'] }}<br>@endif
            @if($document['client_info']['phone']){{ $document['client_info']['phone'] }}<br>@endif
            @if($document['client_info']['email']){{ $document['client_info']['email'] }}@endif
        </div>
    </div>
    <div class="info-box right">
        <div class="info-label">Date</div>
        <div>{{ \Carbon\Carbon::parse($document['issue_date'])->format('d/m/Y') }}</div>
        @if($document['due_date'])
            <br><div class="info-label">Due Date</div>
            <div>{{ \Carbon\Carbon::parse($document['due_date'])->format('d/m/Y') }}</div>
        @endif
        @if($document['business_info']['tax_number'])
            <br><div class="info-label">Tax Number</div>
            <div>{{ $document['business_info']['tax_number'] }}</div>
        @endif
    </div>
</div>

<!-- Items Table -->
<table class="items">
    <thead>
        <tr>
            <th class="qty">Qty</th>
            <th>Description</th>
            @if($document['type'] !== 'delivery_note')
            <th class="price">Unit Price</th>
            <th class="amount">Amount</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($document['items'] as $item)
        <tr>
            <td class="qty">{{ number_format($item['quantity'], $item['quantity'] == floor($item['quantity']) ? 0 : 2) }}{{ $item['unit'] ? ' ' . $item['unit'] : '' }}</td>
            <td>{{ $item['description'] }}</td>
            @if($document['type'] !== 'delivery_note')
            <td class="price">{{ $symbol }} {{ number_format($item['unit_price'], 2) }}</td>
            <td class="amount">{{ $symbol }} {{ number_format($item['amount'], 2) }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Totals -->
@if($document['type'] !== 'delivery_note')
<div class="totals">
    <div class="totals-row">
        <div class="totals-label">Subtotal</div>
        <div class="totals-value">{{ $symbol }} {{ number_format($document['subtotal'], 2) }}</div>
    </div>
    @if($document['discount_rate'] > 0)
    <div class="totals-row">
        <div class="totals-label">Discount ({{ $document['discount_rate'] }}%)</div>
        <div class="totals-value">- {{ $symbol }} {{ number_format($document['discount_amount'], 2) }}</div>
    </div>
    @endif
    @if($document['tax_rate'] > 0)
    <div class="totals-row">
        <div class="totals-label">Tax ({{ $document['tax_rate'] }}%)</div>
        <div class="totals-value">{{ $symbol }} {{ number_format($document['tax_amount'], 2) }}</div>
    </div>
    @endif
    <div class="totals-row total">
        <div class="totals-label">Total</div>
        <div class="totals-value">{{ $symbol }} {{ number_format($document['total'], 2) }}</div>
    </div>
</div>
@endif

<!-- Notes -->
@if($document['notes'] || $document['terms'])
<div class="notes-section">
    @if($document['notes'])
    <div class="notes-title">Notes</div>
    <div class="notes-content">{{ $document['notes'] }}</div>
    @endif
    @if($document['terms'])
    <br><div class="notes-title">Terms & Conditions</div>
    <div class="notes-content">{{ $document['terms'] }}</div>
    @endif
</div>
@endif

<!-- Signature Section -->
@if($document['type'] === 'delivery_note' || $document['signature'])
<div class="signature-section">
    @if($document['signature'])
    <div class="signature-box">
        <img src="{{ $document['signature'] }}" class="signature-image" alt="Signature">
        <div class="signature-line">Authorized Signature</div>
    </div>
    @endif
    @if($document['type'] === 'delivery_note')
    <div class="signature-box">
        <div class="signature-line">Prepared by</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">Delivered by</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">Received by</div>
    </div>
    @endif
</div>
@endif

<!-- Footer -->
<div class="footer">
    Generated with MyGrowNet Quick Invoice | mygrownet.com/quick-invoice
</div>
