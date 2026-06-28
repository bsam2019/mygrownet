<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Page;
use App\Domain\GrowBuilder\ValueObjects\PageId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;

interface PageRepositoryInterface
{
    public function findById(PageId $id): ?Page;
    public function findBySiteId(SiteId $siteId): array;
    public function findBySiteIdAndSlug(SiteId $siteId, string $slug): ?Page;
    public function findHomepage(SiteId $siteId): ?Page;
    public function findPublishedBySiteId(SiteId $siteId): array;
    public function save(Page $page): Page;
    public function delete(PageId $id): void;
    public function countBySiteId(SiteId $siteId): int;
    public function clearHomepage(SiteId $siteId): void;
}
