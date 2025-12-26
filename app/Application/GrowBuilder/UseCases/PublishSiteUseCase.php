<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\UseCases;

use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;

class PublishSiteUseCase
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
            throw new \DomainException('You do not have permission to publish this site');
        }

        if (!$site->canPublish()) {
            throw new \DomainException('This site cannot be published');
        }

        // Check if site has at least one page
        $pageCount = $this->pageRepository->countBySiteId($site->getId());
        if ($pageCount === 0) {
            throw new \DomainException('Site must have at least one page before publishing');
        }

        // Check if homepage exists
        $homepage = $this->pageRepository->findHomepage($site->getId());
        if (!$homepage) {
            throw new \DomainException('Site must have a homepage before publishing');
        }

        $site->publish();

        return $this->siteRepository->save($site);
    }
}
