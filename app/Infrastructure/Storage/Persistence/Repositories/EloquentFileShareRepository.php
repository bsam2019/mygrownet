<?php

namespace App\Infrastructure\Storage\Persistence\Repositories;

use App\Domain\Storage\Repositories\FileShareRepositoryInterface;
use App\Infrastructure\Storage\Persistence\Eloquent\FileShare;

class EloquentFileShareRepository implements FileShareRepositoryInterface
{
    public function findByFileId(string $fileId): array
    {
        return FileShare::where('file_id', $fileId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function findById(string $shareId): ?array
    {
        $share = FileShare::find($shareId);
        return $share ? $share->toArray() : null;
    }

    public function findByToken(string $token): ?array
    {
        $share = FileShare::where('share_token', $token)
            ->with('file')
            ->first();

        return $share ? $share->toArray() : null;
    }

    public function create(array $data): array
    {
        $share = FileShare::create($data);
        return $share->fresh()->toArray();
    }

    public function delete(string $shareId): void
    {
        $share = FileShare::find($shareId);
        if ($share) {
            $share->delete();
        }
    }

    public function incrementDownloadCount(string $shareId): void
    {
        FileShare::where('id', $shareId)->increment('download_count');
    }
}