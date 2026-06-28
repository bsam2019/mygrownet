<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Wedding\Repositories\WeddingEventRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingTemplateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingEventModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class WeddingCardController extends Controller
{
    public function __construct(
        private WeddingEventRepositoryInterface $weddingEventRepository,
        private WeddingTemplateRepositoryInterface $templateRepository
    ) {}

    /**
     * List all wedding cards
     */
    public function index()
    {
        $weddings = WeddingEventModel::with('template')
            ->orderBy('wedding_date', 'desc')
            ->get()
            ->map(fn($wedding) => [
                'id' => $wedding->id,
                'groom_name' => $wedding->groom_name,
                'bride_name' => $wedding->bride_name,
                'slug' => $wedding->slug,
                'wedding_date' => $wedding->wedding_date?->format('Y-m-d'),
                'venue_name' => $wedding->venue_name,
                'template' => $wedding->template ? [
                    'id' => $wedding->template->id,
                    'name' => $wedding->template->name,
                ] : null,
                'is_published' => $wedding->is_published,
                'guest_count' => $wedding->guest_count,
                'status' => $wedding->status,
                'created_at' => $wedding->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Weddings/Index', [
            'weddings' => $weddings,
        ]);
    }

    /**
     * Show create wedding card form
     */
    public function create()
    {
        $templates = array_map(fn($t) => $t->toArray(), $this->templateRepository->findActive());

        return Inertia::render('Admin/Weddings/Create', [
            'templates' => $templates,
        ]);
    }

    /**
     * Store new wedding card
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:wedding_templates,id',
            'groom_name' => 'required|string|max:255',
            'bride_name' => 'required|string|max:255',
            'wedding_date' => 'required|date',
            'venue_name' => 'required|string|max:255',
            'venue_location' => 'required|string|max:255',
            'ceremony_time' => 'nullable|string|max:50',
            'reception_time' => 'nullable|string|max:50',
            'reception_venue' => 'nullable|string|max:255',
            'reception_address' => 'nullable|string|max:255',
            'dress_code' => 'nullable|string|max:100',
            'rsvp_deadline' => 'nullable|date',
            'guest_count' => 'nullable|integer|min:0',
            'how_we_met' => 'nullable|string|max:2000',
            'proposal_story' => 'nullable|string|max:2000',
            'hero_image' => 'nullable|image|max:5120', // 5MB max
            'story_image' => 'nullable|image|max:5120',
        ]);

        // Generate slug from couple names
        $slug = WeddingEventModel::generateSlug(
            $validated['groom_name'],
            $validated['bride_name'],
            $validated['wedding_date']
        );

        // Generate access code
        $accessCode = strtoupper(Str::random(8));

        // Handle image uploads
        $heroImage = null;
        $storyImage = null;

        if ($request->hasFile('hero_image')) {
            $heroImage = $request->file('hero_image')->store('wedding/heroes', 'public');
            $heroImage = '/storage/' . $heroImage;
        }

        if ($request->hasFile('story_image')) {
            $storyImage = $request->file('story_image')->store('wedding/stories', 'public');
            $storyImage = '/storage/' . $storyImage;
        }

        $wedding = WeddingEventModel::create([
            'template_id' => $validated['template_id'],
            'groom_name' => $validated['groom_name'],
            'bride_name' => $validated['bride_name'],
            'partner_name' => $validated['bride_name'], // For backward compatibility
            'slug' => $slug,
            'wedding_date' => $validated['wedding_date'],
            'venue_name' => $validated['venue_name'],
            'venue_location' => $validated['venue_location'],
            'ceremony_time' => $validated['ceremony_time'] ?? '11:00 AM',
            'reception_time' => $validated['reception_time'] ?? '2:00 PM',
            'reception_venue' => $validated['reception_venue'] ?? $validated['venue_name'],
            'reception_address' => $validated['reception_address'] ?? $validated['venue_location'],
            'dress_code' => $validated['dress_code'] ?? 'Formal Attire',
            'rsvp_deadline' => $validated['rsvp_deadline'] ?? Carbon::parse($validated['wedding_date'])->subDays(7),
            'guest_count' => $validated['guest_count'] ?? 100,
            'how_we_met' => $validated['how_we_met'],
            'proposal_story' => $validated['proposal_story'],
            'hero_image' => $heroImage,
            'story_image' => $storyImage,
            'access_code' => $accessCode,
            'status' => 'planning',
            'is_published' => false,
            'budget' => 0,
        ]);

        return redirect()->route('admin.weddings.edit', $wedding->id)
            ->with('success', 'Wedding card created successfully!');
    }

    /**
     * Show edit wedding card form
     */
    public function edit(int $id)
    {
        $wedding = WeddingEventModel::with('template')->findOrFail($id);
        $templates = array_map(fn($t) => $t->toArray(), $this->templateRepository->findActive());

        return Inertia::render('Admin/Weddings/Edit', [
            'wedding' => [
                'id' => $wedding->id,
                'template_id' => $wedding->template_id,
                'groom_name' => $wedding->groom_name,
                'bride_name' => $wedding->bride_name,
                'slug' => $wedding->slug,
                'wedding_date' => $wedding->wedding_date?->format('Y-m-d'),
                'venue_name' => $wedding->venue_name,
                'venue_location' => $wedding->venue_location,
                'ceremony_time' => $wedding->ceremony_time,
                'reception_time' => $wedding->reception_time,
                'reception_venue' => $wedding->reception_venue,
                'reception_address' => $wedding->reception_address,
                'dress_code' => $wedding->dress_code,
                'rsvp_deadline' => $wedding->rsvp_deadline?->format('Y-m-d'),
                'guest_count' => $wedding->guest_count,
                'how_we_met' => $wedding->how_we_met,
                'proposal_story' => $wedding->proposal_story,
                'hero_image' => $wedding->hero_image,
                'story_image' => $wedding->story_image,
                'access_code' => $wedding->access_code,
                'is_published' => $wedding->is_published,
                'status' => $wedding->status,
                'template' => $wedding->template ? [
                    'id' => $wedding->template->id,
                    'name' => $wedding->template->name,
                    'settings' => $wedding->template->settings,
                ] : null,
            ],
            'templates' => $templates,
        ]);
    }

    /**
     * Update wedding card
     */
    public function update(Request $request, int $id)
    {
        $wedding = WeddingEventModel::findOrFail($id);

        $validated = $request->validate([
            'template_id' => 'required|exists:wedding_templates,id',
            'groom_name' => 'required|string|max:255',
            'bride_name' => 'required|string|max:255',
            'wedding_date' => 'required|date',
            'venue_name' => 'required|string|max:255',
            'venue_location' => 'required|string|max:255',
            'ceremony_time' => 'nullable|string|max:50',
            'reception_time' => 'nullable|string|max:50',
            'reception_venue' => 'nullable|string|max:255',
            'reception_address' => 'nullable|string|max:255',
            'dress_code' => 'nullable|string|max:100',
            'rsvp_deadline' => 'nullable|date',
            'guest_count' => 'nullable|integer|min:0',
            'how_we_met' => 'nullable|string|max:2000',
            'proposal_story' => 'nullable|string|max:2000',
            'hero_image' => 'nullable|image|max:5120',
            'story_image' => 'nullable|image|max:5120',
        ]);

        // Handle image uploads
        if ($request->hasFile('hero_image')) {
            $heroImage = $request->file('hero_image')->store('wedding/heroes', 'public');
            $validated['hero_image'] = '/storage/' . $heroImage;
        }

        if ($request->hasFile('story_image')) {
            $storyImage = $request->file('story_image')->store('wedding/stories', 'public');
            $validated['story_image'] = '/storage/' . $storyImage;
        }

        // Update slug if names changed
        if ($wedding->groom_name !== $validated['groom_name'] || 
            $wedding->bride_name !== $validated['bride_name'] ||
            $wedding->wedding_date->format('Y-m-d') !== $validated['wedding_date']) {
            
            // Temporarily remove current slug to avoid self-conflict
            $currentSlug = $wedding->slug;
            $wedding->slug = null;
            $wedding->save();
            
            $validated['slug'] = WeddingEventModel::generateSlug(
                $validated['groom_name'],
                $validated['bride_name'],
                $validated['wedding_date']
            );
        }

        $validated['partner_name'] = $validated['bride_name'];

        $wedding->update($validated);

        return redirect()->route('admin.weddings.edit', $id)
            ->with('success', 'Wedding card updated successfully!');
    }

    /**
     * Publish/unpublish wedding card
     */
    public function togglePublish(int $id)
    {
        $wedding = WeddingEventModel::findOrFail($id);
        
        $wedding->update([
            'is_published' => !$wedding->is_published,
            'published_at' => !$wedding->is_published ? now() : null,
        ]);

        $status = $wedding->is_published ? 'published' : 'unpublished';

        return back()->with('success', "Wedding card {$status} successfully!");
    }

    /**
     * Preview wedding card
     */
    public function preview(int $id)
    {
        $wedding = WeddingEventModel::with('template')->findOrFail($id);
        
        return redirect()->route('wedding.website', $wedding->slug);
    }

    /**
     * Delete wedding card
     */
    public function destroy(int $id)
    {
        $wedding = WeddingEventModel::findOrFail($id);
        $wedding->delete();

        return redirect()->route('admin.weddings.index')
            ->with('success', 'Wedding card deleted successfully!');
    }

    /**
     * Regenerate access code
     */
    public function regenerateAccessCode(int $id)
    {
        $wedding = WeddingEventModel::findOrFail($id);
        $wedding->update(['access_code' => strtoupper(Str::random(8))]);

        return back()->with('success', 'Access code regenerated successfully!');
    }
}
