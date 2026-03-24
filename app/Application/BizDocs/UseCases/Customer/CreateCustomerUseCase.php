<?php

namespace App\Application\BizDocs\UseCases\Customer;

use App\Application\BizDocs\DTOs\CreateCustomerDTO;
use App\Domain\BizDocs\CustomerManagement\Entities\Customer;
use App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface;

class CreateCustomerUseCase
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    public function execute(CreateCustomerDTO $dto): Customer
    {
        // Check for duplicates by phone or email
        if ($dto->phone) {
            $existing = $this->customerRepository->findByPhone($dto->businessId, $dto->phone);
            if ($existing) {
                throw new \DomainException('Customer with this phone number already exists');
            }
        }

        if ($dto->email) {
            $existing = $this->customerRepository->findByEmail($dto->businessId, $dto->email);
            if ($existing) {
                throw new \DomainException('Customer with this email already exists');
            }
        }

        $customer = Customer::create(
            businessId: $dto->businessId,
            name: $dto->name,
            address: $dto->address,
            phone: $dto->phone,
            email: $dto->email,
            tpin: $dto->tpin,
            notes: $dto->notes
        );

        return $this->customerRepository->save($customer);
    }
}
