<?php

namespace Tests\Unit\Domain\LifePlus\Entities;

use App\Domain\LifePlus\Entities\LifePlusNote;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusNoteTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields()
    {
        $createdAt = new DateTimeImmutable('2026-08-10 08:00:00');
        $updatedAt = new DateTimeImmutable('2026-08-11 10:00:00');

        $note = LifePlusNote::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'title' => 'Meeting notes',
            'content' => 'Discuss Q3 goals',
            'is_pinned' => true,
            'is_synced' => false,
            'local_id' => 'local-abc',
            'created_at' => '2026-08-10 08:00:00',
            'updated_at' => '2026-08-11 10:00:00',
        ]);

        $this->assertSame(1, $note->id);
        $this->assertSame(42, $note->userId);
        $this->assertSame('Meeting notes', $note->title);
        $this->assertSame('Discuss Q3 goals', $note->content);
        $this->assertTrue($note->isPinned);
        $this->assertFalse($note->isSynced);
        $this->assertSame('local-abc', $note->localId);
        $this->assertEquals($createdAt, $note->createdAt);
        $this->assertEquals($updatedAt, $note->updatedAt);
    }

    #[Test]
    public function reconstitute_applies_defaults()
    {
        $note = LifePlusNote::reconstitute([
            'user_id' => 1,
            'title' => 'Quick note',
        ]);

        $this->assertNull($note->id);
        $this->assertNull($note->content);
        $this->assertFalse($note->isPinned);
        $this->assertTrue($note->isSynced);
        $this->assertNull($note->localId);
        $this->assertNull($note->createdAt);
        $this->assertNull($note->updatedAt);
    }

    #[Test]
    public function toArray_round_trips_all_fields()
    {
        $data = [
            'id' => 3,
            'user_id' => 7,
            'title' => 'Shopping list',
            'content' => 'Milk, eggs',
            'is_pinned' => true,
            'is_synced' => true,
            'local_id' => null,
            'created_at' => '2026-08-15 12:00:00',
            'updated_at' => null,
        ];

        $note = LifePlusNote::reconstitute($data);
        $result = $note->toArray();

        $this->assertSame($data['id'], $result['id']);
        $this->assertSame($data['user_id'], $result['user_id']);
        $this->assertSame($data['title'], $result['title']);
        $this->assertSame($data['content'], $result['content']);
        $this->assertSame($data['is_pinned'], $result['is_pinned']);
        $this->assertSame($data['is_synced'], $result['is_synced']);
        $this->assertNull($result['local_id']);
        $this->assertSame($data['created_at'], $result['created_at']);
        $this->assertNull($result['updated_at']);
    }
}
