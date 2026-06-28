<?php

namespace App\Http\Controllers\Wedding;

use App\Http\Controllers\Controller;
use App\Domain\Wedding\Repositories\WeddingRsvpRepositoryInterface;
use App\Domain\Wedding\Repositories\WeddingGuestRepositoryInterface;
use App\Domain\Wedding\Entities\WeddingRsvp;
use App\Domain\Wedding\Entities\WeddingGuest;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingEventModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WeddingAdminController extends Controller
{
    public function __construct(
        private WeddingRsvpRepositoryInterface $rsvpRepository,
        private WeddingGuestRepositoryInterface $guestRepository
    ) {}

    /**
     * Check if user has admin access for this wedding
     */
    private function checkAccess(Request $request, string $slug): true|JsonResponse|RedirectResponse
    {
        if (session('wedding_admin_access_' . $slug)) {
            return true;
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Session expired. Please log in again.',
                'redirect' => route('wedding.admin.access', $slug)
            ], 401);
        }

        return redirect()->route('wedding.admin.access', $slug);
    }

    /**
     * Private access page - requires access code
     */
    public function accessPage(string $slug)
    {
        Inertia::setRootView('wedding');
        
        return Inertia::render('Wedding/Admin/Access', [
            'slug' => $slug,
        ]);
    }

    /**
     * Verify access code and redirect to dashboard
     */
    public function verifyAccess(Request $request, string $slug)
    {
        $validated = $request->validate([
            'access_code' => 'required|string',
        ]);

        // Try to find wedding event by slug
        $weddingEvent = WeddingEventModel::where('slug', $slug)->first();

        if ($weddingEvent && $weddingEvent->access_code) {
            // Use database access code
            if ($validated['access_code'] !== $weddingEvent->access_code) {
                return back()->withErrors(['access_code' => 'Invalid access code']);
            }
        } else {
            // Fallback to hardcoded codes for demo/legacy
            $validCodes = [
                'kaoma-and-mubanga-dec-2025' => 'KAOMA2025',
                'demo' => 'DEMO2025',
            ];

            $expectedCode = $validCodes[$slug] ?? null;

            if (!$expectedCode || $validated['access_code'] !== $expectedCode) {
                return back()->withErrors(['access_code' => 'Invalid access code']);
            }
        }

        // Store access in session
        session(['wedding_admin_access_' . $slug => true]);

        return redirect()->route('wedding.admin.dashboard', $slug);
    }

    /**
     * Wedding admin dashboard - view guests with RSVP status
     */
    public function dashboard(string $slug)
    {
        if (!session('wedding_admin_access_' . $slug)) {
            return redirect()->route('wedding.admin.access', $slug);
        }

        $weddingEventId = $this->getWeddingEventId($slug);
        
        $guests = $this->guestRepository->findByWeddingEventId($weddingEventId);
        $stats = $this->guestRepository->getStats($weddingEventId);

        // Convert domain entities to arrays
        $guestData = array_map(fn($guest) => $guest->toArray(), $guests);

        Inertia::setRootView('wedding');
        
        return Inertia::render('Wedding/Admin/Dashboard', [
            'slug' => $slug,
            'weddingName' => $this->getWeddingName($slug),
            'guests' => $guestData,
            'stats' => $stats,
        ]);
    }

    /**
     * Add an RSVP manually (for responses)
     */
    public function addGuest(Request $request, string $slug)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'attending' => 'required',
            'guest_count' => 'required|integer|min:1|max:10',
            'dietary_restrictions' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:1000',
        ]);

        $weddingEventId = $this->getWeddingEventId($slug);
        $isAttending = filter_var($validated['attending'], FILTER_VALIDATE_BOOLEAN);

        $rsvp = WeddingRsvp::create(
            weddingEventId: $weddingEventId,
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            email: $validated['email'] ?? '',
            phone: $validated['phone'] ?? null,
            attending: $isAttending,
            guestCount: $validated['guest_count'],
            dietaryRestrictions: $validated['dietary_restrictions'] ?? null,
            message: $validated['message'] ?? 'Added manually by admin'
        );

        $this->rsvpRepository->save($rsvp);

        return redirect()->back();
    }

    /**
     * Add to invited guest list
     */
    public function addInvitedGuest(Request $request, string $slug)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'allowed_guests' => 'required|integer|min:1|max:10',
            'group_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $weddingEventId = $this->getWeddingEventId($slug);

        $guest = WeddingGuest::create(
            weddingEventId: $weddingEventId,
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            allowedGuests: $validated['allowed_guests'],
            groupName: $validated['group_name'] ?? null,
            notes: $validated['notes'] ?? null
        );

        $this->guestRepository->save($guest);

        return redirect()->back();
    }

    /**
     * Update an RSVP
     */
    public function updateGuest(Request $request, string $slug, int $id)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'attending' => 'required',
            'guest_count' => 'required|integer|min:1|max:10',
            'dietary_restrictions' => 'nullable|string|max:1000',
        ]);

        $existingRsvp = $this->rsvpRepository->findById($id);
        
        if (!$existingRsvp) {
            return redirect()->back()->withErrors(['error' => 'RSVP not found']);
        }

        $isAttending = filter_var($validated['attending'], FILTER_VALIDATE_BOOLEAN);

        $updatedRsvp = WeddingRsvp::fromArray([
            'id' => $id,
            'wedding_event_id' => $existingRsvp->getWeddingEventId(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? '',
            'phone' => $validated['phone'] ?? null,
            'attending' => $isAttending,
            'guest_count' => $validated['guest_count'],
            'dietary_restrictions' => $validated['dietary_restrictions'] ?? null,
            'message' => $existingRsvp->getMessage(),
            'submitted_at' => $existingRsvp->getSubmittedAt()->format('Y-m-d H:i:s'),
        ]);

        $this->rsvpRepository->update($updatedRsvp);

        return redirect()->back();
    }

    /**
     * Update an invited guest
     */
    public function updateInvitedGuest(Request $request, string $slug, int $id)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'allowed_guests' => 'required|integer|min:1|max:10',
            'group_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'rsvp_status' => 'nullable|in:pending,attending,declined',
            'confirmed_guests' => 'nullable|integer|min:0|max:10',
        ]);

        $existingGuest = $this->guestRepository->findById($id);
        
        if (!$existingGuest) {
            return redirect()->back()->withErrors(['error' => 'Guest not found']);
        }

        $updatedGuest = WeddingGuest::fromArray([
            'id' => $id,
            'wedding_event_id' => $existingGuest->getWeddingEventId(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'allowed_guests' => $validated['allowed_guests'],
            'group_name' => $validated['group_name'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'invitation_sent' => $existingGuest->isInvitationSent(),
            'rsvp_status' => $validated['rsvp_status'] ?? $existingGuest->getRsvpStatus(),
            'confirmed_guests' => $validated['confirmed_guests'] ?? $existingGuest->getConfirmedGuests(),
            'dietary_restrictions' => $existingGuest->getDietaryRestrictions(),
            'rsvp_message' => $existingGuest->getRsvpMessage(),
            'rsvp_submitted_at' => $existingGuest->getRsvpSubmittedAt()?->format('Y-m-d H:i:s'),
        ]);

        $this->guestRepository->update($updatedGuest);

        return redirect()->back();
    }

    /**
     * Delete an RSVP
     */
    public function deleteGuest(Request $request, string $slug, int $id)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $this->rsvpRepository->delete($id);

        return redirect()->back();
    }

    /**
     * Delete an invited guest
     */
    public function deleteInvitedGuest(Request $request, string $slug, int $id)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $this->guestRepository->delete($id);

        return redirect()->back();
    }

    /**
     * Bulk import guests from CSV
     */
    public function importGuests(Request $request, string $slug)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $validated = $request->validate([
            'guests' => 'required|array',
            'guests.*.first_name' => 'required|string|max:255',
            'guests.*.last_name' => 'required|string|max:255',
            'guests.*.email' => 'nullable|email|max:255',
            'guests.*.phone' => 'nullable|string|max:20',
            'guests.*.allowed_guests' => 'nullable|integer|min:1|max:10',
            'guests.*.group_name' => 'nullable|string|max:100',
        ]);

        $weddingEventId = $this->getWeddingEventId($slug);
        $this->guestRepository->bulkImport($weddingEventId, $validated['guests']);

        return redirect()->back();
    }

    /**
     * Generate a new access code for the wedding
     */
    public function generateAccessCode(Request $request, string $slug)
    {
        $access = $this->checkAccess($request, $slug);
        if ($access !== true) {
            return $access;
        }

        $weddingEvent = WeddingEventModel::where('slug', $slug)->first();

        if (!$weddingEvent) {
            return redirect()->back()->withErrors(['error' => 'Wedding not found']);
        }

        // Generate a new 8-character access code
        $newCode = strtoupper(Str::random(8));
        $weddingEvent->access_code = $newCode;
        $weddingEvent->save();

        return redirect()->back();
    }

    /**
     * Logout from admin
     */
    public function logout(string $slug)
    {
        session()->forget('wedding_admin_access_' . $slug);
        return redirect()->route('wedding.admin.access', $slug);
    }

    /**
     * Export RSVPs as CSV
     */
    public function exportCsv(string $slug)
    {
        if (!session('wedding_admin_access_' . $slug)) {
            return redirect()->route('wedding.admin.access', $slug);
        }

        $weddingEventId = $this->getWeddingEventId($slug);
        $rsvps = $this->rsvpRepository->findByWeddingEventId($weddingEventId);

        $filename = "rsvps-{$slug}-" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($rsvps) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Name', 'Email', 'Phone', 'Attending', 'Guest Count', 
                'Dietary Restrictions', 'Message', 'Submitted At'
            ]);

            foreach ($rsvps as $rsvp) {
                fputcsv($file, [
                    $rsvp->getFullName(),
                    $rsvp->getEmail(),
                    $rsvp->getPhone() ?? '',
                    $rsvp->isAttending() ? 'Yes' : 'No',
                    $rsvp->getGuestCount(),
                    $rsvp->getDietaryRestrictions() ?? '',
                    $rsvp->getMessage() ?? '',
                    $rsvp->getSubmittedAt()->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export guest list as CSV
     */
    public function exportGuestsCsv(string $slug)
    {
        if (!session('wedding_admin_access_' . $slug)) {
            return redirect()->route('wedding.admin.access', $slug);
        }

        $weddingEventId = $this->getWeddingEventId($slug);
        $guests = $this->guestRepository->findByWeddingEventId($weddingEventId);

        $filename = "guest-list-{$slug}-" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($guests) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Name', 'Email', 'Phone', 'Group', 'Allowed Guests', 
                'RSVP Status', 'Confirmed Guests', 'Dietary Restrictions', 'Response Date'
            ]);

            foreach ($guests as $guest) {
                fputcsv($file, [
                    $guest->getFullName(),
                    $guest->getEmail() ?? '',
                    $guest->getPhone() ?? '',
                    $guest->getGroupName() ?? '',
                    $guest->getAllowedGuests(),
                    ucfirst($guest->getRsvpStatus()),
                    $guest->getConfirmedGuests(),
                    $guest->getDietaryRestrictions() ?? '',
                    $guest->getRsvpSubmittedAt()?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getWeddingEventId(string $slug): int
    {
        $weddingEvent = WeddingEventModel::where('slug', $slug)->first();
        
        if ($weddingEvent) {
            return $weddingEvent->id;
        }

        // Fallback for demo/legacy
        $eventIds = [
            'kaoma-and-mubanga-dec-2025' => 1,
            'demo' => 1,
        ];

        return $eventIds[$slug] ?? 1;
    }

    private function getWeddingName(string $slug): string
    {
        $weddingEvent = WeddingEventModel::where('slug', $slug)->first();
        
        if ($weddingEvent) {
            return $weddingEvent->partner_name . ' Wedding';
        }

        $names = [
            'kaoma-and-mubanga-dec-2025' => 'Kaoma & Mubanga',
            'demo' => 'Demo Wedding',
        ];

        return $names[$slug] ?? 'Wedding';
    }
}
