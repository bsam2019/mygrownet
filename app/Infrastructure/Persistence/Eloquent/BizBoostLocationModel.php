<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostLocationModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_locations';

    protected $fillable = [
        'business_id',
        'name',
        'address',
        'city',
        'phone',
        'whatsapp',
        'business_hours',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
