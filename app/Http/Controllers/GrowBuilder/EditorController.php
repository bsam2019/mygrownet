<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Application\GrowBuilder\DTOs\SavePageContentDTO;
use App\Application\GrowBuilder\UseCases\SavePageContentUseCase;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\PageId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Services\GrowBuilder\AIUsageService;
use App\Services\GrowBuilder\TierRestrictionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EditorController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private PageRepositoryInterface $pageRepository,
        private SavePageContentUseCase $savePageContentUseCase,
        private AIUsageService $aiUsageService,
        private TierRestrictionService $tierRestrictionService,
    ) {}

    public function index(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $pages = $this->pageRepository->findBySiteId($site->getId());
        
        // If a page ID is specified, load that page
        $currentPage = null;
        if ($request->has('page')) {
            $pageId = (int) $request->get('page');
            $currentPage = $this->pageRepository->findById(PageId::fromInt($pageId));
            if ($currentPage && $currentPage->getSiteId() !== $siteId) {
                $currentPage = null;
            }
        }
        
        // Default to homepage if no page specified
        if (!$currentPage) {
            $currentPage = collect($pages)->first(fn($p) => $p->isHomepage());
        }
        
        // Fallback to first page
        if (!$currentPage && count($pages) > 0) {
            $currentPage = $pages[0];
        }

        return Inertia::render('GrowBuilder/Editor/Index', [
            'site' => $this->siteToArray($site),
            'pages' => collect($pages)->map(fn($p) => $this->pageToArray($p)),
            'currentPage' => $currentPage ? $this->pageToArray($currentPage) : null,
            'sectionTypes' => $this->getSectionTypes(),
            'aiUsage' => $this->aiUsageService->getUsageStats($request->user()),
            'tierRestrictions' => $this->tierRestrictionService->getRestrictions($request->user()),
        ]);
    }

    public function editPage(Request $request, int $siteId, int $pageId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $page = $this->pageRepository->findById(PageId::fromInt($pageId));

        if (!$page || $page->getSiteId() !== $siteId) {
            abort(404);
        }

        $pages = $this->pageRepository->findBySiteId($site->getId());

        return Inertia::render('GrowBuilder/Editor/Index', [
            'site' => $this->siteToArray($site),
            'pages' => collect($pages)->map(fn($p) => $this->pageToArray($p)),
            'currentPage' => $this->pageToArray($page),
            'sectionTypes' => $this->getSectionTypes(),
            'aiUsage' => $this->aiUsageService->getUsageStats($request->user()),
            'tierRestrictions' => $this->tierRestrictionService->getRestrictions($request->user()),
        ]);
    }

    public function savePage(Request $request, int $siteId)
    {
        $validated = $request->validate([
            'page_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'sections' => 'present|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_homepage' => 'boolean',
            'show_in_nav' => 'boolean',
        ]);

        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        // Check for duplicate slug (only for new pages)
        if (!isset($validated['page_id'])) {
            $existingPages = $this->pageRepository->findBySiteId($site->getId());
            $slugExists = collect($existingPages)->contains(fn($p) => $p->getSlug() === $validated['slug']);
            
            if ($slugExists) {
                return response()->json([
                    'success' => false,
                    'error' => "A page with the URL slug \"{$validated['slug']}\" already exists. Please choose a different slug.",
                ], 422);
            }
        }

        try {
            $dto = new SavePageContentDTO(
                siteId: $siteId,
                userId: $request->user()->id,
                sections: $validated['sections'] ?? [],
                pageId: $validated['page_id'] ?? null,
                title: $validated['title'],
                slug: $validated['slug'],
                metaTitle: $validated['meta_title'] ?? null,
                metaDescription: $validated['meta_description'] ?? null,
                isHomepage: $validated['is_homepage'] ?? false,
                showInNav: $validated['show_in_nav'] ?? true,
            );

            $page = $this->savePageContentUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'page' => $this->pageToArray($page),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function savePageContent(Request $request, int $siteId, int $pageId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|array',
            'content.sections' => 'present|array',
        ]);

        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $page = $this->pageRepository->findById(PageId::fromInt($pageId));

        if (!$page || $page->getSiteId() !== $siteId) {
            abort(404);
        }

        try {
            $dto = new SavePageContentDTO(
                siteId: $siteId,
                userId: $request->user()->id,
                sections: $validated['content']['sections'] ?? [],
                pageId: $pageId,
                title: $validated['title'],
                slug: $page->getSlug(),
                metaTitle: $page->getMetaTitle(),
                metaDescription: $page->getMetaDescription(),
                isHomepage: $page->isHomepage(),
                showInNav: $page->showInNav(),
            );

            $page = $this->savePageContentUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'page' => $this->pageToArray($page),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function updatePageMeta(Request $request, int $siteId, int $pageId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'show_in_nav' => 'boolean',
        ]);

        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $page = $this->pageRepository->findById(PageId::fromInt($pageId));

        if (!$page || $page->getSiteId() !== $siteId) {
            abort(404);
        }

        try {
            $dto = new SavePageContentDTO(
                siteId: $siteId,
                userId: $request->user()->id,
                sections: $page->getContent()->getSections(),
                pageId: $pageId,
                title: $validated['title'],
                slug: $validated['slug'],
                metaTitle: $page->getMetaTitle(),
                metaDescription: $page->getMetaDescription(),
                isHomepage: $page->isHomepage(),
                showInNav: $validated['show_in_nav'] ?? $page->showInNav(),
            );

            $page = $this->savePageContentUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'page' => $this->pageToArray($page),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function deletePage(Request $request, int $siteId, int $pageId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $page = $this->pageRepository->findById(PageId::fromInt($pageId));

        if (!$page || $page->getSiteId() !== $siteId) {
            abort(404);
        }

        if ($page->isHomepage()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot delete homepage',
            ], 422);
        }

        $this->pageRepository->delete($page->getId());

        return response()->json(['success' => true]);
    }

    public function saveSiteSettings(Request $request, int $siteId)
    {
        $validated = $request->validate([
            'navigation' => 'nullable|array',
            'navigation.logoText' => 'nullable|string|max:255',
            'navigation.logo' => 'nullable|string|max:500',
            'navigation.navItems' => 'nullable|array',
            'navigation.showCta' => 'nullable|boolean',
            'navigation.ctaText' => 'nullable|string|max:100',
            'navigation.ctaLink' => 'nullable|string|max:255',
            'navigation.sticky' => 'nullable|boolean',
            'navigation.style' => 'nullable|string|in:default,centered,split,floating,transparent,dark,sidebar,mega',
            // Auth buttons
            'navigation.showAuthButtons' => 'nullable|boolean',
            'navigation.showLoginButton' => 'nullable|boolean',
            'navigation.showRegisterButton' => 'nullable|boolean',
            'navigation.loginText' => 'nullable|string|max:50',
            'navigation.registerText' => 'nullable|string|max:50',
            'navigation.loginStyle' => 'nullable|string|in:link,outline,solid',
            'navigation.registerStyle' => 'nullable|string|in:link,outline,solid',
            'footer' => 'nullable|array',
            'footer.copyrightText' => 'nullable|string|max:255',
            'footer.showSocialLinks' => 'nullable|boolean',
            'footer.socialLinks' => 'nullable|array',
            'footer.columns' => 'nullable|array',
            'footer.backgroundColor' => 'nullable|string|max:20',
            'footer.textColor' => 'nullable|string|max:20',
            'footer.logo' => 'nullable|string|max:500',
            'footer.layout' => 'nullable|string|in:columns,centered,split,minimal,stacked,newsletter,social,contact',
        ]);

        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        try {
            $currentSettings = $site->getSettings() ?? [];
            
            if (isset($validated['navigation'])) {
                $currentSettings['navigation'] = $validated['navigation'];
                
                // Sync navigation logo to site.logo for Settings page compatibility
                if (isset($validated['navigation']['logo'])) {
                    $site->updateLogo($validated['navigation']['logo']);
                }
            }
            
            if (isset($validated['footer'])) {
                $currentSettings['footer'] = $validated['footer'];
            }

            $site->setSettings($currentSettings);
            $this->siteRepository->save($site);

            return response()->json([
                'success' => true,
                'settings' => $currentSettings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    private function getSectionTypes(): array
    {
        return [
            [
                'type' => 'hero',
                'name' => 'Hero Banner',
                'icon' => 'photo',
                'category' => 'header',
                'description' => 'Large banner with title, subtitle, and call-to-action',
            ],
            [
                'type' => 'about',
                'name' => 'About Section',
                'icon' => 'information-circle',
                'category' => 'content',
                'description' => 'Tell visitors about your business',
            ],
            [
                'type' => 'services',
                'name' => 'Services Grid',
                'icon' => 'squares-2x2',
                'category' => 'content',
                'description' => 'Display your services in a grid',
            ],
            [
                'type' => 'gallery',
                'name' => 'Image Gallery',
                'icon' => 'photo',
                'category' => 'media',
                'description' => 'Showcase images in a gallery',
            ],
            [
                'type' => 'testimonials',
                'name' => 'Testimonials',
                'icon' => 'chat-bubble-left-right',
                'category' => 'social-proof',
                'description' => 'Customer reviews and testimonials',
            ],
            [
                'type' => 'contact',
                'name' => 'Contact Form',
                'icon' => 'envelope',
                'category' => 'forms',
                'description' => 'Contact form with optional map',
            ],
            [
                'type' => 'cta',
                'name' => 'Call to Action',
                'icon' => 'megaphone',
                'category' => 'conversion',
                'description' => 'Encourage visitors to take action',
            ],
            [
                'type' => 'text',
                'name' => 'Text Block',
                'icon' => 'document-text',
                'category' => 'content',
                'description' => 'Rich text content block',
            ],
            [
                'type' => 'features',
                'name' => 'Features List',
                'icon' => 'check-badge',
                'category' => 'content',
                'description' => 'Highlight key features',
            ],
            [
                'type' => 'pricing',
                'name' => 'Pricing Table',
                'icon' => 'currency-dollar',
                'category' => 'conversion',
                'description' => 'Display pricing plans',
            ],
        ];
    }

    private function siteToArray($site): array
    {
        return [
            'id' => $site->getId()->value(),
            'name' => $site->getName(),
            'subdomain' => $site->getSubdomain()->value(),
            'customDomain' => $site->getCustomDomain(),
            'theme' => $site->getTheme()?->toArray(),
            'settings' => $site->getSettings(),
            'status' => $site->getStatus()->value(),
            'plan' => $site->getPlan()->value(),
            'url' => $site->getUrl(),
        ];
    }

    private function pageToArray($page): array
    {
        return [
            'id' => $page->getId()->value(),
            'title' => $page->getTitle(),
            'slug' => $page->getSlug(),
            'content' => $page->getContent()->toArray(),
            'metaTitle' => $page->getMetaTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'isHomepage' => $page->isHomepage(),
            'isPublished' => $page->isPublished(),
            'showInNav' => $page->showInNav(),
            'navOrder' => $page->getNavOrder(),
        ];
    }
}
