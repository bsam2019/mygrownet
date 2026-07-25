<?php

namespace App\Domain\GrowFinance\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptStorageService
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    private const MAX_FILE_SIZE_MB = 10;

    /**
     * Upload a receipt file
     */
    public function upload(UploadedFile $file, User $user, string $type = 'expense'): array
    {
        // Validate file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return [
                'success' => false,
                'error' => 'Invalid file type. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS),
            ];
        }

        // Check file size
        $fileSizeBytes = $file->getSize();
        $maxSizeBytes = self::MAX_FILE_SIZE_MB * 1024 * 1024;
        if ($fileSizeBytes > $maxSizeBytes) {
            return [
                'success' => false,
                'error' => 'File too large. Maximum size: ' . self::MAX_FILE_SIZE_MB . 'MB',
            ];
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;
        $path = "growfinance/receipts/{$user->id}/{$type}/{$filename}";

        // Store file
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        return [
            'success' => true,
            'path' => $path,
            'size' => $fileSizeBytes,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Delete a receipt file
     */
    public function delete(string $path, User $user): bool
    {
        // Verify the path belongs to this user
        if (!str_contains($path, "growfinance/receipts/{$user->id}/")) {
            return false;
        }

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            return true;
        }

        return false;
    }

    /**
     * Get receipt URL for viewing
     */
    public function getUrl(string $path): ?string
    {
        if (Storage::disk('local')->exists($path)) {
            return route('growfinance.receipts.view', ['path' => base64_encode($path)]);
        }
        return null;
    }

    /**
     * Get receipt contents for download
     */
    public function getContents(string $path, User $user): ?string
    {
        // Verify the path belongs to this user
        if (!str_contains($path, "growfinance/receipts/{$user->id}/")) {
            return null;
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->get($path);
        }

        return null;
    }

    /**
     * Calculate total storage used by user
     */
    public function getUsedStorage(User $user): int
    {
        $path = "growfinance/receipts/{$user->id}";
        
        if (!Storage::disk('local')->exists($path)) {
            return 0;
        }

        $totalSize = 0;
        $files = Storage::disk('local')->allFiles($path);
        
        foreach ($files as $file) {
            $totalSize += Storage::disk('local')->size($file);
        }

        return $totalSize;
    }
}
