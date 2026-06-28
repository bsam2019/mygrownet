<?php

namespace App\Domain\Module\Repositories;

use App\Domain\Module\Entities\Module;

interface ModuleRepositoryInterface
{
    /**
     * Find module by ID
     * @param string $id Module ID
     * @return Module|null
     */
    public function findById(string $id): ?Module;
    
    /**
     * Find module by slug
     * @param string $slug Module slug
     * @return Module|null
     */
    public function findBySlug(string $slug): ?Module;
    
    /**
     * Find modules by category
     * @param string $category Module category
     * @return array<Module>
     */
    public function findByCategory(string $category): array;
    
    /**
     * Find modules by account type
     * @param string $accountType Account type
     * @return array<Module>
     */
    public function findByAccountType(string $accountType): array;
    
    /**
     * Find modules available for a specific account type
     * @param int $userId User ID
     * @param string $accountType Account type
     * @return array<Module>
     */
    public function findAvailableForUser(int $userId, string $accountType): array;
    
    /**
     * Find all active modules
     * @return array<Module>
     */
    public function findAllActive(): array;
    
    /**
     * Find all modules (including inactive)
     * @return array<Module>
     */
    public function findAll(): array;
    
    /**
     * Save module
     * @param Module $module
     * @return void
     */
    public function save(Module $module): void;
    
    /**
     * Delete module
     * @param string $id Module ID
     * @return void
     */
    public function delete(string $id): void;
}
