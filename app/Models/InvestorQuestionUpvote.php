<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorQuestionUpvote extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'investor_account_id',
        'upvoted_at',
    ];

    protected $casts = [
        'upvoted_at' => 'datetime',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(InvestorQuestion::class, 'question_id');
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }
}
