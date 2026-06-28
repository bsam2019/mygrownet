<?php

namespace App\Http\Controllers\Ubumi;

use App\Http\Controllers\Controller;
use App\Application\Ubumi\UseCases\CreateCheckIn\CreateCheckInCommand;
use App\Application\Ubumi\UseCases\CreateCheckIn\CreateCheckInHandler;
use App\Domain\Ubumi\Repositories\CheckInRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckInController extends Controller
{
    public function __construct(
        private readonly CreateCheckInHandler $createCheckInHandler,
        private readonly CheckInRepositoryInterface $checkInRepository,
        private readonly PersonRepositoryInterface $personRepository,
        private readonly \App\Domain\Ubumi\Repositories\FamilyRepositoryInterface $familyRepository
    ) {}

    /**
     * Store a new check-in
     */
    public function store(Request $request, string $familySlug, string $personSlug)
    {
        $validated = $request->validate([
            'status' => 'required|in:well,unwell,need_assistance',
            'note' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'photo_url' => 'nullable|string|max:255',
        ]);

        // Get person by slug
        $person = $this->personRepository->findBySlug($personSlug);
        
        if (!$person) {
            return back()->with('error', 'Person not found');
        }

        $command = new CreateCheckInCommand(
            personId: $person->getId()->toString(),
            status: $validated['status'],
            note: $validated['note'] ?? null,
            location: $validated['location'] ?? null,
            photoUrl: $validated['photo_url'] ?? null
        );

        try {
            $this->createCheckInHandler->handle($command);

            return back()->with('success', 'Check-in recorded successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record check-in: ' . $e->getMessage());
        }
    }

    /**
     * Get check-in history for a person
     */
    public function index(string $familySlug, string $personSlug)
    {
        $person = $this->personRepository->findBySlug($personSlug);
        
        if (!$person) {
            abort(404, 'Person not found');
        }

        $checkIns = $this->checkInRepository->findByPersonId($person->getId(), 50);

        return Inertia::render('Ubumi/CheckIns/Index', [
            'person' => [
                'id' => $person->getId()->toString(),
                'slug' => $person->getSlug()->value(),
                'name' => $person->getName()->toString(),
                'photo_url' => $person->getPhotoUrl(),
            ],
            'familySlug' => $familySlug,
            'checkIns' => array_map(function ($checkIn) {
                return [
                    'id' => $checkIn->id()->value(),
                    'status' => $checkIn->status()->value,
                    'status_label' => $checkIn->status()->label(),
                    'status_emoji' => $checkIn->status()->emoji(),
                    'status_color' => $checkIn->status()->color(),
                    'note' => $checkIn->note(),
                    'location' => $checkIn->location(),
                    'photo_url' => $checkIn->photoUrl(),
                    'checked_in_at' => $checkIn->checkedInAt()->format('Y-m-d H:i:s'),
                    'is_recent' => $checkIn->isRecent(24),
                ];
            }, $checkIns),
        ]);
    }

    /**
     * Get family-wide check-in dashboard
     */
    public function familyDashboard(string $familySlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);
        
        if (!$family) {
            abort(404, 'Family not found');
        }

        // Get all family members
        $persons = $this->personRepository->findByFamilyId($family->getId()->toString());
        
        // Get latest check-ins for each person
        $members = [];
        $summary = [
            'total_members' => 0,
            'well_count' => 0,
            'unwell_count' => 0,
            'need_assistance_count' => 0,
        ];

        foreach ($persons as $person) {
            $latestCheckIn = $this->checkInRepository->findLatestByPersonId($person->getId());
            
            $memberData = [
                'person_id' => $person->getId()->toString(),
                'person_slug' => $person->getSlug()->value(),
                'name' => $person->getName()->toString(),
                'photo_url' => $person->getPhotoUrl(),
                'age' => $person->getAge(),
                'is_deceased' => $person->getIsDeceased(),
                'latest_checkin' => null,
            ];

            if ($latestCheckIn) {
                $memberData['latest_checkin'] = [
                    'status_emoji' => $latestCheckIn->status()->emoji(),
                    'status_label' => $latestCheckIn->status()->label(),
                    'checked_in_at' => $latestCheckIn->checkedInAt()->format('Y-m-d H:i:s'),
                ];

                // Count by status
                if ($latestCheckIn->isRecent(72)) { // Within last 3 days
                    match($latestCheckIn->status()->value) {
                        'well' => $summary['well_count']++,
                        'unwell' => $summary['unwell_count']++,
                        'need_assistance' => $summary['need_assistance_count']++,
                        default => null,
                    };
                }
            }

            if (!$person->getIsDeceased()) {
                $summary['total_members']++;
            }

            $members[] = $memberData;
        }

        // Get active alerts
        $alerts = \Illuminate\Support\Facades\DB::table('ubumi_alerts')
            ->where('family_id', $family->getId()->toString())
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($alert) => [
                'id' => $alert->id,
                'message' => $alert->message,
                'created_at' => $alert->created_at,
            ])
            ->toArray();

        return Inertia::render('Ubumi/CheckIns/FamilyDashboard', [
            'family' => [
                'id' => $family->getId()->toString(),
                'slug' => $family->getSlug()->value(),
                'name' => $family->getName()->toString(),
            ],
            'summary' => $summary,
            'alerts' => $alerts,
            'members' => $members,
        ]);
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledgeAlert(string $alertId)
    {
        \Illuminate\Support\Facades\DB::table('ubumi_alerts')
            ->where('id', $alertId)
            ->update([
                'status' => 'acknowledged',
                'acknowledged_by' => auth()->id(),
                'acknowledged_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Alert acknowledged');
    }
}
