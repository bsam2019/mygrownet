<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaActivityLogModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $query = SaActivityLogModel::where('sa_company_id', $companyId)
            ->with('user')
            ->orderBy('occurred_at', 'desc');

        if ($request->filled('event')) {
            $query->where('event_name', $request->event);
        }

        $logs = $query->paginate(20)->through(function ($log) {
            return [
                'id' => $log->id,
                'user_name' => $log->user?->name ?? 'System',
                'event' => $log->event_name,
                'context' => $log->context,
                'description' => $log->description,
                'properties' => $log->payload,
                'created_at' => $log->occurred_at?->format('Y-m-d H:i:s'),
            ];
        });

        $events = SaActivityLogModel::where('sa_company_id', $companyId)
            ->select('event_name')
            ->distinct()
            ->pluck('event_name');

        return Inertia::render('StockFlow/ActivityLog/Index', [
            'logs' => $logs,
            'events' => $events,
            'filters' => $request->only('event'),
        ]);
    }
}
