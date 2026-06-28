<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusUserProfileModel extends Model
{
    protected $table = 'lifeplus_user_profiles';

    protected $fillable = [
        'user_id',
        'location',
        'bio',
        'skills',
        'avatar_url',
    ];

    protected $casts = [
        'skills' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
