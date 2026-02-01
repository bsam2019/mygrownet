<?php

namespace App\Http\Controllers;

use App\Application\Services\Events\EventService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}

    /**
     * Display events list
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'upcoming');
        
        $events = $this->eventService->getPublishedEvents($filter);
        $registrations = $this->eventService->getUserRegistrations(auth()->id());
        $attendances = $this->eventService->getUserAttendances(auth()->id());

        return Inertia::render('Events/Index', [
            'events' => $events,
            'registrations' => $registrations,
            'attendances' => $attendances,
            'filter' => $filter,
        ]);
    }

    /**
     * Display a single event
     */
    public function show(string $slug)
    {
        $event = $this->eventService->getEventBySlug($slug);

        if (!$event) {
            abort(404, 'Event not found');
        }

        $isRegistered = $this->eventService->isUserRegistered(auth()->id(), $event->id);
        $hasAttended = $this->eventService->hasUserAttended(auth()->id(), $event->id);
        $statistics = $this->eventService->getEventStatistics($event->id);

        return Inertia::render('Events/Show', [
            'event' => $event,
            'isRegistered' => $isRegistered,
            'hasAttended' => $hasAttended,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Register for an event
     */
    public function register(Request $request, int $eventId)
    {
        try {
            $registered = $this->eventService->registerUser(auth()->id(), $eventId);

            if ($registered) {
                return back()->with('success', 'Successfully registered for event');
            }

            return back()->with('info', 'Already registered for this event');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to register: ' . $e->getMessage());
        }
    }

    /**
     * Check in to an event
     */
    public function checkIn(Request $request, int $eventId)
    {
        $request->validate([
            'method' => 'nullable|string|in:manual,qr_code,auto',
        ]);

        try {
            $checkedIn = $this->eventService->checkInUser(
                auth()->id(),
                $eventId,
                $request->input('method', 'manual')
            );

            if ($checkedIn) {
                return back()->with('success', 'Checked in successfully! LGR activity recorded.');
            }

            return back()->with('info', 'Already checked in to this event');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to check in: ' . $e->getMessage());
        }
    }

    /**
     * Check out from an event
     */
    public function checkOut(Request $request, int $eventId)
    {
        try {
            $checkedOut = $this->eventService->checkOutUser(auth()->id(), $eventId);

            if ($checkedOut) {
                return back()->with('success', 'Checked out successfully');
            }

            return back()->with('info', 'Not checked in to this event');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to check out: ' . $e->getMessage());
        }
    }
}
