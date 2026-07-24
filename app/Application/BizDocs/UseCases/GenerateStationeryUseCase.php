<?php

namespace App\Application\BizDocs\UseCases;

use App\Application\BizDocs\DTOs\GenerateStationeryDTO;
use App\Application\BizDocs\Services\StationeryGeneratorService;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentTemplateRepositoryInterface;

class GenerateStationeryUseCase
{
    public function __construct(
        private BusinessProfileRepositoryInterface $businessProfileRepository,
        private DocumentTemplateRepositoryInterface $templateRepository,
        private StationeryGeneratorService $stationeryGenerator,
    ) {}

    public function execute(GenerateStationeryDTO $dto): string
    {
        $businessProfile = $this->businessProfileRepository->findByUserId($dto->businessId);
        if (!$businessProfile) {
            throw new \Exception('Business profile not found');
        }

        $templateModel = $this->templateRepository->findById($dto->templateId);
        if (!$templateModel) {
            throw new \Exception('Template not found');
        }

        if (!in_array($dto->documentsPerPage, [1, 2, 4, 6, 8, 10])) {
            throw new \Exception('Documents per page must be 1, 2, 4, 6, 8, or 10');
        }

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