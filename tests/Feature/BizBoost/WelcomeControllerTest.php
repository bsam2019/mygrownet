<?php

namespace Tests\Feature\BizBoost;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for BizBoost WelcomeController
 * 
 * Tests that the welcome/landing page is publicly accessible
 * and returns the correct data for features and pricing.
 * 
 * Requirements covered:
 * - 1.1: Welcome page accessible without authentication
 * - 5.4: Module access allows unauthenticated access to landing page
 */
class WelcomeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the welcome page is accessible without authentication.
     * Requirement 1.1: WHEN a user visits /bizboost/welcome THEN the system 
     * SHALL display a BizBoost-branded landing page (no auth required)
     */
    public function test_welcome_page_is_accessible_without_authentication(): void
    {
        $response = $this->get('/bizboost/welcome');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('BizBoost/Welcome'));
    }

    /**
     * Test that features data is passed to the view.
     * Requirement 1.1: Landing page displays key features
     */
    public function test_features_data_is_passed_to_view(): void
    {
        $response = $this->get('/bizboost/welcome');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Welcome')
            ->has('features')
            ->where('features', fn ($features) => 
                is_array($features) && count($features) > 0
            )
        );
    }

    /**
     * Test that features contain required structure.
     */
    public function test_features_have_correct_structure(): void
    {
        $response = $this->get('/bizboost/welcome');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Welcome')
            ->has('features.0.icon')
            ->has('features.0.title')
            ->has('features.0.description')
        );
    }
}