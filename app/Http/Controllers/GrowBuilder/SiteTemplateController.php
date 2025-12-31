<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplateIndustry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteTemplateController extends Controller
{
    /**
     * Live preview page for a template
     */
    public function livePreview(int $id): Response
    {
        $template = SiteTemplate::with('pages')->findOrFail($id);

        // Generate a preview URL for the iframe
        $previewUrl = route('growbuilder.templates.render', $id);

        return Inertia::render('GrowBuilder/Templates/Preview', [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'industry' => $template->industry,
                'thumbnail' => $template->thumbnail_url,
                'theme' => $template->theme,
                'settings' => $template->settings,
                'isPremium' => $template->is_premium,
                'pages' => $template->pages->map(fn($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'isHomepage' => $p->is_homepage,
                    'showInNav' => $p->show_in_nav,
                ]),
            ],
            'previewUrl' => $previewUrl,
        ]);
    }

    /**
     * Render template page for iframe preview (returns Site.vue with template data)
     */
    public function renderPreview(int $id, ?string $pageSlug = null): Response
    {
        $template = SiteTemplate::with('pages')->findOrFail($id);
        
        // Find the requested page or default to homepage
        $page = $pageSlug 
            ? $template->pages->firstWhere('slug', $pageSlug)
            : $template->pages->firstWhere('is_homepage', true);
        
        if (!$page) {
            $page = $template->pages->first();
        }

        // Create a fake site object for the preview
        $fakeSite = [
            'id' => 0,
            'name' => $template->name,
            'subdomain' => 'template-preview',
            'theme' => $template->theme,
            'logo' => null,
            'favicon' => null,
            'url' => '#',
        ];

        // Navigation pages
        $navPages = $template->pages
            ->filter(fn($p) => $p->show_in_nav)
            ->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'isHomepage' => $p->is_homepage,
            ]);

        return Inertia::render('GrowBuilder/Preview/Site', [
            'site' => $fakeSite,
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'isHomepage' => $page->is_homepage,
            ],
            'pages' => $navPages,
            'settings' => $template->settings,
            'products' => [],
            'isTemplatePreview' => true,
        ]);
    }

    /**
     * Get all active site templates (API)
     */
    public function index(Request $request)
    {
        $query = SiteTemplate::with('pages')
            ->active()
            ->orderBy('sort_order');

        // Filter by industry
        if ($request->has('industry') && $request->industry !== 'all') {
            $query->byIndustry($request->industry);
        }

        // Filter free/premium
        if ($request->has('type')) {
            if ($request->type === 'free') {
                $query->free();
            } elseif ($request->type === 'premium') {
                $query->where('is_premium', true);
            }
        }

        $templates = $query->get()->map(fn($t) => $this->formatTemplate($t));
        
        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Get industries for filtering
     */
    public function industries()
    {
        $industries = SiteTemplateIndustry::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($i) => [
                'slug' => $i->slug,
                'name' => $i->name,
                'icon' => $i->icon,
            ]);

        return response()->json([
            'industries' => $industries,
        ]);
    }

    /**
     * Get single template details
     */
    public function show(int $id)
    {
        $template = SiteTemplate::with('pages')->findOrFail($id);

        return response()->json([
            'template' => $this->formatTemplate($template, true),
        ]);
    }

    /**
     * Preview template (for modal preview)
     */
    public function preview(int $id)
    {
        $template = SiteTemplate::with('pages')->findOrFail($id);

        return response()->json([
            'template' => $this->formatTemplate($template, true),
            'pages' => $template->pages->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'isHomepage' => $p->is_homepage,
                'showInNav' => $p->show_in_nav,
                'content' => $p->content,
            ]),
        ]);
    }

    /**
     * Format template for API response
     */
    private function formatTemplate(SiteTemplate $template, bool $includePages = false): array
    {
        $data = [
            'id' => $template->id,
            'name' => $template->name,
            'slug' => $template->slug,
            'description' => $template->description,
            'industry' => $template->industry,
            'thumbnail' => $template->thumbnail_url,
            'theme' => $template->theme,
            'isPremium' => $template->is_premium,
            'usageCount' => $template->usage_count,
            'pagesCount' => $template->pages->count(),
        ];

        if ($includePages) {
            $data['pages'] = $template->pages->map(fn($p) => [
                'title' => $p->title,
                'slug' => $p->slug,
                'isHomepage' => $p->is_homepage,
            ]);
            $data['settings'] = $template->settings;
        }

        return $data;
    }
}
