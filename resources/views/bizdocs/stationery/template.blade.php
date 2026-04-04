<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stationery - {{ strtoupper($documentType) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            overflow-x: hidden;
            min-height: 100%;
        }
        
        html {
            height: auto;
            min-height: 100%;
        }
        
        @page {
            margin: 0;
            padding: 0;
            @if($pageSize === 'A5')
                size: 148mm 210mm;
            @else
                size: 210mm 297mm; /* A4 */
            @endif
        }
        
        @php
            // Page dimensions based on size
            $pageWidth = $pageSize === 'A5' ? '148mm' : '210mm';
            $pageHeight = $pageSize === 'A5' ? '210mm' : '297mm';
            $halfHeight = $pageSize === 'A5' ? '105mm' : '148.5mm';
        @endphp
        
        /* 1 document per page */
        .layout-1 .document-wrapper {
            width: 100%;
            height: {{ $pageHeight }};
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
        }
        
        .layout-1 .document-wrapper:not(:last-child) {
            page-break-after: always;
        }
        
        /* 2 documents per page (half-page) */
        .layout-2 .document-wrapper {
            width: 100%;
            height: {{ $halfHeight }};
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
        }
        
        .layout-2 .document-wrapper:not(:last-child) {
            border-bottom: 1px dashed #ccc;
        }
        
        .layout-2 .document-wrapper:nth-child(2n):not(:last-child) {
            page-break-after: always;
            border-bottom: none;
        }
        
        /* 4 documents per page (quarter-page) */
        .layout-4 .document-wrapper {
            width: 50%;
            height: {{ $halfHeight }};
            float: left;
            page-break-inside: avoid;
            position: relative;
            border-right: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            overflow: hidden;
        }
        
        .layout-4 .document-wrapper:nth-child(2n) {
            border-right: none;
        }
        
        .layout-4 .document-wrapper:nth-child(4n):not(:last-child) {
            page-break-after: always;
            border-bottom: none;
        }
        
        .layout-4 .document-wrapper:nth-child(4n-1) {
            border-bottom: none;
        }
        
        /* 6 documents per page (3x2 grid) */
        .layout-6 .document-wrapper {
            width: 33.33%;
            height: {{ $halfHeight }};
            float: left;
            page-break-inside: avoid;
            position: relative;
            border-right: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            overflow: hidden;
        }
        
        .layout-6 .document-wrapper:nth-child(3n) {
            border-right: none;
        }
        
        .layout-6 .document-wrapper:nth-child(6n):not(:last-child) {
            page-break-after: always;
            border-bottom: none;
        }
        
        .layout-6 .document-wrapper:nth-child(6n-2),
        .layout-6 .document-wrapper:nth-child(6n-1) {
            border-bottom: none;
        }
        
        /* 8 documents per page (4x2 grid) */
        .layout-8 .document-wrapper {
            width: 25%;
            height: {{ $halfHeight }};
            float: left;
            page-break-inside: avoid;
            position: relative;
            border-right: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            overflow: hidden;
        }
        
        .layout-8 .document-wrapper:nth-child(4n) {
            border-right: none;
        }
        
        .layout-8 .document-wrapper:nth-child(8n):not(:last-child) {
            page-break-after: always;
            border-bottom: none;
        }
        
        .layout-8 .document-wrapper:nth-child(8n-3),
        .layout-8 .document-wrapper:nth-child(8n-2),
        .layout-8 .document-wrapper:nth-child(8n-1) {
            border-bottom: none;
        }
        
        /* 10 documents per page (5x2 grid) */
        .layout-10 .document-wrapper {
            width: 20%;
            height: {{ $halfHeight }};
            float: left;
            page-break-inside: avoid;
            position: relative;
            border-right: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            overflow: hidden;
        }
        
        .layout-10 .document-wrapper:nth-child(5n) {
            border-right: none;
        }
        
        .layout-10 .document-wrapper:nth-child(10n):not(:last-child) {
            page-break-after: always;
            border-bottom: none;
        }
        
        .layout-10 .document-wrapper:nth-child(10n-4),
        .layout-10 .document-wrapper:nth-child(10n-3),
        .layout-10 .document-wrapper:nth-child(10n-2),
        .layout-10 .document-wrapper:nth-child(10n-1) {
            border-bottom: none;
        }
        
        /* Document content */
        .document-content {
            padding: 15mm;
            position: relative;
            padding-bottom: 15mm; /* Reduced from 20mm */
            overflow: hidden; /* Prevent overflow */
        }
        
        .layout-2 .document-content {
            padding: 8mm;
            padding-bottom: 10mm; /* Reduced from 12mm */
        }
        
        .layout-4 .document-content {
            padding: 6mm;
            padding-bottom: 10mm;
            font-size: 10px;
        }
        
        .layout-6 .document-content {
            padding: 5mm;
            padding-bottom: 8mm;
            font-size: 8px;
        }
        
        .layout-8 .document-content {
            padding: 4mm;
            padding-bottom: 6mm;
            font-size: 7px;
        }
        
        .layout-10 .document-content {
            padding: 3mm;
            padding-bottom: 5mm;
            font-size: 6px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
        }
        
        .layout-4 .header {
            margin-bottom: 8px;
            padding-bottom: 5px;
        }
        
        .layout-6 .header,
        .layout-8 .header,
        .layout-10 .header {
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #2563eb;
        }
        
        .business-name {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }
        
        .layout-4 .business-name {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .layout-6 .business-name {
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .layout-8 .business-name,
        .layout-10 .business-name {
            font-size: 8px;
            margin-bottom: 2px;
        }
        
        .business-details {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.4;
        }
        
        .layout-4 .business-details {
            font-size: 8px;
        }
        
        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            text-align: center;
            margin: 10px 0;
            letter-spacing: 2px;
        }
        
        .layout-4 .doc-title {
            font-size: 14px;
            margin: 8px 0;
            letter-spacing: 1px;
        }
        
        .doc-number {
            font-size: 12px;
            font-weight: bold;
            color: #dc2626;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .layout-4 .doc-number {
            font-size: 10px;
            margin-bottom: 8px;
        }
        
        /* Customer section */
        .customer-section {
            margin-bottom: 10px;
            padding: 8px;
            background: #f9fafb;
            border-left: 3px solid #2563eb;
        }
        
        .layout-4 .customer-section {
            margin-bottom: 8px;
            padding: 5px;
        }
        
        .section-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .layout-4 .section-label {
            font-size: 8px;
            margin-bottom: 3px;
        }
        
        .blank-line {
            border-bottom: 1px solid #d1d5db;
            height: 20px;
            margin-bottom: 8px;
        }
        
        .layout-4 .blank-line {
            height: 15px;
            margin-bottom: 5px;
        }
        
        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .items-table,
        .items-table th,
        .items-table td {
            border: 1px solid #2563eb;
        }
        
        .layout-4 .items-table {
            margin: 8px 0;
        }
        
        .items-table th {
            background: #2563eb;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        
        .layout-4 .items-table th {
            padding: 4px 3px;
            font-size: 8px;
        }
        
        .items-table td {
            padding: 6px 4px;
        }
        
        .layout-4 .items-table td {
            padding: 4px 3px;
        }
        
        /* Totals */
        .totals-section {
            margin-top: 10px;
            float: right;
            width: 250px;
        }
        
        .layout-4 .totals-section {
            margin-top: 8px;
            width: 100%;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px;
            font-size: 11px;
            border: none;
        }
        
        .layout-4 .total-row {
            padding: 4px;
            font-size: 9px;
        }
        
        .grand-total {
            background: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 14px;
            margin-top: 2px;
        }
        
        .layout-4 .grand-total {
            font-size: 10px;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 15px;
            clear: both;
            width: 100%;
        }
        
        .layout-4 .signature-section {
            margin-top: 10px;
        }
        
        .layout-6 .signature-section,
        .layout-8 .signature-section,
        .layout-10 .signature-section {
            margin-top: 8px;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        
        .layout-4 .signature-table {
            font-size: 8px;
        }
        
        .layout-6 .signature-table,
        .layout-8 .signature-table,
        .layout-10 .signature-table {
            font-size: 6px;
        }
        
        .signature-table td {
            padding: 0 5px;
            border: none;
        }
        
        .signature-label {
            font-weight: bold;
            color: #374151;
            white-space: nowrap;
            padding-right: 5px;
        }
        
        .signature-line {
            border-bottom: 1px solid #374151;
            width: 100%;
            height: 20px;
        }
        
        .layout-4 .signature-line {
            height: 15px;
        }
        
        .layout-6 .signature-line,
        .layout-8 .signature-line,
        .layout-10 .signature-line {
            height: 12px;
        }
        
        /* Footer */
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }
        
        .layout-2 .footer {
            font-size: 8px;
            margin-top: 10px;
            padding-top: 6px;
        }
        
        .layout-4 .footer {
            font-size: 7px;
            margin-top: 8px;
            padding-top: 5px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body class="layout-{{ $documentsPerPage }}">
    @foreach($documentNumbers as $index => $docNumber)
    <div class="document-wrapper">
        <div class="document-content">
            <!-- Header -->
            <div class="header">
                @if($logoPath)
                <div style="text-align: center; margin-bottom: 10px;">
                    <img src="{{ $logoPath }}" alt="Logo" style="max-width: 150px; max-height: 60px; width: auto; height: auto;">
                </div>
                @endif
                <div class="business-name">{{ $businessProfile->businessName() }}</div>
                <div class="business-details">
                    {{ $businessProfile->address() }}<br>
                    {{ $businessProfile->phone() }}
                    @if($businessProfile->email()) | {{ $businessProfile->email() }}@endif
                    @if($businessProfile->tpin())<br>TPIN: {{ $businessProfile->tpin() }}@endif
                </div>
            </div>
            
            <!-- Document Title -->
            <div class="doc-title">{{ strtoupper($documentType) }}</div>
            <div class="doc-number">#{{ $docNumber }}</div>
            
            <!-- Customer Section -->
            <div class="customer-section">
                <div class="section-label">Bill To / Customer:</div>
                <div class="blank-line"></div>
                <div class="blank-line"></div>
                <div class="blank-line"></div>
            </div>
            
            <!-- Date and Info -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 11px;">
                <div>
                    <strong>Date:</strong> _________________
                </div>
                @if($documentType === 'invoice')
                <div>
                    <strong>Due Date:</strong> _________________
                </div>
                @endif
            </div>
            
            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"></th>
                        <th style="width: 45%;">Description</th>
                        <th style="width: 15%; text-align: center;">Dimensions</th>
                        <th style="width: 10%; text-align: center;">Qty</th>
                        <th style="width: 12%; text-align: right;">Unit Price</th>
                        <th style="width: 13%; text-align: right;">Amount</th>
                    </tr>
                </thead>
                @php
                    // Use dynamic row count if provided, otherwise use defaults
                    $defaultRowCount = match($documentsPerPage) {
                        1 => 8,   // Full page
                        2 => 4,   // Half page
                        4 => 2,   // Quarter page
                        6 => 1,   // Compact
                        8 => 1,   // Small
                        10 => 1,  // Mini
                        default => 4
                    };
                    $rowCount = $rowCount ?? $defaultRowCount;
                @endphp
                <tbody>
                    @for($i = 0; $i < $rowCount; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    @endfor
                </tbody>
            </table>
            
            <!-- Totals -->
            <div class="clearfix">
                <div class="totals-section">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} __________</span>
                    </div>
                    <div class="total-row">
                        <span>Discount:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} __________</span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} __________</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>TOTAL:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} __________</span>
                    </div>
                </div>
            </div>
            
            <!-- Signature Section -->
            <div class="signature-section">
                <table class="signature-table">
                    <tr>
                        <td style="width: 15%;">
                            <span class="signature-label">Prepared by:</span>
                        </td>
                        <td style="width: 28%;">
                            <div class="signature-line"></div>
                        </td>
                        <td style="width: 12%;">
                            <span class="signature-label">Signature:</span>
                        </td>
                        <td style="width: 28%;">
                            <div class="signature-line"></div>
                        </td>
                        <td style="width: 7%;">
                            <span class="signature-label">Date:</span>
                        </td>
                        <td style="width: 10%;">
                            <div class="signature-line"></div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                Thank you for your business!
                @if($businessProfile->website())
                <br>{{ $businessProfile->website() }}
                @endif
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
