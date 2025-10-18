<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/ActivityLog/Index', [
            'activities' => $activities
        ]);
    }

    public function filter(Request $request)
    {
        $activities = ActivityLog::with('user')
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->date_range, function ($query, $dateRange) {
                $dates = explode(',', $dateRange);
                $query->whereBetween('created_at', $dates);
            })
            ->latest()
            ->paginate(20);

        return response()->json($activities);
    }
}
