<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\IdVerification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdVerificationService
{
    /**
     * Submit ID verification documents
     */
    public function submitVerification(
        User $user,
        string $documentType,
        string $documentNumber,
        UploadedFile $documentFront,
        ?UploadedFile $documentBack = null,
        ?UploadedFile $selfie = null
    ): IdVerification {
        // Store document images
        $frontPath = $this->storeDocument($documentFront, $user->id, 'front');
        $backPath = $documentBack ? $this->storeDocument($documentBack, $user->id, 'back') : null;
        $selfiePath = $selfie ? $this->storeDocument($selfie, $user->id, 'selfie') : null;

        // Create verification record
        $verification = IdVerification::create([
            'user_id' => $user->id,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'document_front_path' => $frontPath,
            'document_back_path' => $backPath,
            'selfie_path' => $selfiePath,
            'status' => IdVerification::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        // Update user verification requirement status
        $user->update(['requires_id_verification' => false]);

        return $verification;
    }

    /**
     * Approve ID verification
     */
    public function approveVerification(IdVerification $verification, int $reviewedBy): void
    {
        $verification->approve($reviewedBy);

        // Log the approval
        AuditLog::logEvent(
            'id_verification_approved',
            $verification,
            $reviewedBy,
            ['status' => IdVerification::STATUS_PENDING],
            ['status' => IdVerification::STATUS_APPROVED]
        );
    }

    /**
     * Reject ID verification
     */
    public function rejectVerification(IdVerification $verification, string $reason, int $reviewedBy): void
    {
        $verification->reject($reason, $reviewedBy);

        // Update user to require verification again
        $verification->user->update(['requires_id_verification' => true]);

        // Log the rejection
        AuditLog::logEvent(
            'id_verification_rejected',
            $verification,
            $reviewedBy,
            ['status' => IdVerification::STATUS_PENDING],
            ['status' => IdVerification::STATUS_REJECTED],
            null,
            null,
            ['rejection_reason' => $reason]
        );
    }

    /**
     * Check if user needs ID verification
     */
    public function requiresVerification(User $user): bool
    {
        // New users always need verification
        if ($user->requires_id_verification) {
            return true;
        }

        // Check if current verification is expired
        $currentVerification = $user->idVerifications()
            ->approved()
            ->latest()
            ->first();

        if (!$currentVerification || $currentVerification->isExpired()) {
            $user->update([
                'requires_id_verification' => true,
                'is_id_verified' => false,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Get pending verifications for admin review
     */
    public function getPendingVerifications()
    {
        return IdVerification::pending()
            ->with(['user'])
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    /**
     * Check for duplicate document numbers
     */
    public function checkDuplicateDocument(string $documentType, string $documentNumber, ?int $excludeUserId = null): bool
    {
        $query = IdVerification::where('document_type', $documentType)
            ->where('document_number', $documentNumber)
            ->where('status', IdVerification::STATUS_APPROVED);

        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * Validate document number format
     */
    public function validateDocumentNumber(string $documentType, string $documentNumber): bool
    {
        return match ($documentType) {
            IdVerification::DOCUMENT_NATIONAL_ID => $this->validateNationalId($documentNumber),
            IdVerification::DOCUMENT_PASSPORT => $this->validatePassport($documentNumber),
            IdVerification::DOCUMENT_DRIVERS_LICENSE => $this->validateDriversLicense($documentNumber),
            default => false,
        };
    }

    /**
     * Store document file securely
     */
    private function storeDocument(UploadedFile $file, int $userId, string $type): string
    {
        $filename = sprintf(
            '%s_%s_%s_%s.%s',
            $userId,
            $type,
            now()->format('Y-m-d_H-i-s'),
            Str::random(8),
            $file->getClientOriginalExtension()
        );

        return $file->storeAs('id-verifications', $filename, 'private');
    }

    /**
     * Validate national ID format (example for Zambian format)
     */
    private function validateNationalId(string $documentNumber): bool
    {
        // Zambian NRC format: 123456/78/9
        return preg_match('/^\d{6}\/\d{2}\/\d{1}$/', $documentNumber);
    }

    /**
     * Validate passport format
     */
    private function validatePassport(string $documentNumber): bool
    {
        // Basic passport format: 2 letters followed by 6-9 digits
        return preg_match('/^[A-Z]{2}\d{6,9}$/', strtoupper($documentNumber));
    }

    /**
     * Validate driver's license format
     */
    private function validateDriversLicense(string $documentNumber): bool
    {
        // Basic format validation - adjust based on local requirements
        return strlen($documentNumber) >= 6 && strlen($documentNumber) <= 15;
    }

    /**
     * Clean up expired verification documents
     */
    public function cleanupExpiredDocuments(): int
    {
        $expiredVerifications = IdVerification::where('expires_at', '<', now()->subMonths(6))
            ->get();

        $cleanedCount = 0;

        foreach ($expiredVerifications as $verification) {
            // Delete files
            if ($verification->document_front_path) {
                Storage::disk('private')->delete($verification->document_front_path);
            }
            if ($verification->document_back_path) {
                Storage::disk('private')->delete($verification->document_back_path);
            }
            if ($verification->selfie_path) {
                Storage::disk('private')->delete($verification->selfie_path);
            }

            // Update verification record
            $verification->update([
                'status' => IdVerification::STATUS_EXPIRED,
                'document_front_path' => null,
                'document_back_path' => null,
                'selfie_path' => null,
            ]);

            $cleanedCount++;
        }

        return $cleanedCount;
    }
}