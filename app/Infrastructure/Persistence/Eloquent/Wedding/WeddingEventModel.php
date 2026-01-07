<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class WeddingEventModel extends Model
{
    use HasFactory;

    protected $table = 'wedding_events';

    protected $fillable = [
        'user_id',
        'template_id',
        'slug',
        'groom_name',
        'bride_name',
        'partner_name',
        'partner_email',
        'partner_phone',
        'wedding_date',
        'venue_name',
        'venue_location',
        'ceremony_time',
        'reception_time',
        'reception_venue',
        'reception_address',
        'dress_code',
        'rsvp_deadline',
        'budget',
        'guest_count',
        'status',
        'is_published',
        'published_at',
        'notes',
        'preferences',
        'custom_settings',
        'hero_image',
        'story_image',
        'how_we_met',
        'proposal_story',
        'access_code',
    ];

    protected $casts = [
        'wedding_date' => 'date',
        'rsvp_deadline' => 'date',
        'published_at' => 'datetime',
        'budget' => 'decimal:2',
        'guest_count' => 'integer',
        'is_published' => 'boolean',
        'preferences' => 'array',
        'custom_settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WeddingTemplateModel::class, 'template_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(WeddingBookingModel::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(WeddingGuestModel::class, 'wedding_event_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planning', 'confirmed']);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('wedding_date', '>', now())
                    ->whereIn('status', ['planning', 'confirmed']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Generate slug from couple names and wedding date
     */
    public static function generateSlug(string $groomName, string $brideName, string $weddingDate): string
    {
        $date = \Carbon\Carbon::parse($weddingDate);
        $slug = strtolower(
            preg_replace('/[^a-z0-9]+/i', '-', 
                trim("{$groomName}-and-{$brideName}-{$date->format('M-Y')}")
            )
        );
        
        // Check for uniqueness and append number if needed
        $baseSlug = $slug;
        $counter = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        
        return $slug;
    }
}