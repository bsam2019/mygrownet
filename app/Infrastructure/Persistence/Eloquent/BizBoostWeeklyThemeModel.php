<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostWeeklyThemeModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_weekly_themes';

    protected $fillable = [
        'business_id',
        'week_start',
        'theme',
        'description',
        'color',
    ];

    protected $casts = [
        'week_start' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
