<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyClientContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'full_name',
        'email',
        'phone',
        'role_title',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the client
     */
    public function client()
    {
        return $this->belongsTo(AgencyClient::class, 'client_id');
    }

    /**
     * Set as primary contact
     */
    public function makePrimary(): void
    {
        // Remove primary flag from other contacts
        static::where('client_id', $this->client_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
        
        $this->update(['is_primary' => true]);
    }

    /**
     * Scope for primary contacts
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
