<?php

namespace App\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use DateTimeImmutable;

class Venture
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $categoryId = null,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly ?string $businessModel = null,
        public readonly ?string $featuredImage = null,
        public readonly ?float $fundingTarget = null,
        public readonly ?float $minimumInvestment = null,
        public readonly ?float $maximumInvestment = null,
        public readonly ?float $sharePrice = null,
        public readonly ?float $totalRaised = null,
        public readonly ?int $investorCount = null,
        public readonly ?DateTimeImmutable $fundingStartDate = null,
        public readonly ?DateTimeImmutable $fundingEndDate = null,
        public readonly ?DateTimeImmutable $expectedLaunchDate = null,
        public readonly ?DateTimeImmutable $actualLaunchDate = null,
        public readonly VentureStatus $status,
        public readonly ?string $companyName = null,
        public readonly ?string $companyRegistrationNumber = null,
        public readonly ?DateTimeImmutable $companyFormationDate = null,
        public readonly ?float $mygrowNetEquityPercentage = null,
        public readonly ?array $revenueProjections = null,
        public readonly ?string $riskFactors = null,
        public readonly ?int $expectedRoiMonths = null,
        public readonly ?bool $isFeatured = null,
        public readonly ?int $viewsCount = null,
        public readonly ?int $createdBy = null,
        public readonly ?int $approvedBy = null,
        public readonly ?DateTimeImmutable $approvedAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function getFundingProgressPercentage(): float
    {
        if ($this->fundingTarget === null || $this->fundingTarget <= 0) {
            return 0;
        }

        return min(100, (($this->totalRaised ?? 0) / $this->fundingTarget) * 100);
    }

    public function isFundingOpen(): bool
    {
        if (!$this->status->isFunding()) {
            return false;
        }

        if ($this->fundingEndDate !== null && $this->fundingEndDate < new DateTimeImmutable()) {
            return false;
        }

        return true;
    }

    public function canAcceptInvestments(): bool
    {
        return $this->isFundingOpen()
            && ($this->totalRaised ?? 0) < ($this->fundingTarget ?? 0);
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            title: $data['title'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            businessModel: $data['business_model'] ?? null,
            featuredImage: $data['featured_image'] ?? null,
            fundingTarget: array_key_exists('funding_target', $data) ? (float) $data['funding_target'] : null,
            minimumInvestment: array_key_exists('minimum_investment', $data) ? (float) $data['minimum_investment'] : null,
            maximumInvestment: array_key_exists('maximum_investment', $data) ? (float) $data['maximum_investment'] : null,
            sharePrice: array_key_exists('share_price', $data) ? (float) $data['share_price'] : null,
            totalRaised: array_key_exists('total_raised', $data) ? (float) $data['total_raised'] : null,
            investorCount: array_key_exists('investor_count', $data) ? (int) $data['investor_count'] : null,
            fundingStartDate: isset($data['funding_start_date']) ? new \DateTimeImmutable($data['funding_start_date']) : null,
            fundingEndDate: isset($data['funding_end_date']) ? new \DateTimeImmutable($data['funding_end_date']) : null,
            expectedLaunchDate: isset($data['expected_launch_date']) ? new \DateTimeImmutable($data['expected_launch_date']) : null,
            actualLaunchDate: isset($data['actual_launch_date']) ? new \DateTimeImmutable($data['actual_launch_date']) : null,
            status: VentureStatus::fromString($data['status'] ?? 'draft'),
            companyName: $data['company_name'] ?? null,
            companyRegistrationNumber: $data['company_registration_number'] ?? null,
            companyFormationDate: isset($data['company_formation_date']) ? new \DateTimeImmutable($data['company_formation_date']) : null,
            mygrowNetEquityPercentage: array_key_exists('mygrownet_equity_percentage', $data) ? (float) $data['mygrownet_equity_percentage'] : null,
            revenueProjections: isset($data['revenue_projections']) ? (is_array($data['revenue_projections']) ? $data['revenue_projections'] : json_decode($data['revenue_projections'], true)) : null,
            riskFactors: $data['risk_factors'] ?? null,
            expectedRoiMonths: array_key_exists('expected_roi_months', $data) ? (int) $data['expected_roi_months'] : null,
            isFeatured: isset($data['is_featured']) ? (bool) $data['is_featured'] : null,
            viewsCount: array_key_exists('views_count', $data) ? (int) $data['views_count'] : null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
            approvedBy: isset($data['approved_by']) ? (int) $data['approved_by'] : null,
            approvedAt: isset($data['approved_at']) ? new \DateTimeImmutable($data['approved_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'business_model' => $this->businessModel,
            'featured_image' => $this->featuredImage,
            'funding_target' => $this->fundingTarget,
            'minimum_investment' => $this->minimumInvestment,
            'maximum_investment' => $this->maximumInvestment,
            'share_price' => $this->sharePrice,
            'total_raised' => $this->totalRaised,
            'investor_count' => $this->investorCount,
            'funding_start_date' => $this->fundingStartDate?->format('Y-m-d H:i:s'),
            'funding_end_date' => $this->fundingEndDate?->format('Y-m-d H:i:s'),
            'expected_launch_date' => $this->expectedLaunchDate?->format('Y-m-d H:i:s'),
            'actual_launch_date' => $this->actualLaunchDate?->format('Y-m-d H:i:s'),
            'status' => $this->status->value(),
            'company_name' => $this->companyName,
            'company_registration_number' => $this->companyRegistrationNumber,
            'company_formation_date' => $this->companyFormationDate?->format('Y-m-d H:i:s'),
            'mygrownet_equity_percentage' => $this->mygrowNetEquityPercentage,
            'revenue_projections' => $this->revenueProjections,
            'risk_factors' => $this->riskFactors,
            'expected_roi_months' => $this->expectedRoiMonths,
            'is_featured' => $this->isFeatured,
            'views_count' => $this->viewsCount,
            'created_by' => $this->createdBy,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
