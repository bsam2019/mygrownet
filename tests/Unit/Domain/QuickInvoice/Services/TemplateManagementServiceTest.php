<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Template;
use App\Domain\QuickInvoice\Repositories\TemplateRepositoryInterface;
use App\Domain\QuickInvoice\Services\TemplateManagementService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class TemplateManagementServiceTest extends TestCase
{
    private TemplateRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $repository;

    private TemplateManagementService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TemplateRepositoryInterface::class);
        $this->service = new TemplateManagementService($this->repository);
    }

    #[Test]
    public function get_user_templates_returns_array(): void
    {
        $template = Template::reconstitute(['user_id' => 1, 'name' => 'My Template', 'usage_count' => 3]);
        $this->repository
            ->method('findByUser')
            ->willReturn([$template]);

        $result = $this->service->getUserTemplates(1);
        $this->assertCount(1, $result);
        $this->assertSame('My Template', $result[0]['name']);
        $this->assertTrue($result[0]['is_custom']);
        $this->assertSame(3, $result[0]['usage_count']);
    }

    #[Test]
    public function get_user_templates_empty(): void
    {
        $this->repository
            ->method('findByUser')
            ->willReturn([]);

        $this->assertSame([], $this->service->getUserTemplates(1));
    }

    #[Test]
    public function create_template_delegates_to_repository(): void
    {
        $data = ['name' => 'New', 'description' => 'A new template', 'layout_json' => ['version' => '1.0']];

        $this->repository
            ->method('save')
            ->willReturnCallback(fn(Template $t) => $t);

        $result = $this->service->createTemplate(42, $data);
        $this->assertSame('New', $result->name);
        $this->assertSame(42, $result->userId);
        $this->assertSame(1, $result->version);
    }

    #[Test]
    public function update_template_found_and_owned_updates(): void
    {
        $existing = Template::reconstitute(['id' => 1, 'user_id' => 5, 'name' => 'Old Name', 'version' => 1]);

        $this->repository
            ->method('findById')
            ->willReturn($existing);

        $this->repository
            ->method('save')
            ->willReturnCallback(fn(Template $t) => $t);

        $result = $this->service->updateTemplate(1, 5, ['name' => 'New Name']);
        $this->assertSame('New Name', $result->name);
        $this->assertSame(5, $result->userId);
    }

    #[Test]
    public function update_template_not_found_throws(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->service->updateTemplate(99, 1, ['name' => 'New']);
    }

    #[Test]
    public function update_template_not_owner_throws(): void
    {
        $template = Template::reconstitute(['id' => 1, 'user_id' => 5, 'name' => 'Template']);

        $this->repository
            ->method('findById')
            ->willReturn($template);

        $this->expectException(\RuntimeException::class);
        $this->service->updateTemplate(1, 99, ['name' => 'Hacked']);
    }

    #[Test]
    public function update_template_increments_version_on_layout_change(): void
    {
        $existing = Template::reconstitute([
            'id' => 1, 'user_id' => 5, 'name' => 'T', 'version' => 2,
            'layout_json' => ['version' => '1.0'],
        ]);

        $this->repository
            ->method('findById')
            ->willReturn($existing);

        $this->repository
            ->method('save')
            ->willReturnCallback(fn(Template $t) => $t);

        $result = $this->service->updateTemplate(1, 5, ['layout_json' => ['version' => '2.0']]);
        $this->assertSame(3, $result->version);
    }

    #[Test]
    public function update_template_does_not_increment_version_on_same_layout(): void
    {
        $layout = ['version' => '1.0'];
        $existing = Template::reconstitute([
            'id' => 1, 'user_id' => 5, 'name' => 'T', 'version' => 2,
            'layout_json' => $layout,
        ]);

        $this->repository
            ->method('findById')
            ->willReturn($existing);

        $this->repository
            ->method('save')
            ->willReturnCallback(fn(Template $t) => $t);

        $result = $this->service->updateTemplate(1, 5, ['layout_json' => $layout]);
        $this->assertSame(2, $result->version);
    }

    #[Test]
    public function delete_template_removes_if_owner(): void
    {
        $template = Template::reconstitute(['id' => 1, 'user_id' => 5, 'name' => 'T']);

        $this->repository
            ->method('findById')
            ->willReturn($template);

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $this->service->deleteTemplate(1, 5);
    }

    #[Test]
    public function delete_template_not_found_throws(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->service->deleteTemplate(99, 1);
    }

    #[Test]
    public function delete_template_not_owner_throws(): void
    {
        $template = Template::reconstitute(['id' => 1, 'user_id' => 5, 'name' => 'T']);

        $this->repository
            ->method('findById')
            ->willReturn($template);

        $this->expectException(\RuntimeException::class);
        $this->service->deleteTemplate(1, 99);
    }

    #[Test]
    public function duplicate_template_delegates_to_repository(): void
    {
        $original = Template::reconstitute(['id' => 5, 'user_id' => 1, 'name' => 'Original']);

        $this->repository
            ->method('findById')
            ->willReturn($original);

        $this->repository
            ->method('replicate')
            ->willReturnCallback(fn($id, $userId, $name) =>
                Template::reconstitute(['id' => 6, 'user_id' => $userId, 'name' => 'Original Copy'])
            );

        $result = $this->service->duplicateTemplate(5, 2);
        $this->assertNotNull($result);
        $this->assertSame(6, $result->id);
        $this->assertSame(2, $result->userId);
    }

    #[Test]
    public function get_template_for_edit_returns_array(): void
    {
        $template = Template::reconstitute([
            'id' => 1, 'user_id' => 5, 'name' => 'T',
            'layout_json' => ['version' => '1.0'],
        ]);

        $this->repository
            ->method('findById')
            ->willReturn($template);

        $result = $this->service->getTemplateForEdit(1, 5);
        $this->assertNotNull($result);
        $this->assertSame('T', $result['name']);
        $this->assertTrue($result['is_owner']);
        $this->assertArrayHasKey('layout_json', $result);
    }

    #[Test]
    public function get_template_for_edit_not_found_returns_null(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->assertNull($this->service->getTemplateForEdit(99, 1));
    }

    #[Test]
    public function get_template_for_edit_not_owner_returns_null(): void
    {
        $template = Template::reconstitute(['id' => 1, 'user_id' => 5, 'name' => 'T']);

        $this->repository
            ->method('findById')
            ->willReturn($template);

        $this->assertNull($this->service->getTemplateForEdit(1, 99));
    }
}
