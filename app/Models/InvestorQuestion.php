<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvestorQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_account_id',
        'subject',
        'question',
        'category',
        'status',
        'is_public',
        'upvotes',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InvestorQuestionAnswer::class, 'question_id');
    }

    public function latestAnswer(): HasOne
    {
        return $this->hasOne(InvestorQuestionAnswer::class, 'question_id')->latestOfMany();
    }

    public function upvotedBy(): HasMany
    {
        return $this->hasMany(InvestorQuestionUpvote::class, 'question_id');
    }

    public function hasBeenUpvotedBy(int $investorAccountId): bool
    {
        return $this->upvotedBy()->where('investor_account_id', $investorAccountId)->exists();
    }
}
