<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Infrastructure\Persistence\Eloquent\Announcement\AnnouncementModel;
use Illuminate\Support\Facades\Validator;

class AnnouncementManagementController extends Controller
{
    /**
     * Display announcement management page
     */
    public function index(Request $request)
    {
        $announcements = AnnouncementModel::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * Store a new announcement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,success,urgent',
            'target_audience' => 'required|string',
            'is_urgent' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $announcement = AnnouncementModel::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'target_audience' => $request->target_audience,
            'is_urgent' => $request->is_urgent ?? false,
            'is_active' => $request->is_active ?? true,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Update an announcement
     */
    public function update(Request $request, $id)
    {
        $announcement = AnnouncementModel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,success,urgent',
            'target_audience' => 'required|string',
            'is_urgent' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $announcement->update([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'target_audience' => $request->target_audience,
            'is_urgent' => $request->is_urgent ?? false,
            'is_active' => $request->is_active ?? $announcement->is_active,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Toggle announcement active status
     */
    public function toggleActive($id)
    {
        $announcement = AnnouncementModel::findOrFail($id);
        $announcement->is_active = !$announcement->is_active;
        $announcement->save();

        return back()->with('success', 'Announcement status updated!');
    }

    /**
     * Delete an announcement
     */
    public function destroy($id)
    {
        $announcement = AnnouncementModel::findOrFail($id);
        $announcement->delete();

        return back()->with('success', 'Announcement deleted successfully!');
    }

    /**
     * Get announcement statistics
     */
    public function stats($id)
    {
        $announcement = AnnouncementModel::findOrFail($id);
        
        $totalReads = $announcement->reads()->count();
        $totalUsers = \App\Models\User::count();
        $readPercentage = $totalUsers > 0 ? round(($totalReads / $totalUsers) * 100, 1) : 0;

        return response()->json([
            'total_reads' => $totalReads,
            'total_users' => $totalUsers,
            'read_percentage' => $readPercentage,
            'unread_count' => $totalUsers - $totalReads,
        ]);
    }
}
