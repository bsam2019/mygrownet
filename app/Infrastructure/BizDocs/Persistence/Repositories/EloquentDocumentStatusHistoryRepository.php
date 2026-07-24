<?php

namespace App\Infrastructure\BizDocs\Persistence\Repositories;

use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentStatusHistoryRepositoryInterface;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentStatusHistoryModel;

class EloquentDocumentStatusHistoryRepository implements DocumentStatusHistoryRepositoryInterface
{
    public function record(int $documentId, ?string $fromStatus, string $toStatus, ?string $notes, ?int $changedBy): void
    {
        DocumentStatusHistoryModel::create([
            'document_id' => $documentId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }

    public function getByDocument(int $documentId): array
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