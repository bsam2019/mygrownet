<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrewMemberModel extends Model
{
    protected $table = 'cms_crew_members';

    protected $fillable = [
        'crew_id', 'employee_id', 'role', 'joined_date', 'left_date', 'status',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'left_date' => 'date',
    ];

    public function crew(): BelongsTo
    {
        return $this->belongsTo(CrewModel::class, 'crew_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }
}
