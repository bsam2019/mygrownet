<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\UseCases;

use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;

class UnpublishSiteUseCase
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private PageRepositoryInterface $pageRepository,
    ) {}

    public function execute(int $siteId, int $userId): Site
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site) {
            throw new \DomainException('Site not found');
        }

        if ($site->getUserId() !== $userId) {
            throw new \DomainException('You do not have permission to unpublish this site');
        }

        // Unpublish all pages for the site
        $pages = $this->pageRepository->findBySiteId($site->getId());
        foreach ($pages as $page) {
            if ($page->isPublished()) {
                $page->unpublish();
                $this->pageRepository->save($page);
                \Log::info("UnpublishSiteUseCase: Unpublished page {$page->getId()->value()} for site {$siteId}");
            }
        }

        // Unpublish the site
        $site->unpublish();

        return $this->siteRepository->save($site);
    }
}
