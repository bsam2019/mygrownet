<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Customer;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\CustomerRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CustomerId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use Throwable;

class CustomerService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
    ) {}

    public function createCustomer(int $companyId, array $data): Customer
    {
        try {
            $customer = Customer::create(
                companyId: CompanyId::fromInt($companyId),
                name: $data['name'],
                phone: $data['phone'] ?? null,
                email: $data['email'] ?? null,
                address: $data['address'] ?? null,
                city: $data['city'] ?? null,
                country: $data['country'] ?? null,
                creditLimit: isset($data['credit_limit']) ? Money::fromFloat((float) $data['credit_limit']) : null,
                paymentTerms: $data['payment_terms'] ?? null,
                notes: $data['notes'] ?? null,
            );
            return $this->customerRepository->save($customer);
        } catch (Throwable $e) {
            throw new OperationFailedException('create customer', $e->getMessage());
        }
    }

    public function updateCustomer(int $customerId, int $companyId, array $data): Customer
    {
        try {
            $customer = $this->customerRepository->findById(CustomerId::fromInt($customerId));
            if (!$customer || $customer->getCompanyId()->toInt() !== $companyId) {
                throw new OperationFailedException('update customer', 'Customer not found');
            }
            $customer->updateDetails(
                name: $data['name'] ?? $customer->getName(),
                phone: $data['phone'] ?? $customer->getPhone(),
                email: $data['email'] ?? $customer->getEmail(),
                address: $data['address'] ?? $customer->getAddress(),
                city: $data['city'] ?? $customer->getCity(),
                country: $data['country'] ?? $customer->getCountry(),
                creditLimit: isset($data['credit_limit']) ? Money::fromFloat((float) $data['credit_limit']) : $customer->getCreditLimit(),
                paymentTerms: $data['payment_terms'] ?? $customer->getPaymentTerms(),
                notes: $data['notes'] ?? $customer->getNotes(),
            );
            return $this->customerRepository->save($customer);
        } catch (Throwable $e) {
            throw new OperationFailedException('update customer', $e->getMessage());
        }
    }

    public function deleteCustomer(int $customerId, int $companyId): void
    {
        $customer = $this->customerRepository->findById(CustomerId::fromInt($customerId));
        if (!$customer || $customer->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('delete customer', 'Customer not found');
        }
        $this->customerRepository->delete(CustomerId::fromInt($customerId));
    }

    public function getCustomers(int $companyId): array
    {
        return $this->customerRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getCustomerById(int $customerId, int $companyId): ?Customer
    {
        $customer = $this->customerRepository->findById(CustomerId::fromInt($customerId));
        return ($customer && $customer->getCompanyId()->toInt() === $companyId) ? $customer : null;
    }
}
