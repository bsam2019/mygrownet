<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Template;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\TemplateCategory;
use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TemplateRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TemplateRepositoryInterface::class);
    }

    public function test_save_and_find_by_id(): void
    {
        $template = Template::create(
            name: 'Business Pro',
            slug: 'business-pro',
            category: TemplateCategory::business(),
            structureJson: [['type' => 'hero']],
        );

        $saved = $this->repository->save($template);
        $this->assertNotNull($saved->getId());

        $found = $this->repository->findById($saved->getId());
        $this->assertNotNull($found);
        $this->assertEquals('Business Pro', $found->getName());
        $this->assertTrue($found->getCategory()->equals(TemplateCategory::business()));
    }

    public function test_find_active(): void
    {
        $active = Template::create('Active', 'active', TemplateCategory::business(), []);
        $this->repository->save($active);

        $inactive = Template::create('Inactive', 'inactive', TemplateCategory::business(), []);
        $inactive->deactivate();
        $this->repository->save($inactive);

        $activeTemplates = $this->repository->findActive();
        $this->assertCount(1, $activeTemplates);
        $this->assertEquals('Active', $activeTemplates[0]->getName());
    }

    public function test_find_free(): void
    {
        $free = Template::create('Free', 'free', TemplateCategory::portfolio(), []);
        $this->repository->save($free);

        $premium = Template::create('Premium', 'premium', TemplateCategory::portfolio(), [], isPremium: true, price: 5000);
        $this->repository->save($premium);

        $freeTemplates = $this->repository->findFree();
        $this->assertCount(1, $freeTemplates);
        $this->assertEquals('Free', $freeTemplates[0]->getName());
    }

    public function test_find_premium(): void
    {
        $p1 = Template::create('P1', 'p1', TemplateCategory::shop(), [], isPremium: true, price: 3000);
        $this->repository->save($p1);

        $free = Template::create('Free', 'free', TemplateCategory::shop(), []);
        $this->repository->save($free);

        $premiumTemplates = $this->repository->findPremium();
        $this->assertCount(1, $premiumTemplates);
        $this->assertEquals('P1', $premiumTemplates[0]->getName());
    }

    public function test_find_by_industry(): void
    {
        $biz = Template::create('Biz', 'biz', TemplateCategory::business(), []);
        $this->repository->save($biz);

        $rest = Template::create('Rest', 'rest', TemplateCategory::restaurant(), []);
        $this->repository->save($rest);

        $bizTemplates = $this->repository->findByIndustry('business');
        $this->assertCount(1, $bizTemplates);
        $this->assertEquals('Biz', $bizTemplates[0]->getName());

        $restTemplates = $this->repository->findByIndustry('restaurant');
        $this->assertCount(1, $restTemplates);
    }

    public function test_find_by_slug(): void
    {
        $template = Template::create('Test', 'my-template', TemplateCategory::business(), []);
        $this->repository->save($template);

        $found = $this->repository->findBySlug('my-template');
        $this->assertNotNull($found);
        $this->assertEquals('Test', $found->getName());

        $notFound = $this->repository->findBySlug('nonexistent');
        $this->assertNull($notFound);
    }

    public function test_get_industries(): void
    {
        $biz = Template::create('Biz', 'biz', TemplateCategory::business(), []);
        $this->repository->save($biz);

        $rest = Template::create('Rest', 'rest', TemplateCategory::restaurant(), []);
        $this->repository->save($rest);

        $industries = $this->repository->getIndustries();
        $this->assertContains('business', $industries);
        $this->assertContains('restaurant', $industries);
    }

    public function test_find_all_paginated(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repository->save(Template::create("T{$i}", "t{$i}", TemplateCategory::business(), []));
        }

        $paginator = $this->repository->findAllPaginated(2);
        $this->assertEquals(5, $paginator->total());
        $this->assertCount(2, $paginator->items());
    }

    public function test_save_updates_existing(): void
    {
        $template = Template::create('Original', 'original', TemplateCategory::business(), []);
        $saved = $this->repository->save($template);

        $saved->incrementUsage();
        $saved->updateStructure([['type' => 'new-section']]);
        $this->repository->save($saved);

        $found = $this->repository->findById($saved->getId());
        $this->assertEquals(1, $found->getUsageCount());
        $this->assertEquals([['type' => 'new-section']], $found->getStructureJson());
    }

    public function test_delete(): void
    {
        $template = Template::create('Del', 'del', TemplateCategory::business(), []);
        $saved = $this->repository->save($template);

        $this->repository->delete($saved->getId());
        $this->assertNull($this->repository->findById($saved->getId()));
    }
}
