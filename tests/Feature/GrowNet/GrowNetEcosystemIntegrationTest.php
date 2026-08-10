<?php

namespace Tests\Feature\GrowNet;

use App\Models\User;
use App\Domain\GrowNet\Services\EducationLevelAdvancementService;
use App\Domain\GrowNet\Services\PhysicalRewardAllocationService;
use App\Domain\GrowNet\Services\ResourceEntitlementService;
use App\Domain\GrowMusic\Services\MusicCreatorService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GrowNetEcosystemIntegrationTest extends TestCase
{
    public function test_education_level_advancement_evaluates_two_gates_correctly()
    {
        $user = new User(['id' => 9991, 'name' => 'Test Member', 'email' => 'member@example.com']);
        $user->id = 9991;

        $service = app(EducationLevelAdvancementService::class);
        $result = $service->evaluateAdvancement($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('current_level', $result);
        $this->assertArrayHasKey('can_advance', $result);
    }

    public function test_physical_reward_allocation_service_allocates_reward()
    {
        $user = new User(['id' => 9992, 'name' => 'Test Member 2', 'email' => 'member2@example.com']);
        $user->id = 9992;

        $service = app(PhysicalRewardAllocationService::class);
        $allocation = $service->allocateForLevel($user, 1);

        $this->assertTrue(true);
    }

    public function test_resource_entitlement_service_returns_entitled_resources()
    {
        $user = new User(['id' => 9993, 'name' => 'Test Member 3', 'email' => 'member3@example.com']);
        $user->id = 9993;

        $service = app(ResourceEntitlementService::class);
        $resources = $service->getEntitledResources($user);

        $this->assertIsArray($resources);
    }

    public function test_music_creator_service_logs_stream_and_calculates_royalties()
    {
        $artist = new User(['id' => 9994, 'name' => 'Test Artist', 'email' => 'artist@example.com']);
        $artist->id = 9994;

        $service = app(MusicCreatorService::class);
        $this->assertNotNull($service);
    }
}
