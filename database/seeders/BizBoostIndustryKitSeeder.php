<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BizBoostIndustryKitSeeder extends Seeder
{
    public function run(): void
    {
        // Industry kits are stored as templates with industry-specific content
        $industryKits = [
            // Salon Industry Kit
            'salon' => [
                [
                    'name' => 'Salon Services Menu',
                    'slug' => 'salon-services-menu',
                    'description' => 'Complete price list for salon services',
                    'category' => 'pricing',
                ],
                [
                    'name' => 'Hair Styling Promo',
                    'slug' => 'salon-hair-promo',
                    'description' => 'Promote hair styling services',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Nail Art Showcase',
                    'slug' => 'salon-nail-showcase',
                    'description' => 'Display nail art designs',
                    'category' => 'social',
                ],
                [
                    'name' => 'Salon Appointment Reminder',
                    'slug' => 'salon-appointment',
                    'description' => 'Remind customers about appointments',
                    'category' => 'social',
                ],
                [
                    'name' => 'Bridal Package',
                    'slug' => 'salon-bridal',
                    'description' => 'Promote bridal beauty packages',
                    'category' => 'promotion',
                ],
            ],
            
            // Restaurant Industry Kit
            'restaurant' => [
                [
                    'name' => 'Daily Special',
                    'slug' => 'restaurant-daily-special',
                    'description' => 'Highlight today\'s special dish',
                    'category' => 'menu',
                ],
                [
                    'name' => 'Weekend Brunch',
                    'slug' => 'restaurant-brunch',
                    'description' => 'Promote weekend brunch menu',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Delivery Available',
                    'slug' => 'restaurant-delivery',
                    'description' => 'Announce delivery services',
                    'category' => 'social',
                ],
                [
                    'name' => 'Happy Hour',
                    'slug' => 'restaurant-happy-hour',
                    'description' => 'Promote happy hour deals',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Customer Review',
                    'slug' => 'restaurant-review',
                    'description' => 'Share customer testimonials',
                    'category' => 'social',
                ],
            ],
            
            // Boutique Industry Kit
            'boutique' => [
                [
                    'name' => 'New Arrivals',
                    'slug' => 'boutique-new-arrivals',
                    'description' => 'Showcase new fashion items',
                    'category' => 'social',
                ],
                [
                    'name' => 'Seasonal Sale',
                    'slug' => 'boutique-seasonal-sale',
                    'description' => 'Announce seasonal discounts',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Outfit of the Day',
                    'slug' => 'boutique-ootd',
                    'description' => 'Daily outfit inspiration',
                    'category' => 'social',
                ],
                [
                    'name' => 'Size Guide',
                    'slug' => 'boutique-size-guide',
                    'description' => 'Help customers find their size',
                    'category' => 'social',
                ],
                [
                    'name' => 'Flash Sale',
                    'slug' => 'boutique-flash-sale',
                    'description' => 'Limited time offers',
                    'category' => 'promotion',
                ],
            ],
            
            // Barbershop Industry Kit
            'barbershop' => [
                [
                    'name' => 'Haircut Styles',
                    'slug' => 'barber-styles',
                    'description' => 'Showcase available haircut styles',
                    'category' => 'social',
                ],
                [
                    'name' => 'Price List',
                    'slug' => 'barber-prices',
                    'description' => 'Display service prices',
                    'category' => 'pricing',
                ],
                [
                    'name' => 'Walk-ins Welcome',
                    'slug' => 'barber-walkins',
                    'description' => 'Announce availability',
                    'category' => 'social',
                ],
                [
                    'name' => 'Beard Grooming',
                    'slug' => 'barber-beard',
                    'description' => 'Promote beard services',
                    'category' => 'social',
                ],
                [
                    'name' => 'Loyalty Card',
                    'slug' => 'barber-loyalty',
                    'description' => 'Promote loyalty program',
                    'category' => 'promotion',
                ],
            ],
            
            // Grocery Industry Kit
            'grocery' => [
                [
                    'name' => 'Weekly Specials',
                    'slug' => 'grocery-weekly',
                    'description' => 'Highlight weekly deals',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Fresh Produce',
                    'slug' => 'grocery-fresh',
                    'description' => 'Showcase fresh items',
                    'category' => 'social',
                ],
                [
                    'name' => 'Bulk Deals',
                    'slug' => 'grocery-bulk',
                    'description' => 'Promote bulk buying discounts',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Delivery Service',
                    'slug' => 'grocery-delivery',
                    'description' => 'Announce delivery options',
                    'category' => 'social',
                ],
                [
                    'name' => 'Store Hours',
                    'slug' => 'grocery-hours',
                    'description' => 'Display operating hours',
                    'category' => 'social',
                ],
            ],
            
            // Photography Industry Kit
            'photography' => [
                [
                    'name' => 'Portfolio Showcase',
                    'slug' => 'photo-portfolio',
                    'description' => 'Display your best work',
                    'category' => 'social',
                ],
                [
                    'name' => 'Booking Available',
                    'slug' => 'photo-booking',
                    'description' => 'Announce availability',
                    'category' => 'social',
                ],
                [
                    'name' => 'Wedding Package',
                    'slug' => 'photo-wedding',
                    'description' => 'Promote wedding photography',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Mini Session',
                    'slug' => 'photo-mini-session',
                    'description' => 'Offer mini photo sessions',
                    'category' => 'promotion',
                ],
                [
                    'name' => 'Behind the Scenes',
                    'slug' => 'photo-bts',
                    'description' => 'Share your process',
                    'category' => 'social',
                ],
            ],
            
            // Mobile Money Industry Kit
            'mobile_money' => [
                [
                    'name' => 'Services Available',
                    'slug' => 'momo-services',
                    'description' => 'List available services',
                    'category' => 'social',
                ],
                [
                    'name' => 'Float Available',
                    'slug' => 'momo-float',
                    'description' => 'Announce float availability',
                    'category' => 'social',
                ],
                [
                    'name' => 'Operating Hours',
                    'slug' => 'momo-hours',
                    'description' => 'Display business hours',
                    'category' => 'social',
                ],
                [
                    'name' => 'Location Map',
                    'slug' => 'momo-location',
                    'description' => 'Help customers find you',
                    'category' => 'social',
                ],
                [
                    'name' => 'Commission Rates',
                    'slug' => 'momo-rates',
                    'description' => 'Display transaction fees',
                    'category' => 'pricing',
                ],
            ],
        ];

        foreach ($industryKits as $industry => $templates) {
            foreach ($templates as $template) {
                DB::table('bizboost_templates')->updateOrInsert(
                    ['slug' => $template['slug']],
                    [
                        'name' => $template['name'],
                        'slug' => $template['slug'],
                        'description' => $template['description'],
                        'category' => $template['category'],
                        'industry' => $industry,
                        'template_data' => json_encode([
                            'type' => $template['category'],
                            'industry' => $industry,
                            'layout' => 'standard',
                            'placeholders' => ['title', 'description', 'image', 'logo'],
                        ]),
                        'width' => 1080,
                        'height' => 1080,
                        'is_premium' => false,
                        'is_active' => true,
                        'is_featured' => false,
                        'usage_count' => 0,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('BizBoost industry kits seeded successfully!');
        $this->command->info('Industries: ' . implode(', ', array_keys($industryKits)));
    }
}
