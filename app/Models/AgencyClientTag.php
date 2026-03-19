<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\GrowBuilder\Models\Scopes\AgencyScope;

class AgencyClientTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'name',
        'color',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new AgencyScope());
        
        // Auto-set agency_id on creation
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->currentAgency) {
                $model->agency_id = auth()->user()->currentAgency->id;
            }
        });
    }

    /**
     * Get the agency
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get clients with this tag
     */
    public function clients()
    {
        return $this->belongsToMany(AgencyClient::class, 'agency_client_tag_map', 'tag_id', 'client_id')
            ->withTimestamps();
    }

    /**
     * Get clients count
     */
    public function getClientsCountAttribute(): int
    {
        return $this->clients()->count();
    }
}
