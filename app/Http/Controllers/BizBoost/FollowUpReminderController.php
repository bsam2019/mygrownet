<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\FollowUpReminderService;
use App\Domain\BizBoost\Services\BusinessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FollowUpReminderController extends Controller
{
    public function __construct(
        private FollowUpReminderService $reminderService,
        private BusinessService $businessService,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $filters = $request->only(['search', 'status', 'priority', 'type']);

        return Inertia::render('BizBoost/FollowUpReminders/Index', [
            'reminders' => $this->reminderService->getReminders($business->id, $filters),
            'filters' => $filters,
            'stats' => $this->reminderService->getStats($business->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:call,email,sms,meeting,task,other',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'due_date' => 'required|date',
            'remind_at' => 'nullable|date',
            'customer_id' => 'nullable|integer|exists:biz_boost_customers,id',
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'related_type' => 'nullable|string|max:100',
            'related_id' => 'nullable|integer',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->reminderService->createReminder($business->id, $validated);

        return back()->with('success', 'Reminder created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:call,email,sms,meeting,task,other',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'due_date' => 'required|date',
            'remind_at' => 'nullable|date',
            'customer_id' => 'nullable|integer|exists:biz_boost_customers,id',
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|string|in:pending,completed,cancelled',
            'related_type' => 'nullable|string|max:100',
            'related_id' => 'nullable|integer',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->reminderService->updateReminder($business->id, $id, $validated);

        return back()->with('success', 'Reminder updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->reminderService->deleteReminder($business->id, $id);

        return back()->with('success', 'Reminder deleted successfully.');
    }

    public function complete(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->reminderService->updateReminder($business->id, $id, ['status' => 'completed']);

        return back()->with('success', 'Reminder marked as completed.');
    }

    public function snooze(Request $request, int $id)
    {
        $validated = $request->validate([
            'snooze_until' => 'required|date|after:now',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->reminderService->updateReminder($business->id, $id, ['remind_at' => $validated['snooze_until']]);

        return back()->with('success', 'Reminder snoozed.');
    }

    public function calendar(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $filters = $request->only(['date_from', 'date_to', 'status']);

        return Inertia::render('BizBoost/FollowUpReminders/Calendar', [
            'reminders' => $this->reminderService->getReminders($business->id, $filters),
        ]);
    }
}