<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusNote;
use App\Domain\LifePlus\Repositories\NoteRepositoryInterface;
use App\Domain\LifePlus\Services\NoteService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NoteServiceTest extends TestCase
{
    private NoteRepositoryInterface $noteRepo;
    private NoteService $service;

    protected function setUp(): void
    {
        $this->noteRepo = $this->createMock(NoteRepositoryInterface::class);
        $this->service = new NoteService($this->noteRepo);
    }

    #[Test]
    public function getNotes_returns_mapped_notes()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'My note', 'content' => 'Hello world']);
        $this->noteRepo->expects($this->once())->method('findByUser')->with(42)->willReturn([$note]);

        $result = $this->service->getNotes(42);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('My note', $result[0]['title']);
        $this->assertStringEndsWith('...', $result[0]['excerpt']);
    }

    #[Test]
    public function getNote_returns_null_on_not_found()
    {
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);
        $this->assertNull($this->service->getNote(1, 42));
    }

    #[Test]
    public function getNote_returns_null_on_user_mismatch()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Other']);
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn($note);

        $this->assertNull($this->service->getNote(1, 42));
    }

    #[Test]
    public function getNote_returns_mapped_note()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Mine', 'content' => 'Secret']);
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn($note);

        $result = $this->service->getNote(1, 42);

        $this->assertSame('Mine', $result['title']);
        $this->assertSame('Secret', $result['content']);
    }

    #[Test]
    public function createNote_saves_and_returns_mapped()
    {
        $saved = LifePlusNote::reconstitute(['id' => 10, 'user_id' => 42, 'title' => 'New', 'content' => 'Content']);
        $this->noteRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createNote(42, ['title' => 'New', 'content' => 'Content']);

        $this->assertSame(10, $result['id']);
        $this->assertSame('New', $result['title']);
    }

    #[Test]
    public function updateNote_returns_null_on_not_found()
    {
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);
        $this->assertNull($this->service->updateNote(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updateNote_returns_null_on_user_mismatch()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Other']);
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn($note);

        $this->assertNull($this->service->updateNote(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updateNote_merges_and_saves()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Old', 'content' => 'Old content']);
        $updated = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Updated', 'content' => 'New content']);

        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn($note);
        $this->noteRepo->expects($this->once())->method('save')->willReturnCallback(function (LifePlusNote $n) use ($updated) {
            $this->assertSame('Updated', $n->title);
            return $updated;
        });

        $result = $this->service->updateNote(1, 42, ['title' => 'Updated', 'content' => 'New content']);

        $this->assertSame('Updated', $result['title']);
    }

    #[Test]
    public function togglePin_flips_pin_state()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Note', 'is_pinned' => false]);
        $toggled = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Note', 'is_pinned' => true]);

        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn($note);
        $this->noteRepo->expects($this->once())->method('save')->willReturn($toggled);

        $result = $this->service->togglePin(1, 42);
        $this->assertTrue($result['is_pinned']);
    }

    #[Test]
    public function togglePin_returns_null_on_not_found()
    {
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);
        $this->assertNull($this->service->togglePin(1, 42));
    }

    #[Test]
    public function deleteNote_returns_true_on_success()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Delete']);
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn($note);
        $this->noteRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->deleteNote(1, 42));
    }

    #[Test]
    public function deleteNote_returns_false_on_user_mismatch()
    {
        $note = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Other']);
        $this->noteRepo->expects($this->once())->method('findById')->with(1)->willReturn($note);

        $this->assertFalse($this->service->deleteNote(1, 42));
    }

    #[Test]
    public function syncNotes_creates_new_when_no_local_id()
    {
        $saved = LifePlusNote::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Synced']);
        $this->noteRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->syncNotes(42, [['title' => 'Synced']]);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function syncNotes_updates_existing_when_local_id_matches()
    {
        $existing = LifePlusNote::reconstitute(['id' => 5, 'user_id' => 42, 'title' => 'Old', 'local_id' => 'local-1']);
        $updated = LifePlusNote::reconstitute(['id' => 5, 'user_id' => 42, 'title' => 'Updated', 'local_id' => 'local-1']);

        $this->noteRepo->expects($this->once())->method('findByLocalId')->with(42, 'local-1')->willReturn($existing);
        $this->noteRepo->expects($this->once())->method('findById')->with(5)->willReturn($existing);
        $this->noteRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->syncNotes(42, [['title' => 'Updated', 'local_id' => 'local-1']]);

        $this->assertCount(1, $result);
    }
}
