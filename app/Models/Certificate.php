<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'recognition_event_id',
        'user_id',
        'certificate_number',
        'certificate_type',
        'title',
        'description',
        'issued_date',
        'template_data',
        'pdf_path',
        'is_digital',
        'verification_code',
        'status'
    ];

    protected $casts = [
        'issued_date' => 'datetime',
        'template_data' => 'array',
        'is_digital' => 'boolean'
    ];

    protected $attributes = [
        'template_data' => '[]'
    ];

    protected static function boot()
    {
        parent::boot();

        // Temporarily disabled for memory diagnosis
        // static::creating(function ($certificate) {
        //     if (empty($certificate->certificate_number)) {
        //         $certificate->certificate_number = $certificate->generateCertificateNumber();
        //     }
        //     if (empty($certificate->verification_code)) {
        //         $certificate->verification_code = $certificate->generateVerificationCode();
        //     }
        // });
    }

    // Relationships
    public function recognitionEvent(): BelongsTo
    {
        return $this->belongsTo(RecognitionEvent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('certificate_type', $type);
    }

    public function scopeDigital(Builder $query): Builder
    {
        return $query->where('is_digital', true);
    }

    public function scopePhysical(Builder $query): Builder
    {
        return $query->where('is_digital', false);
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', 'issued');
    }

    // Business Logic Methods
    public function generatePDF(): string
    {
        // This would integrate with a PDF generation service
        // For now, we'll simulate the path
        $filename = "certificate_{$this->certificate_number}.pdf";
        $path = "certificates/{$this->user_id}/{$filename}";
        
        // In a real implementation, you would:
        // 1. Load a certificate template
        // 2. Fill in the template data
        // 3. Generate PDF using a library like TCPDF or DomPDF
        // 4. Store the PDF file
        // 5. Return the file path
        
        $this->update([
            'pdf_path' => $path,
            'status' => 'issued'
        ]);

        return $path;
    }

    public function verify(string $verificationCode): bool
    {
        return $this->verification_code === $verificationCode && $this->status === 'issued';
    }

    public function getPublicVerificationUrl(): string
    {
        return url("/certificates/verify/{$this->verification_code}");
    }

    public function getCertificateData(): array
    {
        return array_merge([
            'certificate_number' => $this->certificate_number,
            'recipient_name' => $this->user->name,
            'title' => $this->title,
            'description' => $this->description,
            'issued_date' => $this->issued_date->format('F j, Y'),
            'verification_code' => $this->verification_code,
            'verification_url' => $this->getPublicVerificationUrl()
        ], $this->template_data);
    }

    private function generateCertificateNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        
        // Use DB query to avoid model boot recursion
        $sequence = \DB::table('certificates')
            ->whereYear('created_at', $year)
            ->count() + 1;
        
        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "MGN-{$year}-{$month}-{$sequence}";
    }

    private function generateVerificationCode(): string
    {
        return strtoupper(Str::random(12));
    }

    // Static methods for certificate types
    public static function getCertificateTypes(): array
    {
        return [
            'achievement' => 'Achievement Certificate',
            'tier_advancement' => 'Tier Advancement Certificate',
            'course_completion' => 'Course Completion Certificate',
            'recognition_award' => 'Recognition Award Certificate',
            'leadership' => 'Leadership Excellence Certificate',
            'community_service' => 'Community Service Certificate',
            'mentorship' => 'Mentorship Certificate',
            'annual_recognition' => 'Annual Recognition Certificate'
        ];
    }

    public static function createAchievementCertificate(User $user, Achievement $achievement): self
    {
        return self::create([
            'user_id' => $user->id,
            'certificate_type' => 'achievement',
            'title' => "Achievement Certificate: {$achievement->name}",
            'description' => "Awarded for successfully earning the '{$achievement->name}' achievement in MyGrowNet",
            'issued_date' => now(),
            'template_data' => [
                'recipient_name' => $user->name,
                'achievement_name' => $achievement->name,
                'achievement_description' => $achievement->description,
                'achievement_category' => $achievement->category,
                'points_earned' => $achievement->points,
                'difficulty_level' => $achievement->difficulty_level,
                'tier_at_earning' => $user->currentTier?->name ?? 'Bronze'
            ],
            'is_digital' => true,
            'status' => 'pending'
        ]);
    }

    public static function createTierAdvancementCertificate(User $user, InvestmentTier $newTier): self
    {
        return self::create([
            'user_id' => $user->id,
            'certificate_type' => 'tier_advancement',
            'title' => "Tier Advancement Certificate: {$newTier->name}",
            'description' => "Awarded for successfully advancing to {$newTier->name} tier in MyGrowNet",
            'issued_date' => now(),
            'template_data' => [
                'recipient_name' => $user->name,
                'new_tier' => $newTier->name,
                'advancement_date' => now()->format('F j, Y'),
                'benefits_unlocked' => $newTier->benefits ?? [],
                'achievement_message' => "Congratulations on reaching {$newTier->name} tier!"
            ],
            'is_digital' => true,
            'status' => 'pending'
        ]);
    }
}