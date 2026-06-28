<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\UseCases;

use App\Application\GrowBuilder\DTOs\SavePageContentDTO;
use App\Domain\GrowBuilder\Entities\Page;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\PageContent;
use App\Domain\GrowBuilder\ValueObjects\PageId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Services\GrowBuilder\TierLimitService;

class SavePageContentUseCase
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private SiteRepositoryInterface $siteRepository,
        private TierLimitService $tierLimitService,
    ) {}

    public function execute(SavePageContentDTO $dto): Page
    {
        // Verify site ownership
        $site = $this->siteRepository->findById(SiteId::fromInt($dto->siteId));
        if (!$site || $site->getUserId() !== $dto->userId) {
            throw new \DomainException('You do not have permission to edit this page');
        }

        if ($dto->pageId) {
            // Update existing page
            $page = $this->pageRepository->findById(PageId::fromInt($dto->pageId));
            if (!$page || $page->getSiteId() !== $dto->siteId) {
                throw new \DomainException('Page not found');
            }

            $page->updateContent(PageContent::fromArray($dto->sections));

            if ($dto->title !== null) {
                $page->updateTitle($dto->title);
            }

            if ($dto->slug !== null) {
                $page->updateSlug($dto->slug);
            }

            $page->setShowInNav($dto->showInNav);

            if ($dto->metaTitle !== null || $dto->metaDescription !== null) {
                $page->updateSeo($dto->metaTitle, $dto->metaDescription, $dto->ogImage);
            }
        } else {
            // Create new page - check tier limits from database
            $pageLimit = $this->tierLimitService->getPageLimit($dto->user);
            $currentCount = $this->pageRepository->countBySiteId($site->getId());

            if ($pageLimit > 0 && $currentCount >= $pageLimit) {
                throw new \DomainException("Page limit reached. Upgrade your subscription to add more pages.");
            }

            $page = Page::create(
                siteId: $dto->siteId,
                title: $dto->title ?? 'New Page',
                slug: $dto->slug ?? 'new-page',
                content: PageContent::fromArray($dto->sections),
                showInNav: $dto->showInNav,
            );

            if ($dto->isHomepage) {
                $this->pageRepository->clearHomepage($site->getId());
                $page->setAsHomepage();
            }
        }

        return $this->pageRepository->save($page);
    }
}
