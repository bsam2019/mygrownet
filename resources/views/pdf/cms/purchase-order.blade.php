<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #333; line-height: 1.5; }
        .container { padding: 20px; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #d97706; padding-bottom: 20px; }
        .company-info { float: left; width: 50%; }
        .company-name { font-size: 20pt; font-weight: bold; color: #d97706; margin-bottom: 5px; }
        .company-details { font-size: 9pt; color: #666; }
        .po-info { float: right; width: 45%; text-align: right; }
        .po-title { font-size: 24pt; font-weight: bold; color: #d97706; margin-bottom: 10px; }
        .po-number { font-size: 12pt; color: #666; margin-bottom: 5px; }
        .clearfix::after { content: ""; display: table; clear: both; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 12pt; font-weight: bold; color: #d97706; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        .supplier-info { background: #f9fafb; padding: 15px; border-radius: 5px; }
        .info-row { margin-bottom: 5px; }
        .info-label { font-weight: bold; color: #666; display: inline-block; width: 100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #d97706; color: white; }
        th { padding: 10px; text-align: left; font-weight: bold; }
        th.right { text-align: right; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        td.right { text-align: right; }
        tbody tr:hover { background: #f9fafb; }
        .totals { margin-top: 20px; float: right; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .total-row.grand { font-size: 14pt; font-weight: bold; color: #d97706; border-top: 2px solid #d97706; border-bottom: 2px solid #d97706; margin-top: 10px; }
        .total-label { font-weight: bold; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 9pt; font-weight: bold; text-transform: uppercase; }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #fef3c7; color: #d97706; }
        .status-received { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .status-confirmed { background: #dbeafe; color: #2563eb; }
        .notes { margin-top: 30px; padding: 15px; background: #f9fafb; border-left: 4px solid #d97706; }
        .notes-title { font-weight: bold; margin-bottom: 5px; }
        .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9pt; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-details">
                    @if($company->address){{ $company->address }}<br>@endif
                    @if($company->phone)Phone: {{ $company->phone }}<br>@endif
                    @if($company->email)Email: {{ $company->email }}<br>@endif
                    @if($company->tax_number)Tax ID: {{ $company->tax_number }}@endif
                </div>
            </div>
            <div class="po-info">
                <div class="po-title">PURCHASE ORDER</div>
                <div class="po-number">#{{ $purchaseOrder->po_number }}</div>
                <div class="info-row"><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('M d, Y') }}</div>
                @if($purchaseOrder->expected_delivery_date)
                    <div class="info-row"><strong>Delivery Date:</strong> {{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}</div>
                @endif
                <div class="info-row">
                    <span class="status-badge status-{{ $purchaseOrder->status }}">
                        {{ ucfirst($purchaseOrder->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Supplier</div>
            <div class="supplier-info">
                <div style="font-weight: bold; font-size: 12pt; margin-bottom: 5px;">{{ $purchaseOrder->supplier_name }}</div>
                @if($purchaseOrder->supplier_address)<div>{{ $purchaseOrder->supplier_address }}</div>@endif
                @if($purchaseOrder->supplier_contact)<div>Contact: {{ $purchaseOrder->supplier_contact }}</div>@endif
                @if($purchaseOrder->job)<div style="margin-top: 8px;"><strong>Job:</strong> {{ $purchaseOrder->job->job_number ?? 'N/A' }} - {{ $purchaseOrder->job->description ?? '' }}</div>@endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">Items</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 45%;">Description</th>
                        <th style="width: 10%;">Unit</th>
                        <th class="right" style="width: 15%;">Quantity</th>
                        <th class="right" style="width: 15%;">Unit Price</th>
                        <th class="right" style="width: 15%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="right">{{ number_format($item->quantity, 2) }}</td>
                            <td class="right">K{{ number_format($item->unit_price, 2) }}</td>
                            <td class="right">K{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="clearfix">
            <div class="totals">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span>K{{ number_format($purchaseOrder->subtotal, 2) }}</span>
                </div>
                @if($purchaseOrder->tax_amount > 0)
                    <div class="total-row">
                        <span class="total-label">Tax:</span>
                        <span>K{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                @endif
                <div class="total-row grand">
                    <span class="total-label">Total:</span>
                    <span>K{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($purchaseOrder->notes)
            <div class="notes">
                <div class="notes-title">Notes:</div>
                <div>{{ $purchaseOrder->notes }}</div>
            </div>
        @endif

        @if($purchaseOrder->terms)
            <div class="notes">
                <div class="notes-title">Terms & Conditions:</div>
                <div>{{ $purchaseOrder->terms }}</div>
            </div>
        @endif

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated purchase order and does not require a signature.</p>
        </div>
    </div>
</body>
</html>