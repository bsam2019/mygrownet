<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationTeamMemberModel extends Model
{
    protected $table = 'cms_installation_team_members';

    protected $fillable = [
        'installation_schedule_id',
        'user_id',
        'role',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(InstallationScheduleModel::class, 'installation_schedule_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
