<?php

namespace App\Http\Controllers\Ubumi;

use App\Http\Controllers\Controller;
use App\Application\Ubumi\UseCases\AddRelationship\AddRelationshipCommand;
use App\Application\Ubumi\UseCases\AddRelationship\AddRelationshipHandler;
use App\Application\Ubumi\UseCases\RemoveRelationship\RemoveRelationshipCommand;
use App\Application\Ubumi\UseCases\RemoveRelationship\RemoveRelationshipHandler;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\RelationshipType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RelationshipController extends Controller
{
    public function __construct(
        private AddRelationshipHandler $addRelationshipHandler,
        private RemoveRelationshipHandler $removeRelationshipHandler,
        private RelationshipRepositoryInterface $relationshipRepository,
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Display relationships for a person
     */
    public function index(string $personId)
    {
        $person = $this->personRepository->findById(PersonId::fromString($personId));

        if (!$person) {
            abort(404, 'Person not found');
        }

        $relationships = $this->relationshipRepository->findByPersonId(PersonId::fromString($personId));

        // Group relationships by type and load related persons
        $groupedRelationships = [];
        foreach ($relationships as $relationship) {
            $type = $relationship->getRelationshipType()->toString();
            
            // Only show relationships where this person is the subject
            if ($relationship->getPersonId()->toString() === $personId) {
                $relatedPerson = $this->personRepository->findById($relationship->getRelatedPersonId());
                
                if ($relatedPerson) {
                    $groupedRelationships[$type][] = [
                        'id' => $relationship->getId(),
                        'type' => $relationship->getRelationshipType()->getLabel(),
                        'related_person' => [
                            'id' => $relatedPerson->getId()->toString(),
                            'name' => $relatedPerson->getName()->toString(),
                            'photo_url' => $relatedPerson->getPhotoUrl(),
                            'age' => $relatedPerson->getAge(),
                            'is_deceased' => $relatedPerson->getIsDeceased(),
                        ],
                    ];
                }
            }
        }

        return Inertia::render('Ubumi/Relationships/Index', [
            'person' => [
                'id' => $person->getId()->toString(),
                'name' => $person->getName()->toString(),
                'family_id' => $person->getFamilyId(),
            ],
            'relationships' => $groupedRelationships,
            'availableTypes' => $this->getAvailableRelationshipTypes(),
        ]);
    }

    /**
     * Store a new relationship
     */
    public function store(Request $request, string $personId)
    {
        $validated = $request->validate([
            'related_person_id' => 'required|string',
            'relationship_type' => 'required|string',
        ]);

        try {
            $command = new AddRelationshipCommand(
                personId: $personId,
                relatedPersonId: $validated['related_person_id'],
                relationshipType: $validated['relationship_type']
            );

            $this->addRelationshipHandler->handle($command);

            return back()->with('success', 'Relationship added successfully');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a relationship
     */
    public function destroy(string $personId, int $relationshipId)
    {
        try {
            $command = new RemoveRelationshipCommand($relationshipId);
            $this->removeRelationshipHandler->handle($command);

            return back()->with('success', 'Relationship removed successfully');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get available relationship types for dropdown
     */
    private function getAvailableRelationshipTypes(): array
    {
        return array_map(function($type) {
            $relationshipType = RelationshipType::fromString($type);
            return [
                'value' => $type,
                'label' => $relationshipType->getLabel(),
            ];
        }, RelationshipType::all());
    }
}
