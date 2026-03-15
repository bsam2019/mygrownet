<?php

namespace App\Domain\QuickInvoice\Services;

/**
 * Domain Service for Template Rendering
 * Converts layout JSON to HTML for preview and PDF generation
 */
class TemplateRenderService
{
    /**
     * Render template to HTML
     */
    public function renderToHtml(array $layoutJson, array $fieldConfig, array $data, array $branding): string
    {
        try {
            $html = '<div class="invoice-template" style="' . $this->getBrandingStyles($branding) . '">';
            
            $blocks = $layoutJson['blocks'] ?? [];
            \Log::info('TemplateRenderService - Rendering blocks', ['block_count' => count($blocks)]);
            
            foreach ($blocks as $index => $block) {
                try {
                    $html .= $this->renderBlock($block, $fieldConfig, $data, $branding);
                } catch (\Exception $e) {
                    \Log::error('TemplateRenderService - Error rendering block', [
                        'block_index' => $index,
                        'block_type' => $block['type'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with other blocks
                }
            }
            
            $html .= '</div>';
            
            \Log::info('TemplateRenderService - HTML rendered', ['html_length' => strlen($html)]);
            
            return $html;
        } catch (\Exception $e) {
            \Log::error('TemplateRenderService - Fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Render a single block
     */
    private function renderBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $type = str_replace('-', '_', $block['type']);
        $method = 'render' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type))) . 'Block';
        
        if (method_exists($this, $method)) {
            return $this->$method($block, $fieldConfig, $data, $branding);
        }
        
        return $this->renderGenericBlock($block);
    }
    
    /**
     * Render header block
     */
    private function renderHeaderBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $config = $block['config'] ?? [];
        $alignment = $config['alignment'] ?? 'space-between';
        
        $html = '<div class="header-block" style="display: flex; justify-content: ' . $alignment . '; align-items: center; margin-bottom: 2rem;">';
        
        // Logo
        if (($config['showLogo'] ?? true) && !empty($branding['logo_url'])) {
            $html .= '<div class="logo"><img src="' . $branding['logo_url'] . '" alt="Logo" style="max-height: 60px;"></div>';
        }
        
        // Company info
        if ($config['showCompanyInfo'] ?? true) {
            $html .= '<div class="company-info" style="text-align: right;">';
            if ($fieldConfig['company_name']['enabled'] ?? false) {
                $html .= '<h2 style="margin: 0; color: ' . $branding['primary_color'] . ';">' . ($data['company_name'] ?? 'Your Company') . '</h2>';
            }
            if ($fieldConfig['company_address']['enabled'] ?? false) {
                $html .= '<p style="margin: 0.25rem 0;">' . ($data['company_address'] ?? '') . '</p>';
            }
            if ($fieldConfig['company_phone']['enabled'] ?? false) {
                $html .= '<p style="margin: 0.25rem 0;">' . ($data['company_phone'] ?? '') . '</p>';
            }
            if ($fieldConfig['company_email']['enabled'] ?? false) {
                $html .= '<p style="margin: 0.25rem 0;">' . ($data['company_email'] ?? '') . '</p>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render invoice meta block
     */
    private function renderInvoicemetaBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $config = $block['config'] ?? [];
        $showTitle = $config['showTitle'] ?? true;
        $titleAlignment = $config['titleAlignment'] ?? 'left';
        
        $html = '<div class="invoice-meta" style="margin-bottom: 2rem;">';
        
        if ($showTitle) {
            $html .= '<h1 style="color: ' . $branding['primary_color'] . '; margin-bottom: 1rem; text-align: ' . $titleAlignment . ';">INVOICE</h1>';
        }
        
        $html .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">';
        
        if ($fieldConfig['invoice_number']['enabled'] ?? false) {
            $html .= '<div><strong>Invoice #:</strong> ' . ($data['invoice_number'] ?? 'INV-001') . '</div>';
        }
        if ($fieldConfig['invoice_date']['enabled'] ?? false) {
            $html .= '<div><strong>Date:</strong> ' . ($data['invoice_date'] ?? date('Y-m-d')) . '</div>';
        }
        if ($fieldConfig['due_date']['enabled'] ?? false) {
            $html .= '<div><strong>Due Date:</strong> ' . ($data['due_date'] ?? '') . '</div>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
    
    /**
     * Render customer details block
     */
    private function renderCustomerdetailsBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $config = $block['config'] ?? [];
        
        $html = '<div class="customer-details" style="margin-bottom: 2rem;">';
        $html .= '<h3 style="margin-bottom: 0.5rem;">Bill To:</h3>';
        
        if ($fieldConfig['customer_name']['enabled'] ?? false) {
            $html .= '<p style="margin: 0.25rem 0;"><strong>' . ($data['customer_name'] ?? 'Customer Name') . '</strong></p>';
        }
        if (($config['showAddress'] ?? true) && ($fieldConfig['customer_address']['enabled'] ?? false)) {
            $html .= '<p style="margin: 0.25rem 0;">' . ($data['customer_address'] ?? '') . '</p>';
        }
        if (($config['showPhone'] ?? false) && ($fieldConfig['customer_phone']['enabled'] ?? false)) {
            $html .= '<p style="margin: 0.25rem 0;">' . ($data['customer_phone'] ?? '') . '</p>';
        }
        if (($config['showEmail'] ?? false) && ($fieldConfig['customer_email']['enabled'] ?? false)) {
            $html .= '<p style="margin: 0.25rem 0;">' . ($data['customer_email'] ?? '') . '</p>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render items table block
     */
    private function renderItemstableBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $html = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">';
        $html .= '<thead><tr style="background-color: ' . $branding['primary_color'] . '; color: white;">';
        $html .= '<th style="padding: 0.75rem; text-align: left;">Description</th>';
        $html .= '<th style="padding: 0.75rem; text-align: center;">Qty</th>';
        $html .= '<th style="padding: 0.75rem; text-align: right;">Price</th>';
        $html .= '<th style="padding: 0.75rem; text-align: right;">Total</th>';
        $html .= '</tr></thead><tbody>';
        
        $items = $data['items'] ?? [
            ['description' => 'Sample Item 1', 'quantity' => 2, 'price' => 500, 'total' => 1000],
            ['description' => 'Sample Item 2', 'quantity' => 1, 'price' => 1000, 'total' => 1000],
        ];
        
        foreach ($items as $item) {
            $html .= '<tr style="border-bottom: 1px solid #e5e7eb;">';
            $html .= '<td style="padding: 0.75rem;">' . $item['description'] . '</td>';
            $html .= '<td style="padding: 0.75rem; text-align: center;">' . $item['quantity'] . '</td>';
            $html .= '<td style="padding: 0.75rem; text-align: right;">K' . number_format($item['price'], 2) . '</td>';
            $html .= '<td style="padding: 0.75rem; text-align: right;">K' . number_format($item['total'], 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
    
    /**
     * Render totals block
     */
    private function renderTotalsBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $config = $block['config'] ?? [];
        $alignment = $config['alignment'] ?? 'right';
        
        $alignmentStyle = match($alignment) {
            'left' => 'margin-right: auto;',
            'center' => 'margin-left: auto; margin-right: auto;',
            'right' => 'margin-left: auto;',
            default => 'margin-left: auto;',
        };
        
        $html = '<div class="totals" style="' . $alignmentStyle . ' max-width: 300px;">';
        
        if (($config['showSubtotal'] ?? true) && ($fieldConfig['subtotal']['enabled'] ?? false)) {
            $html .= '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">';
            $html .= '<span>Subtotal:</span><span>K' . number_format($data['subtotal'] ?? 2000, 2) . '</span>';
            $html .= '</div>';
        }
        
        if (($config['showTax'] ?? true) && ($fieldConfig['tax']['enabled'] ?? false)) {
            $html .= '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">';
            $html .= '<span>Tax (16%):</span><span>K' . number_format($data['tax'] ?? 320, 2) . '</span>';
            $html .= '</div>';
        }
        
        if (($config['showDiscount'] ?? false) && ($fieldConfig['discount']['enabled'] ?? false)) {
            $html .= '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">';
            $html .= '<span>Discount:</span><span>-K' . number_format($data['discount'] ?? 0, 2) . '</span>';
            $html .= '</div>';
        }
        
        if ($fieldConfig['total']['enabled'] ?? false) {
            $html .= '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-weight: bold; font-size: 1.25rem; color: ' . $branding['primary_color'] . ';">';
            $html .= '<span>Total:</span><span>K' . number_format($data['total'] ?? 2320, 2) . '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render text block
     */
    private function renderTextBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $config = $block['config'] ?? [];
        $text = $config['text'] ?? 'Text block';
        $alignment = $config['alignment'] ?? 'left';
        $fontSize = $config['fontSize'] ?? 'text-base';
        
        // Convert Tailwind classes to inline styles
        $fontSizeMap = [
            'text-xs' => '0.75rem',
            'text-sm' => '0.875rem',
            'text-base' => '1rem',
            'text-lg' => '1.125rem',
            'text-xl' => '1.25rem',
        ];
        
        $fontSizeStyle = $fontSizeMap[$fontSize] ?? '1rem';
        
        return '<div class="text-block" style="margin-bottom: 1rem; text-align: ' . $alignment . '; font-size: ' . $fontSizeStyle . ';">' . nl2br(htmlspecialchars($text)) . '</div>';
    }
    
    /**
     * Render divider block
     */
    private function renderDividerBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $config = $block['config'] ?? [];
        $style = $config['style'] ?? 'solid';
        $thickness = $config['thickness'] ?? '1px';
        $color = $config['color'] ?? '#e5e7eb';
        
        return '<hr style="border: none; border-top: ' . $thickness . ' ' . $style . ' ' . $color . '; margin: 1.5rem 0;">';
    }
    
    /**
     * Render generic block (fallback)
     */
    private function renderGenericBlock(array $block): string
    {
        $type = $block['type'] ?? 'unknown';
        
        \Log::warning('TemplateRenderService - Unknown block type, skipping render', [
            'type' => $type,
            'config' => $block['config'] ?? [],
        ]);
        
        // Return empty string to hide unknown blocks from PDF
        return '<!-- Unsupported block type: ' . htmlspecialchars($type) . ' -->';
    }
    
    /**
     * Render company header centered block
     */
    private function renderCompanyHeaderCenteredBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $html = '<div style="text-align: center; padding: 1.5rem 0; border-bottom: 2px solid #e5e7eb; margin-bottom: 2rem;">';
        
        if ($fieldConfig['company_name']['enabled'] ?? false) {
            $html .= '<h2 style="margin: 0 0 0.5rem 0; font-size: 1.5rem; font-weight: bold;">' . ($data['business_info']['name'] ?? 'Company Name') . '</h2>';
        }
        if ($fieldConfig['company_address']['enabled'] ?? false) {
            $html .= '<p style="margin: 0.25rem 0; color: #6b7280; font-size: 0.875rem;">' . ($data['business_info']['address'] ?? '') . '</p>';
        }
        if ($fieldConfig['company_phone']['enabled'] ?? false) {
            $html .= '<p style="margin: 0.25rem 0; color: #6b7280; font-size: 0.875rem;">' . ($data['business_info']['phone'] ?? '') . '</p>';
        }
        if ($fieldConfig['company_email']['enabled'] ?? false) {
            $html .= '<p style="margin: 0.25rem 0; color: #6b7280; font-size: 0.875rem;">' . ($data['business_info']['email'] ?? '') . '</p>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render invoice title bar block
     */
    private function renderInvoiceTitleBarBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $config = $block['config'] ?? [];
        $style = $config['style'] ?? 'default';
        
        if ($style === 'large-title') {
            $html = '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">';
            $html .= '<h1 style="font-size: 2.5rem; font-weight: bold; color: ' . $branding['primary_color'] . '; margin: 0;">INVOICE</h1>';
            if (!empty($branding['logo_url'])) {
                $html .= '<img src="' . $branding['logo_url'] . '" style="max-height: 60px; max-width: 150px;" alt="Logo">';
            }
            $html .= '</div>';
        } else {
            $html = '<div style="background: #1f2937; color: white; padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">';
            $html .= '<h1 style="font-size: 1.5rem; font-weight: bold; margin: 0;">INVOICE</h1>';
            if ($config['showNumber'] !== false) {
                $html .= '<span style="font-size: 1rem;">No: ' . ($data['document_number'] ?? '00001') . '</span>';
            }
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Render customer split block
     */
    private function renderCustomerSplitBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $html = '<div style="display: table; width: 100%; margin-bottom: 2rem; border-bottom: 2px solid ' . $branding['primary_color'] . '; padding-bottom: 1rem;">';
        $html .= '<div style="display: table-cell; width: 50%; vertical-align: top;">';
        $html .= '<div style="font-weight: bold; color: ' . $branding['primary_color'] . '; margin-bottom: 0.5rem;">BILL TO:</div>';
        $html .= '<div style="font-size: 1.1rem; font-weight: bold; margin-bottom: 0.25rem;">' . ($data['client_info']['name'] ?? 'Client Name') . '</div>';
        if ($fieldConfig['customer_address']['enabled'] ?? false) {
            $html .= '<div style="color: #6b7280; font-size: 0.875rem;">' . ($data['client_info']['address'] ?? '') . '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div style="display: table-cell; width: 50%; vertical-align: top; text-align: right;">';
        $html .= '<div><strong>Date:</strong> ' . ($data['issue_date'] ?? date('Y-m-d')) . '</div>';
        if (!empty($data['due_date'])) {
            $html .= '<div><strong>Due:</strong> ' . $data['due_date'] . '</div>';
        }
        $html .= '<div><strong>Invoice #:</strong> ' . ($data['document_number'] ?? 'INV-001') . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render items table minimal block
     */
    private function renderItemsTableMinimalBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $html = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">';
        $html .= '<thead><tr style="border-bottom: 2px solid ' . $branding['primary_color'] . ';">';
        $html .= '<th style="padding: 0.75rem; text-align: left; font-weight: bold;">Description</th>';
        $html .= '<th style="padding: 0.75rem; text-align: center; font-weight: bold;">Qty</th>';
        $html .= '<th style="padding: 0.75rem; text-align: right; font-weight: bold;">Price</th>';
        $html .= '<th style="padding: 0.75rem; text-align: right; font-weight: bold;">Total</th>';
        $html .= '</tr></thead><tbody>';
        
        $items = $data['items'] ?? [
            ['description' => 'Sample Item 1', 'quantity' => 2, 'unit_price' => 500, 'amount' => 1000],
            ['description' => 'Sample Item 2', 'quantity' => 1, 'unit_price' => 1000, 'amount' => 1000],
        ];
        
        foreach ($items as $item) {
            $html .= '<tr style="border-bottom: 1px solid #e5e7eb;">';
            $html .= '<td style="padding: 0.75rem;">' . $item['description'] . '</td>';
            $html .= '<td style="padding: 0.75rem; text-align: center;">' . $item['quantity'] . '</td>';
            $html .= '<td style="padding: 0.75rem; text-align: right;">K' . number_format($item['unit_price'], 2) . '</td>';
            $html .= '<td style="padding: 0.75rem; text-align: right;">K' . number_format($item['amount'], 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
    
    /**
     * Render payment details block
     */
    private function renderPaymentDetailsBlock(array $block, array $fieldConfig, array $data, array $branding): string
    {
        $html = '<div style="background: #f8fafc; padding: 1rem; margin-bottom: 2rem; border-left: 4px solid ' . $branding['primary_color'] . ';">';
        $html .= '<h3 style="margin: 0 0 0.5rem 0; font-size: 0.875rem; font-weight: bold; color: ' . $branding['primary_color'] . ';">PAYMENT DETAILS</h3>';
        $html .= '<p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Please make payment to the account details provided.</p>';
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Get branding styles
     */
    private function getBrandingStyles(array $branding): string
    {
        return 'font-family: ' . ($branding['font_family'] ?? 'Inter, sans-serif') . '; ' .
               'color: #111827; ' .
               'padding: 2rem; ' .
               'background: white;';
    }
}
