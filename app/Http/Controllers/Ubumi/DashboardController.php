<?php

namespace App\Http\Controllers\Ubumi;

use App\Http\Controllers\Controller;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\PersonId;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private PersonRepositoryInterface $personRepository,
        private RelationshipRepositoryInterface $relationshipRepository
    ) {}

    /**
     * Display the Ubumi dashboard
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Get all families for the user
        $families = $this->familyRepository->findByAdminUserId($userId);
        
        // Calculate stats
        $totalPersons = 0;
        $totalRelationships = 0;
        $recentFamilies = [];
        
        foreach ($families as $family) {
            $familyId = $family->getId()->toString();
            $persons = $this->personRepository->findByFamilyId($familyId);
            $personCount = count($persons);
            $totalPersons += $personCount;
            
            // Count relationships for this family
            foreach ($persons as $person) {
                $personId = PersonId::fromString($person->getId()->toString());
                $relationships = $this->relationshipRepository->findByPersonId($personId);
                $totalRelationships += count($relationships);
            }
            
            // Add to recent families (limit to 6)
            if (count($recentFamilies) < 6) {
                $recentFamilies[] = [
                    'id' => $familyId,
                    'slug' => $family->getSlug()->value(),
                    'name' => $family->getName()->toString(),
                    'member_count' => $personCount,
                ];
            }
        }
        
        // Avoid double-counting bidirectional relationships
        $totalRelationships = (int)($totalRelationships / 2);
        
        return Inertia::render('Ubumi/Index', [
            'stats' => [
                'families' => count($families),
                'persons' => $totalPersons,
                'relationships' => $totalRelationships,
            ],
            'recentFamilies' => $recentFamilies,
        ]);
    }
}
