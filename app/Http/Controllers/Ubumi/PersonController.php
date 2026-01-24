<?php

namespace App\Http\Controllers\Ubumi;

use App\Http\Controllers\Controller;
use App\Application\Ubumi\UseCases\AddPerson\AddPersonCommand;
use App\Application\Ubumi\UseCases\AddPerson\AddPersonHandler;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersonController extends Controller
{
    public function __construct(
        private AddPersonHandler $addPersonHandler,
        private PersonRepositoryInterface $personRepository,
        private FamilyRepositoryInterface $familyRepository,
        private \App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface $relationshipRepository,
        private \App\Domain\Ubumi\Repositories\CheckInRepositoryInterface $checkInRepository,
        private \App\Services\MediaUploadService $mediaUploadService,
        private \App\Domain\Ubumi\Services\DuplicateDetectionService $duplicateDetectionService
    ) {}

    /**
     * Display all persons across all families (for mobile navigation)
     */
    public function indexAll()
    {
        // Get all families for the authenticated user
        $families = $this->familyRepository->findByAdminUserId(auth()->id());
        
        $allPersons = [];
        foreach ($families as $family) {
            $persons = $this->personRepository->findByFamilyId($family->getId()->toString());
            foreach ($persons as $person) {
                $allPersons[] = [
                    'id' => $person->getId()->toString(),
                    'name' => $person->getName()->toString(),
                    'photo_url' => $person->getPhotoUrl(),
                    'age' => $person->getAge(),
                    'gender' => $person->getGender(),
                    'is_deceased' => $person->isDeceased(),
                    'family_id' => $family->getId()->toString(),
                    'family_name' => $family->getName()->toString(),
                ];
            }
        }

        return Inertia::render('Ubumi/Persons/Index', [
            'persons' => $allPersons,
            'family' => null, // No specific family context
        ]);
    }

    /**
     * Display family members
     */
    public function index(string $familyId)
    {
        $family = $this->familyRepository->findById(FamilyId::fromString($familyId));

        if (!$family) {
            abort(404, 'Family not found');
        }

        $persons = $this->personRepository->findByFamilyId($family->getId()->toString());

        return Inertia::render('Ubumi/Persons/Index', [
            'family' => [
                'id' => $family->getId()->toString(),
                'name' => $family->getName()->toString(),
            ],
            'persons' => array_map(function($person) {
                return [
                    'id' => $person->getId()->toString(),
                    'name' => $person->getName()->toString(),
                    'photo_url' => $person->getPhotoUrl(),
                    'age' => $person->getAge(),
                    'gender' => $person->getGender(),
                    'is_deceased' => $person->getIsDeceased(),
                ];
            }, $persons),
        ]);
    }

    /**
     * Show create person form
     */
    public function create(string $familySlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);

        if (!$family) {
            abort(404, 'Family not found');
        }

        // Get existing family members for relationship selection
        $existingPersons = $this->personRepository->findByFamilyId($family->getId()->toString());
        $availablePersons = array_map(function($person) {
            return [
                'id' => $person->getId()->toString(),
                'name' => $person->getName()->toString(),
            ];
        }, $existingPersons);

        return Inertia::render('Ubumi/Persons/Create', [
            'family' => [
                'id' => $family->getId()->toString(),
                'slug' => $family->getSlug()->value(),
                'name' => $family->getName()->toString(),
            ],
            'availablePersons' => $availablePersons,
            'relationshipTypes' => $this->getAvailableRelationshipTypes(),
        ]);
    }

    /**
     * Store a new person
     */
    public function store(Request $request, string $familySlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);

        if (!$family) {
            abort(404, 'Family not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo_url' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'approximate_age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'related_person_id' => 'nullable|string',
            'relationship_type' => 'nullable|string',
        ]);

        $command = new AddPersonCommand(
            familyId: $family->getId()->toString(),
            name: $validated['name'],
            createdBy: auth()->id(),
            photoUrl: $validated['photo_url'] ?? null,
            dateOfBirth: isset($validated['date_of_birth']) ? new \DateTimeImmutable($validated['date_of_birth']) : null,
            approximateAge: $validated['approximate_age'] ?? null,
            gender: $validated['gender'] ?? null
        );

        $person = $this->addPersonHandler->handle($command);

        // Add relationship if provided
        if (!empty($validated['related_person_id']) && !empty($validated['relationship_type'])) {
            try {
                $relationshipCommand = new \App\Application\Ubumi\UseCases\AddRelationship\AddRelationshipCommand(
                    personId: $person->getId()->toString(),
                    relatedPersonId: $validated['related_person_id'],
                    relationshipType: $validated['relationship_type']
                );

                app(\App\Application\Ubumi\UseCases\AddRelationship\AddRelationshipHandler::class)->handle($relationshipCommand);
            } catch (\Exception $e) {
                // Log error but don't fail the person creation
                \Log::warning('Failed to add relationship during person creation', [
                    'person_id' => $person->getId()->toString(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('ubumi.families.show', $familySlug)
            ->with('success', 'Family member added successfully');
    }

    /**
     * Display person details
     */
    public function show(string $familySlug, string $personSlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);
        
        if (!$family) {
            abort(404, 'Family not found');
        }

        $person = $this->personRepository->findBySlug($personSlug);

        if (!$person || $person->getFamilyId() !== $family->getId()->toString()) {
            abort(404, 'Person not found');
        }

        // Get relationships
        $relationships = $this->relationshipRepository->findByPersonId($person->getId());
        $groupedRelationships = [];
        
        foreach ($relationships as $relationship) {
            // Only show relationships where this person is the subject
            if ($relationship->getPersonId()->toString() === $person->getId()->toString()) {
                $relatedPerson = $this->personRepository->findById($relationship->getRelatedPersonId());
                
                if ($relatedPerson) {
                    $type = $relationship->getRelationshipType()->toString();
                    $groupedRelationships[$type][] = [
                        'id' => $relationship->getId(),
                        'type' => $relationship->getRelationshipType()->getLabel(),
                        'related_person' => [
                            'id' => $relatedPerson->getId()->toString(),
                            'slug' => $relatedPerson->getSlug()->value(),
                            'name' => $relatedPerson->getName()->toString(),
                            'photo_url' => $relatedPerson->getPhotoUrl(),
                            'age' => $relatedPerson->getAge(),
                            'is_deceased' => $relatedPerson->getIsDeceased(),
                        ],
                    ];
                }
            }
        }

        // Get all family members for relationship selection
        $familyMembers = $this->personRepository->findByFamilyId($family->getId()->toString());
        $availablePersons = array_filter(
            array_map(function($p) use ($person) {
                if ($p->getId()->toString() !== $person->getId()->toString()) {
                    return [
                        'id' => $p->getId()->toString(),
                        'name' => $p->getName()->toString(),
                    ];
                }
                return null;
            }, $familyMembers)
        );

        // Get latest check-in
        $latestCheckIn = $this->checkInRepository->findLatestByPersonId($person->getId());
        $latestCheckInData = null;
        
        if ($latestCheckIn) {
            $latestCheckInData = [
                'id' => $latestCheckIn->id()->value(),
                'status' => $latestCheckIn->status()->value,
                'status_label' => $latestCheckIn->status()->label(),
                'status_emoji' => $latestCheckIn->status()->emoji(),
                'status_color' => $latestCheckIn->status()->color(),
                'note' => $latestCheckIn->note(),
                'location' => $latestCheckIn->location(),
                'photo_url' => $latestCheckIn->photoUrl(),
                'checked_in_at' => $latestCheckIn->checkedInAt()->format('Y-m-d H:i:s'),
                'is_recent' => $latestCheckIn->isRecent(24),
            ];
        }

        return Inertia::render('Ubumi/Persons/Show', [
            'person' => [
                'id' => $person->getId()->toString(),
                'slug' => $person->getSlug()->value(),
                'family_id' => $person->getFamilyId(),
                'family_slug' => $family->getSlug()->value(),
                'name' => $person->getName()->toString(),
                'photo_url' => $person->getPhotoUrl(),
                'date_of_birth' => $person->getDateOfBirth()?->format('Y-m-d'),
                'approximate_age' => $person->getApproximateAge()?->getValue(),
                'age' => $person->getAge(),
                'gender' => $person->getGender(),
                'is_deceased' => $person->getIsDeceased(),
                'created_at' => $person->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $person->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ],
            'relationships' => $groupedRelationships,
            'availablePersons' => array_values($availablePersons),
            'relationshipTypes' => $this->getAvailableRelationshipTypes(),
            'latestCheckIn' => $latestCheckInData,
        ]);
    }

    /**
     * Show edit person form
     */
    public function edit(string $familySlug, string $personSlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);
        
        if (!$family) {
            abort(404, 'Family not found');
        }

        $person = $this->personRepository->findBySlug($personSlug);

        if (!$person || $person->getFamilyId() !== $family->getId()->toString()) {
            abort(404, 'Person not found');
        }

        return Inertia::render('Ubumi/Persons/Edit', [
            'person' => [
                'id' => $person->getId()->toString(),
                'slug' => $person->getSlug()->value(),
                'family_id' => $person->getFamilyId(),
                'family_slug' => $family->getSlug()->value(),
                'name' => $person->getName()->toString(),
                'photo_url' => $person->getPhotoUrl(),
                'date_of_birth' => $person->getDateOfBirth()?->format('Y-m-d'),
                'approximate_age' => $person->getApproximateAge()?->getValue(),
                'gender' => $person->getGender(),
                'is_deceased' => $person->getIsDeceased(),
            ],
        ]);
    }

    /**
     * Update person
     */
    public function update(Request $request, string $familySlug, string $personSlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);
        
        if (!$family) {
            abort(404, 'Family not found');
        }

        $person = $this->personRepository->findBySlug($personSlug);

        if (!$person || $person->getFamilyId() !== $family->getId()->toString()) {
            abort(404, 'Person not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo_url' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'approximate_age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
        ]);

        $person->updateProfile(
            \App\Domain\Ubumi\ValueObjects\PersonName::fromString($validated['name']),
            $validated['photo_url'] ?? null,
            isset($validated['date_of_birth']) ? new \DateTimeImmutable($validated['date_of_birth']) : null,
            isset($validated['approximate_age']) ? \App\Domain\Ubumi\ValueObjects\ApproximateAge::fromInt($validated['approximate_age']) : null,
            $validated['gender'] ?? null
        );

        $this->personRepository->save($person);

        return back()->with('success', 'Person updated successfully');
    }

    /**
     * Add a relationship
     */
    public function addRelationship(Request $request, string $familySlug, string $personSlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);
        
        if (!$family) {
            abort(404, 'Family not found');
        }

        $person = $this->personRepository->findBySlug($personSlug);

        if (!$person || $person->getFamilyId() !== $family->getId()->toString()) {
            abort(404, 'Person not found');
        }

        $validated = $request->validate([
            'related_person_id' => 'required|string',
            'relationship_type' => 'required|string',
        ]);

        try {
            $command = new \App\Application\Ubumi\UseCases\AddRelationship\AddRelationshipCommand(
                personId: $person->getId()->toString(),
                relatedPersonId: $validated['related_person_id'],
                relationshipType: $validated['relationship_type']
            );

            app(\App\Application\Ubumi\UseCases\AddRelationship\AddRelationshipHandler::class)->handle($command);

            return back()->with('success', 'Relationship added successfully');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a relationship
     */
    public function removeRelationship(string $familySlug, string $personSlug, int $relationshipId)
    {
        $family = $this->familyRepository->findBySlug($familySlug);
        
        if (!$family) {
            abort(404, 'Family not found');
        }

        $person = $this->personRepository->findBySlug($personSlug);

        if (!$person || $person->getFamilyId() !== $family->getId()->toString()) {
            abort(404, 'Person not found');
        }

        try {
            $command = new \App\Application\Ubumi\UseCases\RemoveRelationship\RemoveRelationshipCommand($relationshipId);
            app(\App\Application\Ubumi\UseCases\RemoveRelationship\RemoveRelationshipHandler::class)->handle($command);

            return back()->with('success', 'Relationship removed successfully');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Check for potential duplicates
     */
    public function checkDuplicates(Request $request, string $familySlug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'approximate_age' => 'nullable|integer|min:0|max:150',
        ]);

        $family = $this->familyRepository->findBySlug($familySlug);

        if (!$family) {
            return response()->json(['duplicates' => []]);
        }

        // Get all persons in the family
        $existingPersons = $this->personRepository->findByFamilyId($family->getId()->toString());

        // Create a temporary person for comparison
        $tempPerson = \App\Domain\Ubumi\Entities\Person::create(
            \App\Domain\Ubumi\ValueObjects\PersonId::generate(),
            $family->getId()->toString(),
            \App\Domain\Ubumi\ValueObjects\PersonName::fromString($validated['name']),
            auth()->id(),
            null, // photo
            isset($validated['date_of_birth']) ? new \DateTimeImmutable($validated['date_of_birth']) : null,
            isset($validated['approximate_age']) ? \App\Domain\Ubumi\ValueObjects\ApproximateAge::fromInt($validated['approximate_age']) : null,
            null // gender
        );

        // Find potential duplicates
        $duplicates = [];
        foreach ($existingPersons as $existingPerson) {
            $score = $this->duplicateDetectionService->calculateSimilarity($tempPerson, $existingPerson);
            
            if ($score >= 0.6) { // 60% similarity threshold
                $duplicates[] = [
                    'id' => $existingPerson->getId()->toString(),
                    'name' => $existingPerson->getName()->toString(),
                    'photo_url' => $existingPerson->getPhotoUrl(),
                    'age' => $existingPerson->getAge(),
                    'date_of_birth' => $existingPerson->getDateOfBirth()?->format('Y-m-d'),
                    'similarity_score' => round($score * 100, 1),
                ];
            }
        }

        // Sort by similarity score (highest first)
        usort($duplicates, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return response()->json([
            'duplicates' => $duplicates,
        ]);
    }

    /**
     * Upload a photo for a person
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        try {
            $url = $this->mediaUploadService->uploadLogo(
                $request->file('file'),
                'public',
                'ubumi/photos',
                800 // Max width for person photos
            );

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload photo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete person
     */
    public function destroy(string $familySlug, string $personSlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);
        
        if (!$family) {
            abort(404, 'Family not found');
        }

        $person = $this->personRepository->findBySlug($personSlug);

        if (!$person || $person->getFamilyId() !== $family->getId()->toString()) {
            abort(404, 'Person not found');
        }

        $this->personRepository->softDelete($person->getId());

        return redirect()
            ->route('ubumi.families.show', $familySlug)
            ->with('success', 'Person deleted successfully');
    }

    /**
     * Get available relationship types for dropdown
     */
    private function getAvailableRelationshipTypes(): array
    {
        return array_map(function($type) {
            $relationshipType = \App\Domain\Ubumi\ValueObjects\RelationshipType::fromString($type);
            return [
                'value' => $type,
                'label' => $relationshipType->getLabel(),
            ];
        }, \App\Domain\Ubumi\ValueObjects\RelationshipType::all());
    }
}
