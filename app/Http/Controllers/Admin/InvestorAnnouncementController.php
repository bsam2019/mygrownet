<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\AnnouncementService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvestorAnnouncementController extends Controller
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    public function index(): Response
    {
        $announcements = $this->announcementService->getAllAnnouncements();
        
        // Add stats to each announcement
        $announcementsWithStats = array_map(function ($announcement) {
            $stats = $this->announcementService->getAnnouncementStats($announcement->getId());
            return array_merge($announcement->toArray(), [
                'read_count' => $stats['read_count'] ?? 0,
                'type_label' => $announcement->getType()->label(),
                'type_color' => $announcement->getType()->color(),
                'priority_label' => $announcement->getPriority()->label(),
                'priority_color' => $announcement->getPriority()->color(),
            ]);
        }, $announcements);

        return Inertia::render('Admin/Investor/Announcements/Index', [
            'announcements' => $announcementsWithStats,
            'types' => $this->announcementService->getAnnouncementTypes(),
            'priorities' => $this->announcementService->getAnnouncementPriorities(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Investor/Announcements/Create', [
            'types' => $this->announcementService->getAnnouncementTypes(),
            'priorities' => $this->announcementService->getAnnouncementPriorities(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'type' => 'required|in:general,financial,dividend,meeting,urgent,milestone',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_pinned' => 'boolean',
            'send_email' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
            'publish_immediately' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        $announcement = $this->announcementService->createAnnouncement($validated);

        return redirect()->route('admin.investor-announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function show(int $id): Response
    {
        $announcement = $this->announcementService->getAnnouncementById($id);
        
        if (!$announcement) {
            abort(404);
        }

        $stats = $this->announcementService->getAnnouncementStats($id);

        return Inertia::render('Admin/Investor/Announcements/Show', [
            'announcement' => array_merge($announcement->toArray(), [
                'type_label' => $announcement->getType()->label(),
                'type_color' => $announcement->getType()->color(),
                'priority_label' => $announcement->getPriority()->label(),
                'priority_color' => $announcement->getPriority()->color(),
            ]),
            'stats' => $stats,
        ]);
    }

    public function edit(int $id): Response
    {
        $announcement = $this->announcementService->getAnnouncementById($id);
        
        if (!$announcement) {
            abort(404);
        }

        return Inertia::render('Admin/Investor/Announcements/Edit', [
            'announcement' => $announcement->toArray(),
            'types' => $this->announcementService->getAnnouncementTypes(),
            'priorities' => $this->announcementService->getAnnouncementPriorities(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'type' => 'required|in:general,financial,dividend,meeting,urgent,milestone',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_pinned' => 'boolean',
            'send_email' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $announcement = $this->announcementService->updateAnnouncement($id, $validated);

        if (!$announcement) {
            abort(404);
        }

        return redirect()->route('admin.investor-announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(int $id)
    {
        $deleted = $this->announcementService->deleteAnnouncement($id);

        if (!$deleted) {
            return back()->with('error', 'Failed to delete announcement.');
        }

        return redirect()->route('admin.investor-announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    public function publish(int $id)
    {
        $announcement = $this->announcementService->publishAnnouncement($id);

        if (!$announcement) {
            return back()->with('error', 'Failed to publish announcement.');
        }

        return back()->with('success', 'Announcement published successfully.');
    }

    public function unpublish(int $id)
    {
        $announcement = $this->announcementService->unpublishAnnouncement($id);

        if (!$announcement) {
            return back()->with('error', 'Failed to unpublish announcement.');
        }

        return back()->with('success', 'Announcement unpublished successfully.');
    }
}
