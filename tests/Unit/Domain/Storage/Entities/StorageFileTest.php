<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\Entities;

use App\Domain\Storage\Entities\StorageFile;
use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\S3Path;
use PHPUnit\Framework\TestCase;

final class StorageFileTest extends TestCase
{
    private S3Path $s3Path;
    private FileSize $size;

    protected function setUp(): void
    {
        $this->s3Path = S3Path::create('uploads', 'path/to/file.pdf');
        $this->size = FileSize::fromMegabytes(5);
    }

    public function test_can_create_file(): void
    {
        $file = StorageFile::create('uuid-1', 42, null, 'document.pdf', 'application/pdf', $this->size, $this->s3Path);

        $this->assertEquals('uuid-1', $file->getId());
        $this->assertEquals(42, $file->getUserId());
        $this->assertNull($file->getFolderId());
        $this->assertEquals('document.pdf', $file->getOriginalName());
        $this->assertEquals('pdf', $file->getExtension());
        $this->assertTrue($file->getSize()->equals($this->size));
        $this->assertSame($this->s3Path, $file->getS3Path());
    }

    public function test_create_without_folder_has_null_folder_id(): void
    {
        $file = StorageFile::create('uuid-2', 1, null, 'test.txt', 'text/plain', $this->size, $this->s3Path);
        $this->assertNull($file->getFolderId());
    }

    public function test_create_with_folder_id(): void
    {
        $file = StorageFile::create('uuid-3', 1, 'folder-abc', 'test.txt', 'text/plain', $this->size, $this->s3Path);
        $this->assertEquals('folder-abc', $file->getFolderId());
    }

    public function test_create_extracts_extension_from_filename(): void
    {
        $file = StorageFile::create('uuid-4', 1, null, 'archive.tar.gz', 'application/gzip', $this->size, $this->s3Path);
        $this->assertEquals('gz', $file->getExtension());
    }

    public function test_rename_updates_name_and_extension(): void
    {
        $file = StorageFile::create('uuid-5', 1, null, 'old.pdf', 'application/pdf', $this->size, $this->s3Path);
        $file->rename('new.docx');

        $this->assertEquals('new.docx', $file->getOriginalName());
        $this->assertEquals('docx', $file->getExtension());
    }

    public function test_rename_with_empty_name_throws_exception(): void
    {
        $file = StorageFile::create('uuid-6', 1, null, 'name.pdf', 'application/pdf', $this->size, $this->s3Path);

        $this->expectException(\DomainException::class);
        $file->rename('');
    }

    public function test_move_to_folder(): void
    {
        $file = StorageFile::create('uuid-7', 1, null, 'file.pdf', 'application/pdf', $this->size, $this->s3Path);
        $file->moveTo('folder-xyz');

        $this->assertEquals('folder-xyz', $file->getFolderId());
    }

    public function test_move_to_root(): void
    {
        $file = StorageFile::create('uuid-8', 1, 'folder-old', 'file.pdf', 'application/pdf', $this->size, $this->s3Path);
        $file->moveTo(null);

        $this->assertNull($file->getFolderId());
    }

    public function test_set_and_get_checksum(): void
    {
        $file = StorageFile::create('uuid-9', 1, null, 'file.pdf', 'application/pdf', $this->size, $this->s3Path);
        $this->assertNull($file->getChecksum());

        $file->setChecksum('abc123def456');
        $this->assertEquals('abc123def456', $file->getChecksum());
    }

    public function test_belongs_to_user(): void
    {
        $file = StorageFile::create('uuid-10', 42, null, 'file.pdf', 'application/pdf', $this->size, $this->s3Path);

        $this->assertTrue($file->belongsToUser(42));
        $this->assertFalse($file->belongsToUser(99));
    }

    public function test_mime_type_is_mime_type_object(): void
    {
        $file = StorageFile::create('uuid-11', 1, null, 'image.png', 'image/png', $this->size, $this->s3Path);

        $this->assertTrue($file->getMimeType()->isImage());
        $this->assertFalse($file->getMimeType()->isDocument());
    }
}
