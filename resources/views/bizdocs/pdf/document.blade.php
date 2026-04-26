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
            width: 100%;
        }
        .header-left, .header-right {
            float: left;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            text-align: right;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
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
            width: 100%;
        }
        .info-col {
            float: left;
            width: 50%;
            padding-right: 20px;
            vertical-align: top;
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
            <div class="header-content clearfix">
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
            <div class="info-grid clearfix">
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
                @php
                    $items = $document->items();
                    // Log for debugging
                    if(function_exists('\Log::info')) {
                        \Log::info('PDF Items Debug', [
                            'document_id' => $document->id(),
                            'items_count' => count($items),
                            'items_data' => array_map(function($item) {
                                return [
                                    'description' => $item->description(),
                                    'quantity' => $item->quantity(),
                                    'unit_price' => $item->unitPrice()->amount()
                                ];
                            }, $items)
                        ]);
                    }
                @endphp
                @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $item->description() }}</td>
                        <td class="text-right">{{ number_format($item->quantity(), 2) }}</td>
                        <td class="text-right">{{ $document->currency() }} {{ number_format($item->unitPrice()->amount() / 100, 2) }}</td>
                        <td class="text-right">{{ $item->taxRate() }}%</td>
                        <td class="text-right">{{ $document->currency() }} {{ number_format($item->calculateLineTotal()->amount() / 100, 2) }}</td>
                    </tr>
                @endforeach
                @if(empty($items))
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                            No items found for this document
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals -->
        <table style="width:300px;border-collapse:collapse;margin-left:auto;margin-top:20px;float:right;">
            <tr>
                <td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;white-space:nowrap;">Subtotal:</td>
                <td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:right;white-space:nowrap;">{{ $document->currency() }} {{ number_format($totals['subtotal']->amount() / 100, 2) }}</td>
            </tr>
            @if($totals['discount_total']->amount() > 0)
            <tr>
                <td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;white-space:nowrap;">Discount:</td>
                <td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:right;white-space:nowrap;color:#dc2626;">-{{ $document->currency() }} {{ number_format($totals['discount_total']->amount() / 100, 2) }}</td>
            </tr>
            @endif
            @if($totals['tax_total']->amount() > 0)
            <tr>
                <td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;white-space:nowrap;">Tax:</td>
                <td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:right;white-space:nowrap;">{{ $document->currency() }} {{ number_format($totals['tax_total']->amount() / 100, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding:10px;font-size:14pt;font-weight:bold;border-top:2px solid #2563eb;white-space:nowrap;">Total:</td>
                <td style="padding:10px;font-size:14pt;font-weight:bold;border-top:2px solid #2563eb;text-align:right;white-space:nowrap;">{{ $document->currency() }} {{ number_format($totals['grand_total']->amount() / 100, 2) }}</td>
            </tr>
        </table>
        <div style="clear:both;"></div>

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
