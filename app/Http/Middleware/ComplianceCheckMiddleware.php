<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ComplianceService;
use Illuminate\Support\Facades\Log;

class ComplianceCheckMiddleware
{
    public function __construct(
        private ComplianceService $complianceService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check commission cap compliance for earnings-related endpoints
        if ($this->isEarningsRelatedRequest($request)) {
            $compliance = $this->complianceService->checkCommissionCapCompliance();
            
            if (!$compliance['is_compliant']) {
                Log::warning('Commission cap compliance violation detected', [
                    'current_percentage' => $compliance['current_percentage'],
                    'cap_percentage' => $compliance['cap_percentage'],
                    'excess_amount' => $compliance['excess_amount'],
                    'request_path' => $request->path(),
                    'user_id' => auth()->id()
                ]);

                // Add compliance warning to response headers
                $response = $next($request);
                
                if (method_exists($response, 'header')) {
                    $response->header('X-Compliance-Warning', 'Commission cap exceeded');
                    $response->header('X-Commission-Percentage', $compliance['current_percentage']);
                }
                
                return $response;
            }
        }

        // Validate earnings representations for projection endpoints
        if ($this->isEarningsProjectionRequest($request)) {
            $earningsData = $this->extractEarningsData($request);
            
            if ($earningsData) {
                $validation = $this->complianceService->validateEarningsRepresentation($earningsData);
                
                if (!$validation['is_valid']) {
                    Log::warning('Invalid earnings representation detected', [
                        'violations' => $validation['violations'],
                        'warnings' => $validation['warnings'],
                        'request_path' => $request->path(),
                        'user_id' => auth()->id()
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Earnings representation violates compliance requirements',
                        'violations' => $validation['violations'],
                        'warnings' => $validation['warnings']
                    ], 400);
                }

                if (!empty($validation['warnings'])) {
                    Log::info('Earnings representation warnings', [
                        'warnings' => $validation['warnings'],
                        'request_path' => $request->path(),
                        'user_id' => auth()->id()
                    ]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if the request is earnings-related
     */
    private function isEarningsRelatedRequest(Request $request): bool
    {
        $earningsRelatedPaths = [
            'earnings-projection',
            'mygrownet/api/commission-summary',
            'mygrownet/api/five-level-commission-data',
            'referrals/commissions',
            'referrals/calculate-commission'
        ];

        foreach ($earningsRelatedPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request is for earnings projections
     */
    private function isEarningsProjectionRequest(Request $request): bool
    {
        return str_contains($request->path(), 'earnings-projection/calculate') ||
               str_contains($request->path(), 'earnings-projection/scenarios');
    }

    /**
     * Extract earnings data from request for validation
     */
    private function extractEarningsData(Request $request): ?array
    {
        if ($request->has('tier') && $request->has('active_referrals')) {
            // Extract basic projection parameters
            return [
                'tier' => $request->input('tier'),
                'active_referrals' => $request->input('active_referrals'),
                'network_depth' => $request->input('network_depth', 3),
                'months' => $request->input('months', 12)
            ];
        }

        if ($request->has('earnings_data')) {
            return $request->input('earnings_data');
        }

        return null;
    }
}