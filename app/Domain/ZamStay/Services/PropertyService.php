<?php

namespace App\Domain\ZamStay\Services;

use App\Domain\ZamStay\Entities\Property;
use App\Domain\ZamStay\Exceptions\ZamStayException;
use App\Domain\ZamStay\Repositories\PropertyRepositoryInterface;

class PropertyService
{
    public function __construct(
        private readonly PropertyRepositoryInterface $propertyRepo,
    ) {}

    public function findById(int $id): ?Property
    {
        return $this->propertyRepo->findById($id);
    }

    public function findOrFail(int $id): Property
    {
        $property = $this->findById($id);
        if (!$property) {
            throw ZamStayException::notFound('Property');
        }
        return $property;
    }

    public function search(array $filters): array
    {
        return $this->propertyRepo->findAllActive($filters);
    }

    public function create(int $ownerId, array $data): Property
    {
        $data['owner_id'] = $ownerId;
        $entity = Property::reconstitute($data);
        return $this->propertyRepo->save($entity);
    }

    public function update(Property $property, array $data): Property
    {
        $merged = array_merge($property->toArray(), $data);
        $entity = Property::reconstitute($merged);
        return $this->propertyRepo->save($entity);
    }

    public function findByOwner(int $ownerId): array
    {
        return $this->propertyRepo->findByOwner($ownerId);
    }

    public function getHomeData(): array
    {
        return [
            'featured' => $this->propertyRepo->findFeatured(6),
            'latest' => $this->propertyRepo->findLatest(8),
            'locations' => $this->propertyRepo->findDistinctLocations(),
        ];
    }
}
