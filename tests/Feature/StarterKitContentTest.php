<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StarterKitContentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $premiumUser;
    protected ContentItemModel $basicContent;
    protected ContentItemModel $premiumContent;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create([
            'has_starter_kit' => true,
            'starter_kit_tier' => 'basic',
        ]);

        $this->premiumUser = User::factory()->create([
            'has_starter_kit' => true,
            'starter_kit_tier' => 'premium',
        ]);

        // Create test content
        $this->basicContent = ContentItemModel::create([
            'title' => 'Basic E-Book',
            'description' => 'Test basic content',
            'category' => 'ebook',
            'tier_restriction' => 'all',
            'unlock_day' => 0,
            'estimated_value' => 100,
            'is_downloadable' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->premiumContent = ContentItemModel::create([
            'title' => 'Premium E-Book',
            'description' => 'Test premium content',
            'category' => 'ebook',
            'tier_restriction' => 'premium',
            'unlock_day' => 0,
            'estimated_value' => 200,
            'is_downloadable' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Storage::fake('private');
        Storage::fake('public');
    }

    /** @test */
    public function user_without_starter_kit_cannot_access_content()
    {
        $userWithoutKit = User::factory()->create(['has_starter_kit' => false]);

        $response = $this->actingAs($userWithoutKit)
            ->get(route('mygrownet.content.index'));

        $response->assertRedirect(route('mygrownet.starter-kit.purchase'));
    }

    /** @test */
    public function basic_user_can_see_basic_content()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.content.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('MyGrowNet/StarterKitContent')
            ->has('contentItems')
        );
    }

    /** @test */
    public function basic_user_cannot_access_premium_content()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.content.show', $this->premiumContent->id));

        $response->assertRedirect(route('mygrownet.starter-kit.upgrade'));
    }

    /** @test */
    public function premium_user_can_access_all_content()
    {
        $response = $this->actingAs($this->premiumUser)
            ->get(route('mygrownet.content.show', $this->premiumContent->id));

        $response->assertOk();
    }

    /** @test */
    public function user_can_download_content()
    {
        // Create a fake file
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('starter-kit/ebooks', 'private');

        $this->basicContent->update([
            'file_path' => $path,
            'file_type' => 'pdf',
            'file_size' => $file->getSize(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.content.download', $this->basicContent->id));

        $response->assertOk();
        $this->assertEquals(1, $this->basicContent->fresh()->download_count);
    }

    /** @test */
    public function download_increments_counter()
    {
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('starter-kit/ebooks', 'private');

        $this->basicContent->update([
            'file_path' => $path,
            'file_type' => 'pdf',
            'file_size' => $file->getSize(),
        ]);

        $initialCount = $this->basicContent->download_count;

        $this->actingAs($this->user)
            ->get(route('mygrownet.content.download', $this->basicContent->id));

        $this->assertEquals($initialCount + 1, $this->basicContent->fresh()->download_count);
    }

    /** @test */
    public function access_is_tracked()
    {
        $this->actingAs($this->user)
            ->get(route('mygrownet.content.show', $this->basicContent->id));

        $this->assertDatabaseHas('starter_kit_content_access', [
            'user_id' => $this->user->id,
            'content_item_id' => $this->basicContent->id,
        ]);
    }

    /** @test */
    public function commission_calculator_requires_starter_kit()
    {
        $userWithoutKit = User::factory()->create(['has_starter_kit' => false]);

        $response = $this->actingAs($userWithoutKit)
            ->get(route('mygrownet.tools.commission-calculator'));

        $response->assertRedirect(route('mygrownet.starter-kit.purchase'));
    }

    /** @test */
    public function commission_calculator_loads_for_starter_kit_users()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.tools.commission-calculator'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('MyGrowNet/Tools/CommissionCalculator')
            ->has('commissionRates')
            ->has('networkStats')
        );
    }

    /** @test */
    public function premium_tools_require_premium_tier()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.tools.business-plan-generator'));

        $response->assertRedirect(route('mygrownet.starter-kit.upgrade'));
    }

    /** @test */
    public function premium_user_can_access_premium_tools()
    {
        $response = $this->actingAs($this->premiumUser)
            ->get(route('mygrownet.tools.business-plan-generator'));

        $response->assertOk();
    }
}
