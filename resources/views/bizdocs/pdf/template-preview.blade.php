<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: {{ $template->template_structure['global_styles']['font_family'] ?? 'sans-serif' }};
            color: #1f2937;
            background: white;
            padding: 40px;
            @if(($template->template_structure['global_styles']['font_size'] ?? 'medium') === 'small')
            font-size: 11px;
            @elseif(($template->template_structure['global_styles']['font_size'] ?? 'medium') === 'large')
            font-size: 15px;
            @else
            font-size: 13px;
            @endif
        }
        
        .document {
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            @if(($template->template_structure['header']['business_info_layout'] ?? 'stacked') === 'inline')
            display: flex;
            justify-content: space-between;
            align-items: center;
            @else
            display: block;
            @endif
            margin-bottom: 40px;
            padding: 20px;
            background: {{ $template->template_structure['header']['background_color'] ?? 'transparent' }};
            @if($template->template_structure['header']['background_color'] ?? false)
            border-radius: 8px;
            @endif
        }
        
        .logo-section {
            @if(($template->template_structure['header']['logo_position'] ?? 'left') === 'center')
            text-align: center;
            @elseif(($template->template_structure['header']['logo_position'] ?? 'left') === 'right')
            text-align: right;
            @else
            text-align: left;
            @endif
            margin-bottom: 15px;
        }
        
        .logo-placeholder {
            display: inline-block;
            @if(($template->template_structure['header']['logo_size'] ?? 'medium') === 'small')
            width: 60px;
            height: 60px;
            font-size: 30px;
            @elseif(($template->template_structure['header']['logo_size'] ?? 'medium') === 'large')
            width: 120px;
            height: 120px;
            font-size: 60px;
            @else
            width: 80px;
            height: 80px;
            font-size: 40px;
            @endif
            background: {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .business-info {
            @if(($template->template_structure['header']['business_info_layout'] ?? 'stacked') === 'inline')
            flex: 1;
            margin-left: 20px;
            @else
            margin-top: 15px;
            @endif
        }
        
        .business-name {
            font-size: 24px;
            font-weight: bold;
            color: {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            margin-bottom: 8px;
        }
        
        .business-details {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        /* Document Title */
        .document-title {
            text-align: {{ $template->template_structure['document_title']['alignment'] ?? 'right' }};
            margin-bottom: 30px;
            padding: 15px;
            @if(($template->template_structure['document_title']['alignment'] ?? 'right') === 'center')
            background: linear-gradient(to right, transparent, {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }}15, transparent);
            @endif
        }
        
        .document-title h1 {
            @if(($template->template_structure['document_title']['font_size'] ?? '2xl') === '3xl')
            font-size: 36px;
            @elseif(($template->template_structure['document_title']['font_size'] ?? '2xl') === 'xl')
            font-size: 24px;
            @else
            font-size: 30px;
            @endif
            font-weight: {{ $template->template_structure['document_title']['font_weight'] ?? 'bold' }};
            color: {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            margin-bottom: 8px;
            letter-spacing: 2px;
        }
        
        .document-number {
            font-size: 14px;
            color: #6b7280;
        }
        
        /* Customer Block */
        .customer-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f9fafb;
            border-left: 4px solid {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            @if(($template->template_structure['customer_block']['position'] ?? 'left') === 'right')
            margin-left: auto;
            max-width: 400px;
            @endif
        }
        
        .customer-label {
            font-size: 11px;
            font-weight: bold;
            color: {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .customer-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .customer-details {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
            @if(!($template->template_structure['items_table']['show_borders'] ?? true))
            border: none;
            @endif
        }
        
        .items-table th {
            background: {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            color: white;
            padding: 12px;
            text-align: left;
            @if(($template->template_structure['items_table']['font_size'] ?? 'base') === 'sm')
            font-size: 11px;
            @elseif(($template->template_structure['items_table']['font_size'] ?? 'base') === 'lg')
            font-size: 14px;
            @else
            font-size: 12px;
            @endif
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table td {
            padding: 12px;
            @if($template->template_structure['items_table']['show_borders'] ?? true)
            border: 1px solid #e5e7eb;
            @else
            border-bottom: 1px solid #e5e7eb;
            @endif
            @if(($template->template_structure['items_table']['font_size'] ?? 'base') === 'sm')
            font-size: 11px;
            @elseif(($template->template_structure['items_table']['font_size'] ?? 'base') === 'lg')
            font-size: 14px;
            @else
            font-size: 13px;
            @endif
        }
        
        @if($template->template_structure['items_table']['row_striping'] ?? false)
        .items-table tbody tr:nth-child(even) {
            background: {{ $template->template_structure['items_table']['row_striping_color'] ?? '#f9fafb' }};
        }
        @endif
        
        .text-right {
            text-align: right;
        }
        
        /* Totals */
        .totals-section {
            @if(($template->template_structure['totals_block']['alignment'] ?? 'right') === 'left')
            margin-right: auto;
            @else
            margin-left: auto;
            @endif
            width: 350px;
            margin-bottom: 30px;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            font-size: 13px;
        }
        
        .totals-row.subtotal,
        .totals-row.tax,
        .totals-row.discount {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .totals-row.grand-total {
            background: {{ $template->template_structure['totals_block']['background_color'] ?? '#f3f4f6' }};
            font-weight: bold;
            font-size: 18px;
            color: {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            border-radius: 6px;
            margin-top: 10px;
            padding: 15px;
        }
        
        /* Footer */
        .footer-section {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
        }
        
        .notes-section, .terms-section {
            margin-bottom: 25px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: {{ $template->template_structure['global_styles']['primary_color'] ?? '#2563eb' }};
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .section-content {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.8;
        }
        
        .signature-section {
            display: flex;
            @if(($template->template_structure['footer']['signature_position'] ?? 'left') === 'center')
            justify-content: center;
            gap: 80px;
            @else
            justify-content: space-between;
            @endif
            margin-top: 50px;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            padding-top: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Page size specific */
        @if(($template->template_structure['global_styles']['page_size'] ?? 'A4') === 'half-page')
        .document {
            max-width: 400px;
        }
        .header {
            padding: 15px;
        }
        .document-title h1 {
            font-size: 24px !important;
        }
        @elseif(($template->template_structure['global_styles']['page_size'] ?? 'A4') === 'A5')
        .document {
            max-width: 600px;
        }
        @endif
    </style>
</head>
<body>
    <div class="document">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo-placeholder">🏢</div>
            </div>
            <div class="business-info">
                <div class="business-name">{{ $businessProfile->businessName() }}</div>
                <div class="business-details">
                    {{ $businessProfile->address() }}<br>
                    Phone: {{ $businessProfile->phone() }}<br>
                    @if($businessProfile->email())
                    Email: {{ $businessProfile->email() }}<br>
                    @endif
                    @if($businessProfile->tpin())
                    TPIN: {{ $businessProfile->tpin() }}
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Document Title -->
        <div class="document-title">
            <h1>{{ $template->template_structure['document_title']['text'] ?? 'INVOICE' }}</h1>
            <div class="document-number">{{ $document->number }}</div>
            <div class="document-number">Date: {{ $document->issueDate->format('d M Y') }}</div>
            @if($document->dueDate)
            <div class="document-number">Due: {{ $document->dueDate->format('d M Y') }}</div>
            @endif
        </div>
        
        <!-- Customer Section -->
        <div class="customer-section">
            <div class="customer-label">{{ $template->template_structure['customer_block']['label_text'] ?? 'Bill To' }}</div>
            <div class="customer-name">{{ $customer->name }}</div>
            <div class="customer-details">
                @if(in_array('address', $template->template_structure['customer_block']['fields'] ?? []) && $customer->address)
                {{ $customer->address }}<br>
                @endif
                @if(in_array('phone', $template->template_structure['customer_block']['fields'] ?? []) && $customer->phone)
                Phone: {{ $customer->phone }}<br>
                @endif
                @if(in_array('email', $template->template_structure['customer_block']['fields'] ?? []) && $customer->email)
                Email: {{ $customer->email }}<br>
                @endif
                @if(in_array('tpin', $template->template_structure['customer_block']['fields'] ?? []) && $customer->tpin)
                TPIN: {{ $customer->tpin }}
                @endif
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    @if(in_array('description', $template->template_structure['items_table']['columns'] ?? []))
                    <th>Description</th>
                    @endif
                    @if(in_array('quantity', $template->template_structure['items_table']['columns'] ?? []))
                    <th class="text-right">Qty</th>
                    @endif
                    @if(in_array('unit_price', $template->template_structure['items_table']['columns'] ?? []))
                    <th class="text-right">Unit Price</th>
                    @endif
                    @if(in_array('tax', $template->template_structure['items_table']['columns'] ?? []))
                    <th class="text-right">Tax</th>
                    @endif
                    @if(in_array('discount', $template->template_structure['items_table']['columns'] ?? []))
                    <th class="text-right">Discount</th>
                    @endif
                    @if(in_array('total', $template->template_structure['items_table']['columns'] ?? []))
                    <th class="text-right">Total</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    @if(in_array('description', $template->template_structure['items_table']['columns'] ?? []))
                    <td>{{ $item->description }}</td>
                    @endif
                    @if(in_array('quantity', $template->template_structure['items_table']['columns'] ?? []))
                    <td class="text-right">{{ $item->quantity }}</td>
                    @endif
                    @if(in_array('unit_price', $template->template_structure['items_table']['columns'] ?? []))
                    <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($item->unitPrice / 100, 2) }}</td>
                    @endif
                    @if(in_array('tax', $template->template_structure['items_table']['columns'] ?? []))
                    <td class="text-right">{{ $item->taxRate }}%</td>
                    @endif
                    @if(in_array('discount', $template->template_structure['items_table']['columns'] ?? []))
                    <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format($item->discountAmount / 100, 2) }}</td>
                    @endif
                    @if(in_array('total', $template->template_structure['items_table']['columns'] ?? []))
                    <td class="text-right">{{ $businessProfile->defaultCurrency() }} {{ number_format(($item->quantity * $item->unitPrice - $item->discountAmount) / 100, 2) }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-row subtotal">
                <span>Subtotal:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['subtotal'] / 100, 2) }}</span>
            </div>
            <div class="totals-row tax">
                <span>Tax:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['taxTotal'] / 100, 2) }}</span>
            </div>
            @if(($template->template_structure['totals_block']['show_discount'] ?? true) && $totals['discountTotal'] > 0)
            <div class="totals-row discount">
                <span>Discount:</span>
                <span>-{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['discountTotal'] / 100, 2) }}</span>
            </div>
            @endif
            <div class="totals-row grand-total">
                <span>Grand Total:</span>
                <span>{{ $businessProfile->defaultCurrency() }} {{ number_format($totals['grandTotal'] / 100, 2) }}</span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-section">
            @if($template->template_structure['footer']['show_notes'] ?? true)
            <div class="notes-section">
                <div class="section-title">Notes</div>
                <div class="section-content">
                    Thank you for your business. We appreciate your trust in our services.
                </div>
            </div>
            @endif
            
            @if($template->template_structure['footer']['show_terms'] ?? true)
            <div class="terms-section">
                <div class="section-title">Terms & Conditions</div>
                <div class="section-content">
                    Payment is due within 30 days. Late payments may incur additional charges.
                </div>
            </div>
            @endif
            
            @if($template->template_structure['footer']['show_signature'] ?? true)
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">Authorized Signature</div>
                </div>
                @if($template->template_structure['footer']['show_stamp'] ?? true)
                <div class="signature-box">
                    <div class="signature-line">Company Stamp</div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</body>
</html>
