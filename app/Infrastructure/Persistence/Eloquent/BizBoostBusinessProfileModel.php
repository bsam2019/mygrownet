<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostBusinessProfileModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_business_profiles';

    protected $fillable = [
        'business_id',
        'hero_image_path',
        'about_image_path',
        'banner_image_path',
        'about',
        'business_story',
        'mission',
        'vision',
        'founding_year',
        'business_hours',
        'team_members',
        'achievements',
        'services',
        'testimonials',
        'tagline',
        'contact_email',
        'gallery_images',
        'seo_meta',
        'theme_settings',
        'show_products',
        'show_services',
        'show_gallery',
        'show_testimonials',
        'show_business_hours',
        'show_contact_form',
        'show_whatsapp_button',
        'show_social_links',
        'is_published',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'seo_meta' => 'array',
        'theme_settings' => 'array',
        'business_hours' => 'array',
        'team_members' => 'array',
        'achievements' => 'array',
        'services' => 'array',
        'testimonials' => 'array',
        'founding_year' => 'integer',
        'show_products' => 'boolean',
        'show_services' => 'boolean',
        'show_gallery' => 'boolean',
        'show_testimonials' => 'boolean',
        'show_business_hours' => 'boolean',
        'show_contact_form' => 'boolean',
        'show_whatsapp_button' => 'boolean',
        'show_social_links' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
