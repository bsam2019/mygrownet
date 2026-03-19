<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'user_id',
        'role_id',
        'status',
        'invited_by',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * Get the agency
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role
     */
    public function role()
    {
        return $this->belongsTo(AgencyRole::class, 'role_id');
    }

    /**
     * Get the user who invited this member
     */
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is invited but not joined
     */
    public function isPending(): bool
    {
        return $this->status === 'invited';
    }

    /**
     * Activate the user
     */
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    /**
     * Deactivate the user
     */
    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for invited users
     */
    public function scopeInvited($query)
    {
        return $query->where('status', 'invited');
    }
}
