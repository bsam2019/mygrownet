<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorshipGrant extends Model
{
    use HasFactory;

    protected $table = 'growstream_sponsorship_grants';

    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'amount',
        'currency',
        'milestones',
        'status',
        'rejection_reason',
        'reviewed_by',
        'allocated_at',
        'disbursed_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'milestones' => 'array',
        'allocated_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
