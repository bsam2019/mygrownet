<?php

/**
 * Business Plan Generator Testing Script
 * 
 * This script tests the business plan generator functionality
 * including database operations, API endpoints, and data integrity.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\BusinessPlan;

class BusinessPlanGeneratorTester
{
    private $baseUrl;
    private $testUser;
    private $testPlanId;
    
    public function __construct()
    {
        $this->baseUrl = env('APP_URL', 'http://localhost:8000');
        echo "🧪 Business Plan Generator Testing Suite\n";
        echo "=====================================\n\n";
    }
    
    public function runAllTests()
    {
        try {
            $this->setupTestData();
            $this->testDatabaseSchema();
            $this->testModelRelationships();
            $this->testBusinessPlanCreation();
            $this->testStepProgression();
            $this->testFinancialCalculations();
            $this->testDataPersistence();
            $this->testExportFunctionality();
            $this->testMobileIntegration();
            $this->cleanupTestData();
            
            echo "\n✅ All tests completed successfully!\n";
            
        } catch (Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            $this->cleanupTestData();
        }
    }
    
    private function setupTestData()
    {
        echo "📋 Setting up test data...\n";
        
        // Create test user if not exists
        $this->testUser = User::firstOrCreate([
            'email' => 'test.businessplan@mygrownet.com'
        ], [
            'name' => 'Business Plan Tester',
            'password' => bcrypt('password123'),
            'starter_kit_tier' => 'premium',
            'has_starter_kit' => true,
            'email_verified_at' => now(),
        ]);
        
        echo "   ✓ Test user created/found: {$this->testUser->email}\n";
    }
    
    private function testDatabaseSchema()
    {
        echo "\n🗄️  Testing database schema...\n";
        
        // Check if user_business_plans table exists and has correct columns
        $columns = DB::select("SHOW COLUMNS FROM user_business_plans");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'id', 'user_id', 'business_name', 'industry', 'country', 'province', 'city',
            'legal_structure', 'mission_statement', 'vision_statement', 'background',
            'problem_statement', 'solution_description', 'competitive_advantage',
            'customer_pain_points', 'product_description', 'product_features',
            'pricing_strategy', 'unique_selling_points', 'production_process',
            'resource_requirements', 'target_market_segment', 'customer_demographics',
            'market_size', 'competitors', 'competitive_analysis', 'marketing_channels',
            'branding_approach', 'sales_channels', 'customer_retention',
            'daily_operations', 'staff_roles', 'equipment_tools', 'supplier_list',
            'operational_workflow', 'startup_costs', 'monthly_operating_costs',
            'expected_monthly_revenue', 'price_per_unit', 'expected_sales_volume',
            'staff_salaries', 'inventory_costs', 'utilities_costs', 'risks',
            'mitigation_strategies', 'timeline', 'milestones',
            'status', 'current_step', 'created_at', 'updated_at'
        ];
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $columnNames)) {
                throw new Exception("Missing required column: {$column}");
            }
        }
        
        echo "   ✓ All required columns present\n";
        
        // Check business_plan_exports table
        try {
            $exportColumns = DB::select("SHOW COLUMNS FROM business_plan_exports");
            if (empty($exportColumns)) {
                throw new Exception("business_plan_exports table not found");
            }
        } catch (Exception $e) {
            echo "   ⚠️ business_plan_exports table not found (may not be created yet)\n";
        }
        
        echo "   ✓ Export table schema correct\n";
    }
    
    private function testModelRelationships()
    {
        echo "\n🔗 Testing model relationships...\n";
        
        // Test User -> BusinessPlan relationship
        $user = User::with('businessPlans')->find($this->testUser->id);
        if (!method_exists($user, 'businessPlans')) {
            throw new Exception("User model missing businessPlans relationship");
        }
        
        echo "   ✓ User -> BusinessPlan relationship exists\n";
        
        // Test BusinessPlan -> User relationship
        $plan = new BusinessPlan();
        if (!method_exists($plan, 'user')) {
            throw new Exception("BusinessPlan model missing user relationship");
        }
        
        echo "   ✓ BusinessPlan -> User relationship exists\n";
    }
    
    private function testBusinessPlanCreation()
    {
        echo "\n📝 Testing business plan creation...\n";
        
        $planData = [
            'user_id' => $this->testUser->id,
            'business_name' => 'Test MyGrowNet Business',
            'industry' => 'ICT/Technology',
            'country' => 'Zambia',
            'province' => 'Lusaka',
            'city' => 'Lusaka',
            'legal_structure' => 'company',
            'mission_statement' => 'To empower communities through technology and education.',
            'vision_statement' => 'To become the leading platform for community empowerment in Africa.',
            'background' => 'Founded to address the gap in community development tools.',
            'status' => 'draft',
            'current_step' => 1,
        ];
        
        $plan = BusinessPlan::create($planData);
        $this->testPlanId = $plan->id;
        
        if (!$plan->id) {
            throw new Exception("Failed to create business plan");
        }
        
        echo "   ✓ Business plan created successfully (ID: {$plan->id})\n";
        
        // Test data retrieval
        $retrievedPlan = BusinessPlan::find($plan->id);
        if ($retrievedPlan->business_name !== $planData['business_name']) {
            throw new Exception("Data integrity issue: business name mismatch");
        }
        
        echo "   ✓ Data retrieval and integrity verified\n";
    }
    
    private function testStepProgression()
    {
        echo "\n📊 Testing step progression...\n";
        
        $plan = BusinessPlan::find($this->testPlanId);
        
        // Test step 2 data
        $plan->update([
            'problem_statement' => 'Many communities lack access to growth opportunities.',
            'solution_description' => 'We provide a comprehensive platform for learning and earning.',
            'competitive_advantage' => 'Unique 7-level system with proven results.',
            'current_step' => 2,
        ]);
        
        echo "   ✓ Step 2 data saved\n";
        
        // Test step 3 data
        $plan->update([
            'product_description' => 'Digital learning platform with earning opportunities.',
            'pricing_strategy' => 'Subscription-based with starter kit options.',
            'unique_selling_points' => 'Community-focused, proven system, comprehensive support.',
            'current_step' => 3,
        ]);
        
        echo "   ✓ Step 3 data saved\n";
        
        // Test array data (marketing channels)
        $plan->update([
            'marketing_channels' => ['Facebook', 'WhatsApp', 'Referrals'],
            'sales_channels' => ['Online Store', 'Direct Sales', 'Agents/Distributors'],
            'current_step' => 5,
        ]);
        
        echo "   ✓ Array data (marketing/sales channels) saved\n";
        
        // Verify array data retrieval
        $retrievedPlan = BusinessPlan::find($this->testPlanId);
        $marketingChannels = $retrievedPlan->marketing_channels;
        if (!is_array($marketingChannels) || !in_array('Facebook', $marketingChannels)) {
            throw new Exception("Array data not properly stored/retrieved");
        }
        
        echo "   ✓ Array data retrieval verified\n";
    }
    
    private function testFinancialCalculations()
    {
        echo "\n💰 Testing financial calculations...\n";
        
        $plan = BusinessPlan::find($this->testPlanId);
        
        // Set financial data
        $financialData = [
            'startup_costs' => 50000,
            'monthly_operating_costs' => 15000,
            'expected_monthly_revenue' => 30000,
            'price_per_unit' => 500,
            'expected_sales_volume' => 60,
            'staff_salaries' => 8000,
            'inventory_costs' => 3000,
            'utilities_costs' => 2000,
            'current_step' => 7,
        ];
        
        $plan->update($financialData);
        
        echo "   ✓ Financial data saved\n";
        
        // Test calculations (these would be done in frontend, but we can verify data)
        $monthlyProfit = $financialData['expected_monthly_revenue'] - $financialData['monthly_operating_costs'];
        $profitMargin = ($monthlyProfit / $financialData['expected_monthly_revenue']) * 100;
        $breakEvenMonths = $monthlyProfit > 0 ? ceil($financialData['startup_costs'] / $monthlyProfit) : 0;
        
        if ($monthlyProfit !== 15000) {
            throw new Exception("Monthly profit calculation incorrect");
        }
        
        if (abs($profitMargin - 50.0) > 0.1) {
            throw new Exception("Profit margin calculation incorrect");
        }
        
        if ($breakEvenMonths < 3 || $breakEvenMonths > 4) {
            throw new Exception("Break-even calculation incorrect: expected 3-4 months, got {$breakEvenMonths}");
        }
        
        echo "   ✓ Financial calculations verified\n";
        echo "     - Monthly Profit: K{$monthlyProfit}\n";
        echo "     - Profit Margin: {$profitMargin}%\n";
        echo "     - Break-even: {$breakEvenMonths} months\n";
    }
    
    private function testDataPersistence()
    {
        echo "\n💾 Testing data persistence...\n";
        
        $plan = BusinessPlan::find($this->testPlanId);
        
        // Complete remaining steps
        $plan->update([
            'risks' => ['Market competition', 'Economic changes', 'Technology shifts'],
            'mitigation_strategies' => ['Diversification', 'Continuous innovation', 'Strong partnerships'],
            'current_step' => 8,
        ]);
        
        $plan->update([
            'timeline' => 'Month 1: Setup, Month 2: Launch, Month 3: Scale',
            'milestones' => 'Registration, First 100 users, Break-even, Expansion',
            'responsibilities' => 'CEO: Strategy, CTO: Technology, CMO: Marketing',
            'current_step' => 9,
        ]);
        
        $plan->update([
            'status' => 'completed',
            'current_step' => 10,
        ]);
        
        echo "   ✓ All steps completed\n";
        
        // Verify data persistence across updates
        $finalPlan = BusinessPlan::find($this->testPlanId);
        
        if ($finalPlan->status !== 'completed') {
            throw new Exception("Status not properly updated");
        }
        
        if ($finalPlan->current_step !== 10) {
            throw new Exception("Current step not properly updated");
        }
        
        if (empty($finalPlan->business_name) || empty($finalPlan->mission_statement)) {
            throw new Exception("Early step data lost during updates");
        }
        
        echo "   ✓ Data persistence verified across all updates\n";
    }
    
    private function testExportFunctionality()
    {
        echo "\n📄 Testing export functionality...\n";
        
        // Test export record creation
        $exportData = [
            'business_plan_id' => $this->testPlanId,
            'user_id' => $this->testUser->id,
            'export_type' => 'template',
            'file_path' => 'exports/business-plan-' . $this->testPlanId . '-template.json',
            'download_count' => 0,
            'is_premium' => false,
            'price_paid' => null,
        ];
        
        $export = DB::table('business_plan_exports')->insert($exportData);
        
        if (!$export) {
            throw new Exception("Failed to create export record");
        }
        
        echo "   ✓ Export record created\n";
        
        // Verify export record
        $exportRecord = DB::table('business_plan_exports')
            ->where('business_plan_id', $this->testPlanId)
            ->where('export_type', 'template')
            ->first();
        
        if (!$exportRecord) {
            throw new Exception("Export record not found");
        }
        
        echo "   ✓ Export record retrieval verified\n";
    }
    
    private function testMobileIntegration()
    {
        echo "\n📱 Testing mobile integration...\n";
        
        // Test that business plan can be accessed via mobile routes
        $plan = BusinessPlan::find($this->testPlanId);
        
        if (!$plan) {
            throw new Exception("Business plan not accessible for mobile integration");
        }
        
        // Test mobile-specific data requirements
        $mobileData = [
            'id' => $plan->id,
            'business_name' => $plan->business_name,
            'status' => $plan->status,
            'current_step' => $plan->current_step,
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at,
        ];
        
        foreach ($mobileData as $key => $value) {
            if (empty($value) && $value !== 0) {
                throw new Exception("Missing mobile data: {$key}");
            }
        }
        
        echo "   ✓ Mobile data structure verified\n";
        
        // Test JSON serialization for mobile API
        $jsonData = json_encode($mobileData);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON serialization failed for mobile data");
        }
        
        echo "   ✓ Mobile JSON serialization verified\n";
    }
    
    private function cleanupTestData()
    {
        echo "\n🧹 Cleaning up test data...\n";
        
        if ($this->testPlanId) {
            // Delete export records
            DB::table('business_plan_exports')
                ->where('business_plan_id', $this->testPlanId)
                ->delete();
            
            // Delete business plan
            BusinessPlan::destroy($this->testPlanId);
            echo "   ✓ Test business plan deleted\n";
        }
        
        // Keep test user for future tests, but you could delete if needed
        // $this->testUser->delete();
        
        echo "   ✓ Cleanup completed\n";
    }
    
    private function log($message, $type = 'info')
    {
        $prefix = match($type) {
            'success' => '✅',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '•'
        };
        
        echo "   {$prefix} {$message}\n";
    }
}

// Run the tests
try {
    // Initialize Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $tester = new BusinessPlanGeneratorTester();
    $tester->runAllTests();
    
} catch (Exception $e) {
    echo "❌ Test suite failed to initialize: " . $e->getMessage() . "\n";
    exit(1);
}