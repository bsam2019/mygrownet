<?php

namespace App\Application\Storage\UseCases;

use App\Infrastructure\Storage\Persistence\Eloquent\StorageFile;
use App\Infrastructure\Storage\Persistence\Eloquent\FileShare;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;
use Illuminate\Support\Str;

class CreateFileShareUseCase
{
    public function execute(
        string $fileId,
        int $userId,
        ?string $password = null,
        ?int $expiresInDays = null,
        ?int $maxDownloads = null
    ): array {
        $file = StorageFile::findOrFail($fileId);
        
        // Authorization check
        if ($file->user_id !== $userId) {
            throw new \DomainException('You do not have permission to share this file');
        }
        
        // Check if user's plan allows sharing
        $subscription = UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->first();
        
        if (!$subscription || !$subscription->storagePlan->allow_sharing) {
            throw new \DomainException('Your current plan does not support file sharing. Please upgrade to Basic or higher.');
        }
        
        // Generate unique share token
        $shareToken = Str::random(32);
        
        // Calculate expiry
        $expiresAt = $expiresInDays ? now()->addDays($expiresInDays) : null;
        
        // Create share
        $share = FileShare::create([
            'file_id' => $fileId,
            'user_id' => $userId,
            'share_token' => $shareToken,
            'password' => $password ? bcrypt($password) : null,
            'expires_at' => $expiresAt,
            'max_downloads' => $maxDownloads,
            'is_active' => true,
            'allow_preview' => true,
        ]);
        
        return [
            'share_id' => $share->id,
            'share_token' => $shareToken,
            'share_url' => url("/share/{$shareToken}"),
            'expires_at' => $expiresAt?->toIso8601String(),
            'max_downloads' => $maxDownloads,
        ];
    }
}
