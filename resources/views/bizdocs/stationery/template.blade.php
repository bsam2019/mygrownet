<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stationery - {{ strtoupper($documentType) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; }
        
        @page {
            margin: 0;
        }
        
        /* 1 document per page */
        .layout-1 .document-wrapper {
            width: 100%;
            height: 297mm; /* A4 height */
            page-break-after: always;
            position: relative;
        }
        
        /* 2 documents per page (half-page) */
        .layout-2 .document-wrapper {
            width: 100%;
            height: 148.5mm; /* Half A4 height */
            page-break-inside: avoid;
            position: relative;
            border-bottom: 1px dashed #ccc;
        }
        
        .layout-2 .document-wrapper:nth-child(2n) {
            page-break-after: always;
            border-bottom: none;
        }
        
        /* 4 documents per page (quarter-page) */
        .layout-4 .document-wrapper {
            width: 50%;
            height: 148.5mm; /* Half A4 height */
            float: left;
            page-break-inside: avoid;
            position: relative;
            border-right: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
        }
        
        .layout-4 .document-wrapper:nth-child(2n) {
            border-right: none;
        }
        
        .layout-4 .document-wrapper:nth-child(4n) {
            page-break-after: always;
            border-bottom: none;
        }
        
        .layout-4 .document-wrapper:nth-child(4n-1) {
            border-bottom: none;
        }
        
        /* Document content */
        .document-content {
            padding: 20mm;
            height: 100%;
            position: relative;
        }
        
        .layout-2 .document-content {
            padding: 10mm;
        }
        
        .layout-4 .document-content {
            padding: 8mm;
            font-size: 10px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }
        
        .layout-4 .header {
            margin-bottom: 8px;
            padding-bottom: 5px;
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
        
        .business-details {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.4;
        }
        
        .layout-4 .business-details {
            font-size: 8px;
        }
        
        .doc-title {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            text-align: center;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        
        .layout-4 .doc-title {
            font-size: 14px;
            margin: 8px 0;
            letter-spacing: 1px;
        }
        
        .doc-number {
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .layout-4 .doc-number {
            font-size: 10px;
            margin-bottom: 8px;
        }
        
        /* Customer section */
        .customer-section {
            margin-bottom: 15px;
            padding: 10px;
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
            margin: 15px 0;
        }
        
        .layout-4 .items-table {
            margin: 8px 0;
        }
        
        .items-table th {
            background: #2563eb;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        
        .layout-4 .items-table th {
            padding: 4px 3px;
            font-size: 8px;
        }
        
        .items-table td {
            padding: 8px 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .layout-4 .items-table td {
            padding: 4px 3px;
        }
        
        /* Totals */
        .totals-section {
            margin-top: 15px;
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
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
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
        }
        
        .layout-4 .grand-total {
            font-size: 10px;
        }
        
        /* Footer */
        .footer {
            position: absolute;
            bottom: 20mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .layout-2 .footer {
            bottom: 10mm;
            left: 10mm;
            right: 10mm;
            font-size: 9px;
        }
        
        .layout-4 .footer {
            bottom: 8mm;
            left: 8mm;
            right: 8mm;
            font-size: 7px;
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
                        <th style="width: 40%;">Description</th>
                        <th style="width: 15%; text-align: center;">Qty</th>
                        <th style="width: 20%; text-align: right;">Unit Price</th>
                        <th style="width: 25%; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < ($documentsPerPage === 4 ? 3 : 5); $i++)
                    <tr>
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
                        <span>Tax:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} __________</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>TOTAL:</span>
                        <span>{{ $businessProfile->defaultCurrency() }} __________</span>
                    </div>
                </div>
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
