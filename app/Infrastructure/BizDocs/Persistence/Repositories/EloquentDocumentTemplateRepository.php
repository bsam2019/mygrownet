<?php

namespace App\Infrastructure\BizDocs\Persistence\Repositories;

use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentTemplateRepositoryInterface;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel;
use Illuminate\Support\Facades\DB;

class EloquentDocumentTemplateRepository implements DocumentTemplateRepositoryInterface
{
    public function findById(int $id)
    {
        return DocumentTemplateModel::find($id);
    }

    public function getAvailableForBusiness(int $businessId): array
    {
        return DocumentTemplateModel::where(function ($query) use ($businessId) {
                $query->where('visibility', 'industry')
                      ->orWhere(function ($q) use ($businessId) {
                          $q->where('visibility', 'business')
                            ->where('owner_id', $businessId);
                      });
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('visibility', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }

    public function findAll(): array
    {
        return DocumentTemplateModel::all()->toArray();
    }

    public function getUserTemplatePreference(int $businessId, string $documentType): ?array
    {
        $preference = DB::table('bizdocs_user_template_preferences')
            ->where('business_id', $businessId)
            ->where('document_type', $documentType)
            ->first();

        return $preference ? (array) $preference : null;
    }

    public function setUserTemplatePreference(int $businessId, string $documentType, int $templateId): void
    {
        DB::table('bizdocs_user_template_preferences')->updateOrInsert(
            [
                'business_id' => $businessId,
                'document_type' => $documentType,
            ],
            [
                'template_id' => $templateId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}