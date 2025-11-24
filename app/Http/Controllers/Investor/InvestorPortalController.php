<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use App\Domain\Investor\Services\DocumentManagementService;
use App\Domain\Investor\Services\FinancialReportingService;
use App\Domain\Announcement\Services\AnnouncementService;
use App\Infrastructure\Persistence\Eloquent\Announcement\AnnouncementModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class InvestorPortalController extends Controller
{
    public function __construct(
        private readonly InvestorAccountRepositoryInterface $accountRepository,
        private readonly InvestmentRoundRepositoryInterface $roundRepository,
        private readonly DocumentManagementService $documentService,
        private readonly FinancialReportingService $financialReportingService,
        private readonly AnnouncementService $announcementService
    ) {}

    /**
     * Show investor login page
     */
    public function showLogin()
    {
        return Inertia::render('Investor/Login');
    }

    /**
     * Handle investor login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'access_code' => 'required|string',
        ]);

        // Find investor by email
        $investor = $this->accountRepository->findByEmail($validated['email']);

        if (!$investor) {
            return back()->withErrors([
                'email' => 'No investor account found with this email.',
            ]);
        }

        // For now, use a simple access code system
        // In production, you'd want proper password hashing
        $expectedCode = $this->generateAccessCode($investor->getEmail(), $investor->getId());

        if ($validated['access_code'] !== $expectedCode) {
            return back()->withErrors([
                'access_code' => 'Invalid access code.',
            ]);
        }

        // Store investor ID in session
        session(['investor_id' => $investor->getId()]);
        session(['investor_email' => $investor->getEmail()]);

        return redirect()->route('investor.dashboard');
    }

    /**
     * Show investor dashboard
     */
    public function dashboard()
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            session()->forget(['investor_id', 'investor_email']);
            return redirect()->route('investor.login');
        }

        // Get investment round details
        $round = $this->roundRepository->findById($investor->getInvestmentRoundId());

        // Calculate investment metrics
        $investmentAmount = $investor->getInvestmentAmount();
        $equityPercentage = $investor->getEquityPercentage();
        $currentValuation = $round ? $round->getValuation() : 0;
        $currentValue = $currentValuation * ($equityPercentage / 100);
        $roi = $investmentAmount > 0 ? (($currentValue - $investmentAmount) / $investmentAmount) * 100 : 0;
        
        // Calculate holding period
        $investmentDate = $investor->getInvestmentDate();
        $holdingDays = max(0, now()->diffInDays($investmentDate, false));
        $holdingMonths = max(0, now()->diffInMonths($investmentDate, false));

        // Get platform metrics
        try {
            $metricsService = app(\App\Domain\Investor\Services\PlatformMetricsService::class);
            $platformMetrics = $metricsService->getPublicMetrics();
        } catch (\Exception $e) {
            \Log::error('Platform Metrics Error: ' . $e->getMessage());
            $platformMetrics = [
                'totalMembers' => 0,
                'monthlyRevenue' => 0,
                'activeRate' => 0,
                'retention' => 0,
                'revenueGrowth' => ['labels' => [], 'data' => []],
            ];
        }

        // Get all investors for round stats
        try {
            $allInvestors = $this->accountRepository->findByInvestmentRound($investor->getInvestmentRoundId());
            $totalInvestors = count($allInvestors);
            $totalRaised = array_sum(array_map(fn($inv) => $inv->getInvestmentAmount(), $allInvestors));
        } catch (\Exception $e) {
            \Log::error('Investor Stats Error: ' . $e->getMessage());
            $totalInvestors = 1;
            $totalRaised = $investmentAmount;
        }

        // Get financial reporting data
        try {
            $financialSummary = $this->financialReportingService->getFinancialSummary();
            $performanceMetrics = $this->financialReportingService->getPerformanceMetrics();
        } catch (\Exception $e) {
            \Log::error('Financial Reporting Error: ' . $e->getMessage());
            $financialSummary = null;
            $performanceMetrics = null;
        }

        // Build the data array
        $data = [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
                'investment_amount' => $investor->getInvestmentAmount(),
                'investment_date' => $investor->getInvestmentDate()->format('Y-m-d'),
                'investment_date_formatted' => $investor->getInvestmentDate()->format('F j, Y'),
                'status' => $investor->getStatus()->value(),
                'status_label' => $investor->getStatus()->label(),
                'equity_percentage' => $investor->getEquityPercentage(),
                'holding_days' => $holdingDays,
                'holding_months' => $holdingMonths,
            ],
            'investmentMetrics' => [
                'initial_investment' => $investmentAmount,
                'current_value' => round($currentValue, 2),
                'roi_percentage' => round($roi, 2),
                'equity_percentage' => $equityPercentage,
                'valuation_at_investment' => $round ? $round->getValuation() : 0,
                'current_valuation' => $currentValuation,
            ],
            'round' => $round ? [
                'id' => $round->getId(),
                'name' => $round->getName(),
                'valuation' => $round->getValuation(),
                'goal_amount' => $round->getGoalAmount(),
                'raised_amount' => $round->getRaisedAmount(),
                'progress_percentage' => $round->getProgressPercentage(),
                'total_investors' => $totalInvestors,
                'total_raised' => $totalRaised,
                'status' => $round->getStatus()->value(),
                'status_label' => $round->getStatus()->label(),
            ] : null,
            'platformMetrics' => [
                'total_members' => $platformMetrics['totalMembers'],
                'monthly_revenue' => $platformMetrics['monthlyRevenue'],
                'active_rate' => $platformMetrics['activeRate'],
                'retention_rate' => $platformMetrics['retention'],
                'revenue_growth' => $platformMetrics['revenueGrowth'],
            ],
            'financialSummary' => $financialSummary,
            'performanceMetrics' => $performanceMetrics,
        ];

        // Get investor-relevant announcements
        try {
            $announcements = AnnouncementModel::active()
                ->where(function ($query) use ($investor) {
                    $query->where('target_audience', 'all')
                          ->orWhere('target_audience', 'investors')
                          ->orWhere('target_audience', 'like', '%investor%');
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($announcement) {
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'message' => $announcement->message,
                        'type' => $announcement->type,
                        'is_urgent' => $announcement->is_urgent,
                        'created_at' => $announcement->created_at->format('Y-m-d H:i:s'),
                        'created_at_human' => $announcement->created_at->diffForHumans(),
                    ];
                });
            
            $data['announcements'] = $announcements;
        } catch (\Exception $e) {
            \Log::error('Announcements Error: ' . $e->getMessage());
            $data['announcements'] = [];
        }

        // Debug: Log the complete data
        \Log::info('Investor Dashboard Complete Data', ['keys' => array_keys($data)]);

        return Inertia::render('Investor/Dashboard', $data);
    }

    /**
     * Show investor documents
     */
    public function documents()
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            session()->forget(['investor_id', 'investor_email']);
            return redirect()->route('investor.login');
        }

        // Get documents grouped by category
        $groupedDocuments = $this->documentService->getDocumentsForInvestor(
            $investor->getInvestmentRoundId()
        );

        // Get available categories for filtering
        $categories = $this->documentService->getAvailableCategories();

        return Inertia::render('Investor/Documents', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
                'investment_date' => $investor->getInvestmentDate()->format('Y-m-d'),
            ],
            'groupedDocuments' => $groupedDocuments,
            'categories' => $categories,
        ]);
    }

    /**
     * Download a document
     */
    public function downloadDocument(int $documentId)
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            session()->forget(['investor_id', 'investor_email']);
            return redirect()->route('investor.login');
        }

        try {
            $fileData = $this->documentService->downloadDocument(
                documentId: $documentId,
                investorAccountId: $investor->getId(),
                ipAddress: request()->ip(),
                userAgent: request()->userAgent()
            );

            return response()->download(
                $fileData['path'],
                $fileData['name'],
                ['Content-Type' => $fileData['mime_type']]
            );

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark announcement as read for investor
     */
    public function markAnnouncementAsRead($id)
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
            // Use the existing announcement read tracking system
            // Create a pseudo user ID for investors to track reads
            $pseudoUserId = 'investor_' . $investorId;
            
            \DB::table('announcement_reads')->updateOrInsert(
                [
                    'announcement_id' => $id,
                    'user_id' => $pseudoUserId,
                ],
                [
                    'read_at' => now(),
                ]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Mark announcement as read error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark as read'], 500);
        }
    }

    /**
     * Show investor messages
     */
    public function messages()
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            session()->forget(['investor_id', 'investor_email']);
            return redirect()->route('investor.login');
        }

        // For now, return a simple messages page
        // In the future, this could integrate with the messaging system
        return Inertia::render('Investor/Messages', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'messages' => [], // Placeholder for future implementation
        ]);
    }

    /**
     * Logout investor
     */
    public function logout()
    {
        session()->forget(['investor_id', 'investor_email']);
        return redirect()->route('investor.login')->with('success', 'Logged out successfully');
    }

    /**
     * Generate access code for investor
     * In production, use proper password hashing
     */
    private function generateAccessCode(string $email, int $id): string
    {
        // Simple access code: first 4 chars of email + investor ID
        // In production, use proper password system
        return strtoupper(substr($email, 0, 4)) . $id;
    }

    /**
     * Send access code to investor email
     * This would be called by admin when creating investor account
     */
    public function sendAccessCode(int $investorId)
    {
        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            return back()->with('error', 'Investor not found');
        }

        $accessCode = $this->generateAccessCode($investor->getEmail(), $investor->getId());

        // TODO: Send email with access code
        // Mail::to($investor->getEmail())->send(new InvestorAccessCode($accessCode));

        return back()->with('success', "Access code sent to {$investor->getEmail()}");
    }
}
