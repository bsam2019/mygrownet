<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Services\QrCodeService;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\AnalyticsEventRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MiniWebsiteController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private ProductRepositoryInterface $productRepo,
        private AnalyticsEventRepositoryInterface $analyticsEventRepo,
        private QrCodeService $qrCodeService,
    ) {}

    public function show(Request $request, string $slug)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $profile = $this->businessService->getProfile($business->id);
        if (!$profile || !$profile->isPublished) {
            abort(404);
        }

        $this->trackEvent($business->id, 'page_view', $request);

        return Inertia::render('BizBoost/Public/BusinessPage', [
            'business' => $business->toArray(),
            'profile' => $profile->toArray(),
            'products' => $this->productRepo->findActiveByBusiness($business->id),
        ]);
    }

    public function products(Request $request, string $slug)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $profile = $this->businessService->getProfile($business->id);
        if (!$profile || !$profile->isPublished || !$profile->showProducts) {
            abort(404);
        }

        $products = $this->productRepo->findActiveByBusiness($business->id, $request->only(['category']));

        $this->trackEvent($business->id, 'products_view', $request);

        return Inertia::render('BizBoost/Public/Products', [
            'business' => $business->toArray(),
            'products' => $products,
            'categories' => $this->productRepo->getCategories($business->id),
            'filters' => $request->only(['category']),
        ]);
    }

    public function product(Request $request, string $slug, int $productId)
    {
        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $profile = $this->businessService->getProfile($business->id);
        if (!$profile || !$profile->isPublished || !$profile->showProducts) {
            abort(404);
        }

        $product = $this->productRepo->findById($productId);
        if (!$product) {
            abort(404);
        }

        $this->trackEvent($business->id, 'product_view', $request, ['product_id' => $productId]);

        return Inertia::render('BizBoost/Public/Product', [
            'business' => $business->toArray(),
            'product' => $product->toArray(),
            'relatedProducts' => $this->productRepo->findActiveByBusiness($business->id, ['exclude' => $productId]),
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

        $business = $this->businessService->findBusinessBySlug($slug);
        if (!$business || !$business->isActive) {
            abort(404);
        }

        $profile = $this->businessService->getProfile($business->id);
        if (!$profile || !$profile->showContactForm) {
            abort(404);
        }

        $this->trackEvent($business->id, 'contact_form', $request, [
            'name' => $validated['name'], 'has_email' => !empty($validated['email']), 'has_phone' => !empty($validated['phone']),
        ]);

        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }

    public function qrRedirect(Request $request, string $code)
    {
        $qrCode = $this->qrCodeService->findByShortCode($code);
        if (!$qrCode) {
            abort(404);
        }

        $this->qrCodeService->incrementScan($qrCode->id);

        $this->trackEvent($qrCode->businessId, 'qr_scan', $request, ['qr_code_id' => $qrCode->id, 'qr_type' => $qrCode->type]);

        return redirect($qrCode->data);
    }

    private function trackEvent(int $businessId, string $eventType, Request $request, array $payload = []): void
    {
        $this->analyticsEventRepo->save(new \App\Domain\BizBoost\Entities\AnalyticsEvent(
            id: null,
            businessId: $businessId,
            eventType: $eventType,
            source: 'mini_website',
            postId: null,
            payload: $payload,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            referrer: $request->header('referer'),
            recordedAt: now()->toDateTimeString(),
            createdAt: null,
            updatedAt: null,
        ));
    }
}