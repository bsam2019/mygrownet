<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectiveActionModel extends Model
{
    protected $table = 'cms_corrective_actions';

    protected $fillable = [
        'ncr_id',
        'action_description',
        'action_type',
        'responsible_person',
        'target_date',
        'completed_date',
        'status',
        'verification_notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_date' => 'date',
    ];

    public function ncr(): BelongsTo
    {
        return $this->belongsTo(NonConformanceModel::class, 'ncr_id');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person');
    }
}
