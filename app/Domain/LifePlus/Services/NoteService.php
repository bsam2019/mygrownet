<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusNoteModel;

class NoteService
{
    public function getNotes(int $userId): array
    {
        return LifePlusNoteModel::where('user_id', $userId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($n) => $this->mapNote($n))
            ->toArray();
    }

    public function getNote(int $id, int $userId): ?array
    {
        $note = LifePlusNoteModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        return $note ? $this->mapNote($note) : null;
    }

    public function createNote(int $userId, array $data): array
    {
        $note = LifePlusNoteModel::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'is_pinned' => $data['is_pinned'] ?? false,
            'is_synced' => $data['is_synced'] ?? true,
            'local_id' => $data['local_id'] ?? null,
        ]);

        return $this->mapNote($note);
    }

    public function updateNote(int $id, int $userId, array $data): ?array
    {
        $note = LifePlusNoteModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$note) return null;

        $note->update($data);
        return $this->mapNote($note->fresh());
    }

    public function togglePin(int $id, int $userId): ?array
    {
        $note = LifePlusNoteModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$note) return null;

        $note->update(['is_pinned' => !$note->is_pinned]);
        return $this->mapNote($note->fresh());
    }

    public function deleteNote(int $id, int $userId): bool
    {
        return LifePlusNoteModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function syncNotes(int $userId, array $notes): array
    {
        $synced = [];
        foreach ($notes as $note) {
            if (!empty($note['local_id'])) {
                $existing = LifePlusNoteModel::where('user_id', $userId)
                    ->where('local_id', $note['local_id'])
                    ->first();

                if ($existing) {
                    $existing->update($note);
                    $synced[] = $this->mapNote($existing->fresh());
                } else {
                    $synced[] = $this->createNote($userId, $note);
                }
            } else {
                $synced[] = $this->createNote($userId, $note);
            }
        }
        return $synced;
    }

    private function mapNote($note): array
    {
        return [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'excerpt' => $note->content ? substr($note->content, 0, 100) . '...' : null,
            'is_pinned' => $note->is_pinned,
            'is_synced' => $note->is_synced,
            'local_id' => $note->local_id,
            'updated_at' => $note->updated_at->format('M d, Y'),
            'created_at' => $note->created_at->toISOString(),
        ];
    }
}
