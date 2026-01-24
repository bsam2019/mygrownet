<?php

namespace App\Infrastructure\Ubumi\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInModel extends Model
{
    use HasUuids;

    protected $table = 'ubumi_check_ins';

    protected $fillable = [
        'person_id',
        'status',
        'note',
        'location',
        'photo_url',
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonModel::class, 'person_id');
    }
}
