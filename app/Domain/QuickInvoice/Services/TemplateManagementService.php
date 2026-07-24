<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Template;
use App\Domain\QuickInvoice\Repositories\TemplateRepositoryInterface;

class TemplateManagementService
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository
    ) {}

    public function getUserTemplates(int $userId): array
    {
        return array_map(fn(Template $t) => $this->templateToArray($t), $this->templateRepository->findByUser($userId));
    }

    public function createTemplate(int $userId, array $data): Template
    {
        $data['user_id'] = $userId;
        $data['version'] = 1;
        $template = Template::reconstitute($data);
        return $this->templateRepository->save($template);
    }

    public function updateTemplate(int $id, int $userId, array $data): Template
    {
        $template = $this->templateRepository->findById($id);

        if (!$template || $template->userId !== $userId) {
            throw new \RuntimeException('Template not found or unauthorized');
        }

        $merged = array_merge($template->toArray(), $data);

        if (isset($data['layout_json']) && $data['layout_json'] !== $template->layoutJson) {
            $merged['version'] = ($template->version ?? 1) + 1;
        }

        return $this->templateRepository->save(Template::reconstitute($merged));
    }

    public function deleteTemplate(int $id, int $userId): void
    {
        $template = $this->templateRepository->findById($id);

        if (!$template || $template->userId !== $userId) {
            throw new \RuntimeException('Template not found or unauthorized');
        }

        $this->templateRepository->delete($id);
    }

    public function duplicateTemplate(int $id, int $userId): ?Template
    {
        return $this->templateRepository->replicate($id, $userId, '');
    }

    public function getTemplateForEdit(int $id, int $userId): ?array
    {
        $template = $this->templateRepository->findById($id);

        if (!$template || $template->userId !== $userId) {
            return null;
        }

        return $this->templateToArray($template);
    }

    private function templateToArray(Template $t): array
    {
        return [
            'id' => $t->id,
            'name' => $t->name,
            'description' => $t->description,
            'is_custom' => true,
            'is_owner' => true,
            'owner_name' => 'You',
            'usage_count' => $t->usageCount ?? 0,
            'last_used_at' => $t->lastUsedAt?->format('Y-m-d H:i:s'),
            'created_at' => $t->createdAt?->format('Y-m-d H:i:s'),
            'primary_color' => $t->primaryColor,
            'secondary_color' => $t->secondaryColor,
            'font_family' => $t->fontFamily,
            'layout_json' => $t->layoutJson,
        ];
    }
}