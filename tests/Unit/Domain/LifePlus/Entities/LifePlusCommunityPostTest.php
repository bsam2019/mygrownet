<?php

namespace Tests\Unit\Domain\LifePlus\Entities;

use App\Domain\LifePlus\Entities\LifePlusCommunityPost;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusCommunityPostTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields()
    {
        $eventDate = new DateTimeImmutable('2026-09-01 14:00:00');
        $expiresAt = new DateTimeImmutable('2026-10-01 00:00:00');
        $createdAt = new DateTimeImmutable('2026-08-20 08:00:00');
        $updatedAt = new DateTimeImmutable('2026-08-21 10:00:00');

        $post = LifePlusCommunityPost::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'type' => 'event',
            'title' => 'Community clean-up',
            'content' => 'Join us Saturday',
            'location' => 'Town park',
            'event_date' => '2026-09-01 14:00:00',
            'image_url' => 'https://example.com/poster.jpg',
            'is_promoted' => true,
            'expires_at' => '2026-10-01 00:00:00',
            'created_at' => '2026-08-20 08:00:00',
            'updated_at' => '2026-08-21 10:00:00',
        ]);

        $this->assertSame(1, $post->id);
        $this->assertSame(42, $post->userId);
        $this->assertSame('event', $post->type);
        $this->assertSame('Community clean-up', $post->title);
        $this->assertSame('Join us Saturday', $post->content);
        $this->assertSame('Town park', $post->location);
        $this->assertEquals($eventDate, $post->eventDate);
        $this->assertSame('https://example.com/poster.jpg', $post->imageUrl);
        $this->assertTrue($post->isPromoted);
        $this->assertEquals($expiresAt, $post->expiresAt);
        $this->assertEquals($createdAt, $post->createdAt);
        $this->assertEquals($updatedAt, $post->updatedAt);
    }

    #[Test]
    public function reconstitute_applies_defaults()
    {
        $post = LifePlusCommunityPost::reconstitute([
            'user_id' => 1,
            'title' => 'Notice',
        ]);

        $this->assertNull($post->id);
        $this->assertSame('notice', $post->type);
        $this->assertNull($post->content);
        $this->assertNull($post->location);
        $this->assertNull($post->eventDate);
        $this->assertNull($post->imageUrl);
        $this->assertFalse($post->isPromoted);
        $this->assertNull($post->expiresAt);
        $this->assertNull($post->createdAt);
        $this->assertNull($post->updatedAt);
    }

    #[Test]
    public function toArray_round_trips_all_fields()
    {
        $data = [
            'id' => 3,
            'user_id' => 7,
            'type' => 'notice',
            'title' => 'Road closure',
            'content' => null,
            'location' => null,
            'event_date' => null,
            'image_url' => null,
            'is_promoted' => false,
            'expires_at' => null,
            'created_at' => '2026-08-19 12:00:00',
            'updated_at' => null,
        ];

        $post = LifePlusCommunityPost::reconstitute($data);
        $result = $post->toArray();

        $this->assertSame($data['id'], $result['id']);
        $this->assertSame($data['user_id'], $result['user_id']);
        $this->assertSame($data['type'], $result['type']);
        $this->assertSame($data['title'], $result['title']);
        $this->assertNull($result['content']);
        $this->assertNull($result['location']);
        $this->assertNull($result['event_date']);
        $this->assertNull($result['image_url']);
        $this->assertFalse($result['is_promoted']);
        $this->assertNull($result['expires_at']);
        $this->assertSame($data['created_at'], $result['created_at']);
        $this->assertNull($result['updated_at']);
    }
}
