<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusGigApplicationModel extends Model
{
    protected $table = 'lifeplus_gig_applications';

    protected $fillable = [
        'gig_id',
        'user_id',
        'message',
        'status',
    ];

    public function gig(): BelongsTo
    {
        return $this->belongsTo(LifePlusGigModel::class, 'gig_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
