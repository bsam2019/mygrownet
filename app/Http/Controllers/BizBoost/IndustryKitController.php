<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\TemplateRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndustryKitController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private BusinessService $businessService,
        private TemplateRepositoryInterface $templateRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $hasAccess = $this->subscriptionService->hasFeature($request->user(), 'industry_kits', 'bizboost');

        return Inertia::render('BizBoost/IndustryKits/Index', [
            'industryKits' => $this->getIndustryKits(),
            'currentIndustry' => $business->industry,
            'hasAccess' => $hasAccess,
        ]);
    }

    public function show(Request $request, string $industry)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $hasAccess = $this->subscriptionService->hasFeature($request->user(), 'industry_kits', 'bizboost');

        if (!$hasAccess) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'industry_kits',
                'message' => 'Industry Kits require Basic plan or higher.',
                'suggestedTier' => 'basic',
            ]);
        }

        $kit = $this->getIndustryKit($industry);
        if (!$kit) {
            abort(404, 'Industry kit not found.');
        }

        $templates = $this->templateRepo->findByBusiness($business->id);

        return Inertia::render('BizBoost/IndustryKits/Show', [
            'kit' => $kit,
            'templates' => $templates,
            'business' => $business->toArray(),
        ]);
    }

    public function applyKit(Request $request, string $industry)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        if (!$this->subscriptionService->hasFeature($request->user(), 'industry_kits', 'bizboost')) {
            return back()->with('error', 'Industry Kits require Basic plan or higher.');
        }

        $kit = $this->getIndustryKit($industry);
        if (!$kit) {
            return back()->with('error', 'Invalid industry kit selected.');
        }

        $this->businessService->updateBusiness($business->id, ['industry' => $industry]);

        return redirect()->route('bizboost.industry-kits.show', $industry)
            ->with('success', "Industry kit '{$kit['name']}' applied successfully! Your templates and suggestions are now customized for your business.");
    }

    private function getIndustryKits(): array
    {
        return [
            [
                'id' => 'boutique',
                'name' => 'Boutique & Fashion',
                'description' => 'Perfect for clothing stores, fashion boutiques, and accessory shops.',
                'icon' => 'ShoppingBagIcon',
                'color' => 'pink',
                'template_count' => 45,
                'features' => [
                    '45+ fashion-focused templates',
                    'New arrival announcement posts',
                    'Sale and discount templates',
                    'Outfit of the day ideas',
                    'Size guide templates',
                    'Customer testimonial layouts',
                ],
                'sample_posts' => [
                    'New arrivals just dropped! 🛍️ Come check out our latest collection.',
                    'Flash Sale! 30% off all dresses this weekend only.',
                    'Style tip: Pair this top with high-waisted jeans for the perfect casual look.',
                ],
            ],
            [
                'id' => 'salon',
                'name' => 'Salon & Beauty',
                'description' => 'Designed for hair salons, nail bars, and beauty parlors.',
                'icon' => 'SparklesIcon',
                'color' => 'purple',
                'template_count' => 40,
                'features' => [
                    '40+ beauty-focused templates',
                    'Before/after transformation posts',
                    'Service menu templates',
                    'Appointment reminder posts',
                    'Hair care tips content',
                    'Special occasion packages',
                ],
                'sample_posts' => [
                    'Transform your look! ✨ Book your appointment today.',
                    'Braids special this week! Get 20% off all braiding styles.',
                    'Hair tip: Deep condition weekly for healthier, shinier hair.',
                ],
            ],
            [
                'id' => 'barbershop',
                'name' => 'Barbershop',
                'description' => 'Tailored for barbershops and men\'s grooming services.',
                'icon' => 'ScissorsIcon',
                'color' => 'blue',
                'template_count' => 35,
                'features' => [
                    '35+ barbershop templates',
                    'Haircut style showcase',
                    'Price list templates',
                    'Walk-in welcome posts',
                    'Grooming tips content',
                    'Loyalty program promotions',
                ],
                'sample_posts' => [
                    'Fresh cuts, fresh week! 💈 Walk-ins welcome.',
                    'Beard trim + haircut combo only K50 this month.',
                    'Grooming tip: Use beard oil daily for a softer, healthier beard.',
                ],
            ],
            [
                'id' => 'restaurant',
                'name' => 'Restaurant & Food',
                'description' => 'Perfect for restaurants, cafes, and food vendors.',
                'icon' => 'CakeIcon',
                'color' => 'orange',
                'template_count' => 50,
                'features' => [
                    '50+ food-focused templates',
                    'Menu showcase templates',
                    'Daily special announcements',
                    'Food photography layouts',
                    'Delivery promotion posts',
                    'Customer review highlights',
                ],
                'sample_posts' => [
                    'Today\'s special: Nshima with village chicken! 🍗',
                    'Order now and get free delivery within 5km!',
                    'Weekend brunch menu is here! Book your table.',
                ],
            ],
            [
                'id' => 'grocery',
                'name' => 'Grocery & Retail',
                'description' => 'Designed for grocery stores, supermarkets, and retail shops.',
                'icon' => 'ShoppingCartIcon',
                'color' => 'green',
                'template_count' => 35,
                'features' => [
                    '35+ retail templates',
                    'Weekly deals announcements',
                    'Product spotlight posts',
                    'Fresh arrivals content',
                    'Bulk buy promotions',
                    'Store hours reminders',
                ],
                'sample_posts' => [
                    'Fresh vegetables just arrived! 🥬 Come early for the best picks.',
                    'Weekend special: Buy 2 get 1 free on all cooking oil.',
                    'We\'re open 7 days a week, 7am to 8pm!',
                ],
            ],
            [
                'id' => 'hardware',
                'name' => 'Hardware Store',
                'description' => 'For hardware stores, building materials, and tool shops.',
                'icon' => 'WrenchIcon',
                'color' => 'yellow',
                'template_count' => 30,
                'features' => [
                    '30+ hardware templates',
                    'Product catalog layouts',
                    'DIY tips and tricks',
                    'Bulk order promotions',
                    'New stock announcements',
                    'Contractor specials',
                ],
                'sample_posts' => [
                    'Building season is here! 🏗️ Stock up on cement at wholesale prices.',
                    'DIY tip: Always measure twice, cut once!',
                    'Contractors: Ask about our bulk discount program.',
                ],
            ],
            [
                'id' => 'photography',
                'name' => 'Photography',
                'description' => 'Perfect for photographers and videographers.',
                'icon' => 'CameraIcon',
                'color' => 'indigo',
                'template_count' => 40,
                'features' => [
                    '40+ photography templates',
                    'Portfolio showcase layouts',
                    'Package pricing templates',
                    'Behind-the-scenes content',
                    'Client testimonials',
                    'Booking call-to-action posts',
                ],
                'sample_posts' => [
                    'Capturing your special moments 📸 Book your session today!',
                    'Wedding package special: Full day coverage + album at K5,000.',
                    'Behind the scenes from yesterday\'s corporate shoot.',
                ],
            ],
            [
                'id' => 'mobile_money',
                'name' => 'Mobile Money Booth',
                'description' => 'For mobile money agents and financial service points.',
                'icon' => 'DevicePhoneMobileIcon',
                'color' => 'emerald',
                'template_count' => 25,
                'features' => [
                    '25+ mobile money templates',
                    'Service availability posts',
                    'Transaction limit reminders',
                    'Location and hours content',
                    'Promotion announcements',
                    'Safety tips for customers',
                ],
                'sample_posts' => [
                    'Cash in, cash out, bill payments - we\'ve got you covered! 💰',
                    'Float available! No waiting, quick service.',
                    'Safety tip: Never share your PIN with anyone.',
                ],
            ],
            [
                'id' => 'electronics',
                'name' => 'Electronics',
                'description' => 'For electronics shops, phone repairs, and tech stores.',
                'icon' => 'ComputerDesktopIcon',
                'color' => 'cyan',
                'template_count' => 35,
                'features' => [
                    '35+ electronics templates',
                    'New product launches',
                    'Repair service promotions',
                    'Tech tips and tricks',
                    'Accessory bundles',
                    'Trade-in program posts',
                ],
                'sample_posts' => [
                    'New phones in stock! 📱 Latest models at best prices.',
                    'Screen repair in 30 minutes or less!',
                    'Tech tip: Clear your cache regularly for better phone performance.',
                ],
            ],
            [
                'id' => 'general',
                'name' => 'General Business',
                'description' => 'Versatile templates for any type of business.',
                'icon' => 'BuildingStorefrontIcon',
                'color' => 'gray',
                'template_count' => 60,
                'features' => [
                    '60+ versatile templates',
                    'Grand opening announcements',
                    'Holiday greetings',
                    'Customer appreciation posts',
                    'Business milestone celebrations',
                    'General promotional content',
                ],
                'sample_posts' => [
                    'Thank you for your continued support! 🙏',
                    'We\'re celebrating 1 year in business! Special offers all week.',
                    'Happy Independence Day, Zambia! 🇿🇲',
                ],
            ],
        ];
    }

    private function getIndustryKit(string $industry): ?array
    {
        $kits = $this->getIndustryKits();
        return collect($kits)->firstWhere('id', $industry);
    }

    
}
