<?php

namespace App\Infrastructure\Ubumi\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Relationship Eloquent Model
 * 
 * Data layer representation of Relationship
 */
class RelationshipModel extends Model
{
    protected $table = 'ubumi_relationships';

    protected $fillable = [
        'person_id',
        'related_person_id',
        'relationship_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(PersonModel::class, 'person_id');
    }

    public function relatedPerson(): BelongsTo
    {
        return $this->belongsTo(PersonModel::class, 'related_person_id');
    }
}
