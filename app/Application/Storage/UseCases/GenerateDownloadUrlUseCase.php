<?php

namespace App\Application\Storage\UseCases;

use App\Infrastructure\Storage\S3\S3StorageService;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;

class GenerateDownloadUrlUseCase
{
    public function __construct(
        private S3StorageService $s3Service,
        private StorageFileRepositoryInterface $fileRepository
    ) {}

    public function execute(string $fileId, int $userId): array
    {
        $file = $this->fileRepository->findById($fileId);

        if (!$file || $file->getUserId() !== $userId) {
            throw new \DomainException('File not found or access denied');
        }

        $downloadUrl = $this->s3Service->generatePresignedDownloadUrl(
            $file->getS3Path()->getKey()
        );

        return [
            'url' => $downloadUrl,
            'expires_in' => 900,
            'filename' => $file->getOriginalName(),
        ];
    }
}
