<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusNote;
use App\Domain\LifePlus\Repositories\NoteRepositoryInterface;

class NoteService
{
    public function __construct(
        private readonly NoteRepositoryInterface $noteRepo,
    ) {}

    public function getNotes(int $userId): array
    {
        return array_map(fn($n) => $this->mapNote($n), $this->noteRepo->findByUser($userId));
    }

    public function getNote(int $id, int $userId): ?array
    {
        $note = $this->noteRepo->findById($id);
        if (!$note || $note->userId !== $userId) return null;
        return $this->mapNote($note);
    }

    public function createNote(int $userId, array $data): array
    {
        $note = LifePlusNote::reconstitute([
            'user_id' => $userId,
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'is_pinned' => $data['is_pinned'] ?? false,
            'is_synced' => $data['is_synced'] ?? true,
            'local_id' => $data['local_id'] ?? null,
        ]);

        return $this->mapNote($this->noteRepo->save($note));
    }

    public function updateNote(int $id, int $userId, array $data): ?array
    {
        $note = $this->noteRepo->findById($id);
        if (!$note || $note->userId !== $userId) return null;

        $merged = array_merge($note->toArray(), $data);
        return $this->mapNote($this->noteRepo->save(LifePlusNote::reconstitute($merged)));
    }

    public function togglePin(int $id, int $userId): ?array
    {
        $note = $this->noteRepo->findById($id);
        if (!$note || $note->userId !== $userId) return null;

        $merged = $note->toArray();
        $merged['is_pinned'] = !$note->isPinned;
        return $this->mapNote($this->noteRepo->save(LifePlusNote::reconstitute($merged)));
    }

    public function deleteNote(int $id, int $userId): bool
    {
        $note = $this->noteRepo->findById($id);
        if (!$note || $note->userId !== $userId) return false;
        return $this->noteRepo->delete($id);
    }

    public function syncNotes(int $userId, array $notes): array
    {
        $synced = [];
        foreach ($notes as $note) {
            if (!empty($note['local_id'])) {
                $existing = $this->noteRepo->findByLocalId($userId, $note['local_id']);
                if ($existing) {
                    $synced[] = $this->updateNote($existing->id, $userId, $note);
                } else {
                    $synced[] = $this->createNote($userId, $note);
                }
            } else {
                $synced[] = $this->createNote($userId, $note);
            }
        }
        return array_filter($synced);
    }

    private function mapNote(LifePlusNote $note): array
    {
        return [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'excerpt' => $note->content ? substr($note->content, 0, 100) . '...' : null,
            'is_pinned' => $note->isPinned,
            'is_synced' => $note->isSynced,
            'local_id' => $note->localId,
        ];
    }
}
