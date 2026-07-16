<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Category;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\CategoryRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CategoryId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Throwable;

class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository) {}

    public function createCategory(int $companyId, array $data): Category
    {
        try {
            $category = Category::create(
                companyId: CompanyId::fromInt($companyId), name: $data['name'],
                slug: \Str::slug($data['name']), description: $data['description'] ?? null,
                parentId: isset($data['parent_id']) ? CategoryId::fromInt((int) $data['parent_id']) : null,
                sortOrder: (int) ($data['sort_order'] ?? 0),
            );
            return $this->categoryRepository->save($category);
        } catch (Throwable $e) {
            throw new OperationFailedException('create category', $e->getMessage());
        }
    }

    public function updateCategory(int $id, int $companyId, array $data): Category
    {
        $category = $this->categoryRepository->findById(CategoryId::fromInt($id));
        if (!$category || $category->getCompanyId()->toInt() !== $companyId) throw new OperationFailedException('update category', 'Not found');
        $category->update(
            name: $data['name'] ?? $category->getName(), slug: \Str::slug($data['name'] ?? $category->getName()),
            description: $data['description'] ?? $category->getDescription(),
            parentId: array_key_exists('parent_id', $data) ? ($data['parent_id'] ? CategoryId::fromInt((int) $data['parent_id']) : null) : $category->getParentId(),
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
        return $this->categoryRepository->save($category);
    }

    public function deleteCategory(int $id, int $companyId): void
    {
        $category = $this->categoryRepository->findById(CategoryId::fromInt($id));
        if (!$category || $category->getCompanyId()->toInt() !== $companyId) throw new OperationFailedException('delete category', 'Not found');
        $this->categoryRepository->delete(CategoryId::fromInt($id));
    }

    public function getCategories(int $companyId): array { return $this->categoryRepository->findByCompanyId(CompanyId::fromInt($companyId)); }
    public function getRootCategories(int $companyId): array { return $this->categoryRepository->findRoots(CompanyId::fromInt($companyId)); }
    public function getTree(int $companyId): array
    {
        $categories = $this->getCategories($companyId);
        return $this->buildTree($categories);
    }

    private function buildTree(array $categories, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($categories as $cat) {
            if ($cat->getParentId()?->toInt() === $parentId || ($parentId === null && $cat->getParentId() === null)) {
                $children = $this->buildTree($categories, $cat->id());
                $node = $cat->toArray();
                $node['children'] = $children;
                $branch[] = $node;
            }
        }
        return $branch;
    }
}
