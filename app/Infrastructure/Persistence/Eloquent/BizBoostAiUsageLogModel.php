<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostAiUsageLogModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_ai_usage_logs';

    protected $fillable = [
        'business_id',
        'user_id',
        'content_type',
        'model',
        'input_tokens',
        'output_tokens',
        'credits_used',
        'request_params',
        'prompt',
        'response',
        'was_successful',
        'error_message',
    ];

    protected $casts = [
        'request_params' => 'array',
        'was_successful' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
