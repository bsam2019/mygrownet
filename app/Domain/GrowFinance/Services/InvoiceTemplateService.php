<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\InvoiceTemplate;
use App\Domain\GrowFinance\Repositories\InvoiceTemplateRepositoryInterface;
use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Str;

class InvoiceTemplateService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private InvoiceTemplateRepositoryInterface $templateRepo
    ) {}

    /**
     * Get all templates for a business
     */
    public function getTemplates(int $businessId): array
    {
        return array_map(
            fn(InvoiceTemplate $t) => $t->toArray(),
            $this->templateRepo->findActive($businessId)
        );
    }

    /**
     * Get default template for a business
     */
    public function getDefaultTemplate(int $businessId): ?array
    {
        return $this->templateRepo->findDefault($businessId)?->toArray();
    }

    /**
     * Check if user can create more templates
     */
    public function canCreateTemplate(User $user): array
    {
        $limits = $this->subscriptionService->getUserLimits($user);
        $maxTemplates = $limits['invoice_templates'] ?? 0;

        if ($maxTemplates === 0) {
            return [
                'allowed' => false,
                'reason' => 'Custom invoice templates are available on Professional plan and above.',
            ];
        }

        $currentCount = count($this->templateRepo->findActive($user->id));

        if ($maxTemplates !== -1 && $currentCount >= $maxTemplates) {
            return [
                'allowed' => false,
                'reason' => "Template limit reached ({$maxTemplates}). Upgrade for unlimited templates.",
                'used' => $currentCount,
                'limit' => $maxTemplates,
            ];
        }

        return [
            'allowed' => true,
            'used' => $currentCount,
            'limit' => $maxTemplates,
        ];
    }

    /**
     * Create a new template
     */
    public function createTemplate(int $businessId, array $data): array
    {
        $template = new InvoiceTemplate(
            id: null,
            businessId: $businessId,
            name: $data['name'],
            slug: Str::slug($data['name']),
            description: $data['description'] ?? null,
            layout: $data['layout'] ?? 'standard',
            colors: $data['colors'] ?? ['primary' => '#2563eb', 'secondary' => '#64748b', 'accent' => '#059669', 'text' => '#1f2937', 'background' => '#ffffff'],
            fonts: $data['fonts'] ?? ['heading' => 'Inter', 'body' => 'Inter'],
            logoPosition: $data['logo_position'] ?? 'left',
            showLogo: $data['show_logo'] ?? true,
            showWatermark: $data['show_watermark'] ?? false,
            headerText: $data['header_text'] ?? null,
            footerText: $data['footer_text'] ?? null,
            termsText: $data['terms_text'] ?? null,
            customFields: $data['custom_fields'] ?? null,
            isDefault: $data['is_default'] ?? false,
            isActive: true,
            createdAt: null,
            updatedAt: null,
        );

        return $this->templateRepo->save($template)->toArray();
    }

    /**
     * Update a template
     */
    public function updateTemplate(int $businessId, int $templateId, array $data): array
    {
        $template = $this->templateRepo->findById($templateId);

        $name = $data['name'] ?? $template->name;
        $slug = $template->slug;
        if (isset($data['name']) && $data['name'] !== $template->name) {
            $slug = Str::slug($data['name']);
        }

        $updated = new InvoiceTemplate(
            id: $templateId,
            businessId: $businessId,
            name: $name,
            slug: $slug,
            description: $data['description'] ?? $template->description,
            layout: $data['layout'] ?? $template->layout,
            colors: $data['colors'] ?? $template->colors,
            fonts: $data['fonts'] ?? $template->fonts,
            logoPosition: $data['logo_position'] ?? $template->logoPosition,
            showLogo: $data['show_logo'] ?? $template->showLogo,
            showWatermark: $data['show_watermark'] ?? $template->showWatermark,
            headerText: $data['header_text'] ?? $template->headerText,
            footerText: $data['footer_text'] ?? $template->footerText,
            termsText: $data['terms_text'] ?? $template->termsText,
            customFields: $data['custom_fields'] ?? $template->customFields,
            isDefault: $data['is_default'] ?? $template->isDefault,
            isActive: $template->isActive,
            createdAt: $template->createdAt,
            updatedAt: null,
        );

        return $this->templateRepo->save($updated)->toArray();
    }

    /**
     * Delete a template
     */
    public function deleteTemplate(int $businessId, int $templateId): bool
    {
        $template = $this->templateRepo->findById($templateId);

        if (!$template) {
            return false;
        }

        $templates = $this->templateRepo->findActive($businessId);

        if (count($templates) <= 1) {
            return false;
        }

        if ($template->isDefault) {
            foreach ($templates as $t) {
                if ($t->id !== $templateId) {
                    $newDefault = new InvoiceTemplate(
                        id: $t->id,
                        businessId: $t->businessId,
                        name: $t->name,
                        slug: $t->slug,
                        description: $t->description,
                        layout: $t->layout,
                        colors: $t->colors,
                        fonts: $t->fonts,
                        logoPosition: $t->logoPosition,
                        showLogo: $t->showLogo,
                        showWatermark: $t->showWatermark,
                        headerText: $t->headerText,
                        footerText: $t->footerText,
                        termsText: $t->termsText,
                        customFields: $t->customFields,
                        isDefault: true,
                        isActive: $t->isActive,
                        createdAt: $t->createdAt,
                        updatedAt: null,
                    );
                    $this->templateRepo->save($newDefault);
                    break;
                }
            }
        }

        $deactivated = new InvoiceTemplate(
            id: $templateId,
            businessId: $businessId,
            name: $template->name,
            slug: $template->slug,
            description: $template->description,
            layout: $template->layout,
            colors: $template->colors,
            fonts: $template->fonts,
            logoPosition: $template->logoPosition,
            showLogo: $template->showLogo,
            showWatermark: $template->showWatermark,
            headerText: $template->headerText,
            footerText: $template->footerText,
            termsText: $template->termsText,
            customFields: $template->customFields,
            isDefault: false,
            isActive: false,
            createdAt: $template->createdAt,
            updatedAt: null,
        );
        $this->templateRepo->save($deactivated);

        return true;
    }

    /**
     * Set template as default
     */
    public function setAsDefault(int $businessId, int $templateId): array
    {
        $template = $this->templateRepo->findById($templateId);

        $updated = new InvoiceTemplate(
            id: $templateId,
            businessId: $businessId,
            name: $template->name,
            slug: $template->slug,
            description: $template->description,
            layout: $template->layout,
            colors: $template->colors,
            fonts: $template->fonts,
            logoPosition: $template->logoPosition,
            showLogo: $template->showLogo,
            showWatermark: $template->showWatermark,
            headerText: $template->headerText,
            footerText: $template->footerText,
            termsText: $template->termsText,
            customFields: $template->customFields,
            isDefault: true,
            isActive: $template->isActive,
            createdAt: $template->createdAt,
            updatedAt: null,
        );

        return $this->templateRepo->save($updated)->toArray();
    }

    /**
     * Get available layouts
     */
    public function getAvailableLayouts(): array
    {
        return [
            'standard' => 'Standard',
            'modern' => 'Modern',
            'minimal' => 'Minimal',
            'professional' => 'Professional',
        ];
    }

    /**
     * Create default template for new business
     */
    public function createDefaultTemplate(int $businessId): array
    {
        return $this->createTemplate($businessId, [
            'name' => 'Default Template',
            'description' => 'Standard invoice template',
            'layout' => 'standard',
            'is_default' => true,
        ]);
    }
}
