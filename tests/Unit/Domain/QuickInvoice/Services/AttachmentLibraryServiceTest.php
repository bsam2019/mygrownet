<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Repositories\AttachmentRepositoryInterface;
use App\Domain\QuickInvoice\Services\AttachmentLibraryService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class AttachmentLibraryServiceTest extends TestCase
{
    private AttachmentRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $repository;

    private AttachmentLibraryService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createStub(AttachmentRepositoryInterface::class);
        $this->service = new AttachmentLibraryService($this->repository);
    }

    #[Test]
    public function get_attachments_returns_mapped_array(): void
    {
        $this->repository
            ->method('findByUser')
            ->willReturn([
                [
                    'id' => 1,
                    'name' => 'Report',
                    'original_filename' => 'report.pdf',
                    'path' => 'quick-invoice/library/1/report.pdf',
                    'type' => 'application/pdf',
                    'size' => 204800,
                    'description' => 'Monthly report',
                    'created_at' => '2026-07-01 12:00:00',
                ],
                [
                    'id' => 2,
                    'name' => 'Photo',
                    'original_filename' => 'photo.jpg',
                    'path' => 'quick-invoice/library/1/photo.jpg',
                    'type' => 'image/jpeg',
                    'size' => 512000,
                    'description' => null,
                    'created_at' => '2026-07-15 14:00:00',
                ],
            ]);

        $result = $this->service->getAttachments(1);

        $this->assertCount(2, $result);

        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Report', $result[0]['name']);
        $this->assertSame('200 KB', $result[0]['formatted_size']);
        $this->assertFalse($result[0]['is_image']);
        $this->assertTrue($result[0]['is_pdf']);
        $this->assertSame('2026-07-01 12:00:00', $result[0]['created_at']);

        $this->assertSame(2, $result[1]['id']);
        $this->assertSame('500 KB', $result[1]['formatted_size']);
        $this->assertTrue($result[1]['is_image']);
        $this->assertFalse($result[1]['is_pdf']);
    }

    #[Test]
    public function get_attachments_empty(): void
    {
        $this->repository
            ->method('findByUser')
            ->willReturn([]);

        $this->assertSame([], $this->service->getAttachments(1));
    }

    #[Test]
    public function update_attachment_not_found_returns_null(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->assertNull($this->service->updateAttachment(99, 1, ['name' => 'New']));
    }

    #[Test]
    public function update_attachment_not_owner_returns_null(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(['id' => 1, 'user_id' => 10]);

        $this->assertNull($this->service->updateAttachment(1, 99, ['name' => 'Hack']));
    }

    #[Test]
    public function delete_attachment_not_found_returns_false(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->assertFalse($this->service->deleteAttachment(99, 1));
    }

    #[Test]
    public function delete_attachment_not_owner_returns_false(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(['id' => 1, 'user_id' => 5, 'path' => 'file.pdf']);

        $this->assertFalse($this->service->deleteAttachment(1, 99));
    }

    #[Test]
    public function download_attachment_not_found_returns_null(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->assertNull($this->service->downloadAttachment(99, 1));
    }

    #[Test]
    public function download_attachment_not_owner_returns_null(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(['id' => 1, 'user_id' => 5]);

        $this->assertNull($this->service->downloadAttachment(1, 99));
    }

    #[Test]
    public function get_by_ids_delegates_to_repository(): void
    {
        $this->repository
            ->method('findByUserAndIds')
            ->willReturn([]);

        $result = $this->service->getByIds(1, [1, 2]);
        $this->assertSame([], $result);
    }
}
