<?php

namespace Tests\Feature\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class LocationsTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_can_view_locations_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/bizboost/locations');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Locations/Index')
            ->has('locations')
            ->has('locationLimit')
        );
    }

    public function test_can_create_location(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/bizboost/locations', [
                'name' => 'Main Store',
                'address' => '123 Main Street',
                'city' => 'Lusaka',
                'phone' => '+260971234567',
            ]);

        $response->assertRedirect('/bizboost/locations');
        
        $this->assertDatabaseHas('bizboost_locations', [
            'business_id' => $this->business->id,
            'name' => 'Main Store',
            'city' => 'Lusaka',
        ]);
    }

    public function test_first_location_is_set_as_primary(): void
    {
        $this->actingAs($this->user)
            ->post('/bizboost/locations', [
                'name' => 'First Location',
            ]);

        $location = DB::table('bizboost_locations')
            ->where('business_id', $this->business->id)
            ->first();

        $this->assertTrue((bool) $location->is_primary);
    }

    public function test_can_update_location(): void
    {
        $locationId = DB::table('bizboost_locations')->insertGetId([
            'business_id' => $this->business->id,
            'name' => 'Old Name',
            'is_primary' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->put("/bizboost/locations/{$locationId}", [
                'name' => 'New Name',
                'city' => 'Kitwe',
            ]);

        $response->assertRedirect('/bizboost/locations');
        
        $this->assertDatabaseHas('bizboost_locations', [
            'id' => $locationId,
            'name' => 'New Name',
            'city' => 'Kitwe',
        ]);
    }

    public function test_cannot_delete_primary_location(): void
    {
        $locationId = DB::table('bizboost_locations')->insertGetId([
            'business_id' => $this->business->id,
            'name' => 'Primary Location',
            'is_primary' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/bizboost/locations/{$locationId}");

        $response->assertSessionHasErrors('delete');
        
        $this->assertDatabaseHas('bizboost_locations', [
            'id' => $locationId,
        ]);
    }

    public function test_can_set_location_as_primary(): void
    {
        // Create two locations
        $location1Id = DB::table('bizboost_locations')->insertGetId([
            'business_id' => $this->business->id,
            'name' => 'Location 1',
            'is_primary' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $location2Id = DB::table('bizboost_locations')->insertGetId([
            'business_id' => $this->business->id,
            'name' => 'Location 2',
            'is_primary' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->post("/bizboost/locations/{$location2Id}/set-primary");

        $response->assertRedirect();

        $this->assertDatabaseHas('bizboost_locations', [
            'id' => $location1Id,
            'is_primary' => false,
        ]);

        $this->assertDatabaseHas('bizboost_locations', [
            'id' => $location2Id,
            'is_primary' => true,
        ]);
    }
}
