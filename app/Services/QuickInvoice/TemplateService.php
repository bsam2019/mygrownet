<?php

namespace App\Services\QuickInvoice;

class TemplateService
{
    /**
     * Get all available templates with their configurations
     */
    public static function getTemplates(): array
    {
        return [
            'classic' => [
                'id' => 'classic',
                'name' => 'Classic',
                'description' => 'Traditional professional style with clean lines',
                'category' => 'general',
                'tier_required' => 'free',
                'features' => ['Professional layout', 'Clean typography', 'Standard colors'],
                'base_colors' => ['#2563eb', '#1f2937', '#059669'],
                'preview_image' => '/images/templates/classic-preview.png',
            ],
            'modern' => [
                'id' => 'modern',
                'name' => 'Modern',
                'description' => 'Contemporary design with gradient header',
                'category' => 'general',
                'tier_required' => 'basic',
                'features' => ['Gradient header', 'Modern fonts', 'Colorful accents'],
                'base_colors' => ['#3b82f6', '#8b5cf6', '#06b6d4'],
                'preview_image' => '/images/templates/modern-preview.png',
            ],
            'minimal' => [
                'id' => 'minimal',
                'name' => 'Minimal',
                'description' => 'Clean and simple, content-focused design',
                'category' => 'general',
                'tier_required' => 'free',
                'features' => ['Minimal design', 'Focus on content', 'Clean spacing'],
                'base_colors' => ['#6b7280', '#374151', '#111827'],
                'preview_image' => '/images/templates/minimal-preview.png',
            ],
            'professional' => [
                'id' => 'professional',
                'name' => 'Professional',
                'description' => 'Corporate look with sidebar accent',
                'category' => 'general',
                'tier_required' => 'basic',
                'features' => ['Corporate style', 'Sidebar layout', 'Professional colors'],
                'base_colors' => ['#1e40af', '#7c3aed', '#dc2626'],
                'preview_image' => '/images/templates/professional-preview.png',
            ],
            'bold' => [
                'id' => 'bold',
                'name' => 'Bold',
                'description' => 'Eye-catching with strong colors',
                'category' => 'general',
                'tier_required' => 'basic',
                'features' => ['Bold colors', 'Strong typography', 'Eye-catching design'],
                'base_colors' => ['#ea580c', '#dc2626', '#7c2d12'],
                'preview_image' => '/images/templates/bold-preview.png',
            ],
            // Industry-Specific Templates (Pro tier and above)
            'construction' => [
                'id' => 'construction',
                'name' => 'Construction Pro',
                'description' => 'Optimized for construction and contracting',
                'category' => 'construction',
                'tier_required' => 'pro',
                'features' => [
                    'Area calculations',
                    'Material quantity tracking',
                    'Labor cost breakdown',
                    'Project phase billing',
                    'Equipment rental tracking'
                ],
                'base_colors' => ['#f59e0b', '#dc2626', '#374151'],
                'preview_image' => '/images/templates/construction-preview.png',
                'custom_fields' => [
                    'project_location' => 'Project Location',
                    'project_phase' => 'Project Phase',
                    'area_sqm' => 'Area (sq.m)',
                    'material_type' => 'Material Type',
                    'labor_hours' => 'Labor Hours',
                ],
            ],
            'service' => [
                'id' => 'service',
                'name' => 'Service Excellence',
                'description' => 'Perfect for service-based businesses',
                'category' => 'service',
                'tier_required' => 'pro',
                'features' => [
                    'Time tracking integration',
                    'Service category breakdown',
                    'Hourly rate calculations',
                    'Service level indicators',
                    'Follow-up scheduling'
                ],
                'base_colors' => ['#059669', '#3b82f6', '#8b5cf6'],
                'preview_image' => '/images/templates/service-preview.png',
                'custom_fields' => [
                    'service_category' => 'Service Category',
                    'service_date' => 'Service Date',
                    'duration_hours' => 'Duration (Hours)',
                    'technician_name' => 'Technician',
                    'next_service_date' => 'Next Service Due',
                ],
            ],
            'retail' => [
                'id' => 'retail',
                'name' => 'Retail Pro',
                'description' => 'Product-focused with inventory integration',
                'category' => 'retail',
                'tier_required' => 'pro',
                'features' => [
                    'Product image display',
                    'SKU tracking',
                    'Inventory status',
                    'Bulk discount calculations',
                    'Customer loyalty points'
                ],
                'base_colors' => ['#dc2626', '#f59e0b', '#059669'],
                'preview_image' => '/images/templates/retail-preview.png',
                'custom_fields' => [
                    'sku' => 'SKU',
                    'product_category' => 'Category',
                    'brand' => 'Brand',
                    'warranty_period' => 'Warranty',
                    'loyalty_points' => 'Loyalty Points Earned',
                ],
            ],
            'creative' => [
                'id' => 'creative',
                'name' => 'Creative Studio',
                'description' => 'For designers and creative agencies',
                'category' => 'creative',
                'tier_required' => 'pro',
                'features' => [
                    'Portfolio showcase',
                    'Creative brief summary',
                    'Revision tracking',
                    'Usage rights specification',
                    'Creative timeline'
                ],
                'base_colors' => ['#8b5cf6', '#ec4899', '#f59e0b'],
                'preview_image' => '/images/templates/creative-preview.png',
                'custom_fields' => [
                    'project_type' => 'Project Type',
                    'creative_brief' => 'Creative Brief',
                    'deliverables' => 'Deliverables',
                    'usage_rights' => 'Usage Rights',
                    'revision_round' => 'Revision Round',
                ],
            ],
            'medical' => [
                'id' => 'medical',
                'name' => 'Medical Professional',
                'description' => 'Healthcare-compliant layouts',
                'category' => 'medical',
                'tier_required' => 'pro',
                'features' => [
                    'Patient information privacy',
                    'Medical procedure codes',
                    'Insurance billing format',
                    'Prescription tracking',
                    'Compliance indicators'
                ],
                'base_colors' => ['#059669', '#3b82f6', '#6b7280'],
                'preview_image' => '/images/templates/medical-preview.png',
                'custom_fields' => [
                    'patient_id' => 'Patient ID',
                    'procedure_code' => 'Procedure Code',
                    'insurance_provider' => 'Insurance Provider',
                    'diagnosis' => 'Diagnosis',
                    'treatment_date' => 'Treatment Date',
                ],
            ],
        ];
    }

    /**
     * Get templates available for a specific subscription tier
     */
    public static function getTemplatesForTier(string $tier): array
    {
        $templates = self::getTemplates();
        
        // For now, all tiers get all templates (free access)
        // Admin can enable restrictions later when usage increases
        return $templates;
        
        // Original tier-based logic (commented out for now):
        /*
        $tierHierarchy = ['free', 'basic', 'pro', 'enterprise'];
        $tierIndex = array_search(strtolower($tier), $tierHierarchy);
        
        if ($tierIndex === false) {
            $tierIndex = 0; // Default to free
        }
        
        $availableTiers = array_slice($tierHierarchy, 0, $tierIndex + 1);
        
        return array_filter($templates, function ($template) use ($availableTiers) {
            return in_array($template['tier_required'], $availableTiers);
        });
        */
    }

    /**
     * Get templates by category
     */
    public static function getTemplatesByCategory(string $category): array
    {
        $templates = self::getTemplates();
        
        return array_filter($templates, function ($template) use ($category) {
            return $template['category'] === $category;
        });
    }

    /**
     * Get template configuration by ID
     */
    public static function getTemplate(string $templateId): ?array
    {
        $templates = self::getTemplates();
        return $templates[$templateId] ?? null;
    }

    /**
     * Check if user has access to a specific template
     */
    public static function hasAccessToTemplate(string $templateId, string $userTier): bool
    {
        $template = self::getTemplate($templateId);
        if (!$template) {
            return false;
        }
        
        // For now, all users have access to all templates (free access)
        // Admin can enable restrictions later when usage increases
        return true;
        
        // Original tier-based logic (commented out for now):
        /*
        $tierHierarchy = ['free', 'basic', 'pro', 'enterprise'];
        $userTierIndex = array_search(strtolower($userTier), $tierHierarchy);
        $requiredTierIndex = array_search($template['tier_required'], $tierHierarchy);
        
        return $userTierIndex !== false && $requiredTierIndex !== false && $userTierIndex >= $requiredTierIndex;
        */
    }

    /**
     * Get color palettes for design studio
     */
    public static function getColorPalettes(): array
    {
        return [
            [
                'name' => 'Professional Blue',
                'colors' => ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd'],
                'description' => 'Trust and reliability',
            ],
            [
                'name' => 'Corporate Gray',
                'colors' => ['#374151', '#4b5563', '#6b7280', '#9ca3af'],
                'description' => 'Professional and neutral',
            ],
            [
                'name' => 'Success Green',
                'colors' => ['#059669', '#10b981', '#34d399', '#6ee7b7'],
                'description' => 'Growth and prosperity',
            ],
            [
                'name' => 'Premium Purple',
                'colors' => ['#7c3aed', '#8b5cf6', '#a78bfa', '#c4b5fd'],
                'description' => 'Luxury and creativity',
            ],
            [
                'name' => 'Energy Orange',
                'colors' => ['#ea580c', '#f97316', '#fb923c', '#fdba74'],
                'description' => 'Dynamic and energetic',
            ],
            [
                'name' => 'Construction Amber',
                'colors' => ['#f59e0b', '#fbbf24', '#fcd34d', '#fde68a'],
                'description' => 'Construction and industrial',
            ],
            [
                'name' => 'Medical Teal',
                'colors' => ['#0d9488', '#14b8a6', '#5eead4', '#99f6e4'],
                'description' => 'Healthcare and wellness',
            ],
            [
                'name' => 'Creative Pink',
                'colors' => ['#ec4899', '#f472b6', '#f9a8d4', '#fce7f3'],
                'description' => 'Creative and artistic',
            ],
        ];
    }

    /**
     * Get font options for design studio
     */
    public static function getFonts(): array
    {
        return [
            [
                'name' => 'Inter',
                'family' => 'Inter, sans-serif',
                'description' => 'Modern and clean',
                'preview' => 'The quick brown fox jumps over the lazy dog',
                'category' => 'modern',
            ],
            [
                'name' => 'Roboto',
                'family' => 'Roboto, sans-serif',
                'description' => 'Professional and readable',
                'preview' => 'The quick brown fox jumps over the lazy dog',
                'category' => 'professional',
            ],
            [
                'name' => 'Open Sans',
                'family' => 'Open Sans, sans-serif',
                'description' => 'Friendly and approachable',
                'preview' => 'The quick brown fox jumps over the lazy dog',
                'category' => 'friendly',
            ],
            [
                'name' => 'Lato',
                'family' => 'Lato, sans-serif',
                'description' => 'Elegant and sophisticated',
                'preview' => 'The quick brown fox jumps over the lazy dog',
                'category' => 'elegant',
            ],
            [
                'name' => 'Poppins',
                'family' => 'Poppins, sans-serif',
                'description' => 'Geometric and modern',
                'preview' => 'The quick brown fox jumps over the lazy dog',
                'category' => 'geometric',
            ],
            [
                'name' => 'Montserrat',
                'family' => 'Montserrat, sans-serif',
                'description' => 'Urban and contemporary',
                'preview' => 'The quick brown fox jumps over the lazy dog',
                'category' => 'contemporary',
            ],
        ];
    }
}