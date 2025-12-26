<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Page;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\PageContent;
use App\Domain\GrowBuilder\ValueObjects\PageId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use DateTimeImmutable;

class EloquentPageRepository implements PageRepositoryInterface
{
    public function findById(PageId $id): ?Page
    {
        $model = GrowBuilderPage::find($id->value());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySiteId(SiteId $siteId): array
    {
        return GrowBuilderPage::where('site_id', $siteId->value())
            ->orderBy('nav_order')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findBySiteIdAndSlug(SiteId $siteId, string $slug): ?Page
    {
        $model = GrowBuilderPage::where('site_id', $siteId->value())
            ->where('slug', $slug)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findHomepage(SiteId $siteId): ?Page
    {
        $model = GrowBuilderPage::where('site_id', $siteId->value())
            ->where('is_homepage', true)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findPublishedBySiteId(SiteId $siteId): array
    {
        return GrowBuilderPage::where('site_id', $siteId->value())
            ->where('is_published', true)
            ->orderBy('nav_order')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function save(Page $page): Page
    {
        $data = [
            'site_id' => $page->getSiteId(),
            'title' => $page->getTitle(),
            'slug' => $page->getSlug(),
            'content_json' => $page->getContent()->toArray(),
            'meta_title' => $page->getMetaTitle(),
            'meta_description' => $page->getMetaDescription(),
            'og_image' => $page->getOgImage(),
            'is_homepage' => $page->isHomepage(),
            'is_published' => $page->isPublished(),
            'show_in_nav' => $page->showInNav(),
            'nav_order' => $page->getNavOrder(),
        ];

        if ($page->getId()) {
            $model = GrowBuilderPage::findOrFail($page->getId()->value());
            $model->update($data);
        } else {
            $model = GrowBuilderPage::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(PageId $id): void
    {
        GrowBuilderPage::destroy($id->value());
    }

    public function countBySiteId(SiteId $siteId): int
    {
        return GrowBuilderPage::where('site_id', $siteId->value())->count();
    }

    public function clearHomepage(SiteId $siteId): void
    {
        GrowBuilderPage::where('site_id', $siteId->value())
            ->where('is_homepage', true)
            ->update(['is_homepage' => false]);
    }

    private function toDomainEntity(GrowBuilderPage $model): Page
    {
        return Page::reconstitute(
            id: PageId::fromInt($model->id),
            siteId: $model->site_id,
            title: $model->title,
            slug: $model->slug,
            content: PageContent::fromArray($model->content_json['sections'] ?? $model->content_json ?? []),
            metaTitle: $model->meta_title,
            metaDescription: $model->meta_description,
            ogImage: $model->og_image,
            isHomepage: $model->is_homepage,
            isPublished: $model->is_published,
            showInNav: $model->show_in_nav,
            navOrder: $model->nav_order,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
