<?php

namespace Tests\Feature\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CampaignsTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_user_can_view_campaigns_index(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        // Create some campaigns
        BizBoostCampaignModel::factory()->count(3)->create([
            'business_id' => $business->id,
        ]);

        $response = $this->actingAs($user)->get(route('bizboost.campaigns.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Campaigns/Index')
            ->has('campaigns.data', 3)
            ->has('stats')
        );
    }

    public function test_user_can_view_campaign_create_page(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->get(route('bizboost.campaigns.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Campaigns/Create')
            ->has('objectives')
            ->has('templates')
        );
    }

    public function test_user_can_create_campaign(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->post(route('bizboost.campaigns.store'), [
            'name' => 'Holiday Sale Campaign',
            'description' => 'Promote our holiday discounts',
            'objective' => 'increase_sales',
            'duration_days' => 7,
            'start_date' => now()->addDay()->toDateString(),
            'target_platforms' => ['facebook'],
            'auto_generate_content' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_campaigns', [
            'name' => 'Holiday Sale Campaign',
            'objective' => 'increase_sales',
            'status' => 'draft',
        ]);
    }

    public function test_user_can_view_campaign_details(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'name' => 'Test Campaign',
        ]);

        $response = $this->actingAs($user)->get(route('bizboost.campaigns.show', $campaign->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Campaigns/Show')
            ->where('campaign.name', 'Test Campaign')
            ->has('posts')
            ->has('analytics')
        );
    }

    public function test_user_can_edit_draft_campaign(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->get(route('bizboost.campaigns.edit', $campaign->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Campaigns/Edit')
            ->has('campaign')
            ->has('objectives')
        );
    }

    public function test_user_cannot_edit_active_campaign(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('bizboost.campaigns.edit', $campaign->id));

        $response->assertRedirect(route('bizboost.campaigns.show', $campaign->id));
    }

    public function test_user_can_update_draft_campaign(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'draft',
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($user)->put(route('bizboost.campaigns.update', $campaign->id), [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'objective' => 'promote_stock',
            'duration_days' => 14,
            'start_date' => now()->addDays(2)->toDateString(),
            'target_platforms' => ['facebook', 'instagram'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated Name',
            'objective' => 'promote_stock',
        ]);
    }

    public function test_user_can_start_campaign_with_posts(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'draft',
        ]);

        // Add a post to the campaign
        $business->posts()->create([
            'title' => 'Campaign Post',
            'caption' => 'Test caption',
            'status' => 'draft',
            'post_type' => 'standard',
            'campaign_id' => $campaign->id,
        ]);

        $response = $this->actingAs($user)->post(route('bizboost.campaigns.start', $campaign->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_campaigns', [
            'id' => $campaign->id,
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_start_campaign_without_posts(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->post(route('bizboost.campaigns.start', $campaign->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bizboost_campaigns', [
            'id' => $campaign->id,
            'status' => 'draft',
        ]);
    }

    public function test_user_can_pause_active_campaign(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('bizboost.campaigns.pause', $campaign->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_campaigns', [
            'id' => $campaign->id,
            'status' => 'paused',
        ]);
    }

    public function test_user_can_resume_paused_campaign(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'paused',
        ]);

        $response = $this->actingAs($user)->post(route('bizboost.campaigns.resume', $campaign->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_campaigns', [
            'id' => $campaign->id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_delete_draft_campaign(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->delete(route('bizboost.campaigns.destroy', $campaign->id));

        $response->assertRedirect(route('bizboost.campaigns.index'));
        $this->assertDatabaseMissing('bizboost_campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function test_user_cannot_delete_active_campaign(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete(route('bizboost.campaigns.destroy', $campaign->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bizboost_campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function test_user_cannot_access_other_users_campaign(): void
    {
        $user1 = $this->createUserWithBusiness();
        $user2 = $this->createUserWithBusiness();
        
        $business2 = BizBoostBusinessModel::where('user_id', $user2->id)->first();
        $campaign = BizBoostCampaignModel::factory()->create([
            'business_id' => $business2->id,
        ]);

        $response = $this->actingAs($user1)->get(route('bizboost.campaigns.show', $campaign->id));

        $response->assertStatus(404);
    }
}
