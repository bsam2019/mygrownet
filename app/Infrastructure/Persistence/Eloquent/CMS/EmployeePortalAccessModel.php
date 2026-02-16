<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class EmployeePortalAccessModel extends Model
{
    protected $table = 'cms_employee_portal_access';

    protected $fillable = [
        'worker_id',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'reset_token',
        'reset_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'reset_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'reset_token_expires_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
