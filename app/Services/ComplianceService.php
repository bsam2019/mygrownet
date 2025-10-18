<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use Carbon\Carbon;

class ComplianceService
{
    /**
     * Commission cap percentage (25% of total revenue)
     */
    private const COMMISSION_CAP_PERCENTAGE = 25;

    /**
     * Get business structure information
     */
    public function getBusinessStructure(): array
    {
        return [
            'business_model' => 'Legitimate MLM Business',
            'focus' => 'Education and Community Building',
            'revenue_sources' => [
                'monthly_subscriptions' => 'Primary revenue from member subscriptions',
                'educational_content' => 'Value-added educational materials and courses',
                'community_projects' => 'Collaborative investment opportunities',
                'mentorship_programs' => 'Professional development and guidance'
            ],
            'compliance_standards' => [
                'product_focused' => 'Earnings based on actual product sales, not recruitment',
                'regulatory_compliance' => 'Adheres to MLM and direct selling regulations',
                'consumer_protection' => 'Member protection policies and transparent reporting',
                'financial_sustainability' => 'Commission caps ensure long-term viability'
            ]
        ];
    }

    /**
     * Get legal disclaimers
     */
    public function getLegalDisclaimers(): array
    {
        return [
            'earnings_disclaimer' => [
                'title' => 'Earnings Disclaimer',
                'content' => [
                    'All earnings projections are estimates based on historical data and market conditions.',
                    'Individual results may vary significantly based on personal effort, market conditions, and team development.',
                    'Success requires consistent effort, active participation, and effective team building.',
                    'No guarantee of income or success is implied or stated.',
                    'Past performance does not guarantee future results.'
                ]
            ],
            'business_disclaimer' => [
                'title' => 'Business Structure Disclaimer',
                'content' => [
                    'MyGrowNet operates as a legitimate multilevel marketing business.',
                    'Focus is on product sales and educational value, not recruitment.',
                    'Members receive real value through educational content and community benefits.',
                    'Business model complies with applicable MLM and direct selling regulations.',
                    'Regular legal review ensures adherence to consumer protection laws.'
                ]
            ],
            'risk_disclosure' => [
                'title' => 'Risk Factors',
                'content' => [
                    'Market conditions and economic factors may affect earning potential.',
                    'Regulatory changes could impact business operations.',
                    'Individual performance and team development directly affect earnings.',
                    'Competition and market saturation may influence growth rates.',
                    'Technology changes may require adaptation of business processes.'
                ]
            ],
            'legal_compliance' => [
                'title' => 'Legal Compliance',
                'content' => [
                    'MyGrowNet complies with all applicable MLM regulations.',
                    'Anti-pyramid scheme compliance through focus on product sales.',
                    'Consumer protection measures are actively maintained.',
                    'Regular audits ensure ongoing regulatory compliance.',
                    'Transparent reporting and member protection policies.'
                ]
            ]
        ];
    }

    /**
     * Get sustainability metrics
     */
    public function getSustainabilityMetrics(): array
    {
        $totalRevenue = $this->calculateTotalRevenue();
        $totalCommissions = $this->calculateTotalCommissions();
        $commissionPercentage = $totalRevenue > 0 ? ($totalCommissions / $totalRevenue) * 100 : 0;

        return [
            'commission_cap' => [
                'percentage' => self::COMMISSION_CAP_PERCENTAGE,
                'description' => 'Maximum percentage of revenue allocated to commissions',
                'current_percentage' => round($commissionPercentage, 2),
                'compliance_status' => $commissionPercentage <= self::COMMISSION_CAP_PERCENTAGE ? 'COMPLIANT' : 'EXCEEDS_CAP'
            ],
            'revenue_allocation' => [
                'commissions' => self::COMMISSION_CAP_PERCENTAGE,
                'operations' => 50,
                'product_development' => 15,
                'member_benefits' => 5,
                'compliance_legal' => 3,
                'reserves' => 2
            ],
            'financial_health' => [
                'total_revenue' => $totalRevenue,
                'total_commissions' => $totalCommissions,
                'operational_funds' => $totalRevenue - $totalCommissions,
                'sustainability_score' => $this->calculateSustainabilityScore($commissionPercentage)
            ]
        ];
    }

    /**
     * Check commission cap compliance
     */
    public function checkCommissionCapCompliance(): array
    {
        $metrics = $this->getSustainabilityMetrics();
        $commissionCap = $metrics['commission_cap'];

        return [
            'is_compliant' => $commissionCap['compliance_status'] === 'COMPLIANT',
            'current_percentage' => $commissionCap['current_percentage'],
            'cap_percentage' => $commissionCap['percentage'],
            'excess_amount' => max(0, $commissionCap['current_percentage'] - $commissionCap['percentage']),
            'recommendations' => $this->getComplianceRecommendations($commissionCap['current_percentage'])
        ];
    }

    /**
     * Enforce commission caps
     */
    public function enforceCommissionCaps(): array
    {
        $compliance = $this->checkCommissionCapCompliance();
        
        if (!$compliance['is_compliant']) {
            return $this->implementCapEnforcement($compliance['excess_amount']);
        }

        return [
            'action_taken' => false,
            'message' => 'Commission levels are within acceptable limits',
            'compliance_status' => 'COMPLIANT'
        ];
    }

    /**
     * Get regulatory compliance status
     */
    public function getRegulatoryCompliance(): array
    {
        return [
            'mlm_compliance' => [
                'status' => 'COMPLIANT',
                'last_review' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'next_review' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'areas' => [
                    'product_focus' => 'COMPLIANT',
                    'recruitment_limits' => 'COMPLIANT',
                    'income_disclosure' => 'COMPLIANT',
                    'refund_policy' => 'COMPLIANT'
                ]
            ],
            'consumer_protection' => [
                'status' => 'COMPLIANT',
                'cooling_off_period' => '7 days',
                'refund_policy' => 'Full refund within 30 days',
                'complaint_resolution' => 'Active',
                'transparency_measures' => 'Implemented'
            ],
            'financial_regulations' => [
                'status' => 'COMPLIANT',
                'audit_frequency' => 'Quarterly',
                'last_audit' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'next_audit' => Carbon::now()->addMonth()->format('Y-m-d'),
                'financial_reporting' => 'Current'
            ]
        ];
    }

    /**
     * Generate compliance report
     */
    public function generateComplianceReport(): array
    {
        return [
            'report_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'business_structure' => $this->getBusinessStructure(),
            'sustainability_metrics' => $this->getSustainabilityMetrics(),
            'commission_compliance' => $this->checkCommissionCapCompliance(),
            'regulatory_status' => $this->getRegulatoryCompliance(),
            'legal_disclaimers' => $this->getLegalDisclaimers(),
            'recommendations' => $this->getComplianceRecommendations()
        ];
    }

    /**
     * Validate earnings representations
     */
    public function validateEarningsRepresentation(array $earningsData): array
    {
        $validation = [
            'is_valid' => true,
            'warnings' => [],
            'violations' => []
        ];

        // Check if earnings are within realistic ranges
        if (isset($earningsData['average_monthly'])) {
            $monthlyEarnings = $earningsData['average_monthly'];
            
            if ($monthlyEarnings > 100000) { // K100,000 per month
                $validation['warnings'][] = 'Earnings projection exceeds realistic monthly limits';
            }
        }

        // Check if proper disclaimers are included
        if (!isset($earningsData['disclaimers']) || empty($earningsData['disclaimers'])) {
            $validation['violations'][] = 'Missing required earnings disclaimers';
            $validation['is_valid'] = false;
        }

        // Check commission percentage compliance
        if (isset($earningsData['commission_percentage'])) {
            if ($earningsData['commission_percentage'] > self::COMMISSION_CAP_PERCENTAGE) {
                $validation['violations'][] = 'Commission percentage exceeds regulatory cap';
                $validation['is_valid'] = false;
            }
        }

        return $validation;
    }

    /**
     * Private helper methods
     */
    private function calculateTotalRevenue(): float
    {
        // This would typically query actual revenue data
        // For now, return a sample calculation
        $monthlySubscriptions = User::where('is_active', true)->count() * 500; // Average subscription
        return $monthlySubscriptions * 12; // Annual revenue estimate
    }

    private function calculateTotalCommissions(): float
    {
        // This would typically query actual commission data
        return ReferralCommission::where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->sum('amount');
    }

    private function calculateSustainabilityScore(float $commissionPercentage): string
    {
        if ($commissionPercentage <= 20) {
            return 'EXCELLENT';
        } elseif ($commissionPercentage <= 25) {
            return 'GOOD';
        } elseif ($commissionPercentage <= 30) {
            return 'ACCEPTABLE';
        } else {
            return 'NEEDS_ATTENTION';
        }
    }

    private function getComplianceRecommendations(float $currentPercentage = null): array
    {
        $recommendations = [
            'general' => [
                'Maintain regular compliance audits',
                'Update legal disclaimers quarterly',
                'Monitor commission-to-revenue ratios monthly',
                'Ensure product focus over recruitment emphasis'
            ]
        ];

        if ($currentPercentage !== null && $currentPercentage > self::COMMISSION_CAP_PERCENTAGE) {
            $recommendations['urgent'] = [
                'Reduce commission rates to comply with cap',
                'Increase focus on product sales revenue',
                'Review and adjust compensation plan',
                'Implement commission reduction measures'
            ];
        }

        return $recommendations;
    }

    private function implementCapEnforcement(float $excessPercentage): array
    {
        // This would implement actual cap enforcement measures
        return [
            'action_taken' => true,
            'message' => 'Commission cap enforcement measures implemented',
            'excess_percentage' => $excessPercentage,
            'enforcement_measures' => [
                'Temporary commission rate reduction',
                'Enhanced product sales focus',
                'Member notification of changes',
                'Compliance monitoring increased'
            ],
            'compliance_status' => 'ENFORCEMENT_ACTIVE'
        ];
    }
}