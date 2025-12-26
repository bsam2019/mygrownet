<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\SitePlan;
use App\Domain\GrowBuilder\ValueObjects\SiteStatus;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Domain\GrowBuilder\ValueObjects\Theme;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use DateTimeImmutable;

class EloquentSiteRepository implements SiteRepositoryInterface
{
    public function findById(SiteId $id): ?Site
    {
        $model = GrowBuilderSite::find($id->value());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySubdomain(Subdomain $subdomain): ?Site
    {
        $model = GrowBuilderSite::where('subdomain', $subdomain->value())->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCustomDomain(string $domain): ?Site
    {
        $model = GrowBuilderSite::where('custom_domain', $domain)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        return GrowBuilderSite::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function subdomainExists(Subdomain $subdomain): bool
    {
        return GrowBuilderSite::where('subdomain', $subdomain->value())->exists();
    }

    public function customDomainExists(string $domain): bool
    {
        return GrowBuilderSite::where('custom_domain', $domain)->exists();
    }

    public function save(Site $site): Site
    {
        $data = [
            'user_id' => $site->getUserId(),
            'template_id' => $site->getTemplateId(),
            'name' => $site->getName(),
            'subdomain' => $site->getSubdomain()->value(),
            'custom_domain' => $site->getCustomDomain(),
            'description' => $site->getDescription(),
            'logo' => $site->getLogo(),
            'favicon' => $site->getFavicon(),
            'settings' => $site->getSettings(),
            'theme' => $site->getTheme()?->toArray(),
            'social_links' => $site->getSocialLinks(),
            'contact_info' => $site->getContactInfo(),
            'business_hours' => $site->getBusinessHours(),
            'seo_settings' => $site->getSeoSettings(),
            'status' => $site->getStatus()->value(),
            'plan' => $site->getPlan()->value(),
            'published_at' => $site->getPublishedAt()?->format('Y-m-d H:i:s'),
            'plan_expires_at' => $site->getPlanExpiresAt()?->format('Y-m-d H:i:s'),
        ];

        if ($site->getId()) {
            $model = GrowBuilderSite::findOrFail($site->getId()->value());
            $model->update($data);
        } else {
            $model = GrowBuilderSite::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(SiteId $id): void
    {
        GrowBuilderSite::destroy($id->value());
    }

    public function countByUserId(int $userId): int
    {
        return GrowBuilderSite::where('user_id', $userId)->count();
    }

    private function toDomainEntity(GrowBuilderSite $model): Site
    {
        return Site::reconstitute(
            id: SiteId::fromInt($model->id),
            userId: $model->user_id,
            templateId: $model->template_id,
            name: $model->name,
            subdomain: Subdomain::fromString($model->subdomain),
            customDomain: $model->custom_domain,
            description: $model->description,
            logo: $model->logo,
            favicon: $model->favicon,
            settings: $model->settings ?? [],
            theme: $model->theme ? Theme::fromArray($model->theme) : null,
            socialLinks: $model->social_links ?? [],
            contactInfo: $model->contact_info ?? [],
            businessHours: $model->business_hours ?? [],
            seoSettings: $model->seo_settings ?? [],
            status: SiteStatus::fromString($model->status),
            plan: SitePlan::fromString($model->plan),
            publishedAt: $model->published_at ? new DateTimeImmutable($model->published_at->toDateTimeString()) : null,
            planExpiresAt: $model->plan_expires_at ? new DateTimeImmutable($model->plan_expires_at->toDateTimeString()) : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
