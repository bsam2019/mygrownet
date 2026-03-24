<?php

namespace App\Infrastructure\BizDocs\Persistence\Repositories;

use App\Domain\BizDocs\BusinessIdentity\Entities\BusinessProfile;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Infrastructure\BizDocs\Persistence\Eloquent\BusinessProfileModel;

class EloquentBusinessProfileRepository implements BusinessProfileRepositoryInterface
{
    public function save(BusinessProfile $profile): BusinessProfile
    {
        $data = [
            'user_id' => $profile->userId(),
            'business_name' => $profile->businessName(),
            'address' => $profile->address(),
            'phone' => $profile->phone(),
            'email' => $profile->email(),
            'logo' => $profile->logo(),
            'tpin' => $profile->tpin(),
            'website' => $profile->website(),
            'bank_name' => $profile->bankName(),
            'bank_account' => $profile->bankAccount(),
            'bank_branch' => $profile->bankBranch(),
            'default_currency' => $profile->defaultCurrency(),
            'default_tax_rate' => $profile->defaultTaxRate(),
            'default_terms' => $profile->defaultTerms(),
            'default_notes' => $profile->defaultNotes(),
            'default_payment_instructions' => $profile->defaultPaymentInstructions(),
            'signature_image' => $profile->signatureImage(),
            'stamp_image' => $profile->stampImage(),
            'prepared_by' => $profile->preparedBy(),
        ];

        if ($profile->id()) {
            $model = BusinessProfileModel::findOrFail($profile->id());
            $model->update($data);
        } else {
            $model = BusinessProfileModel::create($data);
        }

        return $this->findById($model->id);
    }

    public function findById(int $id): ?BusinessProfile
    {
        $model = BusinessProfileModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findByUserId(int $userId): ?BusinessProfile
    {
        $model = BusinessProfileModel::where('user_id', $userId)->first();

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function delete(int $id): bool
    {
        return BusinessProfileModel::where('id', $id)->delete() > 0;
    }

    private function toDomainEntity(BusinessProfileModel $model): BusinessProfile
    {
        return BusinessProfile::fromPersistence(
            $model->id,
            $model->user_id,
            $model->business_name,
            $model->address,
            $model->phone,
            $model->email,
            $model->logo,
            $model->tpin,
            $model->website,
            $model->bank_name,
            $model->bank_account,
            $model->bank_branch,
            $model->default_currency,
            $model->default_tax_rate ?? 16.00,
            $model->default_terms,
            $model->default_notes,
            $model->default_payment_instructions,
            $model->signature_image,
            $model->stamp_image,
            $model->prepared_by
        );
    }
}
