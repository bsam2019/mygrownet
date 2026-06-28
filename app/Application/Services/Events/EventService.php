<?php

namespace App\Application\Services\Events;

use App\Infrastructure\Persistence\Eloquent\Events\LiveEventModel;
use App\Infrastructure\Persistence\Eloquent\Events\EventRegistrationModel;
use App\Infrastructure\Persistence\Eloquent\Events\EventAttendanceModel;
use App\Services\LgrActivityTrackingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventService
{
    public function __construct(
        private LgrActivityTrackingService $lgrTrackingService
    ) {}

    /**
     * Get all published events
     */
    public function getPublishedEvents(string $filter = 'all')
    {
        $query = LiveEventModel::published();

        match ($filter) {
            'upcoming' => $query->upcoming(),
            'past' => $query->past(),
            'happening_now' => $query->happeningNow(),
            default => null,
        };

        return $query->orderBy('scheduled_at', 'desc')->get();
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents()
    {
        return LiveEventModel::published()
            ->upcoming()
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Get event by slug
     */
    public function getEventBySlug(string $slug): ?LiveEventModel
    {
        return LiveEventModel::where('slug', $slug)
            ->published()
            ->first();
    }

    /**
     * Check if user is registered for event
     */
    public function isUserRegistered(int $userId, int $eventId): bool
    {
        return EventRegistrationModel::where('user_id', $userId)
            ->where('live_event_id', $eventId)
            ->exists();
    }

    /**
     * Check if user has attended event
     */
    public function hasUserAttended(int $userId, int $eventId): bool
    {
        return EventAttendanceModel::where('user_id', $userId)
            ->where('live_event_id', $eventId)
            ->exists();
    }

    /**
     * Register user for event
     */
    public function registerUser(int $userId, int $eventId): bool
    {
        try {
            // Check if already registered
            if ($this->isUserRegistered($userId, $eventId)) {
                return false;
            }

            // Get event to check max attendees
            $event = LiveEventModel::findOrFail($eventId);

            if ($event->max_attendees) {
                $currentRegistrations = EventRegistrationModel::where('live_event_id', $eventId)->count();
                if ($currentRegistrations >= $event->max_attendees) {
                    throw new \Exception('Event is full');
                }
            }

            EventRegistrationModel::create([
                'user_id' => $userId,
                'live_event_id' => $eventId,
                'registered_at' => now(),
            ]);

            Log::info('User registered for event', [
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to register user for event: ' . $e->getMessage(), [
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);
            throw $e;
        }
    }

    /**
     * Check in user to event
     * CRITICAL: This triggers LGR activity tracking
     */
    public function checkInUser(int $userId, int $eventId, string $method = 'manual'): bool
    {
        try {
            DB::beginTransaction();

            // Check if already checked in
            if ($this->hasUserAttended($userId, $eventId)) {
                DB::rollBack();
                return false;
            }

            // Get the event
            $event = LiveEventModel::findOrFail($eventId);

            // Create attendance record
            EventAttendanceModel::create([
                'user_id' => $userId,
                'live_event_id' => $eventId,
                'checked_in_at' => now(),
                'check_in_method' => $method,
            ]);

            // CRITICAL: Record LGR activity
            $this->lgrTrackingService->recordEventAttendance($userId, $event->title, $eventId);

            DB::commit();

            Log::info('User checked in to event', [
                'user_id' => $userId,
                'event_id' => $eventId,
                'event_title' => $event->title,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to check in user to event: ' . $e->getMessage(), [
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);
            throw $e;
        }
    }

    /**
     * Check out user from event
     */
    public function checkOutUser(int $userId, int $eventId): bool
    {
        try {
            $attendance = EventAttendanceModel::where('user_id', $userId)
                ->where('live_event_id', $eventId)
                ->first();

            if (!$attendance) {
                return false;
            }

            // Calculate attendance duration
            $checkedInAt = $attendance->checked_in_at;
            $checkedOutAt = now();
            $attendanceMinutes = $checkedInAt->diffInMinutes($checkedOutAt);

            $attendance->update([
                'checked_out_at' => $checkedOutAt,
                'attendance_minutes' => $attendanceMinutes,
            ]);

            Log::info('User checked out from event', [
                'user_id' => $userId,
                'event_id' => $eventId,
                'attendance_minutes' => $attendanceMinutes,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to check out user from event: ' . $e->getMessage(), [
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);
            throw $e;
        }
    }

    /**
     * Get user's registered events
     */
    public function getUserRegistrations(int $userId)
    {
        return EventRegistrationModel::forUser($userId)
            ->with('event')
            ->orderBy('registered_at', 'desc')
            ->get();
    }

    /**
     * Get user's attended events
     */
    public function getUserAttendances(int $userId)
    {
        return EventAttendanceModel::forUser($userId)
            ->with('event')
            ->orderBy('checked_in_at', 'desc')
            ->get();
    }

    /**
     * Get user's attendance count for today
     */
    public function getUserAttendanceCountToday(int $userId): int
    {
        return EventAttendanceModel::forUser($userId)
            ->checkedInToday()
            ->count();
    }

    /**
     * Get event statistics
     */
    public function getEventStatistics(int $eventId): array
    {
        $registrations = EventRegistrationModel::where('live_event_id', $eventId)->count();
        $attendances = EventAttendanceModel::where('live_event_id', $eventId)->count();

        return [
            'registrations' => $registrations,
            'attendances' => $attendances,
            'attendance_rate' => $registrations > 0 
                ? round(($attendances / $registrations) * 100, 2) 
                : 0,
        ];
    }
}
