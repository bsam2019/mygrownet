<?php

namespace App\Domain\CMS\Core\Services;

use App\Domain\CMS\Core\ValueObjects\CustomerNumber;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    public function createCustomer(array $data): CustomerModel
    {
        return DB::transaction(function () use ($data) {
            // Generate customer number
            $lastCustomer = CustomerModel::where('company_id', $data['company_id'])
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastCustomer ? (int) substr($lastCustomer->customer_number, -4) + 1 : 1;
            $customerNumber = CustomerNumber::generate($sequence);

            $customer = CustomerModel::create([
                'company_id' => $data['company_id'],
                'customer_number' => $customerNumber->value(),
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'secondary_phone' => $data['secondary_phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? 'Zambia',
                'tax_number' => $data['tax_number'] ?? null,
                'credit_limit' => $data['credit_limit'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Log audit trail
            $this->auditTrail->log(
                companyId: $customer->company_id,
                userId: $data['created_by'],
                entityType: 'customer',
                entityId: $customer->id,
                action: 'created',
                newValues: $customer->toArray()
            );

            return $customer;
        });
    }

    public function updateCustomer(CustomerModel $customer, array $data, int $updatedBy): CustomerModel
    {
        $oldValues = $customer->toArray();

        $customer->update($data);

        // Log audit trail
        $this->auditTrail->log(
            companyId: $customer->company_id,
            userId: $updatedBy,
            entityType: 'customer',
            entityId: $customer->id,
            action: 'updated',
            oldValues: $oldValues,
            newValues: $customer->fresh()->toArray()
        );

        return $customer->fresh();
    }

    public function updateOutstandingBalance(CustomerModel $customer, float $amount): CustomerModel
    {
        $customer->update([
            'outstanding_balance' => $customer->outstanding_balance + $amount,
        ]);

        return $customer->fresh();
    }

    public function canDeleteCustomer(CustomerModel $customer): bool
    {
        // Cannot delete if has outstanding balance
        if ($customer->hasOutstandingBalance()) {
            return false;
        }

        // Cannot delete if has active jobs
        $activeJobs = $customer->jobs()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        return $activeJobs === 0;
    }
}
