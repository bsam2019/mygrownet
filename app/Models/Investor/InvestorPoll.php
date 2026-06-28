<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestorPoll extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'options',
        'poll_type',
        'start_date',
        'end_date',
        'status',
        'allow_multiple',
    ];

    protected $casts = [
        'options' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'allow_multiple' => 'boolean',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(InvestorPollVote::class, 'poll_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    public function getVoteCountAttribute(): int
    {
        return $this->votes()->count();
    }

    public function getResultsAttribute(): array
    {
        $results = [];
        $totalVotes = $this->votes()->count();

        foreach ($this->options as $index => $option) {
            $voteCount = $this->votes()
                ->whereJsonContains('selected_options', $index)
                ->count();
            
            $results[] = [
                'option' => $option,
                'votes' => $voteCount,
                'percentage' => $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0,
            ];
        }

        return $results;
    }
}
