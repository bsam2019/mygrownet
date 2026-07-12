<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureResolutionModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'venture_resolutions';

    protected $fillable = [
        'venture_id',
        'title',
        'description',
        'type',
        'status',
        'voting_starts_at',
        'voting_ends_at',
        'pass_threshold_percentage',
        'votes_for',
        'votes_against',
        'votes_abstain',
        'total_voted_equity',
        'result_notes',
        'created_by',
    ];

    protected $casts = [
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'pass_threshold_percentage' => 'decimal:2',
        'votes_for' => 'decimal:4',
        'votes_against' => 'decimal:4',
        'votes_abstain' => 'decimal:4',
        'total_voted_equity' => 'decimal:4',
    ];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(VentureModel::class, 'venture_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(VentureVoteModel::class, 'resolution_id');
    }

    public function scopeVoting($query)
    {
        return $query->where('status', 'voting');
    }

    public function isVoting(): bool
    {
        return $this->status === 'voting';
    }

    public function isOpenForVoting(): bool
    {
        return $this->status === 'voting'
            && (!$this->voting_starts_at || $this->voting_starts_at->isPast())
            && (!$this->voting_ends_at || $this->voting_ends_at->isFuture());
    }
}
