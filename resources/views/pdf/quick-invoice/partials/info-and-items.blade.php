@php
    $symbol = match($document['currency']) {
        'ZMW' => 'K', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ZAR' => 'R',
        default => $document['currency'],
    };
@endphp

<!-- Info Grid -->
<div class="info-grid">
    <div class="info-card">
        <div class="info-card-inner">
            <div class="info-label">Bill To</div>
            <div class="client-name">{{ $document['client_info']['name'] }}</div>
            <div style="color: #6b7280; font-size: 10px;">
                @if($document['client_info']['address']){{ $document['client_info']['address'] }}<br>@endif
                @if($document['client_info']['phone']){{ $document['client_info']['phone'] }}<br>@endif
                @if($document['client_info']['email']){{ $document['client_info']['email'] }}@endif
            </div>
        </div>
    </div>
    <div class="info-card right">
        <div class="info-card-inner">
            <div class="info-label">Document Details</div>
            <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($document['issue_date'])->format('d M Y') }}</div>
            @if($document['due_date'])
            <div><strong>Due:</strong> {{ \Carbon\Carbon::parse($document['due_date'])->format('d M Y') }}</div>
            @endif
            @if($document['business_info']['tax_number'])
            <div><strong>TPIN:</strong> {{ $document['business_info']['tax_number'] }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Items Table -->
<table class="items">
    <thead>
        <tr>
            <th class="qty">Qty</th>
            <th>Description</th>
            @if($document['type'] !== 'delivery_note')
            <th class="price">Price</th>
            <th class="amount">Amount</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($document['items'] as $item)
        <tr>
            <td class="qty">{{ number_format($item['quantity'], $item['quantity'] == floor($item['quantity']) ? 0 : 2) }}{{ $item['unit'] ? ' '.$item['unit'] : '' }}</td>
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
    @if($document['notes'])<br>@endif
    <div class="notes-title">Terms & Conditions</div>
    <div class="notes-content">{{ $document['terms'] }}</div>
    @endif
</div>
@endif

<!-- Signature -->
@if($document['type'] === 'delivery_note' || !empty($document['signature']))
<div class="signature-section">
    @if(!empty($document['signature']))
    <div class="signature-box">
        <img src="{{ $document['signature'] }}" class="signature-image" alt="Signature">
        <div class="signature-line">Authorized Signature</div>
    </div>
    @endif
    @if($document['type'] === 'delivery_note')
    <div class="signature-box"><div class="signature-line">Prepared by</div></div>
    <div class="signature-box"><div class="signature-line">Delivered by</div></div>
    <div class="signature-box"><div class="signature-line">Received by</div></div>
    @endif
</div>
@endif

<div class="footer">Generated with MyGrowNet Quick Invoice | mygrownet.com/quick-invoice</div>
