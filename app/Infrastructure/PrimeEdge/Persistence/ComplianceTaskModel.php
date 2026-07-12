<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class ComplianceTaskModel extends Model
{
    protected $table = 'prime_edge_compliance_tasks';

    protected $fillable = [
        'id',
        'client_id',
        'type',
        'description',
        'due_date',
        'recurrence',
        'status',
        'reminder_days',
        'notes',
        'completed_at',
        'reminded_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_days' => 'integer',
        'completed_at' => 'datetime',
        'reminded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
