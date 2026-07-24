<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Repositories\AttachmentRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class AttachmentLibraryService
{
    public function __construct(
        private readonly AttachmentRepositoryInterface $attachmentRepository
    ) {}

    public function getAttachments(int $userId): array
    {
        return array_map(function ($attachment) {
            return [
                'id' => $attachment['id'],
                'name' => $attachment['name'],
                'original_filename' => $attachment['original_filename'],
                'path' => $attachment['path'],
                'type' => $attachment['type'],
                'size' => $attachment['size'],
                'formatted_size' => $this->formatSize((int) ($attachment['size'] ?? 0)),
                'description' => $attachment['description'] ?? null,
                'is_image' => $this->isImage($attachment['type'] ?? ''),
                'is_pdf' => ($attachment['type'] ?? '') === 'application/pdf',
                'created_at' => isset($attachment['created_at']) ? date('Y-m-d H:i:s', strtotime($attachment['created_at'])) : null,
            ];
        }, $this->attachmentRepository->findByUser($userId));
    }

    public function saveAttachment(int $userId, string $name, $file, ?string $description = null): array
    {
        $path = $file->store('quick-invoice/library/' . $userId, 's3');

        return $this->attachmentRepository->create([
            'user_id' => $userId,
            'name' => $name,
            'original_filename' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'description' => $description,
        ]);
    }

    public function updateAttachment(int $id, int $userId, array $data): ?array
    {
        $attachment = $this->findUserAttachment($id, $userId);
        if (!$attachment) return null;

        return $this->attachmentRepository->update($id, $data);
    }

    public function deleteAttachment(int $id, int $userId): bool
    {
        $attachment = $this->findUserAttachment($id, $userId);
        if (!$attachment) return false;

        Storage::disk('s3')->delete($attachment['path']);
        return $this->attachmentRepository->delete($id);
    }

    public function downloadAttachment(int $id, int $userId): ?array
    {
        $attachment = $this->findUserAttachment($id, $userId);
        if (!$attachment) return null;

        $fileContent = Storage::disk('s3')->get($attachment['path']);

        return [
            'content' => $fileContent,
            'type' => $attachment['type'],
            'filename' => $attachment['original_filename'],
        ];
    }

    public function getByIds(int $userId, array $ids): array
    {
        return $this->attachmentRepository->findByUserAndIds($userId, $ids);
    }

    private function findUserAttachment(int $id, int $userId): ?array
    {
        $attachment = $this->attachmentRepository->findById($id);
        if (!$attachment || $attachment['user_id'] !== $userId) {
            return null;
        }
        return $attachment;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / (1024 * 1024), 1) . ' MB';
    }

    private function isImage(string $type): bool
    {
        return in_array($type, ['image/jpeg', 'image/jpg', 'image/png']);
    }
}