<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst($document->type()->value()) }} {{ $document->number()->value() }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
        }
        .container {
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .header-right {
            text-align: right;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 24pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .document-number {
            font-size: 14pt;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-col {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            padding-right: 20px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #2563eb;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        .totals-table td {
            border: none;
            padding: 5px 10px;
        }
        .totals-table .total-row {
            font-size: 14pt;
            font-weight: bold;
            border-top: 2px solid #2563eb;
            padding-top: 10px;
        }
        .notes {
            background-color: #f9fafb;
            padding: 15px;
            border-left: 3px solid #2563eb;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    @if(isset($logoPath) && $logoPath)
                        <div style="margin-bottom: 10px;">
                            <img src="{{ $logoPath }}" alt="Logo" style="max-width: 150px; max-height: 60px;">
                        </div>
                    @endif
                    <div class="company-name">{{ $businessProfile->businessName() }}</div>
                    <div>{{ $businessProfile->address() }}</div>
                    <div>Phone: {{ $businessProfile->phone() }}</div>
                    @if($businessProfile->email())
                        <div>Email: {{ $businessProfile->email() }}</div>
                    @endif
                    @if($businessProfile->tpin())
                        <div>TPIN: {{ $businessProfile->tpin() }}</div>
                    @endif
                </div>
                <div class="header-right">
                    <div class="document-title">{{ strtoupper($document->type()->value()) }}</div>
                    <div class="document-number">{{ $document->number()->value() }}</div>
                </div>
            </div>
        </div>

        <!-- Document Info -->
        <div class="section">
            <div class="info-grid">
                <div class="info-col">
                    <div class="section-title">Bill To</div>
                    <div style="font-weight: bold; margin-bottom: 5px;">{{ $customer->name() }}</div>
                    @if($customer->address())
                        <div>{{ $customer->address() }}</div>
                    @endif
                    @if($customer->phone())
                        <div>Phone: {{ $customer->phone() }}</div>
                    @endif
                    @if($customer->email())
                        <div>Email: {{ $customer->email() }}</div>
                    @endif
                    @if($customer->tpin())
                        <div>TPIN: {{ $customer->tpin() }}</div>
                    @endif
                </div>
                <div class="info-col">
                    <div class="info-row">
                        <span class="info-label">Issue Date:</span>
                        {{ $document->issueDate()->format('d M Y') }}
                    </div>
                    @if($document->dueDate())
                        <div class="info-row">
                            <span class="info-label">Due Date:</span>
                            {{ $document->dueDate()->format('d M Y') }}
                        </div>
                    @endif
                    @if($document->validityDate())
                        <div class="info-row">
                            <span class="info-label">Valid Until:</span>
                            {{ $document->validityDate()->format('d M Y') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($document->items() as $item)
                    <tr>
                        <td>{{ $item->description() }}</td>
                        <td class="text-right">{{ number_format($item->quantity(), 2) }}</td>
                        <td class="text-right">{{ $document->currency() }} {{ number_format($item->unitPrice()->amount() / 100, 2) }}</td>
                        <td class="text-right">{{ $item->taxRate() }}%</td>
                        <td class="text-right">{{ $document->currency() }} {{ number_format($item->calculateLineTotal()->amount() / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ $document->currency() }} {{ number_format($totals['subtotal']->amount() / 100, 2) }}</td>
            </tr>
            @if($totals['discount_total']->amount() > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-{{ $document->currency() }} {{ number_format($totals['discount_total']->amount() / 100, 2) }}</td>
                </tr>
            @endif
            @if(($document->collectTax() ?? true) && $totals['tax_total']->amount() > 0)
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">{{ $document->currency() }} {{ number_format($totals['tax_total']->amount() / 100, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">{{ $document->currency() }} {{ number_format($totals['grand_total']->amount() / 100, 2) }}</td>
            </tr>
        </table>

        <!-- Notes -->
        @if($document->notes())
            <div class="notes">
                <div style="font-weight: bold; margin-bottom: 5px;">Notes:</div>
                <div>{{ $document->notes() }}</div>
            </div>
        @endif

        <!-- Terms -->
        @if($document->terms())
            <div class="notes">
                <div style="font-weight: bold; margin-bottom: 5px;">Terms & Conditions:</div>
                <div>{{ $document->terms() }}</div>
            </div>
        @endif

        <!-- Payment Instructions -->
        @if($document->paymentInstructions())
            <div class="notes">
                <div style="font-weight: bold; margin-bottom: 5px;">Payment Instructions:</div>
                <div>{{ $document->paymentInstructions() }}</div>
            </div>
        @endif

        <!-- Bank Details -->
        @if($businessProfile->bankName())
            <div class="notes">
                <div style="font-weight: bold; margin-bottom: 5px;">Bank Details:</div>
                <div>Bank: {{ $businessProfile->bankName() }}</div>
                <div>Account: {{ $businessProfile->bankAccount() }}</div>
                <div>Branch: {{ $businessProfile->bankBranch() }}</div>
            </div>
        @endif

        <!-- Signature -->
        @if(isset($signaturePath) && $signaturePath)
            <div style="margin-top: 40px; text-align: right;">
                <div style="margin-bottom: 10px;">
                    <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 150px; max-height: 50px;">
                </div>
                <div style="border-top: 1px solid #333; display: inline-block; padding-top: 5px; min-width: 200px;">
                    Authorized Signature
                </div>
                @if($businessProfile->preparedBy())
                    <div style="margin-top: 10px; font-size: 9pt; color: #666;">
                        Prepared by: {{ $businessProfile->preparedBy() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            @if($businessProfile->website())
                <div>{{ $businessProfile->website() }}</div>
            @endif
        </div>
    </div>
</body>
</html>
