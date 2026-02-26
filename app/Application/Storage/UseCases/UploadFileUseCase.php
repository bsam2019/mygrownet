<?php

namespace App\Application\Storage\UseCases;

use App\Domain\Storage\Entities\StorageFile;
use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\S3Path;
use App\Domain\Storage\Services\QuotaEnforcementService;
use App\Domain\Storage\Services\FileValidationService;
use App\Infrastructure\Storage\S3\S3StorageService;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;
use Illuminate\Support\Str;

class UploadFileUseCase
{
    public function __construct(
        private QuotaEnforcementService $quotaService,
        private FileValidationService $validationService,
        private S3StorageService $s3Service,
        private StorageFileRepositoryInterface $fileRepository
    ) {}

    public function initiate(
        int $userId,
        ?string $folderId,
        string $filename,
        int $sizeBytes,
        string $mimeType
    ): array {
        // Get user's storage plan
        $subscription = $this->getActiveSubscription($userId);

        // Validate file
        $fileSize = FileSize::fromBytes($sizeBytes);
        $errors = $this->validationService->validateUpload(
            $filename,
            $fileSize,
            $mimeType,
            $subscription->storagePlan
        );

        if (!empty($errors)) {
            throw new \DomainException(implode(', ', $errors));
        }

        // Check quota
        if (!$this->quotaService->canUpload($userId, $fileSize)) {
            throw new \DomainException('Storage quota exceeded');
        }

        // Get folder path for S3 key
        $folderPath = $this->getFolderPath($folderId);

        // Generate S3 path
        $s3Path = S3Path::forUser(
            $userId,
            $filename,
            $this->s3Service->getBucket(),
            $folderPath
        );

        // Create domain entity
        $file = StorageFile::create(
            Str::uuid()->toString(),
            $userId,
            $folderId,
            $filename,
            $mimeType,
            $fileSize,
            $s3Path
        );

        // Save to repository
        $this->fileRepository->save($file);

        // Generate presigned upload URL
        $uploadUrl = $this->s3Service->generatePresignedUploadUrl(
            $s3Path->getKey(),
            $mimeType
        );

        return [
            'file_id' => $file->getId(),
            'upload_url' => $uploadUrl,
            's3_key' => $s3Path->getKey(),
            'expires_in' => 900,
        ];
    }

    private function getFolderPath(?string $folderId): ?string
    {
        if (!$folderId) {
            return null;
        }

        // Get folder and build path from root
        $folder = \App\Infrastructure\Storage\Persistence\Eloquent\StorageFolder::find($folderId);
        if (!$folder) {
            return null;
        }

        // Use cached path if available
        if ($folder->path_cache) {
            return $folder->path_cache;
        }

        // Build path from folder hierarchy
        $path = [];
        $current = $folder;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent_id ? \App\Infrastructure\Storage\Persistence\Eloquent\StorageFolder::find($current->parent_id) : null;
        }

        return implode('/', $path);
    }

    public function complete(string $fileId, int $userId): void
    {
        \Log::info('Upload complete use case started', [
            'file_id' => $fileId,
            'user_id' => $userId,
        ]);

        $file = $this->fileRepository->findById($fileId);

        if (!$file) {
            \Log::error('File not found in repository', ['file_id' => $fileId]);
            throw new \DomainException('File not found');
        }

        if ($file->getUserId() !== $userId) {
            \Log::error('Access denied - user mismatch', [
                'file_id' => $fileId,
                'file_user_id' => $file->getUserId(),
                'request_user_id' => $userId,
            ]);
            throw new \DomainException('Access denied');
        }

        $s3Key = $file->getS3Path()->getKey();
        \Log::info('Checking if file exists in S3', ['s3_key' => $s3Key]);

        // Verify S3 object exists (with retry for eventual consistency)
        $maxRetries = 3;
        $retryDelay = 500000; // 0.5 seconds in microseconds
        $fileExists = false;
        
        for ($i = 0; $i < $maxRetries; $i++) {
            if ($this->s3Service->fileExists($s3Key)) {
                $fileExists = true;
                break;
            }
            
            if ($i < $maxRetries - 1) {
                \Log::info('File not found in S3, retrying...', [
                    'attempt' => $i + 1,
                    's3_key' => $s3Key,
                ]);
                usleep($retryDelay);
            }
        }
        
        if (!$fileExists) {
            \Log::error('File not found in S3 after retries', [
                's3_key' => $s3Key,
                'file_id' => $fileId,
                'retries' => $maxRetries,
            ]);
            $this->fileRepository->delete($file);
            throw new \DomainException('File not found in storage');
        }

        \Log::info('File exists in S3, updating usage', [
            's3_key' => $s3Key,
            'size_bytes' => $file->getSize()->toBytes(),
        ]);

        // Update usage
        $this->quotaService->incrementUsage($userId, $file->getSize());

        \Log::info('Upload completed successfully', [
            'file_id' => $fileId,
            'filename' => $file->getOriginalName(),
        ]);

        // Dispatch domain event
        event(new \App\Domain\Storage\Events\FileUploaded(
            $file->getId(),
            $userId,
            $file->getSize()->toBytes(),
            $file->getMimeType()->getValue(),
            new \DateTimeImmutable()
        ));
    }

    private function getActiveSubscription(int $userId): UserStorageSubscription
    {
        return UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->firstOrFail();
    }
}
