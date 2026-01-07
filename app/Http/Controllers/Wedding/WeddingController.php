<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Domain\Wedding\Services\WeddingPlanningService;
use App\Domain\Wedding\Repositories\WeddingEventRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingVendorRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingRsvpRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingGuestRepositoryInterface;
use App\Domain\Wedding\Entities\WeddingRsvp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class WeddingController extends Controller
{
    public function __construct(
        private WeddingPlanningService $weddingPlanningService,
        private WeddingEventRepositoryInterface $weddingEventRepository,
        private WeddingVendorRepositoryInterface $weddingVendorRepository,
        private WeddingRsvpRepositoryInterface $rsvpRepository,
        private WeddingGuestRepositoryInterface $guestRepository
    ) {}

    /**
     * Public landing page for wedding services
     */
    public function landingPage()
    {
        // Get all active templates
        $templates = \App\Infrastructure\Persistence\Eloquent\Wedding\WeddingTemplateModel::where('is_active', true)
            ->orderBy('is_premium', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'category' => $t->category ?? 'wedding',
                'category_name' => $t->category_name ?? 'Wedding',
                'category_icon' => $t->category_icon ?? '💍',
                'preview_text' => $t->preview_text ?? 'Your Event',
                'description' => $t->description,
                'preview_image' => $t->preview_image,
                'is_premium' => $t->is_premium,
                'settings' => $t->settings,
            ])
            ->values()
            ->toArray();

        // Pricing packages
        $packages = [
            [
                'name' => 'Basic',
                'price' => 500,
                'currency' => 'K',
                'features' => [
                    '1 Template Design',
                    'Up to 50 Guests',
                    'RSVP Management',
                    'Mobile Responsive',
                    '3 Months Active',
                ],
                'popular' => false,
            ],
            [
                'name' => 'Standard',
                'price' => 1500,
                'currency' => 'K',
                'features' => [
                    'All Templates',
                    'Up to 150 Guests',
                    'RSVP Management',
                    'Guest List Export',
                    'Custom Colors',
                    '6 Months Active',
                ],
                'popular' => true,
            ],
            [
                'name' => 'Premium',
                'price' => 3000,
                'currency' => 'K',
                'features' => [
                    'All Templates + Premium',
                    'Unlimited Guests',
                    'RSVP Management',
                    'Guest List Export',
                    'Custom Domain',
                    'Photo Gallery',
                    '12 Months Active',
                    'Priority Support',
                ],
                'popular' => false,
            ],
        ];

        return Inertia::render('Wedding/LandingPage', [
            'templates' => $templates,
            'packages' => $packages,
        ]);
    }

    /**
     * Preview a template with demo data
     */
    public function previewTemplate($slug)
    {
        $template = \App\Infrastructure\Persistence\Eloquent\Wedding\WeddingTemplateModel::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            abort(404, 'Template not found');
        }

        $settings = $template->settings;
        $demoImages = $settings['demoImages'] ?? [];

        // Demo wedding event data
        $demoWeddingEvent = [
            'id' => 0,
            'bride_name' => 'Sarah',
            'groom_name' => 'Michael',
            'wedding_date' => now()->addMonths(3)->format('Y-m-d'),
            'venue_name' => 'Grand Ballroom',
            'venue_address' => 'Lusaka, Zambia',
            'guest_count' => 150,
            'budget' => 50000,
            'ceremony_time' => '2:00 PM',
            'reception_time' => '5:00 PM',
            'reception_venue' => 'Grand Ballroom',
            'reception_address' => 'Lusaka, Zambia',
            'dress_code' => 'Formal Attire',
            'rsvp_deadline' => now()->addMonths(2)->addWeeks(2)->format('Y-m-d'),
            'hero_image' => $demoImages['hero'] ?? $template->preview_image,
            'story_image' => $demoImages['couple'] ?? $template->preview_image,
            'how_we_met' => 'Our love story began when we met at a friend\'s wedding. Little did we know that day would change our lives forever.',
            'proposal_story' => 'On a beautiful sunset evening, surrounded by the golden glow of the setting sun, Michael got down on one knee and asked the question that would begin our forever.',
        ];

        $ogMeta = [
            'title' => "{$template->name} Template Preview - MyGrowNet Weddings",
            'description' => $template->description,
            'image' => $template->preview_image,
            'url' => url()->current(),
            'type' => 'website',
        ];

        // Map template slug to component
        $templateComponentMap = [
            'flora-classic' => 'Wedding/WeddingWebsite',
            'modern-minimal' => 'Wedding/Templates/ModernMinimal',
            'elegant-gold' => 'Wedding/Templates/ElegantGold',
            'garden-party' => 'Wedding/Templates/GardenParty',
            'sunset-romance' => 'Wedding/Templates/SunsetRomance',
        ];

        $component = $templateComponentMap[$slug] ?? 'Wedding/WeddingWebsite';

        return Inertia::render($component, [
            'weddingEvent' => $demoWeddingEvent,
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'settings' => $settings,
            ],
            'galleryImages' => [],
            'ogMeta' => $ogMeta,
            'isPreview' => true,
        ])->rootView('wedding')->withViewData(['ogMeta' => $ogMeta]);
    }

    public function index()
    {
        $user = auth()->user();
        $userEvents = $this->weddingEventRepository->findByUserId($user->id);
        $activeEvent = $this->weddingEventRepository->findUserActiveEvent($user->id);
        
        return Inertia::render('Wedding/Dashboard', [
            'userEvents' => $userEvents,
            'activeEvent' => $activeEvent,
            'hasActiveEvent' => !is_null($activeEvent)
        ]);
    }

    public function create()
    {
        return Inertia::render('Wedding/CreateEvent');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_name' => 'required|string|max:255',
            'wedding_date' => 'required|date|after:today',
            'budget' => 'required|numeric|min:0',
            'guest_count' => 'required|integer|min:0'
        ]);

        try {
            $weddingEvent = $this->weddingPlanningService->createWeddingEvent(
                userId: auth()->id(),
                partnerName: $validated['partner_name'],
                weddingDate: Carbon::parse($validated['wedding_date']),
                budgetAmount: $validated['budget'],
                guestCount: $validated['guest_count']
            );

            return redirect()->route('wedding.show', $weddingEvent->getId())
                ->with('success', 'Wedding event created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(int $id)
    {
        $weddingEvent = $this->weddingEventRepository->findById($id);
        
        if (!$weddingEvent || $weddingEvent->getUserId() !== auth()->id()) {
            abort(404);
        }

        $budgetBreakdown = $this->weddingPlanningService->generateBudgetBreakdown($weddingEvent->getBudget());
        $timeline = $this->weddingPlanningService->generateWeddingTimeline($weddingEvent->getWeddingDate());
        $progress = $this->weddingPlanningService->getWeddingProgress($weddingEvent);

        return Inertia::render('Wedding/EventDetails', [
            'weddingEvent' => $weddingEvent,
            'budgetBreakdown' => $budgetBreakdown,
            'timeline' => $timeline,
            'progress' => $progress
        ]);
    }

    public function vendors()
    {
        $featuredVendors = $this->weddingVendorRepository->findFeaturedVendors(8);
        $topRatedVendors = $this->weddingVendorRepository->findTopRatedVendors(8);
        $vendorStats = $this->weddingVendorRepository->getVendorStats();

        return Inertia::render('Wedding/Vendors', [
            'featuredVendors' => $featuredVendors,
            'topRatedVendors' => $topRatedVendors,
            'vendorStats' => $vendorStats
        ]);
    }

    public function planning()
    {
        $user = auth()->user();
        $activeEvent = $this->weddingEventRepository->findUserActiveEvent($user->id);
        
        if (!$activeEvent) {
            return redirect()->route('wedding.create')
                ->with('info', 'Please create a wedding event first to access planning tools.');
        }

        $budgetBreakdown = $this->weddingPlanningService->generateBudgetBreakdown($activeEvent->getBudget());
        $timeline = $this->weddingPlanningService->generateWeddingTimeline($activeEvent->getWeddingDate());
        $progress = $this->weddingPlanningService->getWeddingProgress($activeEvent);

        return Inertia::render('Wedding/Planning', [
            'weddingEvent' => $activeEvent,
            'budgetBreakdown' => $budgetBreakdown,
            'timeline' => $timeline,
            'progress' => $progress
        ]);
    }

    public function budgetCalculator()
    {
        return Inertia::render('Wedding/BudgetCalculator');
    }

    public function calculateBudget(Request $request)
    {
        $validated = $request->validate([
            'guest_count' => 'required|integer|min:1',
            'wedding_style' => 'required|in:budget,standard,premium,luxury'
        ]);

        $recommendedBudget = $this->weddingPlanningService->calculateRecommendedBudget(
            $validated['guest_count'],
            $validated['wedding_style']
        );

        $budgetBreakdown = $this->weddingPlanningService->generateBudgetBreakdown($recommendedBudget);

        return response()->json([
            'recommended_budget' => $recommendedBudget->toArray(),
            'budget_breakdown' => $budgetBreakdown,
            'guest_count' => $validated['guest_count'],
            'wedding_style' => $validated['wedding_style']
        ]);
    }

    public function weddingWebsite($slug)
    {
        // Find wedding event by slug using Eloquent model directly (supports new template fields)
        $wedding = \App\Infrastructure\Persistence\Eloquent\Wedding\WeddingEventModel::with('template')
            ->where('slug', $slug)
            ->first();
        
        if (!$wedding) {
            // Try finding by ID if slug doesn't work
            if (is_numeric($slug)) {
                $wedding = \App\Infrastructure\Persistence\Eloquent\Wedding\WeddingEventModel::with('template')
                    ->find((int)$slug);
            }
        }
        
        if (!$wedding) {
            abort(404, 'Wedding website not found');
        }

        // Check if published (unless in preview mode)
        if (!$wedding->is_published && !request()->has('preview')) {
            abort(404, 'Wedding website not found');
        }

        // Get template settings (use Flora Classic defaults if no template)
        $templateSettings = $wedding->template?->settings ?? [
            'colors' => [
                'primary' => '#9333ea',
                'secondary' => '#ec4899',
                'accent' => '#f59e0b',
                'background' => '#ffffff',
                'text' => '#1f2937',
                'textLight' => '#6b7280',
            ],
            'fonts' => [
                'heading' => 'Great Vibes',
                'body' => 'Inter',
            ],
            'layout' => [
                'heroStyle' => 'centered',
                'navigationStyle' => 'tabs',
                'showCountdown' => true,
                'showGallery' => true,
            ],
            'decorations' => [
                'backgroundPattern' => 'flora',
                'headerImage' => '/images/Wedding/flora.jpg',
                'borderStyle' => 'elegant',
            ],
        ];

        // Get gallery images (placeholder for now)
        $galleryImages = [
            ['url' => '/images/wedding/gallery/couple-1.jpg'],
            ['url' => '/images/wedding/gallery/couple-2.jpg'],
            ['url' => '/images/wedding/gallery/couple-3.jpg'],
            ['url' => '/images/wedding/gallery/couple-4.jpg'],
        ];

        $groomName = $wedding->groom_name ?? 'Groom';
        $brideName = $wedding->bride_name ?? $wedding->partner_name ?? 'Bride';
        $weddingDate = $wedding->wedding_date;
        
        // Open Graph meta data for social sharing
        $ogMeta = [
            'title' => "{$groomName} & {$brideName} Wedding - " . $weddingDate->format('F j, Y'),
            'description' => "You are invited to celebrate the wedding of {$groomName} & {$brideName} on " . $weddingDate->format('F j, Y') . ".",
            'image' => $wedding->hero_image ? url($wedding->hero_image) : url('/images/wedding/default-couple.jpg'),
            'url' => url()->current(),
            'type' => 'website',
        ];
        
        return Inertia::render('Wedding/WeddingWebsite', [
            'weddingEvent' => [
                'id' => $wedding->id,
                'bride_name' => $brideName,
                'groom_name' => $groomName,
                'wedding_date' => $weddingDate->format('Y-m-d'),
                'venue_name' => $wedding->venue_name ?? 'Beautiful Wedding Venue',
                'venue_address' => $wedding->venue_location ?? 'Lusaka, Zambia',
                'guest_count' => $wedding->guest_count,
                'budget' => $wedding->budget,
                'ceremony_time' => $wedding->ceremony_time ?? '2:00 PM',
                'reception_time' => $wedding->reception_time ?? '5:00 PM',
                'reception_venue' => $wedding->reception_venue ?? $wedding->venue_name ?? 'Beautiful Wedding Venue',
                'reception_address' => $wedding->reception_address ?? $wedding->venue_location ?? 'Lusaka, Zambia',
                'dress_code' => $wedding->dress_code ?? 'Formal Attire',
                'rsvp_deadline' => $wedding->rsvp_deadline?->format('Y-m-d') ?? $weddingDate->copy()->subDays(14)->format('Y-m-d'),
                'hero_image' => $wedding->hero_image ?? '/images/wedding/default-couple.jpg',
                'story_image' => $wedding->story_image ?? '/images/wedding/couple-story.jpg',
                'how_we_met' => $wedding->how_we_met ?? 'Our love story began in the most unexpected way, and we knew from that moment that we were meant to be together.',
                'proposal_story' => $wedding->proposal_story ?? 'It was a moment we\'ll never forget - surrounded by love, laughter, and the promise of forever.',
            ],
            'template' => [
                'id' => $wedding->template?->id,
                'name' => $wedding->template?->name ?? 'Flora Classic',
                'slug' => $wedding->template?->slug ?? 'flora-classic',
                'settings' => $templateSettings,
            ],
            'galleryImages' => $galleryImages,
            'ogMeta' => $ogMeta,
        ])->rootView('wedding')->withViewData(['ogMeta' => $ogMeta]);
    }

    public function submitRSVP(Request $request, $id)
    {
        $validated = $request->validate([
            'guest_id' => 'nullable|integer',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'attending' => 'required|in:yes,no,accepted,declined',
            'guest_count' => 'nullable|integer|min:0|max:10',
            'dietary_restrictions' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:1000'
        ]);

        // Normalize attending value
        $isAttending = in_array($validated['attending'], ['yes', 'accepted']);
        $status = $isAttending ? 'attending' : 'declined';
        $confirmedGuests = $isAttending ? ($validated['guest_count'] ?? 1) : 0;

        try {
            // Try to find the guest by ID first, then by name
            $guest = null;
            
            if (!empty($validated['guest_id'])) {
                $guest = $this->guestRepository->findById($validated['guest_id']);
            }
            
            if (!$guest) {
                // Search by name
                $fullName = $validated['first_name'] . ' ' . $validated['last_name'];
                $guest = $this->guestRepository->findByName($id, $fullName);
            }

            if (!$guest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Guest not found on the invitation list. Please contact the couple.',
                ], 404);
            }

            // Update the guest's RSVP status and email if provided
            $updatedGuest = $this->guestRepository->updateRsvpStatus(
                guestId: $guest->getId(),
                status: $status,
                confirmedGuests: $confirmedGuests,
                dietaryRestrictions: $validated['dietary_restrictions'] ?? null,
                message: $validated['message'] ?? null,
                email: $validated['email'] ?? null
            );

            \Log::info('RSVP Submitted', [
                'wedding_event_id' => $id,
                'guest_id' => $guest->getId(),
                'guest_name' => $guest->getFullName(),
                'status' => $status,
                'confirmed_guests' => $confirmedGuests,
            ]);

            return response()->json([
                'success' => true,
                'message' => $isAttending 
                    ? 'Thank you! We look forward to celebrating with you.' 
                    : 'Thank you for letting us know. We\'ll miss you!',
                'guest' => $updatedGuest->toArray(),
            ]);
        } catch (\Exception $e) {
            \Log::error('RSVP submission failed', [
                'error' => $e->getMessage(),
                'wedding_event_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to submit RSVP. Please try again.',
            ], 500);
        }
    }

    public function demoWebsite()
    {
        // Kaoma & Mubanga Wedding - 6th December 2025
        $demoWeddingEvent = [
            'id' => 1,
            'bride_name' => 'Mubanga',
            'groom_name' => 'Kaoma',
            'wedding_date' => '2025-12-06',
            'venue_name' => '3Sixty Convention Centre',
            'venue_address' => 'Twin Palm Road, Ibex Hill, Lusaka',
            'guest_count' => 150,
            'budget' => 75000,
            'ceremony_time' => '11:00 AM',
            'reception_time' => '1:30 PM',
            'reception_venue' => '3Sixty Convention Centre - Main Hall',
            'reception_address' => 'Twin Palm Road, Ibex Hill, Lusaka',
            'dress_code' => 'Formal Attire',
            'rsvp_deadline' => '2025-12-03',
            'hero_image' => '/images/Wedding/main.jpg?v=' . time(),
            'story_image' => '/images/Wedding/main.jpg?v=' . time(),
            'how_we_met' => 'Our love story began in the most unexpected way when we met at a mutual friend\'s gathering. From that first conversation, we knew there was something special between us.',
            'proposal_story' => 'It was a beautiful evening surrounded by the people we love most. When Kaoma got down on one knee, time seemed to stand still - it was a moment we\'ll treasure forever.',
        ];

        // Demo gallery images
        $galleryImages = [
            ['url' => '/images/wedding/gallery/demo-1.jpg'],
            ['url' => '/images/wedding/gallery/demo-2.jpg'],
            ['url' => '/images/wedding/gallery/demo-3.jpg'],
            ['url' => '/images/wedding/gallery/demo-4.jpg'],
            ['url' => '/images/wedding/gallery/demo-5.jpg'],
            ['url' => '/images/wedding/gallery/demo-6.jpg'],
            ['url' => '/images/wedding/gallery/demo-7.jpg'],
            ['url' => '/images/wedding/gallery/demo-8.jpg'],
        ];

        // Open Graph meta data for social sharing
        $ogMeta = [
            'title' => 'Kaoma & Mubanga Wedding - December 6, 2025',
            'description' => 'You are invited to celebrate the wedding of Kaoma & Mubanga on December 6, 2025 at 3Sixty Convention Centre, Lusaka.',
            'image' => url('/images/Wedding/OG.jpg'),
            'url' => url('/kaoma-and-mubanga-dec-2025'),
            'type' => 'website',
        ];

        // Flora Classic template settings (default for Kaoma & Mubanga)
        $templateSettings = [
            'colors' => [
                'primary' => '#9333ea',
                'secondary' => '#ec4899',
                'accent' => '#f59e0b',
                'background' => '#ffffff',
                'text' => '#1f2937',
                'textLight' => '#6b7280',
            ],
            'fonts' => [
                'heading' => 'Great Vibes',
                'body' => 'Inter',
            ],
            'layout' => [
                'heroStyle' => 'centered',
                'navigationStyle' => 'tabs',
                'showCountdown' => true,
                'showGallery' => true,
            ],
            'decorations' => [
                'backgroundPattern' => 'flora',
                'headerImage' => '/images/Wedding/flora.jpg',
                'borderStyle' => 'elegant',
            ],
        ];

        // Pass OG meta to the view for server-side rendering (important for social crawlers)
        return Inertia::render('Wedding/WeddingWebsite', [
            'weddingEvent' => $demoWeddingEvent,
            'template' => [
                'id' => 1,
                'name' => 'Flora Classic',
                'slug' => 'flora-classic',
                'settings' => $templateSettings,
            ],
            'galleryImages' => $galleryImages,
            'ogMeta' => $ogMeta,
        ])->rootView('wedding')->withViewData(['ogMeta' => $ogMeta]);
    }

    /**
     * Search for a guest in the invited guest list
     */
    public function searchGuest(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
        ]);

        try {
            // Search for guest in the invited list
            $guests = $this->guestRepository->searchByName($id, $validated['name']);

            if (empty($guests)) {
                return response()->json([
                    'success' => false,
                    'found' => false,
                    'message' => 'We couldn\'t find your name on the guest list. Please check the spelling or contact the couple.',
                ]);
            }

            // Return all matching guests with differentiating info
            $guestData = array_map(fn($guest) => [
                'id' => $guest->getId(),
                'name' => $guest->getFullName(),
                'first_name' => $guest->getFirstName(),
                'last_name' => $guest->getLastName(),
                'email' => $guest->getEmail(),
                'allowed_guests' => $guest->getAllowedGuests(),
                'group_name' => $guest->getGroupName(),
                'email_hint' => $guest->getEmail() ? $this->maskEmail($guest->getEmail()) : null,
                'phone_hint' => $guest->getPhone() ? $this->maskPhone($guest->getPhone()) : null,
            ], $guests);

            return response()->json([
                'success' => true,
                'found' => true,
                'guests' => $guestData,
                'multiple' => count($guests) > 1,
            ]);
        } catch (\Exception $e) {
            \Log::error('Guest search failed', [
                'error' => $e->getMessage(),
                'wedding_event_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Search failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Mask email for privacy while still allowing identification
     * e.g., "john.smith@gmail.com" becomes "j***h@g***l.com"
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***.***';
        }

        $local = $parts[0];
        $domain = $parts[1];

        // Mask local part: show first and last char
        if (strlen($local) <= 2) {
            $maskedLocal = $local[0] . '***';
        } else {
            $maskedLocal = $local[0] . '***' . $local[strlen($local) - 1];
        }

        // Mask domain: show first char and TLD
        $domainParts = explode('.', $domain);
        if (count($domainParts) >= 2) {
            $domainName = $domainParts[0];
            $tld = end($domainParts);
            $maskedDomain = $domainName[0] . '***.' . $tld;
        } else {
            $maskedDomain = $domain[0] . '***';
        }

        return $maskedLocal . '@' . $maskedDomain;
    }

    /**
     * Mask phone number for privacy while still allowing identification
     * e.g., "0977123456" becomes "***3456" (shows last 4 digits)
     */
    private function maskPhone(string $phone): string
    {
        // Remove any non-digit characters for processing
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) < 4) {
            return '***' . $digits;
        }

        // Show last 4 digits
        $lastFour = substr($digits, -4);

        return '***' . $lastFour;
    }

    /**
     * Handle guest inquiry/RSVP for unlisted guests
     * Stores the inquiry for the couple to review - guest doesn't know they're not on the list
     */
    public function guestInquiry(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:1000',
            'attending' => 'nullable|in:accepted,declined',
            'guest_count' => 'nullable|integer|min:0|max:10',
        ]);

        try {
            // Determine RSVP status from attending field
            $rsvpStatus = 'inquiry';
            if (isset($validated['attending'])) {
                $rsvpStatus = $validated['attending'] === 'accepted' ? 'attending_pending' : 'declined_pending';
            }

            // Build message with RSVP info
            $message = $validated['message'] ?? '';
            if (isset($validated['attending'])) {
                $attendingText = $validated['attending'] === 'accepted' ? 'Will attend' : 'Cannot attend';
                $message = "RSVP Response: {$attendingText}" . ($message ? " | Note: {$message}" : '');
            }

            // Store the inquiry as a pending guest with their RSVP response
            $guest = $this->guestRepository->createPendingGuest(
                weddingEventId: (int) $id,
                name: $validated['name'],
                phone: $validated['phone'] ?? null,
                email: $validated['email'] ?? null,
                message: $message,
                rsvpStatus: $rsvpStatus,
                guestCount: $validated['guest_count'] ?? 1
            );

            \Log::info('Unlisted guest RSVP received', [
                'wedding_event_id' => $id,
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'rsvp_status' => $rsvpStatus,
            ]);

            // Return success - guest doesn't know they're not on the list
            $responseMessage = $validated['attending'] === 'accepted'
                ? 'Thank you! We look forward to celebrating with you.'
                : 'Thank you for letting us know. We\'ll miss you!';

            return response()->json([
                'success' => true,
                'message' => $responseMessage,
            ]);
        } catch (\Exception $e) {
            \Log::error('Guest inquiry failed', [
                'error' => $e->getMessage(),
                'wedding_event_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to send your message. Please try again or contact the couple directly.',
            ], 500);
        }
    }
}