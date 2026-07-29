<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\ValueObjects;

use App\Domain\Storage\ValueObjects\S3Path;
use PHPUnit\Framework\TestCase;

final class S3PathTest extends TestCase
{
    public function test_can_create_with_valid_bucket_and_key(): void
    {
        $path = S3Path::create('my-bucket', 'path/to/file.txt');
        $this->assertEquals('my-bucket', $path->getBucket());
        $this->assertEquals('path/to/file.txt', $path->getKey());
    }

    public function test_cannot_create_with_empty_bucket(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        S3Path::create('', 'some/key');
    }

    public function test_cannot_create_with_empty_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        S3Path::create('my-bucket', '');
    }

    public function test_cannot_create_with_path_traversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        S3Path::create('my-bucket', '../etc/passwd');
    }

    public function test_cannot_create_with_double_dot_in_middle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        S3Path::create('my-bucket', 'foo/../../bar');
    }

    public function test_get_full_path_returns_bucket_slash_key(): void
    {
        $path = S3Path::create('my-bucket', 'path/file.txt');
        $this->assertEquals('my-bucket/path/file.txt', $path->getFullPath());
    }

    public function test_to_string_returns_full_path(): void
    {
        $path = S3Path::create('my-bucket', 'path/file.txt');
        $this->assertEquals('my-bucket/path/file.txt', (string) $path);
    }

    public function test_create_for_user_generates_date_based_path(): void
    {
        $year = date('Y');
        $month = date('m');
        $path = S3Path::forUser(42, 'photo.jpg', 'uploads');

        $this->assertEquals('uploads', $path->getBucket());
        $this->assertStringContainsString("users/user-42/{$year}/{$month}/photo.jpg", $path->getKey());
    }

    public function test_create_for_user_with_folder_path(): void
    {
        $year = date('Y');
        $month = date('m');
        $path = S3Path::forUser(42, 'document.pdf', 'uploads', 'contracts');

        $this->assertStringContainsString("users/user-42/{$year}/{$month}/contracts/document.pdf", $path->getKey());
    }

    public function test_sanitize_filename_removes_dangerous_chars(): void
    {
        $path = S3Path::forUser(1, '../../../etc/passwd', 'bucket');
        $this->assertStringNotContainsString('../', $path->getKey());
    }

    public function test_sanitize_filename_replaces_special_chars(): void
    {
        $path = S3Path::forUser(1, 'my file (1).png', 'bucket');
        $this->assertStringContainsString('my_file__1_.png', $path->getKey());
    }

    public function test_sanitize_filename_preserves_valid_chars(): void
    {
        $path = S3Path::forUser(1, 'report-2024_v2.final.pdf', 'bucket');
        $this->assertStringContainsString('report-2024_v2.final.pdf', $path->getKey());
    }
}
