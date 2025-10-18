<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class RecognitionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'event_type',
        'event_date',
        'location',
        'is_virtual',
        'max_attendees',
        'registration_deadline',
        'eligibility_criteria',
        'awards',
        'agenda',
        'status',
        'budget',
        'spent_amount',
        'celebration_theme',
        'dress_code',
        'special_guests'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'is_virtual' => 'boolean',
        'eligibility_criteria' => 'array',
        'awards' => 'array',
        'agenda' => 'array',
        'budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'special_guests' => 'array'
    ];

    protected $attributes = [
        'eligibility_criteria' => '[]',
        'awards' => '[]',
        'agenda' => '[]',
        'special_guests' => '[]'
    ];

    // Relationships
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recognition_event_attendees')
                    ->withPivot(['registration_date', 'attendance_status', 'special_recognition', 'award_received'])
                    ->withTimestamps();
    }

    public function awardRecipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recognition_awards')
                    ->withPivot(['award_type', 'award_title', 'award_description', 'award_value', 'presented_at'])
                    ->withTimestamps();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    // Scopes
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('event_date', '>', now());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('event_date', '<', now());
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    public function scopeVirtual(Builder $query): Builder
    {
        return $query->where('is_virtual', true);
    }

    public function scopeInPerson(Builder $query): Builder
    {
        return $query->where('is_virtual', false);
    }

    public function scopeRegistrationOpen(Builder $query): Builder
    {
        return $query->where('registration_deadline', '>', now())
                    ->where('status', 'registration_open');
    }

    // Business Logic Methods
    public function isEligible(User $user): bool
    {
        if (empty($this->eligibility_criteria)) {
            return true;
        }

        foreach ($this->eligibility_criteria as $criterion) {
            if (!$this->meetsCriterion($user, $criterion)) {
                return false;
            }
        }

        return true;
    }

    public function registerAttendee(User $user): void
    {
        if (!$this->isEligible($user)) {
            throw new \Exception('User is not eligible for this recognition event.');
        }

        if ($this->registration_deadline && now()->gt($this->registration_deadline)) {
            throw new \Exception('Registration deadline has passed.');
        }

        if ($this->max_attendees && $this->attendees()->count() >= $this->max_attendees) {
            throw new \Exception('Event is at maximum capacity.');
        }

        $this->attendees()->syncWithoutDetaching([
            $user->id => [
                'registration_date' => now(),
                'attendance_status' => 'registered'
            ]
        ]);
    }

    public function markAttendance(User $user, string $status = 'attended'): void
    {
        $this->attendees()->updateExistingPivot($user->id, [
            'attendance_status' => $status
        ]);
    }

    public function awardRecognition(User $user, array $awardDetails): void
    {
        $this->awardRecipients()->syncWithoutDetaching([
            $user->id => [
                'award_type' => $awardDetails['type'],
                'award_title' => $awardDetails['title'],
                'award_description' => $awardDetails['description'],
                'award_value' => $awardDetails['value'] ?? 0,
                'presented_at' => now()
            ]
        ]);

        // Create certificate if applicable
        if ($awardDetails['certificate'] ?? false) {
            $this->createCertificate($user, $awardDetails);
        }

        // Award monetary value if applicable
        if (($awardDetails['value'] ?? 0) > 0) {
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $awardDetails['value'],
                'transaction_type' => 'recognition_award',
                'status' => 'completed',
                'description' => "Recognition award: {$awardDetails['title']} at {$this->name}",
                'reference_number' => 'REC-' . $this->id . '-' . $user->id . '-' . time(),
                'processed_at' => now()
            ]);

            $user->increment('total_earnings', $awardDetails['value']);
        }
    }

    public function generateEventReport(): array
    {
        $attendees = $this->attendees()->get();
        $awardRecipients = $this->awardRecipients()->get();

        return [
            'event_details' => [
                'name' => $this->name,
                'date' => $this->event_date,
                'type' => $this->event_type,
                'location' => $this->location,
                'is_virtual' => $this->is_virtual
            ],
            'attendance' => [
                'registered' => $attendees->where('pivot.attendance_status', 'registered')->count(),
                'attended' => $attendees->where('pivot.attendance_status', 'attended')->count(),
                'no_show' => $attendees->where('pivot.attendance_status', 'no_show')->count(),
                'total_capacity' => $this->max_attendees
            ],
            'awards' => [
                'total_recipients' => $awardRecipients->count(),
                'total_value' => $awardRecipients->sum('pivot.award_value'),
                'by_type' => $awardRecipients->groupBy('pivot.award_type')->map->count(),
                'certificates_issued' => $this->certificates()->count()
            ],
            'budget' => [
                'allocated' => $this->budget,
                'spent' => $this->spent_amount,
                'remaining' => $this->budget - $this->spent_amount
            ],
            'tier_distribution' => $attendees->groupBy(function ($user) {
                return $user->currentTier?->name ?? 'Bronze';
            })->map->count()
        ];
    }

    private function meetsCriterion(User $user, array $criterion): bool
    {
        $type = $criterion['type'];
        $value = $criterion['value'];
        $operator = $criterion['operator'] ?? '>=';

        $currentValue = match ($type) {
            'tier_level' => $this->getTierLevel($user->currentTier?->name ?? 'Bronze'),
            'total_earnings' => $user->total_earnings ?? 0,
            'achievement_count' => $user->userAchievements()->count(),
            'referral_count' => $user->referral_count ?? 0,
            'consecutive_months' => $user->getConsecutiveActiveMonths(),
            'leaderboard_position' => $this->getBestLeaderboardPosition($user),
            default => 0
        };

        return match ($operator) {
            '>=' => $currentValue >= $value,
            '>' => $currentValue > $value,
            '=' => $currentValue == $value,
            '<=' => $currentValue <= $value,
            '<' => $currentValue < $value,
            default => false
        };
    }

    private function getTierLevel(string $tierName): int
    {
        return match ($tierName) {
            'Bronze' => 1,
            'Silver' => 2,
            'Gold' => 3,
            'Diamond' => 4,
            'Elite' => 5,
            default => 0
        };
    }

    private function getBestLeaderboardPosition(User $user): int
    {
        $bestPosition = LeaderboardEntry::forUser($user)
            ->orderBy('position')
            ->value('position');

        return $bestPosition ?? 999;
    }

    private function createCertificate(User $user, array $awardDetails): void
    {
        Certificate::create([
            'recognition_event_id' => $this->id,
            'user_id' => $user->id,
            'certificate_type' => 'recognition_award',
            'title' => $awardDetails['title'],
            'description' => $awardDetails['description'],
            'issued_date' => now(),
            'template_data' => [
                'recipient_name' => $user->name,
                'award_title' => $awardDetails['title'],
                'event_name' => $this->name,
                'event_date' => $this->event_date->format('F j, Y'),
                'achievement_details' => $awardDetails['description']
            ]
        ]);
    }

    // Static methods for creating default events
    public static function createDefaultEvents(): array
    {
        $events = [
            [
                'name' => 'Annual MyGrowNet Gala',
                'slug' => 'annual-gala',
                'description' => 'Annual recognition gala celebrating top performers and community achievements',
                'event_type' => 'annual_gala',
                'event_date' => now()->addMonths(3)->setTime(18, 0),
                'location' => 'Lusaka Convention Center',
                'is_virtual' => false,
                'max_attendees' => 500,
                'registration_deadline' => now()->addMonths(2),
                'eligibility_criteria' => [
                    ['type' => 'tier_level', 'operator' => '>=', 'value' => 2], // Silver and above
                    ['type' => 'consecutive_months', 'operator' => '>=', 'value' => 6]
                ],
                'awards' => [
                    ['type' => 'top_performer', 'title' => 'Elite Achiever of the Year', 'value' => 50000],
                    ['type' => 'leadership', 'title' => 'Outstanding Leadership Award', 'value' => 25000],
                    ['type' => 'community', 'title' => 'Community Champion Award', 'value' => 15000],
                    ['type' => 'newcomer', 'title' => 'Rising Star Award', 'value' => 10000]
                ],
                'status' => 'planning',
                'budget' => 200000,
                'celebration_theme' => 'Growth & Excellence',
                'dress_code' => 'Formal/Business Attire'
            ],
            [
                'name' => 'Quarterly Recognition Ceremony',
                'slug' => 'quarterly-recognition',
                'description' => 'Quarterly virtual ceremony recognizing achievements and milestones',
                'event_type' => 'quarterly_ceremony',
                'event_date' => now()->addMonth()->endOfMonth()->setTime(19, 0),
                'location' => 'Virtual Event Platform',
                'is_virtual' => true,
                'max_attendees' => 1000,
                'registration_deadline' => now()->addMonth()->endOfMonth()->subDays(3),
                'eligibility_criteria' => [
                    ['type' => 'achievement_count', 'operator' => '>=', 'value' => 1]
                ],
                'awards' => [
                    ['type' => 'achievement', 'title' => 'Achievement Master', 'value' => 5000],
                    ['type' => 'referral', 'title' => 'Referral Champion', 'value' => 7500],
                    ['type' => 'education', 'title' => 'Learning Excellence', 'value' => 3000]
                ],
                'status' => 'registration_open',
                'budget' => 50000
            ],
            [
                'name' => 'Elite Members Exclusive Retreat',
                'slug' => 'elite-retreat',
                'description' => 'Exclusive retreat for Elite tier members with networking and recognition',
                'event_type' => 'exclusive_retreat',
                'event_date' => now()->addMonths(4)->setTime(9, 0),
                'location' => 'Victoria Falls Resort',
                'is_virtual' => false,
                'max_attendees' => 50,
                'registration_deadline' => now()->addMonths(3),
                'eligibility_criteria' => [
                    ['type' => 'tier_level', 'operator' => '=', 'value' => 5], // Elite only
                    ['type' => 'consecutive_months', 'operator' => '>=', 'value' => 12]
                ],
                'awards' => [
                    ['type' => 'elite_recognition', 'title' => 'Elite Excellence Certificate', 'value' => 0, 'certificate' => true]
                ],
                'status' => 'planning',
                'budget' => 150000,
                'special_guests' => ['Industry Leaders', 'Financial Experts', 'Motivational Speakers']
            ]
        ];

        $created = [];
        foreach ($events as $eventData) {
            $created[] = self::create($eventData);
        }

        return $created;
    }
}