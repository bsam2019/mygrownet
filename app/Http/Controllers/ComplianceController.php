<?php

namespace App\Http\Controllers;

use App\Services\ComplianceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComplianceController extends Controller
{
    public function __construct(
        private ComplianceService $complianceService
    ) {}

    /**
     * Display the compliance dashboard
     */
    public function index()
    {
        $complianceReport = $this->complianceService->generateComplianceReport();
        
        return Inertia::render('Compliance/Dashboard', [
            'compliance_report' => $complianceReport
        ]);
    }

    /**
     * Get business structure information
     */
    public function businessStructure()
    {
        $businessStructure = $this->complianceService->getBusinessStructure();
        
        return response()->json([
            'success' => true,
            'business_structure' => $businessStructure
        ]);
    }

    /**
     * Get legal disclaimers
     */
    public function legalDisclaimers()
    {
        $disclaimers = $this->complianceService->getLegalDisclaimers();
        
        return response()->json([
            'success' => true,
            'disclaimers' => $disclaimers
        ]);
    }

    /**
     * Get sustainability metrics
     */
    public function sustainabilityMetrics()
    {
        $metrics = $this->complianceService->getSustainabilityMetrics();
        
        return response()->json([
            'success' => true,
            'metrics' => $metrics
        ]);
    }

    /**
     * Check commission cap compliance
     */
    public function commissionCapCompliance()
    {
        $compliance = $this->complianceService->checkCommissionCapCompliance();
        
        return response()->json([
            'success' => true,
            'compliance' => $compliance
        ]);
    }

    /**
     * Enforce commission caps
     */
    public function enforceCommissionCaps()
    {
        try {
            $enforcement = $this->complianceService->enforceCommissionCaps();
            
            return response()->json([
                'success' => true,
                'enforcement' => $enforcement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error enforcing commission caps: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get regulatory compliance status
     */
    public function regulatoryCompliance()
    {
        $compliance = $this->complianceService->getRegulatoryCompliance();
        
        return response()->json([
            'success' => true,
            'regulatory_compliance' => $compliance
        ]);
    }

    /**
     * Validate earnings representation
     */
    public function validateEarnings(Request $request)
    {
        $request->validate([
            'earnings_data' => 'required|array',
            'earnings_data.average_monthly' => 'numeric|min:0',
            'earnings_data.total_earnings' => 'numeric|min:0',
            'earnings_data.commission_percentage' => 'numeric|min:0|max:100'
        ]);

        try {
            $validation = $this->complianceService->validateEarningsRepresentation(
                $request->input('earnings_data')
            );
            
            return response()->json([
                'success' => true,
                'validation' => $validation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating earnings: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate full compliance report
     */
    public function generateReport()
    {
        try {
            $report = $this->complianceService->generateComplianceReport();
            
            return response()->json([
                'success' => true,
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating compliance report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display compliance information page
     */
    public function information()
    {
        $businessStructure = $this->complianceService->getBusinessStructure();
        $disclaimers = $this->complianceService->getLegalDisclaimers();
        $regulatoryCompliance = $this->complianceService->getRegulatoryCompliance();
        
        return Inertia::render('Compliance/Information', [
            'business_structure' => $businessStructure,
            'legal_disclaimers' => $disclaimers,
            'regulatory_compliance' => $regulatoryCompliance
        ]);
    }
}