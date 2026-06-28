<?php

namespace App\Http\Controllers\Ubumi;

use App\Http\Controllers\Controller;
use App\Application\Ubumi\UseCases\CreateFamily\CreateFamilyCommand;
use App\Application\Ubumi\UseCases\CreateFamily\CreateFamilyHandler;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Services\SlugGeneratorService;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\FamilyName;
use App\Domain\Ubumi\ValueObjects\Slug;
use App\Infrastructure\Ubumi\Eloquent\FamilyModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FamilyController extends Controller
{
    public function __construct(
        private CreateFamilyHandler $createFamilyHandler,
        private FamilyRepositoryInterface $familyRepository,
        private \App\Domain\Ubumi\Repositories\PersonRepositoryInterface $personRepository,
        private \App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface $relationshipRepository,
        private SlugGeneratorService $slugGenerator
    ) {}

    /**
     * Display family dashboard
     */
    public function index()
    {
        $families = $this->familyRepository->findByAdminUserId(auth()->id());

        return Inertia::render('Ubumi/Families/Index', [
            'families' => array_map(function($family) {
                return [
                    'id' => $family->getId()->toString(),
                    'slug' => $family->getSlug()->value(),
                    'name' => $family->getName()->toString(),
                    'admin_user_id' => $family->getAdminUserId(),
                    'created_at' => $family->getCreatedAt()->format('Y-m-d H:i:s'),
                ];
            }, $families),
        ]);
    }

    /**
     * Show create family form
     */
    public function create()
    {
        return Inertia::render('Ubumi/Families/Create');
    }

    /**
     * Store a new family
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $command = new CreateFamilyCommand(
            name: $validated['name'],
            adminUserId: auth()->id()
        );

        $family = $this->createFamilyHandler->handle($command);

        return redirect()
            ->route('ubumi.families.show', $family->getSlug()->value())
            ->with('success', 'Family created successfully');
    }

    /**
     * Display family tree
     */
    public function show(string $familySlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);

        if (!$family) {
            abort(404, 'Family not found');
        }

        // Check authorization
        if ($family->getAdminUserId() !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Get all persons in the family
        $persons = $this->personRepository->findByFamilyId($family->getId()->toString());

        // Get all relationships
        $allRelationships = [];
        foreach ($persons as $person) {
            $personRelationships = $this->relationshipRepository->findByPersonId($person->getId());
            foreach ($personRelationships as $relationship) {
                $allRelationships[] = [
                    'id' => $relationship->getId(),
                    'person_id' => $relationship->getPersonId()->toString(),
                    'related_person_id' => $relationship->getRelatedPersonId()->toString(),
                    'relationship_type' => $relationship->getRelationshipType()->toString(),
                ];
            }
        }

        return Inertia::render('Ubumi/Families/Show', [
            'family' => [
                'id' => $family->getId()->toString(),
                'slug' => $family->getSlug()->value(),
                'name' => $family->getName()->toString(),
                'admin_user_id' => $family->getAdminUserId(),
                'created_at' => $family->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
            'persons' => array_map(function($person) {
                return [
                    'id' => $person->getId()->toString(),
                    'slug' => $person->getSlug()->value(),
                    'name' => $person->getName()->toString(),
                    'photo_url' => $person->getPhotoUrl(),
                    'age' => $person->getAge(),
                    'gender' => $person->getGender(),
                    'is_deceased' => $person->getIsDeceased(),
                ];
            }, $persons),
            'relationships' => $allRelationships,
        ]);
    }

    /**
     * Update family
     */
    public function update(Request $request, string $familySlug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $family = $this->familyRepository->findBySlug($familySlug);

        if (!$family) {
            abort(404, 'Family not found');
        }

        // Check authorization
        if ($family->getAdminUserId() !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        // Generate new slug if name changed
        $newSlug = $family->getName()->toString() !== $validated['name']
            ? $this->slugGenerator->generateFamilySlug($validated['name'], $family->getId())
            : $family->getSlug();

        $family->changeName(
            FamilyName::fromString($validated['name']),
            $newSlug
        );
        
        $this->familyRepository->save($family);

        return redirect()
            ->route('ubumi.families.show', $newSlug->value())
            ->with('success', 'Family updated successfully');
    }

    /**
     * Delete family
     */
    public function destroy(string $familySlug)
    {
        $family = $this->familyRepository->findBySlug($familySlug);

        if (!$family) {
            abort(404, 'Family not found');
        }

        // Check authorization
        if ($family->getAdminUserId() !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $this->familyRepository->delete($family->getId());

        return redirect()
            ->route('ubumi.families.index')
            ->with('success', 'Family deleted successfully');
    }
}
