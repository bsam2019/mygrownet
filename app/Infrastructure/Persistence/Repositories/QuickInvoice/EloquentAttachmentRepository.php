<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\QuickInvoice;

use App\Domain\QuickInvoice\Repositories\AttachmentRepositoryInterface;
use App\Models\QuickInvoice\QuickInvoiceAttachmentLibrary;

class EloquentAttachmentRepository implements AttachmentRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = QuickInvoiceAttachmentLibrary::find($id);
        return $model ? $model->toArray() : null;
    }

    public function findByUser(int $userId): array
    {
        return QuickInvoiceAttachmentLibrary::where('user_id', $userId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function create(array $data): array
    {
        $model = QuickInvoiceAttachmentLibrary::create($data);
        return $model->toArray();
    }

    public function update(int $id, array $data): ?array
    {
        $model = QuickInvoiceAttachmentLibrary::find($id);
        if (!$model) {
            return null;
        }

        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return QuickInvoiceAttachmentLibrary::destroy($id) > 0;
    }

    public function findByUserAndIds(int $userId, array $ids): array
    {
        return QuickInvoiceAttachmentLibrary::where('user_id', $userId)
            ->whereIn('id', $ids)
            ->get()
            ->toArray();
    }
}