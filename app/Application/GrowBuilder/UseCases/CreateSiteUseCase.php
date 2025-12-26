<?php

declare(strict_types=1);

namespace App\Application\GrowBuilder\UseCases;

use App\Application\GrowBuilder\DTOs\CreateSiteDTO;
use App\Domain\GrowBuilder\Entities\Page;
use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\PageContent;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use App\Domain\GrowBuilder\ValueObjects\Theme;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\SiteRoleService;

class CreateSiteUseCase
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private TemplateRepositoryInterface $templateRepository,
        private PageRepositoryInterface $pageRepository,
        private SiteRoleService $siteRoleService,
    ) {}

    public function execute(CreateSiteDTO $dto): Site
    {
        // Validate subdomain availability
        $subdomain = Subdomain::fromString($dto->subdomain);
        if ($this->siteRepository->subdomainExists($subdomain)) {
            throw new \DomainException('This subdomain is already taken');
        }

        // Create site
        $site = Site::create(
            userId: $dto->userId,
            name: $dto->name,
            subdomain: $subdomain,
            templateId: $dto->templateId,
            description: $dto->description,
        );

        // Apply template theme if selected
        if ($dto->templateId) {
            $template = $this->templateRepository->findById(TemplateId::fromInt($dto->templateId));
            if ($template && $template->getDefaultStyles()) {
                $site->updateTheme(Theme::fromArray($template->getDefaultStyles()));
            }
            $template->incrementUsage();
            $this->templateRepository->save($template);
        }

        // Save site
        $site = $this->siteRepository->save($site);

        // Create default roles for the site
        $this->createDefaultRoles($site);

        // Create default homepage
        $this->createDefaultHomepage($site, $dto->templateId);

        return $site;
    }

    private function createDefaultRoles(Site $site): void
    {
        // Get the Eloquent model to pass to the role service
        $siteModel = GrowBuilderSite::find($site->getId()->value());
        if ($siteModel) {
            $this->siteRoleService->createDefaultRolesForSite($siteModel);
        }
    }

    private function createDefaultHomepage(Site $site, ?int $templateId): void
    {
        $content = $this->getDefaultHomeContent($templateId);

        $homepage = Page::create(
            siteId: $site->getId()->value(),
            title: 'Home',
            slug: 'home',
            content: $content,
            isHomepage: true,
        );

        $this->pageRepository->save($homepage);
    }

    private function getDefaultHomeContent(?int $templateId): PageContent
    {
        if ($templateId) {
            $template = $this->templateRepository->findById(TemplateId::fromInt($templateId));
            if ($template && !empty($template->getStructureJson())) {
                return PageContent::fromArray($template->getStructureJson()['sections'] ?? []);
            }
        }

        // Default content
        return PageContent::fromArray([
            [
                'type' => 'hero',
                'content' => [
                    'title' => 'Welcome to Our Website',
                    'subtitle' => 'We provide quality products and services',
                    'buttonText' => 'Learn More',
                    'buttonLink' => '#about',
                    'image' => null,
                ],
                'style' => [
                    'backgroundColor' => '#2563eb',
                    'textColor' => '#ffffff',
                ],
            ],
            [
                'type' => 'about',
                'content' => [
                    'title' => 'About Us',
                    'description' => 'Tell your visitors about your business. What makes you unique? What do you offer?',
                    'image' => null,
                ],
            ],
            [
                'type' => 'contact',
                'content' => [
                    'title' => 'Contact Us',
                    'description' => 'Get in touch with us',
                    'showForm' => true,
                    'showMap' => false,
                ],
            ],
        ]);
    }
}
