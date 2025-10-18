<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class IdVerification extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    const DOCUMENT_NATIONAL_ID = 'national_id';
    const DOCUMENT_PASSPORT = 'passport';
    const DOCUMENT_DRIVERS_LICENSE = 'drivers_license';

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'document_front_path',
        'document_back_path',
        'selfie_path',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'expires_at',
        'verification_data',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
        'verification_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for pending verifications
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved verifications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for expired verifications
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere('expires_at', '<', now());
    }

    /**
     * Approve the verification
     */
    public function approve(?int $reviewedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'expires_at' => now()->addYears(2), // ID verification valid for 2 years
        ]);

        // Update user verification status
        $this->user->update([
            'is_id_verified' => true,
            'id_verified_at' => now(),
        ]);
    }

    /**
     * Reject the verification
     */
    public function reject(string $reason, ?int $reviewedBy = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
        ]);
    }

    /**
     * Check if verification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if verification is approved and not expired
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_APPROVED && !$this->isExpired();
    }

    /**
     * Get document front URL
     */
    public function getDocumentFrontUrlAttribute(): ?string
    {
        return $this->document_front_path ? Storage::url($this->document_front_path) : null;
    }

    /**
     * Get document back URL
     */
    public function getDocumentBackUrlAttribute(): ?string
    {
        return $this->document_back_path ? Storage::url($this->document_back_path) : null;
    }

    /**
     * Get selfie URL
     */
    public function getSelfieUrlAttribute(): ?string
    {
        return $this->selfie_path ? Storage::url($this->selfie_path) : null;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_EXPIRED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get available document types
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::DOCUMENT_NATIONAL_ID => 'National ID',
            self::DOCUMENT_PASSPORT => 'Passport',
            self::DOCUMENT_DRIVERS_LICENSE => 'Driver\'s License',
        ];
    }
}