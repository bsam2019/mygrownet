<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\Services;

use App\Domain\Storage\Services\FileValidationService;
use App\Domain\Storage\ValueObjects\FileSize;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use PHPUnit\Framework\TestCase;

final class FileValidationServiceTest extends TestCase
{
    private FileValidationService $service;

    protected function setUp(): void
    {
        $this->service = new FileValidationService();
    }

    public function test_valid_upload_returns_no_errors(): void
    {
        $plan = $this->createStoragePlan(10485760, null);
        $errors = $this->service->validateUpload('photo.jpg', FileSize::fromMegabytes(1), 'image/jpeg', $plan);

        $this->assertEmpty($errors);
    }

    public function test_file_exceeding_max_size_returns_error(): void
    {
        $plan = $this->createStoragePlan(1024, null);
        $errors = $this->service->validateUpload('large.jpg', FileSize::fromMegabytes(10), 'image/jpeg', $plan);

        $this->assertContains('File exceeds maximum size of 1 KB', $errors);
    }

    public function test_disallowed_mime_type_returns_error(): void
    {
        $plan = $this->createStoragePlan(10485760, '["image/*","video/*"]');
        $errors = $this->service->validateUpload('script.exe', FileSize::fromMegabytes(1), 'application/x-msdownload', $plan);

        $this->assertContains('File type not allowed', $errors);
    }

    public function test_allowed_mime_type_passes_validation(): void
    {
        $plan = $this->createStoragePlan(10485760, '["image/*","video/*"]');
        $errors = $this->service->validateUpload('photo.png', FileSize::fromMegabytes(1), 'image/png', $plan);

        $this->assertEmpty($errors);
    }

    public function test_wildcard_mime_type_matches(): void
    {
        $plan = $this->createStoragePlan(10485760, '["application/pdf"]');
        $errors = $this->service->validateUpload('doc.pdf', FileSize::fromMegabytes(1), 'application/pdf', $plan);

        $this->assertEmpty($errors);
    }

    public function test_filename_with_path_traversal_returns_error(): void
    {
        $plan = $this->createStoragePlan(10485760, null);
        $errors = $this->service->validateUpload('../etc/passwd', FileSize::fromMegabytes(1), 'text/plain', $plan);

        $this->assertContains('Invalid filename', $errors);
    }

    public function test_filename_with_backslash_returns_error(): void
    {
        $plan = $this->createStoragePlan(10485760, null);
        $errors = $this->service->validateUpload('folder\\file.txt', FileSize::fromMegabytes(1), 'text/plain', $plan);

        $this->assertContains('Invalid filename', $errors);
    }

    public function test_empty_filename_returns_error(): void
    {
        $plan = $this->createStoragePlan(10485760, null);
        $errors = $this->service->validateUpload('   ', FileSize::fromMegabytes(1), 'text/plain', $plan);

        $this->assertContains('Invalid filename', $errors);
    }

    public function test_multiple_validation_errors_returned(): void
    {
        $plan = $this->createStoragePlan(100, '["image/*"]');
        $errors = $this->service->validateUpload('../bad.exe', FileSize::fromMegabytes(10), 'application/x-msdownload', $plan);

        $this->assertCount(3, $errors);
        $this->assertContains('File type not allowed', $errors);
        $this->assertContains('Invalid filename', $errors);
    }

    public function test_null_mime_types_means_no_restriction(): void
    {
        $plan = $this->createStoragePlan(10485760, null);
        $errors = $this->service->validateUpload('binary.bin', FileSize::fromBytes(100), 'application/octet-stream', $plan);

        $this->assertEmpty($errors);
    }

    private function createStoragePlan(int $maxFileSizeBytes, ?string $allowedMimeTypes): StoragePlan
    {
        $plan = $this->createMock(StoragePlan::class);
        $plan->method('__get')->willReturnMap([
            ['max_file_size_bytes', $maxFileSizeBytes],
            ['allowed_mime_types', $allowedMimeTypes],
        ]);
        return $plan;
    }
}
