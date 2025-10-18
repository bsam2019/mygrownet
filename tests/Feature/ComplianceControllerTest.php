<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplianceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_access_compliance_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Compliance/Dashboard')
                ->has('compliance_report')
        );
    }

    public function test_unauthenticated_user_cannot_access_compliance_dashboard()
    {
        $response = $this->get('/compliance');

        $response->assertRedirect('/login');
    }

    public function test_user_can_access_compliance_information_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/information');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Compliance/Information')
                ->has('business_structure')
                ->has('legal_disclaimers')
                ->has('regulatory_compliance')
        );
    }

    public function test_get_business_structure_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/business-structure');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'business_structure' => [
                'business_model',
                'focus',
                'revenue_sources' => [
                    'monthly_subscriptions',
                    'educational_content',
                    'community_projects',
                    'mentorship_programs'
                ],
                'compliance_standards' => [
                    'product_focused',
                    'regulatory_compliance',
                    'consumer_protection',
                    'financial_sustainability'
                ]
            ]
        ]);
    }

    public function test_get_legal_disclaimers_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/legal-disclaimers');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'disclaimers' => [
                'earnings_disclaimer' => [
                    'title',
                    'content'
                ],
                'business_disclaimer' => [
                    'title',
                    'content'
                ],
                'risk_disclosure' => [
                    'title',
                    'content'
                ],
                'legal_compliance' => [
                    'title',
                    'content'
                ]
            ]
        ]);
    }

    public function test_get_sustainability_metrics_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/sustainability-metrics');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'metrics' => [
                'commission_cap' => [
                    'percentage',
                    'current_percentage',
                    'compliance_status'
                ],
                'revenue_allocation' => [
                    'commissions',
                    'operations',
                    'product_development',
                    'member_benefits',
                    'compliance_legal',
                    'reserves'
                ],
                'financial_health' => [
                    'total_revenue',
                    'total_commissions',
                    'operational_funds',
                    'sustainability_score'
                ]
            ]
        ]);
    }

    public function test_check_commission_cap_compliance_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/commission-cap-compliance');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'compliance' => [
                'is_compliant',
                'current_percentage',
                'cap_percentage',
                'excess_amount',
                'recommendations'
            ]
        ]);
    }

    public function test_enforce_commission_caps_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->post('/compliance/enforce-commission-caps');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'enforcement' => [
                'action_taken',
                'message',
                'compliance_status'
            ]
        ]);
    }

    public function test_get_regulatory_compliance_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/regulatory-compliance');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'regulatory_compliance' => [
                'mlm_compliance' => [
                    'status',
                    'last_review',
                    'next_review',
                    'areas' => [
                        'product_focus',
                        'recruitment_limits',
                        'income_disclosure',
                        'refund_policy'
                    ]
                ],
                'consumer_protection' => [
                    'status',
                    'cooling_off_period',
                    'refund_policy',
                    'complaint_resolution',
                    'transparency_measures'
                ],
                'financial_regulations' => [
                    'status',
                    'audit_frequency',
                    'last_audit',
                    'next_audit',
                    'financial_reporting'
                ]
            ]
        ]);
    }

    public function test_validate_earnings_endpoint_with_valid_data()
    {
        $validEarningsData = [
            'earnings_data' => [
                'average_monthly' => 5000,
                'total_earnings' => 60000,
                'commission_percentage' => 20,
                'disclaimers' => ['Earnings may vary', 'No guarantee of success']
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/compliance/validate-earnings', $validEarningsData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'validation' => [
                'is_valid',
                'warnings',
                'violations'
            ]
        ]);

        $validation = $response->json('validation');
        $this->assertTrue($validation['is_valid']);
    }

    public function test_validate_earnings_endpoint_with_invalid_data()
    {
        $invalidEarningsData = [
            'earnings_data' => [
                'average_monthly' => 5000,
                'commission_percentage' => 30 // Exceeds cap
                // Missing disclaimers
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/compliance/validate-earnings', $invalidEarningsData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $validation = $response->json('validation');
        $this->assertFalse($validation['is_valid']);
        $this->assertNotEmpty($validation['violations']);
    }

    public function test_validate_earnings_endpoint_validates_input()
    {
        $invalidInput = [
            'earnings_data' => [
                'average_monthly' => 'invalid', // Should be numeric
                'commission_percentage' => 150 // Should be max 100
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/compliance/validate-earnings', $invalidInput);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'earnings_data.average_monthly'
        ]);
    }

    public function test_generate_compliance_report_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/generate-report');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $response->assertJsonStructure([
            'success',
            'report' => [
                'report_date',
                'business_structure',
                'sustainability_metrics',
                'commission_compliance',
                'regulatory_status',
                'legal_disclaimers',
                'recommendations'
            ]
        ]);

        // Verify report date format
        $reportDate = $response->json('report.report_date');
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $reportDate);
    }

    public function test_compliance_endpoints_require_authentication()
    {
        $endpoints = [
            'GET' => [
                '/compliance',
                '/compliance/information',
                '/compliance/business-structure',
                '/compliance/legal-disclaimers',
                '/compliance/sustainability-metrics',
                '/compliance/commission-cap-compliance',
                '/compliance/regulatory-compliance',
                '/compliance/generate-report'
            ],
            'POST' => [
                '/compliance/enforce-commission-caps',
                '/compliance/validate-earnings'
            ]
        ];

        foreach ($endpoints['GET'] as $endpoint) {
            $response = $this->get($endpoint);
            $response->assertRedirect('/login');
        }

        foreach ($endpoints['POST'] as $endpoint) {
            $response = $this->post($endpoint);
            $response->assertRedirect('/login');
        }
    }

    public function test_commission_cap_compliance_reflects_actual_data()
    {
        // Create test data that should result in high commission percentage
        User::factory()->count(5)->create(['is_active' => true]);
        ReferralCommission::factory()->count(20)->create([
            'status' => 'paid',
            'amount' => 5000,
            'created_at' => now()->subMonths(3)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/compliance/commission-cap-compliance');

        $response->assertStatus(200);
        
        $compliance = $response->json('compliance');
        $this->assertIsNumeric($compliance['current_percentage']);
        $this->assertEquals(25, $compliance['cap_percentage']);
        $this->assertIsBool($compliance['is_compliant']);
    }

    public function test_sustainability_metrics_calculate_correctly()
    {
        // Create test data
        User::factory()->count(10)->create(['is_active' => true]);
        ReferralCommission::factory()->count(5)->create([
            'status' => 'paid',
            'amount' => 1000,
            'created_at' => now()->subMonths(6)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/compliance/sustainability-metrics');

        $response->assertStatus(200);
        
        $metrics = $response->json('metrics');
        
        // Verify commission cap data
        $this->assertEquals(25, $metrics['commission_cap']['percentage']);
        $this->assertIsNumeric($metrics['commission_cap']['current_percentage']);
        $this->assertContains($metrics['commission_cap']['compliance_status'], ['COMPLIANT', 'EXCEEDS_CAP']);

        // Verify revenue allocation sums to 100%
        $totalAllocation = array_sum($metrics['revenue_allocation']);
        $this->assertEquals(100, $totalAllocation);

        // Verify financial health data
        $this->assertIsNumeric($metrics['financial_health']['total_revenue']);
        $this->assertIsNumeric($metrics['financial_health']['total_commissions']);
        $this->assertContains($metrics['financial_health']['sustainability_score'], 
            ['EXCELLENT', 'GOOD', 'ACCEPTABLE', 'NEEDS_ATTENTION']);
    }

    public function test_legal_disclaimers_contain_required_content()
    {
        $response = $this->actingAs($this->user)
            ->get('/compliance/legal-disclaimers');

        $response->assertStatus(200);
        
        $disclaimers = $response->json('disclaimers');
        
        // Verify earnings disclaimer content
        $earningsDisclaimer = $disclaimers['earnings_disclaimer'];
        $this->assertEquals('Earnings Disclaimer', $earningsDisclaimer['title']);
        $this->assertContains('All earnings projections are estimates based on historical data and market conditions.', 
            $earningsDisclaimer['content']);
        $this->assertContains('No guarantee of income or success is implied or stated.', 
            $earningsDisclaimer['content']);

        // Verify business disclaimer content
        $businessDisclaimer = $disclaimers['business_disclaimer'];
        $this->assertEquals('Business Structure Disclaimer', $businessDisclaimer['title']);
        $this->assertContains('MyGrowNet operates as a legitimate multilevel marketing business.', 
            $businessDisclaimer['content']);

        // Verify risk disclosure content
        $riskDisclosure = $disclaimers['risk_disclosure'];
        $this->assertEquals('Risk Factors', $riskDisclosure['title']);
        $this->assertContains('Market conditions and economic factors may affect earning potential.', 
            $riskDisclosure['content']);

        // Verify legal compliance content
        $legalCompliance = $disclaimers['legal_compliance'];
        $this->assertEquals('Legal Compliance', $legalCompliance['title']);
        $this->assertContains('MyGrowNet complies with all applicable MLM regulations.', 
            $legalCompliance['content']);
    }
}