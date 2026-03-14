<?php

namespace App\Domain\QuickInvoice\Services;

use App\Models\QuickInvoice\CustomTemplate;

/**
 * Domain Service for Template Builder Operations
 * Handles business logic for template creation and validation
 */
class TemplateBuilderService
{
    /**
     * Validate template layout structure
     */
    public function validateLayout(array $layoutJson): array
    {
        $errors = [];
        
        // Check version
        if (!isset($layoutJson['version'])) {
            $errors[] = 'Layout version is required';
        }
        
        // Check blocks
        if (!isset($layoutJson['blocks']) || !is_array($layoutJson['blocks'])) {
            $errors[] = 'Layout must contain blocks array';
        }
        
        // Validate each block
        if (isset($layoutJson['blocks'])) {
            foreach ($layoutJson['blocks'] as $index => $block) {
                if (!isset($block['id'])) {
                    $errors[] = "Block at index {$index} missing id";
                }
                if (!isset($block['type'])) {
                    $errors[] = "Block at index {$index} missing type";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate field configuration
     */
    public function validateFieldConfig(array $fieldConfig): array
    {
        $errors = [];
        
        // Only these fields are truly required for a functional invoice
        $requiredFields = ['invoice_number', 'invoice_date', 'customer_name', 'items_table', 'total'];
        
        foreach ($requiredFields as $field) {
            if (!isset($fieldConfig[$field]) || !$fieldConfig[$field]['enabled']) {
                $errors[] = "Required field '{$field}' must be enabled";
            }
        }
        
        return $errors;
    }
    
    /**
     * Generate default layout for a new template
     */
    public function generateDefaultLayout(): array
    {
        return [
            'version' => '1.0',
            'blocks' => [
                [
                    'id' => 'header-' . uniqid(),
                    'type' => 'header',
                    'position' => ['x' => 0, 'y' => 0],
                    'config' => [
                        'showLogo' => true,
                        'showCompanyInfo' => true,
                        'alignment' => 'space-between',
                    ],
                ],
                [
                    'id' => 'invoice-meta-' . uniqid(),
                    'type' => 'invoice-meta',
                    'position' => ['x' => 0, 'y' => 1],
                    'config' => [
                        'fields' => ['invoice_number', 'invoice_date', 'due_date'],
                    ],
                ],
                [
                    'id' => 'customer-' . uniqid(),
                    'type' => 'customer-details',
                    'position' => ['x' => 0, 'y' => 2],
                    'config' => [
                        'showAddress' => true,
                    ],
                ],
                [
                    'id' => 'items-' . uniqid(),
                    'type' => 'items-table',
                    'position' => ['x' => 0, 'y' => 3],
                    'config' => [
                        'columns' => ['description', 'quantity', 'price', 'total'],
                        'style' => 'striped',
                    ],
                ],
                [
                    'id' => 'totals-' . uniqid(),
                    'type' => 'totals',
                    'position' => ['x' => 0, 'y' => 4],
                    'config' => [
                        'alignment' => 'right',
                        'showSubtotal' => true,
                        'showTax' => true,
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Generate default field configuration
     */
    public function generateDefaultFieldConfig(): array
    {
        return [
            // Company Information
            'company_name' => ['enabled' => true, 'required' => false, 'label' => 'Company Name'],
            'company_address' => ['enabled' => true, 'required' => false, 'label' => 'Address'],
            'company_phone' => ['enabled' => true, 'required' => false, 'label' => 'Phone'],
            'company_email' => ['enabled' => true, 'required' => false, 'label' => 'Email'],
            'tax_number' => ['enabled' => true, 'required' => false, 'label' => 'Tax ID'],
            
            // Invoice Metadata (Required for invoice functionality)
            'invoice_number' => ['enabled' => true, 'required' => true, 'label' => 'Invoice #'],
            'invoice_date' => ['enabled' => true, 'required' => true, 'label' => 'Date'],
            'due_date' => ['enabled' => true, 'required' => false, 'label' => 'Due Date'],
            'po_number' => ['enabled' => false, 'required' => false, 'label' => 'PO Number'],
            
            // Customer Information (Required for invoice functionality)
            'customer_name' => ['enabled' => true, 'required' => true, 'label' => 'Customer Name'],
            'customer_address' => ['enabled' => true, 'required' => false, 'label' => 'Customer Address'],
            'customer_phone' => ['enabled' => false, 'required' => false, 'label' => 'Customer Phone'],
            'customer_email' => ['enabled' => false, 'required' => false, 'label' => 'Customer Email'],
            
            // Line Items (Required for invoice functionality)
            'items_table' => ['enabled' => true, 'required' => true, 'label' => 'Items'],
            
            // Totals
            'subtotal' => ['enabled' => true, 'required' => false, 'label' => 'Subtotal'],
            'tax' => ['enabled' => true, 'required' => false, 'label' => 'Tax'],
            'discount' => ['enabled' => false, 'required' => false, 'label' => 'Discount'],
            'shipping' => ['enabled' => false, 'required' => false, 'label' => 'Shipping'],
            'total' => ['enabled' => true, 'required' => true, 'label' => 'Total'],
            
            // Additional Information
            'payment_terms' => ['enabled' => false, 'required' => false, 'label' => 'Payment Terms'],
            'bank_details' => ['enabled' => false, 'required' => false, 'label' => 'Bank Details'],
            'notes' => ['enabled' => false, 'required' => false, 'label' => 'Notes'],
        ];
    }
    
    /**
     * Create a new template with validation
     */
    public function createTemplate(int $userId, array $data): CustomTemplate
    {
        // Validate layout
        $layoutErrors = $this->validateLayout($data['layout_json']);
        if (!empty($layoutErrors)) {
            throw new \InvalidArgumentException('Invalid layout: ' . implode(', ', $layoutErrors));
        }
        
        // Validate field config
        $fieldErrors = $this->validateFieldConfig($data['field_config']);
        if (!empty($fieldErrors)) {
            throw new \InvalidArgumentException('Invalid field configuration: ' . implode(', ', $fieldErrors));
        }
        
        return CustomTemplate::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'layout_json' => $data['layout_json'],
            'field_config' => $data['field_config'],
            'logo_url' => $data['logo_url'] ?? null,
            'primary_color' => $data['primary_color'],
            'secondary_color' => $data['secondary_color'] ?? null,
            'font_family' => $data['font_family'],
            'version' => 1,
        ]);
    }
    
    /**
     * Update an existing template
     */
    public function updateTemplate(CustomTemplate $template, array $data): CustomTemplate
    {
        // Validate layout
        $layoutErrors = $this->validateLayout($data['layout_json']);
        if (!empty($layoutErrors)) {
            throw new \InvalidArgumentException('Invalid layout: ' . implode(', ', $layoutErrors));
        }
        
        // Validate field config
        $fieldErrors = $this->validateFieldConfig($data['field_config']);
        if (!empty($fieldErrors)) {
            throw new \InvalidArgumentException('Invalid field configuration: ' . implode(', ', $fieldErrors));
        }
        
        // Increment version if layout changed
        if ($template->layout_json !== $data['layout_json']) {
            $data['version'] = ($template->version ?? 1) + 1;
        }
        
        $template->update($data);
        
        return $template;
    }
}
