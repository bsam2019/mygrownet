<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostAnalyticsEventModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostQrCodeModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MiniWebsiteController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with(['profile', 'products' => function ($q) {
                $q->where('is_active', true)
                    ->with('primaryImage')
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('sort_order')
                    ->take(12);
            }])
            ->firstOrFail();

        // Check if mini-website is published
        if (!$business->profile?->is_published) {
            abort(404);
        }

        // Track page view
        $this->trackEvent($business, 'page_view', $request);

        return Inertia::render('BizBoost/Public/BusinessPage', [
            'business' => $business,
            'profile' => $business->profile,
            'products' => $business->products,
        ]);
    }

    public function products(Request $request, string $slug): Response
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with('profile')
            ->firstOrFail();

        if (!$business->profile?->is_published || !$business->profile?->show_products) {
            abort(404);
        }

        $products = $business->products()
            ->where('is_active', true)
            ->with('primaryImage')
            ->when($request->category, fn($q, $cat) => $q->where('category', $cat))
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        $categories = $business->products()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        // Track page view
        $this->trackEvent($business, 'products_view', $request);

        return Inertia::render('BizBoost/Public/Products', [
            'business' => $business,
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['category']),
        ]);
    }

    public function product(Request $request, string $slug, int $productId): Response
    {
        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with('profile')
            ->firstOrFail();

        if (!$business->profile?->is_published || !$business->profile?->show_products) {
            abort(404);
        }

        $product = $business->products()
            ->where('is_active', true)
            ->with('images')
            ->findOrFail($productId);

        // Related products
        $relatedProducts = $business->products()
            ->where('is_active', true)
            ->where('id', '!=', $productId)
            ->when($product->category, fn($q, $cat) => $q->where('category', $cat))
            ->with('primaryImage')
            ->take(4)
            ->get();

        // Track product view
        $this->trackEvent($business, 'product_view', $request, ['product_id' => $productId]);

        return Inertia::render('BizBoost/Public/Product', [
            'business' => $business,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function contact(Request $request, string $slug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        $business = BizBoostBusinessModel::where('slug', $slug)
            ->where('is_active', true)
            ->with('profile')
            ->firstOrFail();

        if (!$business->profile?->is_published || !$business->profile?->show_contact_form) {
            abort(404);
        }

        // Track contact form submission
        $this->trackEvent($business, 'contact_form', $request, [
            'name' => $validated['name'],
            'has_email' => !empty($validated['email']),
            'has_phone' => !empty($validated['phone']),
        ]);

        // In production, send notification to business owner
        // Notification::send($business->user, new ContactFormSubmitted($validated));

        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }

    public function qrRedirect(Request $request, string $code)
    {
        $qrCode = BizBoostQrCodeModel::where('short_code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        // Track QR scan
        $qrCode->increment('scan_count');
        
        // Log scan details
        \DB::table('bizboost_qr_scans')->insert([
            'qr_code_id' => $qrCode->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'scanned_at' => now(),
        ]);

        // Track analytics event
        $this->trackEvent($qrCode->business, 'qr_scan', $request, [
            'qr_code_id' => $qrCode->id,
            'qr_type' => $qrCode->type,
        ]);

        return redirect($qrCode->target_url);
    }

    private function trackEvent(
        BizBoostBusinessModel $business, 
        string $eventType, 
        Request $request, 
        array $payload = []
    ): void {
        BizBoostAnalyticsEventModel::create([
            'business_id' => $business->id,
            'event_type' => $eventType,
            'source' => 'mini_website',
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'recorded_at' => now(),
        ]);
    }
}
