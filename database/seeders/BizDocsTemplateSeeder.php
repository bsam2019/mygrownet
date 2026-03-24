<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BizDocsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing templates
        DB::table('bizdocs_document_templates')->truncate();
        
        $templates = [
            // Modern & Professional Templates
            [
                'name' => 'Blue Professional',
                'document_type' => 'invoice', // Keep for backward compatibility, but not used for filtering
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'general_business',
                'layout_file' => 'blue-professional',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#1e40af',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email'],
                        'label_text' => 'Bill To',
                    ],
                    'document_title' => [
                        'text' => 'INVOICE',
                        'alignment' => 'right',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'tax', 'total'],
                        'show_borders' => true,
                        'row_striping' => true,
                    ],
                    'totals_block' => [
                        'alignment' => 'right',
                        'background_color' => '#1e40af',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                    ],
                    'global_styles' => [
                        'primary_color' => '#1e40af',
                        'font_family' => 'sans-serif',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],
            [
                'name' => 'Green Modern',
                'document_type' => 'invoice',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'general_business',
                'layout_file' => 'green-modern',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#059669',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email'],
                        'label_text' => 'Bill To',
                    ],
                    'document_title' => [
                        'text' => 'INVOICE',
                        'alignment' => 'right',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'tax', 'total'],
                        'show_borders' => true,
                        'row_striping' => true,
                    ],
                    'totals_block' => [
                        'alignment' => 'right',
                        'background_color' => '#059669',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                    ],
                    'global_styles' => [
                        'primary_color' => '#059669',
                        'font_family' => 'sans-serif',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],
            [
                'name' => 'Purple Elegant',
                'document_type' => 'quotation',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'professional_services',
                'layout_file' => 'purple-elegant',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'center',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#7c3aed',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email'],
                        'label_text' => 'Quotation For',
                    ],
                    'document_title' => [
                        'text' => 'QUOTATION',
                        'alignment' => 'center',
                        'font_size' => '3xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'tax', 'total'],
                        'show_borders' => true,
                        'row_striping' => true,
                    ],
                    'totals_block' => [
                        'alignment' => 'right',
                        'background_color' => '#7c3aed',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                    ],
                    'global_styles' => [
                        'primary_color' => '#7c3aed',
                        'font_family' => 'serif',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],
            [
                'name' => 'Professional',
                'document_type' => 'invoice',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'general_business',
                'layout_file' => 'modern-professional',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#ffffff',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email', 'tpin'],
                        'label_text' => 'Bill To',
                    ],
                    'document_title' => [
                        'text' => 'INVOICE',
                        'alignment' => 'right',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'tax', 'total'],
                        'column_order' => [0, 1, 2, 3, 4],
                        'show_borders' => true,
                        'row_striping' => true,
                        'row_striping_color' => '#f9fafb',
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'exclusive',
                        'show_discount' => true,
                        'alignment' => 'right',
                        'background_color' => '#f3f4f6',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                        'show_stamp' => true,
                        'signature_position' => 'left',
                        'stamp_position' => 'right',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#2563eb',
                        'font_family' => 'sans-serif',
                        'font_size' => 'medium',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => true,
            ],
            [
                'name' => 'Simple Compact',
                'document_type' => 'receipt',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'general_business',
                'layout_file' => 'compact-receipt',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'small',
                        'logo_position' => 'center',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#ffffff',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'phone'],
                        'label_text' => 'Received From',
                    ],
                    'document_title' => [
                        'text' => 'RECEIPT',
                        'alignment' => 'center',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'total'],
                        'column_order' => [0, 1, 2, 3],
                        'show_borders' => true,
                        'row_striping' => false,
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'inclusive',
                        'show_discount' => false,
                        'alignment' => 'right',
                        'background_color' => '#f9fafb',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => false,
                        'show_signature' => true,
                        'show_stamp' => true,
                        'signature_position' => 'center',
                        'stamp_position' => 'center',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#059669',
                        'font_family' => 'sans-serif',
                        'font_size' => 'medium',
                        'page_size' => 'half-page',
                    ],
                ]),
                'is_default' => true,
            ],
            [
                'name' => 'Corporate Formal',
                'document_type' => 'quotation',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'general_business',
                'layout_file' => 'corporate-formal',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'inline',
                        'background_color' => '#f9fafb',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email'],
                        'label_text' => 'Quotation For',
                    ],
                    'document_title' => [
                        'text' => 'QUOTATION',
                        'alignment' => 'right',
                        'font_size' => '3xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'tax', 'total'],
                        'column_order' => [0, 1, 2, 3, 4],
                        'show_borders' => true,
                        'row_striping' => true,
                        'row_striping_color' => '#f3f4f6',
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'exclusive',
                        'show_discount' => true,
                        'alignment' => 'right',
                        'background_color' => '#e5e7eb',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                        'show_stamp' => false,
                        'signature_position' => 'left',
                        'stamp_position' => 'right',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#4f46e5',
                        'font_family' => 'serif',
                        'font_size' => 'medium',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => true,
            ],
            [
                'name' => 'Minimal Center',
                'document_type' => 'delivery_note',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'general_business',
                'layout_file' => 'minimal-center',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'small',
                        'logo_position' => 'left',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#ffffff',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone'],
                        'label_text' => 'Deliver To',
                    ],
                    'document_title' => [
                        'text' => 'DELIVERY NOTE',
                        'alignment' => 'center',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity'],
                        'column_order' => [0, 1],
                        'show_borders' => true,
                        'row_striping' => false,
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'exclusive',
                        'show_discount' => false,
                        'alignment' => 'right',
                        'background_color' => '#ffffff',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => false,
                        'show_signature' => true,
                        'show_stamp' => false,
                        'signature_position' => 'left',
                        'stamp_position' => 'right',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#0891b2',
                        'font_family' => 'sans-serif',
                        'font_size' => 'medium',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],

            // Retail & Commerce
            [
                'name' => 'Colorful Modern (Retail)',
                'document_type' => 'invoice',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'retail',
                'layout_file' => 'colorful-modern',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'large',
                        'logo_position' => 'center',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#fef3c7',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'phone'],
                        'label_text' => 'Customer',
                    ],
                    'document_title' => [
                        'text' => 'SALES INVOICE',
                        'alignment' => 'center',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'total'],
                        'column_order' => [0, 1, 2, 3],
                        'show_borders' => true,
                        'row_striping' => true,
                        'row_striping_color' => '#fffbeb',
                        'font_size' => 'lg',
                    ],
                    'totals_block' => [
                        'tax_display' => 'inclusive',
                        'show_discount' => true,
                        'alignment' => 'right',
                        'background_color' => '#fef3c7',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => false,
                        'show_signature' => false,
                        'show_stamp' => true,
                        'signature_position' => 'center',
                        'stamp_position' => 'center',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#f59e0b',
                        'font_family' => 'sans-serif',
                        'font_size' => 'large',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],

            // Education
            [
                'name' => 'Compact (Education)',
                'document_type' => 'receipt',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'education',
                'layout_file' => 'compact-receipt',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'center',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#dbeafe',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone'],
                        'label_text' => 'Student/Parent',
                    ],
                    'document_title' => [
                        'text' => 'FEE RECEIPT',
                        'alignment' => 'center',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'total'],
                        'column_order' => [0, 1, 2, 3],
                        'show_borders' => true,
                        'row_striping' => true,
                        'row_striping_color' => '#eff6ff',
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'inclusive',
                        'show_discount' => false,
                        'alignment' => 'right',
                        'background_color' => '#dbeafe',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => false,
                        'show_signature' => true,
                        'show_stamp' => true,
                        'signature_position' => 'center',
                        'stamp_position' => 'center',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#3b82f6',
                        'font_family' => 'sans-serif',
                        'font_size' => 'medium',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],

            // Healthcare
            [
                'name' => 'Elegant Two Column (Healthcare)',
                'document_type' => 'invoice',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'healthcare',
                'layout_file' => 'elegant-two-column',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#dcfce7',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'phone', 'address'],
                        'label_text' => 'Patient',
                    ],
                    'document_title' => [
                        'text' => 'PHARMACY INVOICE',
                        'alignment' => 'right',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'total'],
                        'column_order' => [0, 1, 2, 3],
                        'show_borders' => true,
                        'row_striping' => true,
                        'row_striping_color' => '#f0fdf4',
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'inclusive',
                        'show_discount' => true,
                        'alignment' => 'right',
                        'background_color' => '#dcfce7',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                        'show_stamp' => true,
                        'signature_position' => 'left',
                        'stamp_position' => 'right',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#10b981',
                        'font_family' => 'sans-serif',
                        'font_size' => 'medium',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],

            // Construction & Services
            [
                'name' => 'Bold Modern (Construction)',
                'document_type' => 'quotation',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'construction',
                'layout_file' => 'bold-modern',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'inline',
                        'background_color' => '#fef2f2',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email'],
                        'label_text' => 'Client',
                    ],
                    'document_title' => [
                        'text' => 'PROJECT QUOTATION',
                        'alignment' => 'right',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'total'],
                        'column_order' => [0, 1, 2, 3],
                        'show_borders' => true,
                        'row_striping' => true,
                        'row_striping_color' => '#fee2e2',
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'exclusive',
                        'show_discount' => true,
                        'alignment' => 'right',
                        'background_color' => '#fef2f2',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                        'show_stamp' => true,
                        'signature_position' => 'left',
                        'stamp_position' => 'right',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#ef4444',
                        'font_family' => 'sans-serif',
                        'font_size' => 'medium',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],

            // Professional Services
            [
                'name' => 'Classic Left',
                'document_type' => 'invoice',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'professional_services',
                'layout_file' => 'classic-left',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'small',
                        'logo_position' => 'left',
                        'business_info_layout' => 'inline',
                        'background_color' => '#f5f3ff',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'email', 'address'],
                        'label_text' => 'Client',
                    ],
                    'document_title' => [
                        'text' => 'INVOICE',
                        'alignment' => 'right',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'total'],
                        'column_order' => [0, 1, 2, 3],
                        'show_borders' => true,
                        'row_striping' => true,
                        'row_striping_color' => '#faf5ff',
                        'font_size' => 'base',
                    ],
                    'totals_block' => [
                        'tax_display' => 'exclusive',
                        'show_discount' => false,
                        'alignment' => 'right',
                        'background_color' => '#f5f3ff',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                        'show_stamp' => false,
                        'signature_position' => 'left',
                        'stamp_position' => 'right',
                    ],
                    'watermark' => [
                        'enabled' => false,
                    ],
                    'global_styles' => [
                        'primary_color' => '#8b5cf6',
                        'font_family' => 'sans-serif',
                        'font_size' => 'medium',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],
            
            // Creative & Bold Templates
            [
                'name' => 'Blue Lines Professional',
                'document_type' => 'quotation',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'construction',
                'layout_file' => 'blue-lines-professional',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'inline',
                        'background_color' => '#ffffff',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone'],
                        'label_text' => 'Quote For',
                    ],
                    'document_title' => [
                        'text' => 'QUOTATION',
                        'alignment' => 'center',
                        'font_size' => '3xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['quantity', 'description', 'unit_price', 'total'],
                        'show_borders' => true,
                        'row_striping' => false,
                    ],
                    'totals_block' => [
                        'alignment' => 'right',
                        'background_color' => '#2563eb',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                    ],
                    'global_styles' => [
                        'primary_color' => '#2563eb',
                        'font_family' => 'sans-serif',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],
            [
                'name' => 'Orange Creative',
                'document_type' => 'invoice',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'creative',
                'layout_file' => 'orange-creative',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'medium',
                        'logo_position' => 'left',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#ea580c',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email'],
                        'label_text' => 'Bill To',
                    ],
                    'document_title' => [
                        'text' => 'INVOICE',
                        'alignment' => 'right',
                        'font_size' => '2xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'tax', 'total'],
                        'show_borders' => true,
                        'row_striping' => true,
                    ],
                    'totals_block' => [
                        'alignment' => 'right',
                        'background_color' => '#ea580c',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                    ],
                    'global_styles' => [
                        'primary_color' => '#ea580c',
                        'font_family' => 'sans-serif',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],
            [
                'name' => 'Red Bold',
                'document_type' => 'invoice',
                'visibility' => 'industry',
                'owner_id' => null,
                'industry_category' => 'construction',
                'layout_file' => 'red-bold',
                'template_structure' => json_encode([
                    'header' => [
                        'logo_size' => 'large',
                        'logo_position' => 'left',
                        'business_info_layout' => 'stacked',
                        'background_color' => '#dc2626',
                    ],
                    'customer_block' => [
                        'position' => 'left',
                        'fields' => ['name', 'address', 'phone', 'email'],
                        'label_text' => 'Bill To',
                    ],
                    'document_title' => [
                        'text' => 'INVOICE',
                        'alignment' => 'right',
                        'font_size' => '3xl',
                        'font_weight' => 'bold',
                    ],
                    'items_table' => [
                        'columns' => ['description', 'quantity', 'unit_price', 'tax', 'total'],
                        'show_borders' => true,
                        'row_striping' => true,
                    ],
                    'totals_block' => [
                        'alignment' => 'right',
                        'background_color' => '#dc2626',
                    ],
                    'footer' => [
                        'show_notes' => true,
                        'show_terms' => true,
                        'show_signature' => true,
                    ],
                    'global_styles' => [
                        'primary_color' => '#dc2626',
                        'font_family' => 'sans-serif',
                        'page_size' => 'A4',
                    ],
                ]),
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('bizdocs_document_templates')->insert([
                'name' => $template['name'],
                'document_type' => $template['document_type'],
                'visibility' => $template['visibility'],
                'owner_id' => $template['owner_id'],
                'industry_category' => $template['industry_category'],
                'layout_file' => $template['layout_file'],
                'template_structure' => $template['template_structure'],
                'is_default' => $template['is_default'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('BizDocs templates seeded successfully!');
        $this->command->info('Created ' . count($templates) . ' industry templates across multiple categories.');
    }
}
