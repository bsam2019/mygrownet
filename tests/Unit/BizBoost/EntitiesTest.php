<?php

namespace Tests\Unit\BizBoost;

use PHPUnit\Framework\TestCase;
use App\Domain\BizBoost\Entities\AdCampaign;
use App\Domain\BizBoost\Entities\AiUsageLog;
use App\Domain\BizBoost\Entities\AnalyticsEvent;
use App\Domain\BizBoost\Entities\BillingLedger;
use App\Domain\BizBoost\Entities\Business;
use App\Domain\BizBoost\Entities\BusinessProfile;
use App\Domain\BizBoost\Entities\Campaign;
use App\Domain\BizBoost\Entities\Category;
use App\Domain\BizBoost\Entities\ClientWallet;
use App\Domain\BizBoost\Entities\Customer;
use App\Domain\BizBoost\Entities\CustomerTag;
use App\Domain\BizBoost\Entities\CustomTemplate;
use App\Domain\BizBoost\Entities\FollowUpReminder;
use App\Domain\BizBoost\Entities\Integration;
use App\Domain\BizBoost\Entities\Location;
use App\Domain\BizBoost\Entities\OmnichannelLog;
use App\Domain\BizBoost\Entities\Order;
use App\Domain\BizBoost\Entities\OrderItem;
use App\Domain\BizBoost\Entities\Post;
use App\Domain\BizBoost\Entities\PostingTime;
use App\Domain\BizBoost\Entities\PostMedia;
use App\Domain\BizBoost\Entities\Product;
use App\Domain\BizBoost\Entities\ProductImage;
use App\Domain\BizBoost\Entities\QrCode;
use App\Domain\BizBoost\Entities\Sale;
use App\Domain\BizBoost\Entities\TeamMember;
use App\Domain\BizBoost\Entities\Template;
use App\Domain\BizBoost\Entities\WalletTransaction;
use App\Domain\BizBoost\Entities\WeeklyTheme;

class EntitiesTest extends TestCase
{
    public static function entityProvider(): array
    {
        $now = '2026-07-29T00:00:00';
        $samples = [];

        $samples[] = [AdCampaign::class, [
            'id' => 1, 'user_id' => 1, 'business_id' => 1, 'name' => 'Camp', 'objective' => 'awareness',
            'client_budget' => 100.5, 'meta_budget' => 90.0, 'platform_markup' => 10.5,
            'meta_campaign_id' => 'mc1', 'meta_ad_set_id' => 'mas1', 'meta_ad_id' => 'ma1',
            'status' => 'active', 'start_date' => '2026-07-01', 'end_date' => '2026-07-31',
            'duration_days' => 30, 'targeting' => ['age' => '18-35'], 'creatives' => ['img1'],
            'insights' => ['views' => 100], 'error_message' => null,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [AiUsageLog::class, [
            'id' => 1, 'business_id' => 1, 'user_id' => 1, 'content_type' => 'post',
            'model' => 'gpt-4', 'input_tokens' => 100, 'output_tokens' => 50, 'credits_used' => 10,
            'request_params' => ['temp' => 0.7], 'prompt' => 'Write a post', 'response' => 'Post text',
            'was_successful' => true, 'error_message' => null,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [AnalyticsEvent::class, [
            'id' => 1, 'business_id' => 1, 'event_type' => 'page_view', 'source' => 'web',
            'post_id' => 1, 'payload' => ['url' => '/'], 'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla', 'referrer' => 'google.com',
            'recorded_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [BillingLedger::class, [
            'id' => 1, 'user_id' => 1, 'service_type' => 'sms', 'campaign_id' => 1,
            'recipient_identifier' => '+260971234567', 'gross_amount_charged' => 10.0,
            'net_vendor_cost' => 7.0, 'pure_platform_profit' => 3.0, 'currency' => 'ZMW',
            'vendor' => 'Airtel', 'delivery_status' => 'delivered', 'reference' => 'ref1',
            'meta' => ['msg' => 'hello'], 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Business::class, [
            'id' => 1, 'user_id' => 1, 'organization_id' => null, 'name' => 'Test Biz',
            'slug' => 'test-biz', 'description' => 'Desc', 'logo_path' => 'logo.png',
            'industry' => 'boutique', 'address' => '123 Main', 'city' => 'Lusaka',
            'province' => 'Lusaka', 'country' => 'Zambia', 'phone' => '+260971234567',
            'whatsapp' => '+260971234567', 'email' => 'biz@test.com', 'website' => 'https://biz.com',
            'timezone' => 'Africa/Lusaka', 'locale' => 'en', 'currency' => 'ZMW',
            'social_links' => ['fb' => 'biz'], 'business_hours' => ['mon' => '9-5'],
            'settings' => ['theme' => 'dark'], 'white_label_config' => null,
            'is_active' => true, 'onboarding_completed' => true, 'marketplace_listed' => false,
            'marketplace_listed_at' => null, 'marketplace_seller_id' => null,
            'marketplace_sync_enabled' => false, 'marketplace_synced_at' => null,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [BusinessProfile::class, [
            'id' => 1, 'business_id' => 1, 'hero_image_path' => 'hero.jpg',
            'about_image_path' => 'about.jpg', 'banner_image_path' => 'banner.jpg',
            'about' => 'About us', 'business_story' => 'Our story', 'mission' => 'Mission',
            'vision' => 'Vision', 'founding_year' => 2020,
            'business_hours' => ['mon' => '9-5'], 'team_members' => [['name' => 'John']],
            'achievements' => ['award1'], 'services' => ['consulting'],
            'testimonials' => [['text' => 'Great']], 'tagline' => 'Tagline',
            'contact_email' => 'info@biz.com', 'gallery_images' => ['img1.jpg'],
            'seo_meta' => ['keywords' => 'test'], 'theme_settings' => ['color' => 'blue'],
            'show_products' => true, 'show_services' => true, 'show_gallery' => false,
            'show_testimonials' => false, 'show_business_hours' => true,
            'show_contact_form' => true, 'show_whatsapp_button' => true,
            'show_social_links' => true, 'is_published' => false,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Campaign::class, [
            'id' => 1, 'business_id' => 1, 'name' => 'Camp1', 'description' => 'Desc',
            'objective' => 'awareness', 'status' => 'active', 'start_date' => '2026-07-01',
            'end_date' => '2026-07-31', 'duration_days' => 30,
            'campaign_config' => ['budget' => 100], 'target_platforms' => ['facebook'],
            'analytics' => ['reach' => 500], 'posts_created' => 5, 'posts_published' => 3,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Category::class, [
            'id' => 1, 'business_id' => 1, 'name' => 'Cat1', 'slug' => 'cat1',
            'description' => 'Category desc', 'color' => '#ff0000', 'icon' => 'icon.png',
            'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [ClientWallet::class, [
            'id' => 1, 'user_id' => 1, 'balance' => 100.0, 'locked_balance' => 20.0,
            'currency' => 'ZMW', 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Customer::class, [
            'id' => 1, 'business_id' => 1, 'name' => 'John', 'phone' => '+260971234567',
            'email' => 'john@test.com', 'whatsapp' => '+260971234567', 'address' => 'Addr',
            'notes' => 'Notes', 'source' => 'manual', 'birthday' => '1990-01-01',
            'total_spent' => 500.0, 'total_orders' => 10, 'last_purchase_at' => $now,
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [CustomerTag::class, [
            'id' => 1, 'business_id' => 1, 'name' => 'VIP', 'color' => '#gold',
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [CustomTemplate::class, [
            'id' => 1, 'business_id' => 1, 'base_template_id' => 1, 'name' => 'CT1',
            'description' => 'Desc', 'category' => 'social', 'template_data' => ['type' => 'post'],
            'thumbnail_path' => 'thumb.png', 'width' => 1080, 'height' => 1080,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [FollowUpReminder::class, [
            'id' => 1, 'business_id' => 1, 'customer_id' => 1, 'title' => 'Follow up',
            'description' => 'Call back', 'due_date' => '2026-08-01', 'due_time' => '10:00',
            'remind_at' => '2026-08-01 09:00:00', 'reminder_type' => 'call',
            'priority' => 'high', 'status' => 'pending', 'notification_sent' => false,
            'completed_at' => null, 'completion_notes' => null, 'snoozed_count' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Integration::class, [
            'id' => 1, 'business_id' => 1, 'provider' => 'facebook', 'provider_user_id' => 'fu1',
            'provider_page_id' => 'fp1', 'provider_page_name' => 'My Page',
            'access_token' => 'tok', 'refresh_token' => 'rtok', 'token_expires_at' => '2027-01-01',
            'scopes' => ['pages_read'], 'meta' => ['info' => 'val'], 'catalog_id' => 'cat1',
            'whatsapp_catalog_settings' => ['enabled' => true], 'status' => 'connected',
            'connected_at' => $now, 'last_used_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Location::class, [
            'id' => 1, 'business_id' => 1, 'name' => 'Main', 'address' => '123 St',
            'city' => 'Lusaka', 'phone' => '+260971234567', 'whatsapp' => '+260971234567',
            'business_hours' => ['mon' => '9-5'], 'is_primary' => true, 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [OmnichannelLog::class, [
            'id' => 1, 'user_id' => 1, 'business_id' => 1, 'channel_type' => 'sms',
            'recipient_phone' => '+260971234567', 'message_content' => 'Hello',
            'client_amount_charged' => 1.0, 'vendor_actual_cost' => 0.5, 'net_platform_profit' => 0.5,
            'delivery_status' => 'sent', 'error_message' => null, 'reference' => 'ref1',
            'meta' => ['segments' => 1], 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Order::class, [
            'id' => 1, 'business_id' => 1, 'order_number' => 'ORD-001', 'customer_name' => 'John',
            'customer_phone' => '+260971234567', 'customer_email' => 'j@t.com',
            'delivery_address' => 'Addr', 'notes' => 'Notes', 'subtotal' => 100.0,
            'delivery_fee' => 10.0, 'total' => 110.0, 'currency' => 'ZMW',
            'payment_method' => 'cash', 'payment_status' => 'paid', 'order_status' => 'delivered',
            'source' => 'web', 'payment_reference' => 'ref1', 'paid_at' => $now,
            'delivered_at' => $now, 'meta' => ['note' => 'fast'], 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [OrderItem::class, [
            'id' => 1, 'order_id' => 1, 'product_id' => 1, 'product_name' => 'Widget',
            'unit_price' => 50.0, 'quantity' => 2, 'subtotal' => 100.0,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Post::class, [
            'id' => 1, 'business_id' => 1, 'title' => 'Post Title', 'caption' => 'Caption',
            'status' => 'published', 'scheduled_at' => null, 'published_at' => $now,
            'platform_targets' => ['facebook'], 'external_ids' => ['fb123'],
            'analytics' => ['likes' => 10], 'post_type' => 'standard', 'template_id' => 1,
            'campaign_id' => 1, 'error_message' => null, 'retry_count' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [PostingTime::class, [
            'id' => 1, 'business_id' => 1, 'day_type' => 'weekday',
            'times' => ['09:00', '15:00'], 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [PostMedia::class, [
            'id' => 1, 'post_id' => 1, 'type' => 'image', 'path' => 'img.jpg',
            'filename' => 'img.jpg', 'file_size' => 1024, 'mime_type' => 'image/jpeg',
            'width' => 1080, 'height' => 720, 'duration' => null, 'thumbnail_path' => 'thumb.jpg',
            'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Product::class, [
            'id' => 1, 'business_id' => 1, 'name' => 'Widget', 'sku' => 'WID-001',
            'description' => 'A widget', 'price' => 50.0, 'sale_price' => 40.0,
            'currency' => 'ZMW', 'category' => 'Goods', 'category_id' => 1,
            'stock_quantity' => 100, 'track_inventory' => true, 'is_active' => true,
            'is_featured' => false, 'sort_order' => 1, 'attributes' => ['color' => 'red'],
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [ProductImage::class, [
            'id' => 1, 'product_id' => 1, 'path' => 'img.jpg', 'filename' => 'img.jpg',
            'file_size' => 1024, 'is_primary' => true, 'sort_order' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [QrCode::class, [
            'id' => 1, 'business_id' => 1, 'name' => 'Menu QR', 'type' => 'menu',
            'target_url' => 'https://menu.com', 'short_code' => 'abc123',
            'qr_image_path' => 'qr.png', 'style_config' => ['color' => 'black'],
            'scan_count' => 42, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Sale::class, [
            'id' => 1, 'business_id' => 1, 'customer_id' => 1, 'product_id' => 1,
            'product_name' => 'Widget', 'quantity' => 2, 'unit_price' => 50.0,
            'total_amount' => 100.0, 'currency' => 'ZMW', 'sale_date' => '2026-07-29',
            'payment_method' => 'cash', 'source' => 'manual', 'linked_post_id' => 1,
            'notes' => 'Sale note', 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [TeamMember::class, [
            'id' => 1, 'business_id' => 1, 'user_id' => 1, 'name' => 'Jane', 'email' => 'j@t.com',
            'role' => 'manager', 'permissions' => ['read'], 'location_id' => 1, 'status' => 'active',
            'invited_at' => $now, 'joined_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [Template::class, [
            'id' => 1, 'name' => 'T1', 'slug' => 't1', 'description' => 'Desc',
            'category' => 'social', 'industry' => 'retail', 'template_data' => ['type' => 'post'],
            'thumbnail_path' => 'thumb.png', 'preview_path' => 'preview.png', 'width' => 1080,
            'height' => 1080, 'is_premium' => false, 'is_active' => true, 'is_featured' => false,
            'usage_count' => 10, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [WalletTransaction::class, [
            'id' => 1, 'wallet_id' => 1, 'type' => 'credit', 'amount' => 100.0,
            'balance_before' => 0.0, 'balance_after' => 100.0, 'currency' => 'ZMW',
            'reference' => 'ref1', 'description' => 'Top up', 'payable_type' => 'App\Models\User',
            'payable_id' => 1, 'status' => 'completed', 'meta' => ['source' => 'manual'],
            'created_at' => $now, 'updated_at' => $now,
        ]];

        $samples[] = [WeeklyTheme::class, [
            'id' => 1, 'business_id' => 1, 'week_start' => '2026-07-27', 'theme' => 'Wellness',
            'description' => 'Wellness week', 'color' => '#green',
            'created_at' => $now, 'updated_at' => $now,
        ]];

        return $samples;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('entityProvider')]
    public function test_reconstitute_toArray_roundtrip(string $class, array $data): void
    {
        $entity = $class::reconstitute($data);
        $this->assertInstanceOf($class, $entity);

        $output = $entity->toArray();
        $reconstituted = $class::reconstitute($output);
        $this->assertInstanceOf($class, $reconstituted);

        $output2 = $reconstituted->toArray();

        $this->assertSame($output, $output2, "$class: toArray -> reconstitute -> toArray did not produce identical output");
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('entityProvider')]
    public function test_reconstitute_with_minimal_data(string $class, array $data): void
    {
        $minimal = array_filter($data, function ($value, $key) {
            return !str_ends_with($key, '_at') && $key !== 'id' && $value !== null && $value !== false && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $entity = $class::reconstitute($minimal);
        $this->assertInstanceOf($class, $entity);
        $output = $entity->toArray();

        $reconstituted = $class::reconstitute($output);
        $output2 = $reconstituted->toArray();
        $this->assertSame($output, $output2, "$class: minimal data round-trip failed");
    }

    public static function createEntityProvider(): array
    {
        return [
            'Business' => [Business::class, ['user_id' => 1, 'name' => 'New Biz', 'slug' => 'new-biz']],
            'Customer' => [Customer::class, ['business_id' => 1, 'name' => 'New Cust', 'phone' => '+260971234567']],
            'Product' => [Product::class, ['business_id' => 1, 'name' => 'New Prod', 'price' => 100]],
            'Sale' => [Sale::class, ['business_id' => 1, 'sale_date' => '2026-07-29']],
            'Post' => [Post::class, ['business_id' => 1, 'caption' => 'New post', 'status' => 'draft']],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('createEntityProvider')]
    public function test_create_method(string $class, array $data): void
    {
        $entity = $class::create($data);
        $this->assertInstanceOf($class, $entity);
        $this->assertNull($entity->id);
    }
}
