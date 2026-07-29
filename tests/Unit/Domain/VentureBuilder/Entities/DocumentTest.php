<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Document;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $doc = new Document(
            ventureId: 1,
            title: 'Business Plan',
            filePath: '/docs/business-plan.pdf',
            visibility: 'public',
            uploadedBy: 42,
            type: 'pdf',
        );

        $this->assertSame(1, $doc->ventureId);
        $this->assertSame('Business Plan', $doc->title);
        $this->assertSame('/docs/business-plan.pdf', $doc->filePath);
        $this->assertSame('public', $doc->visibility);
        $this->assertSame(42, $doc->uploadedBy);
        $this->assertSame('pdf', $doc->type);
    }

    #[Test]
    public function is_public_returns_true_when_public(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'public', uploadedBy: 1, type: 'pdf');
        $this->assertTrue($doc->isPublic());
    }

    #[Test]
    public function is_public_returns_false_when_not_public(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'investors_only', uploadedBy: 1, type: 'pdf');
        $this->assertFalse($doc->isPublic());
    }

    #[Test]
    public function can_be_accessed_by_public_visibility_for_anyone(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'public', uploadedBy: 1, type: 'pdf');
        $this->assertTrue($doc->canBeAccessedBy('guest'));
        $this->assertTrue($doc->canBeAccessedBy('investor'));
        $this->assertTrue($doc->canBeAccessedBy('admin'));
    }

    #[Test]
    public function can_be_accessed_by_investors_only_for_investor_roles(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'investors_only', uploadedBy: 1, type: 'pdf');
        $this->assertTrue($doc->canBeAccessedBy('investor'));
        $this->assertTrue($doc->canBeAccessedBy('shareholder'));
        $this->assertTrue($doc->canBeAccessedBy('admin'));
        $this->assertFalse($doc->canBeAccessedBy('guest'));
    }

    #[Test]
    public function can_be_accessed_by_shareholders_only_for_shareholder_roles(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'shareholders_only', uploadedBy: 1, type: 'pdf');
        $this->assertTrue($doc->canBeAccessedBy('shareholder'));
        $this->assertTrue($doc->canBeAccessedBy('admin'));
        $this->assertFalse($doc->canBeAccessedBy('investor'));
        $this->assertFalse($doc->canBeAccessedBy('guest'));
    }

    #[Test]
    public function can_be_accessed_by_admin_only_for_admin(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'admin_only', uploadedBy: 1, type: 'pdf');
        $this->assertTrue($doc->canBeAccessedBy('admin'));
        $this->assertFalse($doc->canBeAccessedBy('shareholder'));
        $this->assertFalse($doc->canBeAccessedBy('investor'));
        $this->assertFalse($doc->canBeAccessedBy('guest'));
    }

    #[Test]
    public function can_be_accessed_returns_false_for_unknown_visibility(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'unknown', uploadedBy: 1, type: 'pdf');
        $this->assertFalse($doc->canBeAccessedBy('admin'));
    }

    #[Test]
    public function get_file_size_formatted_returns_bytes(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'public', uploadedBy: 1, type: 'pdf', fileSize: 500);
        $this->assertSame('500 B', $doc->getFileSizeFormatted());
    }

    #[Test]
    public function get_file_size_formatted_returns_kb(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'public', uploadedBy: 1, type: 'pdf', fileSize: 2048);
        $this->assertSame('2 KB', $doc->getFileSizeFormatted());
    }

    #[Test]
    public function get_file_size_formatted_returns_mb(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'public', uploadedBy: 1, type: 'pdf', fileSize: 2097152);
        $this->assertSame('2 MB', $doc->getFileSizeFormatted());
    }

    #[Test]
    public function get_file_size_formatted_returns_0_bytes_when_null(): void
    {
        $doc = new Document(ventureId: 1, title: 'T', filePath: '/p', visibility: 'public', uploadedBy: 1, type: 'pdf');
        $this->assertSame('0 B', $doc->getFileSizeFormatted());
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 5,
            'venture_id' => 1,
            'title' => 'Financial Report',
            'file_path' => '/docs/fin.pdf',
            'visibility' => 'shareholders_only',
            'uploaded_by' => 42,
            'type' => 'pdf',
            'file_size' => 1048576,
            'is_confidential' => true,
            'download_count' => 10,
        ];

        $doc = Document::reconstitute($data);

        $this->assertSame(5, $doc->id);
        $this->assertSame('Financial Report', $doc->title);
        $this->assertSame('shareholders_only', $doc->visibility);
        $this->assertTrue($doc->isConfidential);
        $this->assertSame(10, $doc->downloadCount);
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $doc = new Document(
            ventureId: 1,
            title: 'Doc',
            filePath: '/p',
            visibility: 'public',
            uploadedBy: 42,
            type: 'pdf',
            id: 5,
            createdAt: new DateTimeImmutable('2026-01-10 08:00:00'),
        );

        $arr = $doc->toArray();

        $this->assertSame(5, $arr['id']);
        $this->assertSame('public', $arr['visibility']);
        $this->assertSame('2026-01-10 08:00:00', $arr['created_at']);
    }
}
