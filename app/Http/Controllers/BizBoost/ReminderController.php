<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $this->getBusiness($request);

        $reminders = DB::table('bizboost_follow_up_reminders')
            ->leftJoin('bizboost_customers', 'bizboost_follow_up_reminders.customer_id', '=', 'bizboost_customers.id')
            ->where('bizboost_follow_up_reminders.business_id', $business->id)
            ->select(
                'bizboost_follow_up_reminders.*',
                'bizboost_customers.name as customer_name',
                'bizboost_customers.whatsapp as customer_whatsapp'
            )
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->paginate(20);

        $stats = [
            'total' => DB::table('bizboost_follow_up_reminders')->where('business_id', $business->id)->count(),
            'pending' => DB::table('bizboost_follow_up_reminders')->where('business_id', $business->id)->where('status', 'pending')->count(),
            'overdue' => DB::table('bizboost_follow_up_reminders')
                ->where('business_id', $business->id)
                ->where('status', 'pending')
                ->where('due_date', '<', now()->toDateString())
                ->count(),
            'completed_today' => DB::table('bizboost_follow_up_reminders')
                ->where('business_id', $business->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', now())
                ->count(),
        ];

        $customers = $business->customers()->select('id', 'name')->get();

        return Inertia::render('BizBoost/Reminders/Index', [
            'reminders' => $reminders,
            'stats' => $stats,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:bizboost_customers,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'required|date|after_or_equal:today',
            'due_time' => 'nullable|date_format:H:i',
            'reminder_type' => 'required|string|in:follow_up,payment,delivery,appointment,custom',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        $business = $this->getBusiness($request);

        $dueTime = $validated['due_time'] ?? '09:00';
        $remindAt = "{$validated['due_date']} {$dueTime}:00";

        DB::table('bizboost_follow_up_reminders')->insert([
            'business_id' => $business->id,
            'customer_id' => $validated['customer_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'],
            'due_time' => $dueTime,
            'remind_at' => $remindAt,
            'reminder_type' => $validated['reminder_type'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'notification_sent' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Reminder created successfully!');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:bizboost_customers,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'required|date',
            'due_time' => 'nullable|date_format:H:i',
            'reminder_type' => 'required|string|in:follow_up,payment,delivery,appointment,custom',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        $business = $this->getBusiness($request);

        $dueTime = $validated['due_time'] ?? '09:00';
        $remindAt = "{$validated['due_date']} {$dueTime}:00";

        $updated = DB::table('bizboost_follow_up_reminders')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->update([
                'customer_id' => $validated['customer_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'due_date' => $validated['due_date'],
                'due_time' => $dueTime,
                'remind_at' => $remindAt,
                'reminder_type' => $validated['reminder_type'],
                'priority' => $validated['priority'],
                'notification_sent' => false, // Reset notification flag when rescheduled
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return back()->with('error', 'Reminder not found.');
        }

        return back()->with('success', 'Reminder updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $business = $this->getBusiness($request);

        DB::table('bizboost_follow_up_reminders')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->delete();

        return back()->with('success', 'Reminder deleted.');
    }

    public function complete(Request $request, int $id): RedirectResponse
    {
        $business = $this->getBusiness($request);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        DB::table('bizboost_follow_up_reminders')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completion_notes' => $validated['notes'] ?? null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Reminder marked as complete!');
    }

    public function snooze(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'snooze_until' => 'required|date|after:today',
        ]);

        $business = $this->getBusiness($request);

        // Get the existing reminder to preserve the time
        $existing = DB::table('bizboost_follow_up_reminders')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->first();

        if (!$existing) {
            return back()->with('error', 'Reminder not found.');
        }

        $remindAt = "{$validated['snooze_until']} {$existing->due_time}:00";

        DB::table('bizboost_follow_up_reminders')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->update([
                'due_date' => $validated['snooze_until'],
                'remind_at' => $remindAt,
                'snoozed_count' => DB::raw('snoozed_count + 1'),
                'notification_sent' => false, // Reset so notification is sent again
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Reminder snoozed.');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
