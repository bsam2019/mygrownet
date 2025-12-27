<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SiteContactMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteContactController extends Controller
{
    /**
     * Store a contact message from the public site
     */
    public function store(Request $request, string $subdomain)
    {
        $site = GrowBuilderSite::where('subdomain', $subdomain)
            ->where('status', 'published')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        SiteContactMessage::create([
            'site_id' => $site->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        return response()->json(['success' => true, 'message' => 'Message sent successfully!']);
    }

    /**
     * List messages for dashboard (admin view)
     */
    public function index(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $query = SiteContactMessage::forSite($site->id)
            ->with('repliedByUser')
            ->notArchived();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->paginate(15);
        $unreadCount = SiteContactMessage::forSite($site->id)->unread()->count();

        return Inertia::render('SiteMember/Messages/Index', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show single message
     */
    public function show(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $message = SiteContactMessage::forSite($site->id)
            ->with('repliedByUser')
            ->findOrFail($id);

        // Mark as read
        $message->markAsRead();

        return Inertia::render('SiteMember/Messages/Show', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'message' => $message,
        ]);
    }

    /**
     * Reply to a message
     */
    public function reply(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $message = SiteContactMessage::forSite($site->id)->findOrFail($id);

        $validated = $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        $message->update([
            'reply' => $validated['reply'],
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => $user->id,
        ]);

        // TODO: Send email notification to the sender

        return back()->with('success', 'Reply saved successfully.');
    }

    /**
     * Update message status
     */
    public function updateStatus(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');

        $message = SiteContactMessage::forSite($site->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:unread,read,replied,archived',
        ]);

        $message->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated.');
    }

    /**
     * Delete a message
     */
    public function destroy(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');

        $message = SiteContactMessage::forSite($site->id)->findOrFail($id);
        $message->delete();

        return redirect()
            ->route('site.member.messages.index', ['subdomain' => $subdomain])
            ->with('success', 'Message deleted.');
    }

    protected function getSiteData($site): array
    {
        $settings = $site->settings ?? [];
        $logo = $settings['navigation']['logo'] ?? $site->logo ?? null;
        
        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'logo' => $logo,
            'theme' => $site->theme,
        ];
    }

    protected function getUserData($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ? [
                'name' => $user->role->name,
                'slug' => $user->role->slug,
                'level' => $user->role->level,
                'type' => $user->role->type ?? 'client',
                'color' => $user->role->color,
            ] : null,
            'permissions' => $user->role 
                ? $user->role->permissions->pluck('slug')->toArray() 
                : [],
        ];
    }
}
