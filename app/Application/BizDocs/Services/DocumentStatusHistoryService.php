<?php

namespace App\Application\BizDocs\Services;

use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentStatusHistoryModel;

class DocumentStatusHistoryService
{
    public function recordStatusChange(
        int $documentId,
        ?string $fromStatus,
        string $toStatus,
        ?string $notes = null,
        ?int $changedBy = null
    ): void {
        DocumentStatusHistoryModel::create([
            'document_id' => $documentId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }

    public function getHistory(int $documentId): array
    {
        return DocumentStatusHistoryModel::where('document_id', $documentId)
            ->with('user:id,name')
            ->orderBy('changed_at', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'fromStatus' => $history->from_status,
                    'toStatus' => $history->to_status,
                    'notes' => $history->notes,
                    'changedBy' => $history->user?->name ?? 'System',
                    'changedAt' => $history->changed_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }
}
