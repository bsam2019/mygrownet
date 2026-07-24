<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\Customer;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerTagRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepo,
        private CustomerTagRepositoryInterface $tagRepo,
    ) {}

    public function getCustomers(int $businessId, array $filters = []): array
    {
        return $this->customerRepo->findByBusiness($businessId, $filters);
    }

    public function findCustomer(int $id): ?Customer
    {
        return $this->customerRepo->findById($id);
    }

    public function createCustomer(array $data): Customer
    {
        return $this->customerRepo->save(Customer::create($data));
    }

    public function updateCustomer(int $id, array $data): ?Customer
    {
        $existing = $this->customerRepo->findById($id);
        if (!$existing) {
            return null;
        }
        $merged = array_merge($existing->toArray(), $data);
        $merged['id'] = $id;
        return $this->customerRepo->save(Customer::reconstitute($merged));
    }

    public function deleteCustomer(int $id): void
    {
        $this->customerRepo->delete($id);
    }

    public function countByBusiness(int $businessId): int
    {
        return $this->customerRepo->countByBusiness($businessId);
    }

    public function syncCustomerTags(int $customerId, array $tagIds): void
    {
        DB::table('bizboost_customer_tag_pivot')
            ->where('customer_id', $customerId)
            ->delete();

        foreach ($tagIds as $tagId) {
            DB::table('bizboost_customer_tag_pivot')->insert([
                'customer_id' => $customerId,
                'tag_id' => $tagId,
            ]);
        }
    }
}