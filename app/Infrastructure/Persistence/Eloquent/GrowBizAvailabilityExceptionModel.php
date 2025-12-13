<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizAvailabilityExceptionModel extends Model
{
    protected $table = 'growbiz_availability_exceptions';

    protected $fillable = [
        'user_id',
        'provider_id',
        'date',
        'type',
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(GrowBizServiceProviderModel::class, 'provider_id');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'closed' => 'Closed',
            'modified_hours' => 'Modified Hours',
            'extra_availability' => 'Extra Availability',
            default => $this->type,
        };
    }
}
