<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;

interface SiteRepositoryInterface
{
    public function findById(SiteId $id): ?Site;
    public function findBySubdomain(Subdomain $subdomain): ?Site;
    public function findByCustomDomain(string $domain): ?Site;
    public function findByUserId(int $userId): array;
    public function subdomainExists(Subdomain $subdomain): bool;
    public function customDomainExists(string $domain): bool;
    public function save(Site $site): Site;
    public function delete(SiteId $id): void;
    public function countByUserId(int $userId): int;
}
