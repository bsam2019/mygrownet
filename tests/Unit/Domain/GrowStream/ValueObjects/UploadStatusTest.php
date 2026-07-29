<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\UploadStatus;
use PHPUnit\Framework\TestCase;

class UploadStatusTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('pending', UploadStatus::Pending->value);
        $this->assertEquals('uploading', UploadStatus::Uploading->value);
        $this->assertEquals('processing', UploadStatus::Processing->value);
        $this->assertEquals('ready', UploadStatus::Ready->value);
        $this->assertEquals('failed', UploadStatus::Failed->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Pending', UploadStatus::Pending->label());
        $this->assertEquals('Uploading', UploadStatus::Uploading->label());
        $this->assertEquals('Processing', UploadStatus::Processing->label());
        $this->assertEquals('Ready', UploadStatus::Ready->label());
        $this->assertEquals('Failed', UploadStatus::Failed->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#f59e0b', UploadStatus::Pending->color());
        $this->assertEquals('#3b82f6', UploadStatus::Uploading->color());
        $this->assertEquals('#8b5cf6', UploadStatus::Processing->color());
        $this->assertEquals('#22c55e', UploadStatus::Ready->color());
        $this->assertEquals('#ef4444', UploadStatus::Failed->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(UploadStatus::Ready, UploadStatus::fromString('ready'));
        $this->assertSame(UploadStatus::Ready, UploadStatus::fromString('READY'));
        $this->assertSame(UploadStatus::Ready, UploadStatus::fromString('Ready'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UploadStatus::fromString('unknown');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(UploadStatus::Processing, UploadStatus::tryFrom('processing'));
        $this->assertNull(UploadStatus::tryFrom('bogus'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = UploadStatus::all();
        $this->assertCount(5, $all);
        $this->assertEquals('ready', $all[3]['value']);
        $this->assertEquals('Ready', $all[3]['label']);
    }
}
