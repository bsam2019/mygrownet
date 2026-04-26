<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialCategoryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel;

class DefaultMaterialsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding default materials for companies...');

        // Get companies with aluminium_fabrication or construction industry
        $companies = CompanyModel::whereIn('industry_type', ['aluminium_fabrication', 'construction'])->get();

        // If no specific companies found, seed for all companies
        if ($companies->isEmpty()) {
            $this->command->warn('No aluminium or construction companies found. Seeding for all companies...');
            $companies = CompanyModel::all();
        }

        if ($companies->isEmpty()) {
            $this->command->error('No companies found in database.');
            return;
        }

        foreach ($companies as $company) {
            $this->seedMaterialsForCompany($company);
        }

        $this->command->info('✓ Default materials seeded successfully');
    }

    private function seedMaterialsForCompany(CompanyModel $company): void
    {
        $this->command->info("  Seeding materials for: {$company->name}");

        // Create Categories
        $categories = $this->createCategories($company->id);

        // Seed materials based on industry, default to aluminium if not specified
        if ($company->industry_type === 'construction') {
            $this->seedConstructionMaterials($company->id, $categories);
        } else {
            // Default to aluminium materials for all other industries
            $this->seedAluminiumMaterials($company->id, $categories);
        }
    }

    private function createCategories(int $companyId): array
    {
        $categoryData = [
            ['name' => 'Aluminium Profiles', 'code' => 'ALU_PROF', 'icon' => 'rectangle-stack', 'color' => 'gray', 'sort_order' => 1],
            ['name' => 'Glass & Glazing', 'code' => 'GLASS', 'icon' => 'square-2-stack', 'color' => 'blue', 'sort_order' => 2],
            ['name' => 'Hardware & Fittings', 'code' => 'HARDWARE', 'icon' => 'wrench-screwdriver', 'color' => 'amber', 'sort_order' => 3],
            ['name' => 'Sealants & Adhesives', 'code' => 'SEALANT', 'icon' => 'beaker', 'color' => 'green', 'sort_order' => 4],
            ['name' => 'Building Materials', 'code' => 'BUILD_MAT', 'icon' => 'cube', 'color' => 'orange', 'sort_order' => 5],
            ['name' => 'Cement & Concrete', 'code' => 'CEMENT', 'icon' => 'cube-transparent', 'color' => 'stone', 'sort_order' => 6],
            ['name' => 'Steel & Metal', 'code' => 'STEEL', 'icon' => 'bars-3-bottom-left', 'color' => 'slate', 'sort_order' => 7],
            ['name' => 'Timber & Wood', 'code' => 'TIMBER', 'icon' => 'squares-2x2', 'color' => 'yellow', 'sort_order' => 8],
            ['name' => 'Electrical', 'code' => 'ELECTRIC', 'icon' => 'bolt', 'color' => 'yellow', 'sort_order' => 9],
            ['name' => 'Plumbing', 'code' => 'PLUMB', 'icon' => 'wrench', 'color' => 'blue', 'sort_order' => 10],
        ];

        $categories = [];
        foreach ($categoryData as $data) {
            $category = MaterialCategoryModel::firstOrCreate(
                ['company_id' => $companyId, 'code' => $data['code']],
                $data + ['company_id' => $companyId]
            );
            $categories[$data['code']] = $category;
        }

        return $categories;
    }

    private function seedAluminiumMaterials(int $companyId, array $categories): void
    {
        $materials = [
            // Aluminium Profiles
            ['category' => 'ALU_PROF', 'code' => 'ALU-FRAME-001', 'name' => 'Aluminium Frame Profile 50x50mm', 'unit' => 'meters', 'price' => 45.00],
            ['category' => 'ALU_PROF', 'code' => 'ALU-FRAME-002', 'name' => 'Aluminium Frame Profile 60x60mm', 'unit' => 'meters', 'price' => 55.00],
            ['category' => 'ALU_PROF', 'code' => 'ALU-SASH-001', 'name' => 'Aluminium Sash Profile', 'unit' => 'meters', 'price' => 38.00],
            ['category' => 'ALU_PROF', 'code' => 'ALU-MULLION-001', 'name' => 'Aluminium Mullion Profile', 'unit' => 'meters', 'price' => 42.00],
            ['category' => 'ALU_PROF', 'code' => 'ALU-TRACK-001', 'name' => 'Sliding Window Track', 'unit' => 'meters', 'price' => 35.00],

            // Glass
            ['category' => 'GLASS', 'code' => 'GLASS-CLEAR-4MM', 'name' => 'Clear Glass 4mm', 'unit' => 'm²', 'price' => 120.00],
            ['category' => 'GLASS', 'code' => 'GLASS-CLEAR-6MM', 'name' => 'Clear Glass 6mm', 'unit' => 'm²', 'price' => 180.00],
            ['category' => 'GLASS', 'code' => 'GLASS-TINTED-6MM', 'name' => 'Tinted Glass 6mm', 'unit' => 'm²', 'price' => 220.00],
            ['category' => 'GLASS', 'code' => 'GLASS-FROSTED-6MM', 'name' => 'Frosted Glass 6mm', 'unit' => 'm²', 'price' => 250.00],

            // Hardware
            ['category' => 'HARDWARE', 'code' => 'HANDLE-SLIDE-001', 'name' => 'Sliding Window Handle', 'unit' => 'pcs', 'price' => 25.00],
            ['category' => 'HARDWARE', 'code' => 'HANDLE-CASE-001', 'name' => 'Casement Window Handle', 'unit' => 'pcs', 'price' => 35.00],
            ['category' => 'HARDWARE', 'code' => 'HINGE-HEAVY-001', 'name' => 'Heavy Duty Hinge', 'unit' => 'pcs', 'price' => 18.00],
            ['category' => 'HARDWARE', 'code' => 'LOCK-SLIDE-001', 'name' => 'Sliding Window Lock', 'unit' => 'pcs', 'price' => 22.00],
            ['category' => 'HARDWARE', 'code' => 'ROLLER-SLIDE-001', 'name' => 'Sliding Window Roller', 'unit' => 'pcs', 'price' => 15.00],
            ['category' => 'HARDWARE', 'code' => 'SCREW-SS-001', 'name' => 'Stainless Steel Screws (Box)', 'unit' => 'box', 'price' => 45.00],

            // Sealants
            ['category' => 'SEALANT', 'code' => 'SEAL-SIL-001', 'name' => 'Silicone Sealant Clear', 'unit' => 'tube', 'price' => 28.00],
            ['category' => 'SEALANT', 'code' => 'SEAL-SIL-002', 'name' => 'Silicone Sealant Black', 'unit' => 'tube', 'price' => 28.00],
            ['category' => 'SEALANT', 'code' => 'SEAL-WEATHER-001', 'name' => 'Weather Strip Seal', 'unit' => 'meters', 'price' => 12.00],
            ['category' => 'SEALANT', 'code' => 'SEAL-FOAM-001', 'name' => 'Foam Tape Seal', 'unit' => 'roll', 'price' => 35.00],
        ];

        $this->createMaterials($companyId, $categories, $materials);
    }

    private function seedConstructionMaterials(int $companyId, array $categories): void
    {
        $materials = [
            // Cement & Concrete
            ['category' => 'CEMENT', 'code' => 'CEM-OPC-50KG', 'name' => 'Ordinary Portland Cement 50kg', 'unit' => 'bag', 'price' => 120.00],
            ['category' => 'CEMENT', 'code' => 'CEM-RAPID-50KG', 'name' => 'Rapid Hardening Cement 50kg', 'unit' => 'bag', 'price' => 145.00],
            ['category' => 'CEMENT', 'code' => 'SAND-RIVER', 'name' => 'River Sand', 'unit' => 'm³', 'price' => 180.00],
            ['category' => 'CEMENT', 'code' => 'SAND-PLASTER', 'name' => 'Plaster Sand', 'unit' => 'm³', 'price' => 150.00],
            ['category' => 'CEMENT', 'code' => 'AGGREGATE-STONE', 'name' => 'Stone Aggregate', 'unit' => 'm³', 'price' => 200.00],

            // Building Materials
            ['category' => 'BUILD_MAT', 'code' => 'BRICK-CLAY-001', 'name' => 'Clay Bricks', 'unit' => 'pcs', 'price' => 1.50],
            ['category' => 'BUILD_MAT', 'code' => 'BLOCK-CONCRETE-6', 'name' => 'Concrete Block 6 inch', 'unit' => 'pcs', 'price' => 8.00],
            ['category' => 'BUILD_MAT', 'code' => 'BLOCK-CONCRETE-9', 'name' => 'Concrete Block 9 inch', 'unit' => 'pcs', 'price' => 12.00],
            ['category' => 'BUILD_MAT', 'code' => 'ROOF-SHEET-IBR', 'name' => 'IBR Roofing Sheet 3m', 'unit' => 'sheet', 'price' => 85.00],
            ['category' => 'BUILD_MAT', 'code' => 'ROOF-TILE-CLAY', 'name' => 'Clay Roof Tiles', 'unit' => 'pcs', 'price' => 4.50],

            // Steel & Metal
            ['category' => 'STEEL', 'code' => 'REBAR-Y10', 'name' => 'Steel Reinforcement Bar Y10', 'unit' => 'meters', 'price' => 12.00],
            ['category' => 'STEEL', 'code' => 'REBAR-Y12', 'name' => 'Steel Reinforcement Bar Y12', 'unit' => 'meters', 'price' => 16.00],
            ['category' => 'STEEL', 'code' => 'REBAR-Y16', 'name' => 'Steel Reinforcement Bar Y16', 'unit' => 'meters', 'price' => 28.00],
            ['category' => 'STEEL', 'code' => 'WIRE-MESH-BRC', 'name' => 'BRC Wire Mesh', 'unit' => 'm²', 'price' => 45.00],
            ['category' => 'STEEL', 'code' => 'ANGLE-IRON-50X50', 'name' => 'Angle Iron 50x50mm', 'unit' => 'meters', 'price' => 35.00],

            // Timber
            ['category' => 'TIMBER', 'code' => 'TIMBER-2X4', 'name' => 'Timber 2x4 inch', 'unit' => 'meters', 'price' => 18.00],
            ['category' => 'TIMBER', 'code' => 'TIMBER-2X6', 'name' => 'Timber 2x6 inch', 'unit' => 'meters', 'price' => 28.00],
            ['category' => 'TIMBER', 'code' => 'PLYWOOD-12MM', 'name' => 'Plywood 12mm', 'unit' => 'sheet', 'price' => 180.00],
            ['category' => 'TIMBER', 'code' => 'PLYWOOD-18MM', 'name' => 'Plywood 18mm', 'unit' => 'sheet', 'price' => 250.00],

            // Electrical
            ['category' => 'ELECTRIC', 'code' => 'CABLE-2.5MM', 'name' => 'Electrical Cable 2.5mm²', 'unit' => 'meters', 'price' => 8.50],
            ['category' => 'ELECTRIC', 'code' => 'CABLE-4MM', 'name' => 'Electrical Cable 4mm²', 'unit' => 'meters', 'price' => 12.00],
            ['category' => 'ELECTRIC', 'code' => 'CONDUIT-20MM', 'name' => 'PVC Conduit 20mm', 'unit' => 'meters', 'price' => 5.00],
            ['category' => 'ELECTRIC', 'code' => 'SWITCH-SINGLE', 'name' => 'Single Gang Switch', 'unit' => 'pcs', 'price' => 15.00],
            ['category' => 'ELECTRIC', 'code' => 'SOCKET-DOUBLE', 'name' => 'Double Socket Outlet', 'unit' => 'pcs', 'price' => 25.00],

            // Plumbing
            ['category' => 'PLUMB', 'code' => 'PIPE-PVC-110MM', 'name' => 'PVC Pipe 110mm', 'unit' => 'meters', 'price' => 35.00],
            ['category' => 'PLUMB', 'code' => 'PIPE-PVC-50MM', 'name' => 'PVC Pipe 50mm', 'unit' => 'meters', 'price' => 18.00],
            ['category' => 'PLUMB', 'code' => 'PIPE-COPPER-15MM', 'name' => 'Copper Pipe 15mm', 'unit' => 'meters', 'price' => 45.00],
            ['category' => 'PLUMB', 'code' => 'ELBOW-PVC-110MM', 'name' => 'PVC Elbow 110mm', 'unit' => 'pcs', 'price' => 12.00],
            ['category' => 'PLUMB', 'code' => 'TEE-PVC-110MM', 'name' => 'PVC Tee 110mm', 'unit' => 'pcs', 'price' => 15.00],
        ];

        $this->createMaterials($companyId, $categories, $materials);
    }

    private function createMaterials(int $companyId, array $categories, array $materials): void
    {
        foreach ($materials as $material) {
            $category = $categories[$material['category']] ?? null;

            // Make code unique per company by adding company ID prefix if needed
            $code = $material['code'];
            $existingMaterial = MaterialModel::where('code', $code)->first();
            
            if ($existingMaterial && $existingMaterial->company_id !== $companyId) {
                // Code exists for another company, make it unique
                $code = "C{$companyId}-{$material['code']}";
            }

            MaterialModel::firstOrCreate(
                ['company_id' => $companyId, 'code' => $code],
                [
                    'company_id' => $companyId,
                    'category_id' => $category?->id,
                    'code' => $code,
                    'name' => $material['name'],
                    'unit' => $material['unit'],
                    'current_price' => $material['price'],
                    'is_active' => true,
                ]
            );
        }
    }
}
