<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\UseCases;

use App\Application\GrowBuilder\DTOs\UpdateSiteDTO;
use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\Theme;

class UpdateSiteUseCase
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function execute(UpdateSiteDTO $dto): Site
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($dto->siteId));

        if (!$site) {
            throw new \DomainException('Site not found');
        }

        // Verify ownership
        if ($site->getUserId() !== $dto->userId) {
            throw new \DomainException('You do not have permission to update this site');
        }

        if ($dto->name !== null) {
            $site->updateName($dto->name);
        }

        if ($dto->description !== null) {
            $site->updateDescription($dto->description);
        }

        if ($dto->logo !== null) {
            $site->updateLogo($dto->logo);
        }

        if ($dto->favicon !== null) {
            $site->updateFavicon($dto->favicon);
        }

        if ($dto->theme !== null) {
            $site->updateTheme(Theme::fromArray($dto->theme));
        }

        if ($dto->contactInfo !== null) {
            $site->updateContactInfo($dto->contactInfo);
        }

        if ($dto->socialLinks !== null) {
            $site->updateSocialLinks($dto->socialLinks);
        }

        if ($dto->seoSettings !== null) {
            $site->updateSeoSettings($dto->seoSettings);
        }

        if ($dto->customDomain !== null) {
            if (!empty($dto->customDomain) && !$site->getPlan()->canUseCustomDomain()) {
                throw new \DomainException('Custom domains require Business or Pro plan');
            }

            if (!empty($dto->customDomain) && $this->siteRepository->customDomainExists($dto->customDomain)) {
                throw new \DomainException('This domain is already in use');
            }

            $site->setCustomDomain($dto->customDomain ?: null);
        }

        return $this->siteRepository->save($site);
    }
}
