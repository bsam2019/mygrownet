<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\CalculateCommissionRequest;
use App\Http\Requests\Employee\StoreCommissionRequest;
use App\Http\Requests\Employee\ApproveCommissionRequest;
use App\Http\Requests\Employee\MarkCommissionPaidRequest;
use App\Domain\Employee\Services\CommissionCalculationService;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use DateTimeImmutable;
use Exception;

class CommissionController extends Controller
{
    /**
     * Display commission dashboard for field agents
     */
    public function index(Request $request)
    {
        // Simple implementation without complex domain logic
        $filters = $request->only(['search', 'status', 'period', 'type']);

        // Mock commission data for now
        $commissions = collect([
            [
                'id' => 1,
                'employee' => ['first_name' => 'John', 'last_name' => 'Doe', 'position' => ['title' => 'Sales Agent']],
                'commission_type' => 'sales',
                'amount' => 1250.00,
                'commission_rate' => 5.0,
                'commission_period' => 'December 2024',
                'status' => 'paid',
                'created_at' => '2024-12-15'
            ],
            [
                'id' => 2,
                'employee' => ['first_name' => 'Jane', 'last_name' => 'Smith', 'position' => ['title' => 'Field Agent']],
                'commission_type' => 'referral',
                'amount' => 850.00,
                'commission_rate' => 3.5,
                'commission_period' => 'December 2024',
                'status' => 'pending',
                'created_at' => '2024-12-20'
            ]
        ]);

        return Inertia::render('Employee/Commissions/Index', [
            'commissions' => $commissions,
            'filters' => $filters
        ]);
    }

    /**
     * Calculate commission for a specific investment
     */
    public function calculate(CalculateCommissionRequest $request): JsonResponse
    {

        try {
            $employee = $this->employeeRepository->findById(new EmployeeId($request->employee_id));
            $investment = Investment::findOrFail($request->investment_id);

            if (!$employee) {
                return response()->json([
                    'error' => 'Employee not found'
                ], 404);
            }

            $result = match ($request->commission_type) {
                'investment_facilitation' => $this->commissionCalculationService->calculateFieldAgentCommission($employee, $investment),
                'referral' => $this->commissionCalculationService->calculateReferralCommission($employee, $investment->user, $investment),
                default => throw new Exception('Unsupported commission type')
            };

            return response()->json([
                'success' => true,
                'commission' => [
                    'amount' => $result->getCommissionAmount(),
                    'base_rate' => $result->getBaseCommissionRate(),
                    'tier_multiplier' => $result->getTierMultiplier(),
                    'performance_multiplier' => $result->getPerformanceMultiplier(),
                    'calculation_details' => $result->getCalculationDetails(),
                    'calculated_at' => $result->getCalculatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Commission calculation failed', [
                'employee_id' => $request->employee_id,
                'investment_id' => $request->investment_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Commission calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a calculated commission record
     */
    public function store(StoreCommissionRequest $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            $commission = EmployeeCommissionModel::create([
                'employee_id' => $request->employee_id,
                'investment_id' => $request->investment_id,
                'user_id' => $request->user_id,
                'commission_type' => $request->commission_type,
                'base_amount' => $request->base_amount,
                'commission_rate' => $request->commission_rate,
                'commission_amount' => $request->commission_amount,
                'calculation_date' => now()->toDateString(),
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'commission' => $commission,
                'message' => 'Commission record created successfully'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Commission storage failed', [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to store commission record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a commission for payment
     */
    public function approve(ApproveCommissionRequest $request, int $commissionId): JsonResponse
    {

        try {
            $commission = EmployeeCommissionModel::findOrFail($commissionId);

            if ($commission->status !== 'pending') {
                return response()->json([
                    'error' => 'Commission is not in pending status'
                ], 400);
            }

            $commission->update([
                'status' => 'approved',
                'notes' => $request->notes ? $commission->notes . "\n" . $request->notes : $commission->notes,
            ]);

            return response()->json([
                'success' => true,
                'commission' => $commission,
                'message' => 'Commission approved successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Commission approval failed', [
                'commission_id' => $commissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to approve commission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark commission as paid
     */
    public function markPaid(MarkCommissionPaidRequest $request, int $commissionId): JsonResponse
    {

        try {
            $commission = EmployeeCommissionModel::findOrFail($commissionId);

            if ($commission->status !== 'approved') {
                return response()->json([
                    'error' => 'Commission must be approved before marking as paid'
                ], 400);
            }

            $commission->update([
                'status' => 'paid',
                'payment_date' => $request->payment_date,
                'notes' => $request->notes ? $commission->notes . "\n" . $request->notes : $commission->notes,
            ]);

            return response()->json([
                'success' => true,
                'commission' => $commission,
                'message' => 'Commission marked as paid successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Commission payment marking failed', [
                'commission_id' => $commissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to mark commission as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly commission report
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $employee = $this->employeeRepository->findById(new EmployeeId($request->employee_id));
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            $reportDate = new DateTimeImmutable(sprintf('%d-%02d-01', $request->year, $request->month));
            $monthlyCommissions = $this->commissionCalculationService->calculateMonthlyCommissions($employee, $reportDate);

            return response()->json([
                'success' => true,
                'report' => [
                    'employee' => [
                        'id' => $employee->getId()->toInt(),
                        'name' => $employee->getFullName(),
                        'position' => $employee->getPosition()->getTitle(),
                    ],
                    'period' => [
                        'year' => $request->year,
                        'month' => $request->month,
                        'month_name' => $reportDate->format('F Y'),
                    ],
                    'summary' => [
                        'total_commissions' => $monthlyCommissions->getTotalCommissions(),
                        'investment_count' => $monthlyCommissions->getInvestmentCount(),
                        'referral_commissions' => $monthlyCommissions->getReferralCommissions(),
                        'performance_bonus' => $monthlyCommissions->getPerformanceBonus(),
                    ],
                    'breakdown' => $monthlyCommissions->getCommissionBreakdown(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Monthly commission report failed', [
                'employee_id' => $request->employee_id,
                'year' => $request->year,
                'month' => $request->month,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to generate monthly report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quarterly commission report
     */
    public function quarterlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|integer|min:2020|max:2030',
            'quarter' => 'required|integer|min:1|max:4',
        ]);

        try {
            $employee = $this->employeeRepository->findById(new EmployeeId($request->employee_id));
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            $quarterlyCommissions = $this->commissionCalculationService->calculateQuarterlyCommissions(
                $employee, 
                $request->year, 
                $request->quarter
            );

            return response()->json([
                'success' => true,
                'report' => [
                    'employee' => [
                        'id' => $employee->getId()->toInt(),
                        'name' => $employee->getFullName(),
                        'position' => $employee->getPosition()->getTitle(),
                    ],
                    'period' => [
                        'year' => $request->year,
                        'quarter' => $request->quarter,
                        'quarter_name' => "Q{$request->quarter} {$request->year}",
                    ],
                    'summary' => [
                        'total_commissions' => $quarterlyCommissions->getTotalCommissions(),
                        'investment_count' => $quarterlyCommissions->getInvestmentCount(),
                        'referral_commissions' => $quarterlyCommissions->getReferralCommissions(),
                        'performance_bonuses' => $quarterlyCommissions->getPerformanceBonuses(),
                    ],
                    'monthly_breakdown' => array_map(function ($monthly) {
                        return [
                            'month' => $monthly->getMonth()->format('F Y'),
                            'total_commissions' => $monthly->getTotalCommissions(),
                            'investment_count' => $monthly->getInvestmentCount(),
                            'referral_commissions' => $monthly->getReferralCommissions(),
                            'performance_bonus' => $monthly->getPerformanceBonus(),
                        ];
                    }, $quarterlyCommissions->getMonthlyCommissions()),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Quarterly commission report failed', [
                'employee_id' => $request->employee_id,
                'year' => $request->year,
                'quarter' => $request->quarter,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to generate quarterly report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get commission analytics for management
     */
    public function analytics(Request $request): Response
    {
        // Only allow managers and admins to access analytics
        $this->authorize('viewCommissionAnalytics');

        $filters = $request->only(['department_id', 'position_id', 'date_from', 'date_to', 'status']);

        $query = EmployeeCommissionModel::with(['employee.department', 'employee.position', 'investment', 'user']);

        // Apply filters
        if ($filters['department_id']) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if ($filters['position_id']) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('position_id', $filters['position_id']);
            });
        }

        if ($filters['date_from']) {
            $query->where('calculation_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->where('calculation_date', '<=', $filters['date_to']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $commissions = $query->orderBy('calculation_date', 'desc')->paginate(50);

        // Calculate summary statistics
        $totalCommissions = $query->sum('commission_amount');
        $pendingCommissions = $query->where('status', 'pending')->sum('commission_amount');
        $paidCommissions = $query->where('status', 'paid')->sum('commission_amount');

        return Inertia::render('Employee/Commission/Analytics', [
            'commissions' => $commissions,
            'filters' => $filters,
            'statistics' => [
                'total_commissions' => $totalCommissions,
                'pending_commissions' => $pendingCommissions,
                'paid_commissions' => $paidCommissions,
                'approval_rate' => $totalCommissions > 0 ? ($paidCommissions / $totalCommissions) * 100 : 0,
            ],
        ]);
    }

    /**
     * Get commission statistics for an employee
     */
    private function getCommissionStatistics(int $employeeId): array
    {
        $currentYear = date('Y');
        $currentMonth = date('m');

        return [
            'total_earned_this_year' => EmployeeCommissionModel::where('employee_id', $employeeId)
                ->whereYear('calculation_date', $currentYear)
                ->where('status', 'paid')
                ->sum('commission_amount'),
            
            'total_earned_this_month' => EmployeeCommissionModel::where('employee_id', $employeeId)
                ->whereYear('calculation_date', $currentYear)
                ->whereMonth('calculation_date', $currentMonth)
                ->where('status', 'paid')
                ->sum('commission_amount'),
            
            'pending_commissions' => EmployeeCommissionModel::where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->sum('commission_amount'),
            
            'approved_commissions' => EmployeeCommissionModel::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->sum('commission_amount'),
            
            'total_investments_facilitated' => EmployeeCommissionModel::where('employee_id', $employeeId)
                ->where('commission_type', 'investment_facilitation')
                ->whereYear('calculation_date', $currentYear)
                ->count(),
            
            'average_commission_per_investment' => EmployeeCommissionModel::where('employee_id', $employeeId)
                ->where('commission_type', 'investment_facilitation')
                ->whereYear('calculation_date', $currentYear)
                ->avg('commission_amount') ?? 0,
        ];
    }
}