<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareholderResolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'full_text',
        'resolution_type',
        'required_majority',
        'voting_start',
        'voting_end',
        'status',
        'document_path',
        'options',
    ];

    protected $casts = [
        'voting_start' => 'datetime',
        'voting_end' => 'datetime',
        'required_majority' => 'decimal:2',
        'options' => 'array',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(ShareholderVote::class, 'resolution_id');
    }

    public function proxyDelegations(): HasMany
    {
        return $this->hasMany(ProxyDelegation::class, 'resolution_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' 
            && now()->between($this->voting_start, $this->voting_end);
    }

    public function getVoteResults(): array
    {
        $votes = $this->votes;
        $totalVotingPower = $votes->sum('voting_power');
        
        return [
            'for' => [
                'count' => $votes->where('vote', 'for')->count(),
                'power' => $votes->where('vote', 'for')->sum('voting_power'),
                'percentage' => $totalVotingPower > 0 
                    ? round($votes->where('vote', 'for')->sum('voting_power') / $totalVotingPower * 100, 2) 
                    : 0,
            ],
            'against' => [
                'count' => $votes->where('vote', 'against')->count(),
                'power' => $votes->where('vote', 'against')->sum('voting_power'),
                'percentage' => $totalVotingPower > 0 
                    ? round($votes->where('vote', 'against')->sum('voting_power') / $totalVotingPower * 100, 2) 
                    : 0,
            ],
            'abstain' => [
                'count' => $votes->where('vote', 'abstain')->count(),
                'power' => $votes->where('vote', 'abstain')->sum('voting_power'),
                'percentage' => $totalVotingPower > 0 
                    ? round($votes->where('vote', 'abstain')->sum('voting_power') / $totalVotingPower * 100, 2) 
                    : 0,
            ],
            'total_votes' => $votes->count(),
            'total_voting_power' => $totalVotingPower,
            'passed' => $this->hasPassed(),
        ];
    }

    public function hasPassed(): bool
    {
        $forPercentage = $this->getVoteResults()['for']['percentage'];
        return $forPercentage >= $this->required_majority;
    }
}
