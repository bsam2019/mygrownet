<?php

namespace Tests\Feature\BizBoost;

use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_can_view_team_index(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/team')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Team/Index')
                ->has('members')
                ->has('pendingInvitations')
                ->has('teamLimit')
            );
    }

    public function test_can_view_invite_page(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/team/invite')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Team/Invite')
            );
    }

    public function test_can_send_team_invitation(): void
    {
        $this->actingAs($this->user)
            ->post('/bizboost/team/invite', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'editor',
            ])
            ->assertRedirect('/bizboost/team');

        $this->assertDatabaseHas('bizboost_team_members', [
            'business_id' => $this->business->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'editor',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('bizboost_team_invitations', [
            'business_id' => $this->business->id,
        ]);
    }

    public function test_cannot_invite_duplicate_email(): void
    {
        // First invitation
        $this->actingAs($this->user)
            ->post('/bizboost/team/invite', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'editor',
            ]);

        // Duplicate invitation
        $this->actingAs($this->user)
            ->post('/bizboost/team/invite', [
                'name' => 'John Doe Again',
                'email' => 'john@example.com',
                'role' => 'member',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_can_update_member_role(): void
    {
        // Create a team member
        $memberId = \DB::table('bizboost_team_members')->insertGetId([
            'business_id' => $this->business->id,
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'role' => 'member',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->put("/bizboost/team/{$memberId}/role", [
                'role' => 'admin',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('bizboost_team_members', [
            'id' => $memberId,
            'role' => 'admin',
        ]);
    }

    public function test_can_remove_team_member(): void
    {
        $memberId = \DB::table('bizboost_team_members')->insertGetId([
            'business_id' => $this->business->id,
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'role' => 'member',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->delete("/bizboost/team/{$memberId}")
            ->assertRedirect();

        $this->assertDatabaseMissing('bizboost_team_members', ['id' => $memberId]);
    }

    public function test_can_cancel_invitation(): void
    {
        // Send invitation
        $this->actingAs($this->user)
            ->post('/bizboost/team/invite', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'editor',
            ]);

        $invitation = \DB::table('bizboost_team_invitations')
            ->where('business_id', $this->business->id)
            ->first();

        $this->actingAs($this->user)
            ->delete("/bizboost/team/invitations/{$invitation->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('bizboost_team_invitations', ['id' => $invitation->id]);
    }
}
