<?php

namespace App\Infrastructure\Persistence\Eloquent\Workshop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class WorkshopModel extends Model
{
    use SoftDeletes;

    protected $table = 'workshops';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'delivery_format',
        'price',
        'max_participants',
        'lp_reward',
        'bp_reward',
        'start_date',
        'end_date',
        'location',
        'meeting_link',
        'requirements',
        'learning_outcomes',
        'instructor_name',
        'instructor_bio',
        'featured_image',
        'status',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(WorkshopRegistrationModel::class, 'workshop_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WorkshopSessionModel::class, 'workshop_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registeredCount(): int
    {
        return $this->registrations()->whereIn('status', ['registered', 'attended', 'completed'])->count();
    }

    public function availableSlots(): ?int
    {
        if ($this->max_participants === null) {
            return null;
        }
        return max(0, $this->max_participants - $this->registeredCount());
    }
}
