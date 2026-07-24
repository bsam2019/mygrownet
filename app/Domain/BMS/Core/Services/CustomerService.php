<?php

namespace App\Domain\BMS\Core\Services;

use App\Domain\BMS\Core\ValueObjects\CustomerNumber;
use App\Domain\BMS\Entities\Customer;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use App\Domain\BMS\Repositories\JobRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepo,
        private JobRepositoryInterface $jobRepo,
        private AuditTrailService $auditTrail
    ) {}

    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customers = $this->customerRepo->findByCompany($data['company_id']);
            $lastCustomer = !empty($customers) ? end($customers) : null;
            $sequence = $lastCustomer ? (int) substr($lastCustomer->customerNumber, -4) + 1 : 1;
            $customerNumber = CustomerNumber::generate($sequence);
            $data['customer_number'] = $customerNumber->value();

            $customer = Customer::create($data);
            $customer = $this->customerRepo->save($customer);

            $this->auditTrail->log(
                companyId: $customer->companyId,
                userId: $data['created_by'],
                entityType: 'customer',
                entityId: $customer->id,
                action: 'created',
                newValues: $customer->toArray()
            );

            return $customer;
        });
    }

    public function updateCustomer(Customer $customer, array $data, int $updatedBy): Customer
    {
        $oldValues = $customer->toArray();
        $updated = Customer::reconstitute(array_merge($customer->toArray(), $data));
        $updated = $this->customerRepo->save($updated);

        $this->auditTrail->log(
            companyId: $updated->companyId,
            userId: $updatedBy,
            entityType: 'customer',
            entityId: $updated->id,
            action: 'updated',
            oldValues: $oldValues,
            newValues: $updated->toArray()
        );

        return $updated;
    }

    public function updateOutstandingBalance(Customer $customer, float $amount): Customer
    {
        $updated = Customer::reconstitute(array_merge($customer->toArray(), [
            'outstanding_balance' => $customer->outstandingBalance + $amount,
        ]));
        return $this->customerRepo->save($updated);
    }

    public function canDeleteCustomer(Customer $customer): bool
    {
        if ($customer->hasOutstandingBalance()) return false;
        $jobs = $this->jobRepo->findByCustomer($customer->id);
        $activeJobs = array_filter($jobs, fn($j) => in_array($j->status, ['pending', 'in_progress']));
        return count($activeJobs) === 0;
    }
}
