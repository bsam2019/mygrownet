<?php

namespace App\Infrastructure\Ubumi\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Person Eloquent Model
 * 
 * Data layer representation of Person
 */
class PersonModel extends Model
{
    use SoftDeletes;

    protected $table = 'ubumi_persons';

    protected $fillable = [
        'id',
        'family_id',
        'name',
        'slug',
        'photo_url',
        'date_of_birth',
        'approximate_age',
        'gender',
        'is_deceased',
        'is_merged',
        'merged_from',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'datetime',
        'approximate_age' => 'integer',
        'is_deceased' => 'boolean',
        'is_merged' => 'boolean',
        'merged_from' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function family(): BelongsTo
    {
        return $this->belongsTo(FamilyModel::class, 'family_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
