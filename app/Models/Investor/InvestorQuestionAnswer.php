<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorQuestionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer',
        'answered_by',
        'answered_by_title',
        'answered_at',
        'is_official',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_official' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(InvestorQuestion::class, 'question_id');
    }
}
