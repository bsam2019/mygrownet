<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Infrastructure\GrowBuilder\Models\Scopes\AgencyScope;

class AgencyClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'client_code',
        'client_type',
        'client_name',
        'company_name',
        'email',
        'phone',
        'alternative_phone',
        'address',
        'city',
        'country',
        'status',
        'onboarding_status',
        'notes',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new AgencyScope());
        
        // Auto-set agency_id on creation
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->currentAgency) {
                $model->agency_id = auth()->user()->currentAgency->id;
            }
            
            // Auto-generate client code if not set
            if (!$model->client_code) {
                $model->client_code = static::generateClientCode($model->agency_id);
            }
        });
    }

    /**
     * Generate unique client code
     */
    protected static function generateClientCode(int $agencyId): string
    {
        $agency = Agency::find($agencyId);
        $agencyCode = 'AG' . str_pad($agencyId, 3, '0', STR_PAD_LEFT);
        
        // Get next client number for this agency
        $lastClient = static::withoutGlobalScope(AgencyScope::class)
            ->where('agency_id', $agencyId)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastClient ? (intval(substr($lastClient->client_code, -3)) + 1) : 1;
        
        return $agencyCode . '-CL' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get the agency
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get client contacts
     */
    public function contacts()
    {
        return $this->hasMany(AgencyClientContact::class, 'client_id');
    }

    /**
     * Get primary contact
     */
    public function primaryContact()
    {
        return $this->hasOne(AgencyClientContact::class, 'client_id')->where('is_primary', true);
    }

    /**
     * Get client tags
     */
    public function tags()
    {
        return $this->belongsToMany(AgencyClientTag::class, 'agency_client_tag_map', 'client_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * Get client sites
     */
    public function sites()
    {
        return $this->hasMany(\App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::class, 'client_id');
    }

    /**
     * Get client services
     */
    public function services()
    {
        return $this->hasMany(AgencyClientService::class, 'client_id');
    }

    /**
     * Get client invoices
     */
    public function invoices()
    {
        return $this->hasMany(AgencyClientInvoice::class, 'client_id');
    }

    /**
     * Check if client is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if client is a lead
     */
    public function isLead(): bool
    {
        return $this->status === 'lead';
    }

    /**
     * Activate client
     */
    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Suspend client
     */
    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    /**
     * Complete onboarding
     */
    public function completeOnboarding(): void
    {
        $this->update(['onboarding_status' => 'completed']);
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for leads
     */
    public function scopeLeads($query)
    {
        return $query->where('status', 'lead');
    }

    /**
     * Scope for specific client type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('client_type', $type);
    }
}
