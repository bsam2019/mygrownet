<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('unauthenticated user is redirected from register', function () {
    $this->get(route('growstream.creator.register'))->assertRedirect(route('login'));
});

test('creator can submit an application', function () {
    $response = $this->actingAs($this->user)->post(route('growstream.creator.register.store'), [
        'display_name' => 'New Creator',
        'channel_name' => 'My Channel',
        'bio' => 'I make great content',
        'agree_to_terms' => true,
    ]);

    $response->assertRedirect(route('growstream.creator.pending'));

    $this->assertDatabaseHas('growstream_creator_profiles', [
        'user_id' => $this->user->id,
        'status' => 'pending',
        'channel_name' => 'My Channel',
    ]);

    $profile = CreatorProfile::where('user_id', $this->user->id)->first();
    $this->assertDatabaseHas('growstream_creator_agreements', [
        'creator_profile_id' => $profile->id,
        'accepted' => true,
    ]);
});

test('registration requires terms acceptance', function () {
    $response = $this->actingAs($this->user)->post(route('growstream.creator.register.store'), [
        'display_name' => 'New Creator',
        'agree_to_terms' => false,
    ]);

    $response->assertSessionHasErrors('agree_to_terms');
    $this->assertDatabaseMissing('growstream_creator_profiles', ['user_id' => $this->user->id]);
});

test('registration is rejected for an existing profile', function () {
    CreatorProfile::create([
        'user_id' => $this->user->id,
        'display_name' => 'Existing',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->post(route('growstream.creator.register.store'), [
        'display_name' => 'Duplicate',
        'agree_to_terms' => true,
    ]);

    $response->assertSessionHas('error', 'You already have a creator application on file.');
    $this->assertDatabaseCount('growstream_creator_profiles', 1);
});

test('registered user sees pending approval page', function () {
    $this->actingAs($this->user)->post(route('growstream.creator.register.store'), [
        'display_name' => 'New Creator',
        'agree_to_terms' => true,
    ]);

    $this->actingAs($this->user)
        ->get(route('growstream.creator.pending'))
        ->assertOk();
});

test('dashboard redirects to register when not approved', function () {
    $this->actingAs($this->user)
        ->get(route('growstream.creator.dashboard'))
        ->assertRedirect(route('growstream.creator.register'));
});
