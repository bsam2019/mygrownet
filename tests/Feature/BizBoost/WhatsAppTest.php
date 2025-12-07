<?php

namespace Tests\Feature\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class WhatsAppTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_user_can_view_broadcasts_page(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->get(route('bizboost.whatsapp.broadcasts'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/WhatsApp/Broadcasts')
            ->has('broadcasts')
            ->has('customerCount')
            ->has('templates')
        );
    }

    public function test_user_can_view_create_broadcast_page(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->get(route('bizboost.whatsapp.broadcasts.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/WhatsApp/CreateBroadcast')
            ->has('customers')
            ->has('tags')
            ->has('templates')
        );
    }

    public function test_user_can_create_broadcast_to_all_customers(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        // Create some customers with WhatsApp numbers
        $business->customers()->createMany([
            ['name' => 'Customer 1', 'whatsapp' => '+260971234567', 'is_active' => true],
            ['name' => 'Customer 2', 'whatsapp' => '+260972345678', 'is_active' => true],
        ]);

        $response = $this->actingAs($user)->post(route('bizboost.whatsapp.broadcasts.store'), [
            'name' => 'Holiday Promotion',
            'message' => 'Happy holidays! Check out our special offers.',
            'recipient_type' => 'all',
        ]);

        $response->assertRedirect(route('bizboost.whatsapp.broadcasts'));
        $this->assertDatabaseHas('bizboost_whatsapp_broadcasts', [
            'business_id' => $business->id,
            'name' => 'Holiday Promotion',
            'recipient_type' => 'all',
            'recipient_count' => 2,
        ]);
    }

    public function test_user_can_create_broadcast_to_selected_customers(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $customer1 = $business->customers()->create([
            'name' => 'Customer 1',
            'whatsapp' => '+260971234567',
            'is_active' => true,
        ]);
        $customer2 = $business->customers()->create([
            'name' => 'Customer 2',
            'whatsapp' => '+260972345678',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('bizboost.whatsapp.broadcasts.store'), [
            'name' => 'VIP Offer',
            'message' => 'Exclusive offer for our VIP customers!',
            'recipient_type' => 'selected',
            'customer_ids' => [$customer1->id],
        ]);

        $response->assertRedirect(route('bizboost.whatsapp.broadcasts'));
        $this->assertDatabaseHas('bizboost_whatsapp_broadcasts', [
            'business_id' => $business->id,
            'name' => 'VIP Offer',
            'recipient_type' => 'selected',
            'recipient_count' => 1,
        ]);
    }

    public function test_user_can_view_templates_page(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->get(route('bizboost.whatsapp.templates'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/WhatsApp/Templates')
            ->has('templates')
        );
    }

    public function test_user_can_export_customers(): void
    {
        $user = $this->createUserWithBusiness();
        $business = BizBoostBusinessModel::where('user_id', $user->id)->first();

        $business->customers()->createMany([
            ['name' => 'Customer 1', 'whatsapp' => '+260971234567', 'is_active' => true],
            ['name' => 'Customer 2', 'whatsapp' => '+260972345678', 'is_active' => true],
        ]);

        $response = $this->actingAs($user)->get(route('bizboost.whatsapp.export-customers'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_user_can_generate_message_from_template(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->postJson(route('bizboost.whatsapp.generate-message'), [
            'template_id' => 'greeting',
            'variables' => [
                'customer_name' => 'John',
                'business_name' => 'My Shop',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
        $this->assertStringContainsString('John', $response->json('message'));
        $this->assertStringContainsString('My Shop', $response->json('message'));
    }

    public function test_broadcast_validation_requires_name_and_message(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->post(route('bizboost.whatsapp.broadcasts.store'), [
            'recipient_type' => 'all',
        ]);

        $response->assertSessionHasErrors(['name', 'message']);
    }

    public function test_broadcast_validation_requires_customer_ids_for_selected_type(): void
    {
        $user = $this->createUserWithBusiness();

        $response = $this->actingAs($user)->post(route('bizboost.whatsapp.broadcasts.store'), [
            'name' => 'Test Broadcast',
            'message' => 'Test message',
            'recipient_type' => 'selected',
        ]);

        $response->assertSessionHasErrors(['customer_ids']);
    }
}
