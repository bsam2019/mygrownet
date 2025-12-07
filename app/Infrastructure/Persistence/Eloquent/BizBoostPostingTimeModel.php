<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostPostingTimeModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_posting_times';

    protected $fillable = [
        'business_id',
        'day_type',
        'times',
    ];

    protected $casts = [
        'times' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
