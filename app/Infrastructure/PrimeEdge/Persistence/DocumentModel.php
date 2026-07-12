<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class DocumentModel extends Model
{
    protected $table = 'prime_edge_documents';

    protected $fillable = [
        'id',
        'client_id',
        'name',
        'type',
        'file_path',
        'mime_type',
        'file_size',
        'engagement_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
