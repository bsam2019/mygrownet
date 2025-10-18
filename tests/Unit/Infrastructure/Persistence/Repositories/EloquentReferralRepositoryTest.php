<?php

use App\Infrastructure\Persistence\Repositories\EloquentReferralRepository;
use App\Models\MatrixPosition;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new EloquentReferralRepository();
    $this->sponsor = User::factory()->create();
    $this->referredUser = User::factory()->create();
});

describe('EloquentReferralRepository', function () {
    it('can get referral tree with depth limit', function () {
        // Create multi-level referral structure
        $level2User = User::factory()->create();
        $level3User = User::factory()->create();

        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $this->referredUser->id,
            'level' => 1,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->referredUser->id,
            'referred_id' => $level2User->id,
            'level' => 1,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $level2User->id,
            'level' => 2,
        ]);

        Referral::factory()->create([
            'referrer_id' => $level2User->id,
            'referred_id' => $level3User->id,
            'level' => 1,
        ]);

        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $level3User->id,
            'level' => 3,
        ]);

        $tree = $this->repository->getReferralTree($this->sponsor, 3);

        expect($tree)->toHaveKeys(['user', 'level', 'children']);
        expect($tree['user']['id'])->toBe($this->sponsor->id);
        expect($tree['level'])->toBe(0);
        expect($tree['children'])->toHaveCount(1);
        
        // Check first level
        $firstLevel = $tree['children'][0];
        expect($firstLevel['user']['id'])->toBe($this->referredUser->id);
        expect($firstLevel['level'])->toBe(1);
        expect($firstLevel['children'])->toHaveCount(1);
        
        // Check second level
        $secondLevel = $firstLevel['children'][0];
        expect($secondLevel['user']['id'])->toBe($level2User->id);
        expect($secondLevel['level'])->toBe(2);
    });

    it('can get direct referrals', function () {
        $directReferrals = User::factory()->count(3)->create();
        
        foreach ($directReferrals as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 1,
            ]);
        }

        // Create indirect referral (should not be included)
        $indirectReferral = User::factory()->create();
        Referral::factory()->create([
            'referrer_id' => $this->sponsor->id,
            'referred_id' => $indirectReferral->id,
            'level' => 2,
        ]);

        $directReferralsList = $this->repository->getDirectReferrals($this->sponsor);

        expect($directReferralsList)->toHaveCount(3);
        expect($directReferralsList->every(fn($referral) => $referral->level === 1))->toBeTrue();
        expect($directReferralsList->every(fn($referral) => $referral->referrer_id === $this->sponsor->id))->toBeTrue();
    });

    it('can get referrals by specific level', function () {
        $level1Users = User::factory()->count(2)->create();
        $level2Users = User::factory()->count(3)->create();
        $level3Users = User::factory()->count(1)->create();

        foreach ($level1Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 1,
            ]);
        }

        foreach ($level2Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 2,
            ]);
        }

        foreach ($level3Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 3,
            ]);
        }

        $level1Referrals = $this->repository->getReferralsByLevel($this->sponsor, 1);
        $level2Referrals = $this->repository->getReferralsByLevel($this->sponsor, 2);
        $level3Referrals = $this->repository->getReferralsByLevel($this->sponsor, 3);

        expect($level1Referrals)->toHaveCount(2);
        expect($level2Referrals)->toHaveCount(3);
        expect($level3Referrals)->toHaveCount(1);
    });

    it('can build matrix structure data', function () {
        // Create matrix positions
        MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
            'sponsor_id' => null,
        ]);

        $childUsers = User::factory()->count(3)->create();
        
        foreach ($childUsers as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 2,
                'position' => $index + 1,
                'sponsor_id' => $this->sponsor->id,
            ]);
        }

        $matrixStructure = $this->repository->buildMatrixStructure($this->sponsor);

        expect($matrixStructure)->toHaveKeys(['root', 'levels', 'total_positions']);
        expect($matrixStructure['root']['user_id'])->toBe($this->sponsor->id);
        expect($matrixStructure['root']['level'])->toBe(1);
        expect($matrixStructure['levels'])->toHaveCount(2);
        expect($matrixStructure['total_positions'])->toBe(4);
    });

    it('can get referral statistics', function () {
        // Create referrals at different levels
        $level1Users = User::factory()->count(2)->create();
        $level2Users = User::factory()->count(3)->create();
        $level3Users = User::factory()->count(1)->create();

        foreach ($level1Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 1,
                'is_active' => true,
            ]);
        }

        foreach ($level2Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 2,
                'is_active' => true,
            ]);
        }

        foreach ($level3Users as $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 3,
                'is_active' => false, // Inactive referral
            ]);
        }

        $stats = $this->repository->getReferralStatistics($this->sponsor);

        expect($stats)->toHaveKeys([
            'total_referrals',
            'active_referrals',
            'level_1_count',
            'level_2_count',
            'level_3_count',
            'referral_depth',
        ]);

        expect($stats['total_referrals'])->toBe(6);
        expect($stats['active_referrals'])->toBe(5);
        expect($stats['level_1_count'])->toBe(2);
        expect($stats['level_2_count'])->toBe(3);
        expect($stats['level_3_count'])->toBe(1);
        expect($stats['referral_depth'])->toBe(3);
    });

    it('can find available matrix positions', function () {
        // Create matrix root
        MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
            'sponsor_id' => null,
        ]);

        // Fill first two positions
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        MatrixPosition::factory()->create([
            'user_id' => $user1->id,
            'level' => 2,
            'position' => 1,
            'sponsor_id' => $this->sponsor->id,
        ]);

        MatrixPosition::factory()->create([
            'user_id' => $user2->id,
            'level' => 2,
            'position' => 2,
            'sponsor_id' => $this->sponsor->id,
        ]);

        $availablePositions = $this->repository->findAvailableMatrixPositions($this->sponsor);

        expect($availablePositions)->toHaveCount(1);
        expect($availablePositions[0])->toHaveKeys(['level', 'position', 'sponsor_id']);
        expect($availablePositions[0]['level'])->toBe(2);
        expect($availablePositions[0]['position'])->toBe(3);
        expect($availablePositions[0]['sponsor_id'])->toBe($this->sponsor->id);
    });

    it('can get matrix genealogy data', function () {
        // Create complex matrix structure
        MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
        ]);

        $level2Users = User::factory()->count(3)->create();
        $level3Users = User::factory()->count(9)->create();

        // Level 2 positions
        foreach ($level2Users as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 2,
                'position' => $index + 1,
                'sponsor_id' => $this->sponsor->id,
            ]);
        }

        // Level 3 positions
        foreach ($level3Users as $index => $user) {
            $sponsorIndex = intval($index / 3);
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 3,
                'position' => ($index % 3) + 1,
                'sponsor_id' => $level2Users[$sponsorIndex]->id,
            ]);
        }

        $genealogy = $this->repository->getMatrixGenealogy($this->sponsor);

        expect($genealogy)->toHaveKeys(['matrix_tree', 'total_downline', 'matrix_levels']);
        expect($genealogy['total_downline'])->toBe(12); // 3 + 9
        expect($genealogy['matrix_levels'])->toBe(3);
    });

    it('can find spillover candidates', function () {
        // Create full matrix for sponsor
        MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
        ]);

        $directUsers = User::factory()->count(3)->create();
        
        foreach ($directUsers as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 2,
                'position' => $index + 1,
                'sponsor_id' => $this->sponsor->id,
            ]);
        }

        // Fill some positions under first direct user
        $subUsers = User::factory()->count(2)->create();
        
        foreach ($subUsers as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 3,
                'position' => $index + 1,
                'sponsor_id' => $directUsers[0]->id,
            ]);
        }

        $spilloverCandidates = $this->repository->findSpilloverCandidates($this->sponsor);

        expect($spilloverCandidates)->not->toBeEmpty();
        expect($spilloverCandidates[0])->toHaveKeys(['user_id', 'available_positions']);
    });

    it('can get referral performance metrics', function () {
        $referrals = User::factory()->count(5)->create();
        
        foreach ($referrals as $index => $user) {
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => 1,
                'created_at' => now()->subDays($index * 5),
            ]);
        }

        $metrics = $this->repository->getReferralPerformanceMetrics($this->sponsor, 30);

        expect($metrics)->toHaveKeys([
            'total_referrals',
            'referrals_this_period',
            'growth_rate',
            'average_referrals_per_week',
        ]);

        expect($metrics['total_referrals'])->toBe(5);
        expect($metrics['referrals_this_period'])->toBeInt();
        expect($metrics['growth_rate'])->toBeFloat();
    });

    it('handles empty referral networks gracefully', function () {
        $newUser = User::factory()->create();

        $tree = $this->repository->getReferralTree($newUser, 3);
        expect($tree['children'])->toBeEmpty();

        $directReferrals = $this->repository->getDirectReferrals($newUser);
        expect($directReferrals)->toHaveCount(0);

        $stats = $this->repository->getReferralStatistics($newUser);
        expect($stats['total_referrals'])->toBe(0);
        expect($stats['active_referrals'])->toBe(0);

        $matrixStructure = $this->repository->buildMatrixStructure($newUser);
        expect($matrixStructure['total_positions'])->toBe(0);
    });

    it('can get referral network depth', function () {
        // Create 5-level deep referral network
        $users = User::factory()->count(5)->create();
        $currentReferrer = $this->sponsor;

        foreach ($users as $index => $user) {
            Referral::factory()->create([
                'referrer_id' => $currentReferrer->id,
                'referred_id' => $user->id,
                'level' => 1, // Direct referral
            ]);

            // Create upline referral records
            Referral::factory()->create([
                'referrer_id' => $this->sponsor->id,
                'referred_id' => $user->id,
                'level' => $index + 1,
            ]);

            $currentReferrer = $user;
        }

        $depth = $this->repository->getReferralNetworkDepth($this->sponsor);

        expect($depth)->toBe(5);
    });

    it('can find matrix positions by level', function () {
        MatrixPosition::factory()->create([
            'user_id' => $this->sponsor->id,
            'level' => 1,
            'position' => 1,
        ]);

        $level2Users = User::factory()->count(3)->create();
        $level3Users = User::factory()->count(9)->create();

        foreach ($level2Users as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 2,
                'position' => $index + 1,
                'sponsor_id' => $this->sponsor->id,
            ]);
        }

        foreach ($level3Users as $index => $user) {
            MatrixPosition::factory()->create([
                'user_id' => $user->id,
                'level' => 3,
                'position' => $index + 1,
                'sponsor_id' => $level2Users[intval($index / 3)]->id,
            ]);
        }

        $level2Positions = $this->repository->getMatrixPositionsByLevel($this->sponsor, 2);
        $level3Positions = $this->repository->getMatrixPositionsByLevel($this->sponsor, 3);

        expect($level2Positions)->toHaveCount(3);
        expect($level3Positions)->toHaveCount(9);
    });
});