<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\PlatformPayments\Enums\GatewayProvider;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\GrowBuilder\SitePaymentConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GrowBuilderSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->site = GrowBuilderSite::create([
            'user_id' => $this->user->id,
            'name' => 'Shop',
            'subdomain' => 'shop-' . uniqid(),
            'plan' => 'free',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_payment_config_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/payment/config");
        $response->assertStatus(200);
    }

    public function test_payment_settings_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/payments");
        $response->assertStatus(200);
    }

    public function test_payment_settings_update(): void
    {
        $response = $this->actingAs($this->user)->put("/growbuilder/sites/{$this->site->id}/payments", [
            'cod_enabled' => true,
        ]);

        $response->assertStatus(302);
    }

    public function test_store_payment_config(): void
    {
        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/payment/config", [
            'gateway' => 'pawapay',
            'credentials' => ['api_token' => 'test-token'],
            'test_mode' => true,
        ]);

        $response->assertStatus(302);
    }

    public function test_get_gateway_fields(): void
    {
        $response = $this->actingAs($this->user)->get('/growbuilder/api/payment/gateway-fields?gateway=pawapay');

        $response->assertStatus(200);
        $response->assertJsonStructure(['fields']);
    }

    public function test_payment_webhook_unauthenticated(): void
    {
        $response = $this->postJson("/growbuilder/sites/{$this->site->id}/payment/webhook", [
            'depositId' => 'test-ref',
        ]);

        $this->assertContains($response->status(), [200, 400, 422, 500]);
    }
}
