<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\ValueObjects;

use App\Domain\Storage\ValueObjects\MimeType;
use PHPUnit\Framework\TestCase;

final class MimeTypeTest extends TestCase
{
    public function test_can_create_from_valid_string(): void
    {
        $mime = MimeType::fromString('image/jpeg');
        $this->assertEquals('image/jpeg', $mime->getValue());
    }

    public function test_cannot_create_from_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MimeType::fromString('');
    }

    public function test_cannot_create_from_invalid_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MimeType::fromString('not-a-mime');
    }

    public function test_accepts_valid_complex_subtype(): void
    {
        $mime = MimeType::fromString('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertStringContainsString('officedocument', $mime->getValue());
    }

    public function test_matches_wildcard_pattern(): void
    {
        $mime = MimeType::fromString('image/png');
        $this->assertTrue($mime->matches('image/*'));
    }

    public function test_matches_exact_pattern(): void
    {
        $mime = MimeType::fromString('application/pdf');
        $this->assertTrue($mime->matches('application/pdf'));
    }

    public function test_does_not_match_different_pattern(): void
    {
        $mime = MimeType::fromString('audio/mpeg');
        $this->assertFalse($mime->matches('video/*'));
    }

    public function test_detects_image_types(): void
    {
        $this->assertTrue(MimeType::fromString('image/jpeg')->isImage());
        $this->assertTrue(MimeType::fromString('image/png')->isImage());
        $this->assertTrue(MimeType::fromString('image/gif')->isImage());
        $this->assertTrue(MimeType::fromString('image/webp')->isImage());
        $this->assertFalse(MimeType::fromString('video/mp4')->isImage());
    }

    public function test_detects_video_types(): void
    {
        $this->assertTrue(MimeType::fromString('video/mp4')->isVideo());
        $this->assertTrue(MimeType::fromString('video/quicktime')->isVideo());
        $this->assertFalse(MimeType::fromString('audio/mpeg')->isVideo());
    }

    public function test_detects_audio_types(): void
    {
        $this->assertTrue(MimeType::fromString('audio/mpeg')->isAudio());
        $this->assertTrue(MimeType::fromString('audio/ogg')->isAudio());
        $this->assertFalse(MimeType::fromString('image/png')->isAudio());
    }

    public function test_detects_document_types(): void
    {
        $this->assertTrue(MimeType::fromString('application/pdf')->isDocument());
        $this->assertTrue(MimeType::fromString('application/msword')->isDocument());
        $this->assertTrue(MimeType::fromString('application/vnd.openxmlformats-officedocument.wordprocessingml.document')->isDocument());
        $this->assertTrue(MimeType::fromString('application/vnd.ms-excel')->isDocument());
        $this->assertTrue(MimeType::fromString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')->isDocument());
        $this->assertTrue(MimeType::fromString('application/vnd.ms-powerpoint')->isDocument());
        $this->assertTrue(MimeType::fromString('application/vnd.openxmlformats-officedocument.presentationml.presentation')->isDocument());
        $this->assertFalse(MimeType::fromString('image/png')->isDocument());
    }

    public function test_to_string_returns_mime_string(): void
    {
        $mime = MimeType::fromString('text/plain');
        $this->assertEquals('text/plain', (string) $mime);
    }

    public function test_matches_with_subtype_wildcard(): void
    {
        $mime = MimeType::fromString('text/html');
        $this->assertTrue($mime->matches('text/*'));
    }
}
