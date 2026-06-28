<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->when($request->type, function($query, $type) {
                return $query->where('activity_type', $type);
            })
            ->when($request->user_id, function($query, $userId) {
                return $query->where('user_id', $userId);
            });

        if (!auth()->user()->hasRole('Administrator')) {
            $query->where('user_id', auth()->id());
        }

        return Inertia::render('ActivityLog/Index', [
            'activities' => $query->latest()->paginate(15),
            'types' => ActivityLog::distinct('activity_type')->pluck('activity_type')
        ]);
    }

    public static function log($userId, $activityType, $description, $data = [])
    {
        ActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data
        ]);
    }

    public function clear()
    {
        $this->authorize('delete', ActivityLog::class);

        ActivityLog::where('created_at', '<', now()->subMonths(3))->delete();

        return back()->with('success', 'Old activity logs cleared successfully');
    }
}
