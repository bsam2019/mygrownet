<?php

namespace App\Application\BizDocs\UseCases;

use App\Application\BizDocs\DTOs\GenerateStationeryDTO;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel;
use App\Application\BizDocs\Services\StationeryGeneratorService;

class GenerateStationeryUseCase
{
    public function __construct(
        private BusinessProfileRepositoryInterface $businessProfileRepository,
        private StationeryGeneratorService $stationeryGenerator,
    ) {}

    public function execute(GenerateStationeryDTO $dto): string
    {
        // Load business profile
        $businessProfile = $this->businessProfileRepository->findByUserId($dto->businessId);
        if (!$businessProfile) {
            throw new \Exception('Business profile not found');
        }

        // Load template using Eloquent directly
        $templateModel = DocumentTemplateModel::find($dto->templateId);
        if (!$templateModel) {
            throw new \Exception('Template not found');
        }

        // Validate documents per page
        if (!in_array($dto->documentsPerPage, [1, 2, 4, 6, 8, 10])) {
            throw new \Exception('Documents per page must be 1, 2, 4, 6, 8, or 10');
        }

        // Generate stationery PDF and return raw PDF content
        $pdfContent = $this->stationeryGenerator->generate(
            businessProfile: $businessProfile,
            templateModel: $templateModel,
            documentType: $dto->documentType,
            quantity: $dto->quantity,
            documentsPerPage: $dto->documentsPerPage,
            startingNumber: $dto->startingNumber,
            pageSize: $dto->pageSize,
            rowCount: $dto->rowCount,
        );

        return $pdfContent;
    }
}
