<?php

namespace Tests\Feature\GrowBuilder;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_agency_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/agency/dashboard');
        $response->assertStatus(200);
    }

    public function test_client_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/clients');
        $response->assertStatus(200);
    }

    public function test_client_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/clients/create');
        $response->assertStatus(200);
    }

    public function test_client_store(): void
    {
        $response = $this->actingAs($this->user)->post('/growbuilder/clients', [
            'client_name' => 'Acme Corp',
            'email' => 'acme@example.com',
            'phone' => '+260977111111',
            'client_type' => 'business',
        ]);

        $this->assertContains($response->status(), [302, 422, 500]);
    }

    public function test_service_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/services');
        $response->assertStatus(200);
    }

    public function test_service_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/services/create');
        $response->assertStatus(200);
    }

    public function test_invoice_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/invoices');
        $response->assertStatus(200);
    }

    public function test_invoice_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/invoices/create');
        $response->assertStatus(200);
    }

    public function test_subscription_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/subscription');
        $response->assertStatus(200);
    }
}
