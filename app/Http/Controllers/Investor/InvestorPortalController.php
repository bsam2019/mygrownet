<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorNotificationPreferenceRepositoryInterface;
use App\Domain\Investor\Services\DocumentManagementService;
use App\Domain\Investor\Services\FinancialReportingService;
use App\Domain\Investor\Services\InvestorMessagingService;
use App\Domain\Investor\Services\PlatformMetricsService;
use App\Domain\Investor\Services\ShareCertificateService;
use App\Domain\Investor\Services\DividendManagementService;
use App\Domain\Investor\Services\InvestorRelationsService;
use App\Domain\Announcement\Repositories\AnnouncementRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestorPortalController extends Controller
{
    public function __construct(
        private readonly InvestorAccountRepositoryInterface $accountRepository,
        private readonly InvestmentRoundRepositoryInterface $roundRepository,
        private readonly DocumentManagementService $documentService,
        private readonly FinancialReportingService $financialReportingService,
        private readonly AnnouncementRepositoryInterface $announcementRepository,
        private readonly InvestorMessagingService $messagingService,
        private readonly ShareCertificateService $shareCertificateService,
        private readonly DividendManagementService $dividendService,
        private readonly InvestorRelationsService $relationsService,
        private readonly PlatformMetricsService $metricsService,
        private readonly InvestorNotificationPreferenceRepositoryInterface $preferenceRepository
    ) {}

    public function showLogin()
    {
        return Inertia::render('Investor/Login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'access_code' => 'required|string',
        ]);

        $investor = $this->accountRepository->findByEmail($validated['email']);

        if (!$investor) {
            return back()->withErrors([
                'email' => 'No investor account found with this email.',
            ]);
        }

        $expectedCode = $this->generateAccessCode($investor->getEmail(), $investor->getId());

        if ($validated['access_code'] !== $expectedCode) {
            return back()->withErrors([
                'access_code' => 'Invalid access code.',
            ]);
        }

        session(['investor_id' => $investor->getId()]);
        session(['investor_email' => $investor->getEmail()]);

        return redirect()->route('investor.dashboard');
    }

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

        $roundId = $investor->getInvestmentRoundId();
        $round = $roundId ? $this->roundRepository->findById($roundId) : null;

        $investmentAmount = $investor->getInvestmentAmount();
        $equityPercentage = $investor->getEquityPercentage();
        $currentValuation = $round ? $round->getValuation() : 0;
        $currentValue = $currentValuation * ($equityPercentage / 100);
        $roi = $investmentAmount > 0 ? (($currentValue - $investmentAmount) / $investmentAmount) * 100 : 0;

        $investmentDate = $investor->getInvestmentDate();
        $holdingDays = max(0, now()->diffInDays($investmentDate, false));
        $holdingMonths = max(0, now()->diffInMonths($investmentDate, false));

        try {
            $platformMetrics = $this->metricsService->getPublicMetrics();
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

        try {
            $roundId = $investor->getInvestmentRoundId();
            if ($roundId) {
                $allInvestors = $this->accountRepository->findByInvestmentRound($roundId);
                $totalInvestors = count($allInvestors);
                $totalRaised = array_sum(array_map(fn($inv) => $inv->getInvestmentAmount(), $allInvestors));
            } else {
                $totalInvestors = 1;
                $totalRaised = $investmentAmount;
            }
        } catch (\Exception $e) {
            \Log::error('Investor Stats Error: ' . $e->getMessage());
            $totalInvestors = 1;
            $totalRaised = $investmentAmount;
        }

        try {
            $financialSummary = $this->financialReportingService->getFinancialSummary();
            $performanceMetrics = $this->financialReportingService->getPerformanceMetrics();
        } catch (\Exception $e) {
            \Log::error('Financial Reporting Error: ' . $e->getMessage());
            $financialSummary = null;
            $performanceMetrics = null;
        }

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

        try {
            $all = $this->announcementRepository->getActiveAnnouncements();
            $investorAnnouncements = array_values(array_filter($all, fn($a) =>
                $a->getTargetAudience()->value() === 'all' ||
                $a->getTargetAudience()->value() === 'investors'
            ));
            $data['announcements'] = array_map(fn($a) => [
                'id' => $a->getId(),
                'title' => $a->getTitle(),
                'message' => $a->getMessage(),
                'type' => $a->getType()->value(),
                'is_urgent' => $a->isUrgent(),
                'created_at' => $a->getCreatedAt()->format('Y-m-d H:i:s'),
            ], array_slice($investorAnnouncements, 0, 5));
        } catch (\Exception $e) {
            \Log::error('Announcements Error: ' . $e->getMessage());
            $data['announcements'] = [];
        }

        try {
            $data['unreadMessagesCount'] = $this->messagingService->getUnreadCountForInvestor($investorId);
        } catch (\Exception $e) {
            \Log::error('Unread Messages Count Error: ' . $e->getMessage());
            $data['unreadMessagesCount'] = 0;
        }

        try {
            $certificates = $this->shareCertificateService->getCertificatesForInvestor($investorId);
            $data['shareCertificates'] = array_map(fn($cert) => [
                'id' => $cert->getId(),
                'certificate_number' => $cert->getCertificateNumber(),
                'shares' => $cert->getShares()->getValue(),
                'issue_date' => $cert->getIssueDate()->format('Y-m-d'),
                'status' => $cert->getStatus(),
            ], $certificates);
        } catch (\Exception $e) {
            \Log::error('Share Certificates Error: ' . $e->getMessage());
            $data['shareCertificates'] = [];
        }

        try {
            $dividendSummary = $this->dividendService->getDividendSummary($investorId);
            $data['dividendSummary'] = $dividendSummary;
        } catch (\Exception $e) {
            \Log::error('Dividend Summary Error: ' . $e->getMessage());
            $data['dividendSummary'] = [
                'total_received' => 0,
                'pending_amount' => 0,
                'next_payment_date' => null,
                'payment_count' => 0,
            ];
        }

        try {
            $latestReport = $this->relationsService->getLatestQuarterlyReport();
            $data['latestQuarterlyReport'] = $latestReport ? [
                'id' => $latestReport->getId(),
                'title' => $latestReport->getTitle(),
                'quarter' => $latestReport->getQuarter(),
                'year' => $latestReport->getYear(),
                'published_at' => $latestReport->getPublishedAt()?->format('Y-m-d'),
            ] : null;
        } catch (\Exception $e) {
            \Log::error('Latest Quarterly Report Error: ' . $e->getMessage());
            $data['latestQuarterlyReport'] = null;
        }

        try {
            $upcomingMeetings = $this->relationsService->getUpcomingMeetings(3);
            $data['upcomingMeetings'] = $upcomingMeetings;
        } catch (\Exception $e) {
            \Log::error('Upcoming Meetings Error: ' . $e->getMessage());
            $data['upcomingMeetings'] = [];
        }

        \Log::info('Investor Dashboard Complete Data', ['keys' => array_keys($data)]);

        $data = array_merge($data, $this->getLayoutData($investorId));

        return Inertia::render('Investor/Dashboard', $data);
    }

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

        $roundId = $investor->getInvestmentRoundId();
        $groupedDocuments = $roundId
            ? $this->documentService->getDocumentsForInvestor($roundId)
            : [];

        $categories = $this->documentService->getAvailableCategories();

        return Inertia::render('Investor/Documents', array_merge([
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
                'investment_date' => $investor->getInvestmentDate()->format('Y-m-d'),
            ],
            'groupedDocuments' => $groupedDocuments,
            'categories' => $categories,
        ], $this->getLayoutData($investorId)));
    }

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

    public function markAnnouncementAsRead($id)
    {
        $investorId = session('investor_id');

        if (!$investorId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
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

    public function reports()
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

        $reports = $this->financialReportingService->getLatestReports(12);
        $financialSummary = $this->financialReportingService->getFinancialSummary();
        $performanceMetrics = $this->financialReportingService->getPerformanceMetrics();

        return Inertia::render('Investor/Reports', array_merge([
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'reports' => array_map(fn($report) => $report->toArray(), $reports),
            'financialSummary' => $financialSummary,
            'performanceMetrics' => $performanceMetrics,
        ], $this->getLayoutData($investorId)));
    }

    public function announcements()
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
            $all = $this->announcementRepository->getActiveAnnouncements();
            $filtered = array_values(array_filter($all, fn($a) =>
                $a->getTargetAudience()->value() === 'all' ||
                $a->getTargetAudience()->value() === 'investors'
            ));
            $readIds = \DB::table('announcement_reads')
                ->where('user_id', 'investor_' . $investorId)
                ->pluck('announcement_id')
                ->all();
            $announcements = array_map(fn($a) => [
                'id' => $a->getId(),
                'title' => $a->getTitle(),
                'message' => $a->getMessage(),
                'type' => $a->getType()->value(),
                'is_urgent' => $a->isUrgent(),
                'is_read' => in_array($a->getId(), $readIds),
                'created_at' => $a->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $filtered);
        } catch (\Exception $e) {
            \Log::error('Announcements Error: ' . $e->getMessage());
            $announcements = [];
        }

        $types = [
            ['value' => 'general', 'label' => 'General'],
            ['value' => 'financial', 'label' => 'Financial'],
            ['value' => 'update', 'label' => 'Updates'],
            ['value' => 'urgent', 'label' => 'Urgent'],
        ];

        return Inertia::render('Investor/Announcements', array_merge([
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'announcements' => $announcements,
            'types' => $types,
        ], $this->getLayoutData($investorId)));
    }

    public function markInvestorAnnouncementAsRead(int $id)
    {
        $investorId = session('investor_id');

        if (!$investorId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
            \DB::table('announcement_reads')->updateOrInsert(
                [
                    'announcement_id' => $id,
                    'user_id' => 'investor_' . $investorId,
                ],
                [
                    'read_at' => now(),
                ]
            );

            return back()->with('success', 'Announcement marked as read');
        } catch (\Exception $e) {
            \Log::error('Mark announcement as read error: ' . $e->getMessage());
            return back()->with('error', 'Failed to mark as read');
        }
    }

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

        $messages = $this->messagingService->getMessagesForInvestor($investorId);

        try {
            $unreadCount = $this->messagingService->getUnreadCountForInvestor($investorId);
        } catch (\Exception $e) {
            \Log::error('Unread Messages Count Error in messages page: ' . $e->getMessage());
            $unreadCount = 0;
        }

        return Inertia::render('Investor/Messages', array_merge([
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'messages' => $messages,
            'unreadCount' => $unreadCount,
        ], $this->getLayoutData($investorId)));
    }

    public function storeMessage(Request $request)
    {
        $investorId = session('investor_id');

        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'parent_id' => 'nullable|integer|exists:investor_messages,id',
        ]);

        try {
            $this->messagingService->sendMessageFromInvestor(
                investorAccountId: $investorId,
                subject: $validated['subject'],
                content: $validated['content'],
                parentId: $validated['parent_id'] ?? null
            );

            return back()->with('success', 'Message sent successfully');
        } catch (\Exception $e) {
            \Log::error('Investor message error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send message');
        }
    }

    public function markMessageAsRead(int $id)
    {
        $investorId = session('investor_id');

        if (!$investorId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
            $this->messagingService->markAsRead($id, $investorId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function settings()
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

        $preferences = $this->preferenceRepository->findOrCreateForInvestor($investorId);

        return Inertia::render('Investor/Settings', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'preferences' => $preferences->toArray(),
        ]);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $investorId = session('investor_id');

        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $validated = $request->validate([
            'email_announcements' => 'boolean',
            'email_financial_reports' => 'boolean',
            'email_dividends' => 'boolean',
            'email_meetings' => 'boolean',
            'email_messages' => 'boolean',
            'email_urgent_only' => 'boolean',
            'digest_frequency' => 'in:immediate,daily,weekly,none',
        ]);

        $preferences = $this->preferenceRepository->findOrCreateForInvestor($investorId);

        $preferences->updatePreferences(
            emailAnnouncements: $validated['email_announcements'] ?? true,
            emailFinancialReports: $validated['email_financial_reports'] ?? true,
            emailDividends: $validated['email_dividends'] ?? true,
            emailMeetings: $validated['email_meetings'] ?? true,
            emailMessages: $validated['email_messages'] ?? true,
            emailUrgentOnly: $validated['email_urgent_only'] ?? false,
            digestFrequency: $validated['digest_frequency'] ?? 'immediate'
        );

        $this->preferenceRepository->save($preferences);

        return back()->with('success', 'Notification preferences updated');
    }

    public function logout()
    {
        session()->forget(['investor_id', 'investor_email']);
        return redirect()->route('investor.login')->with('success', 'Logged out successfully');
    }

    private function generateAccessCode(string $email, int $id): string
    {
        return strtoupper(substr($email, 0, 4)) . $id;
    }

    public function sendAccessCode(int $investorId)
    {
        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            return back()->with('error', 'Investor not found');
        }

        $accessCode = $this->generateAccessCode($investor->getEmail(), $investor->getId());

        return back()->with('success', "Access code sent to {$investor->getEmail()}");
    }

    private function getUnreadAnnouncementsCount(int $investorId): int
    {
        try {
            $all = $this->announcementRepository->getActiveAnnouncements();
            $filtered = array_values(array_filter($all, fn($a) =>
                $a->getTargetAudience()->value() === 'all' ||
                $a->getTargetAudience()->value() === 'investors'
            ));
            $readIds = \DB::table('announcement_reads')
                ->where('user_id', 'investor_' . $investorId)
                ->pluck('announcement_id')
                ->all();
            $announcementIds = array_map(fn($a) => $a->getId(), $filtered);
            return count($announcementIds) - count(array_intersect($announcementIds, $readIds));
        } catch (\Exception $e) {
            \Log::error('Unread Announcements Count Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getLayoutData(int $investorId): array
    {
        try {
            $unreadMessages = $this->messagingService->getUnreadCountForInvestor($investorId);
        } catch (\Exception $e) {
            \Log::error('Unread Messages Count Error in layout data: ' . $e->getMessage());
            $unreadMessages = 0;
        }

        return [
            'unreadMessages' => $unreadMessages,
            'unreadAnnouncements' => $this->getUnreadAnnouncementsCount($investorId),
        ];
    }

    public function getNotificationCount()
    {
        $investorId = session('investor_id');

        if (!$investorId) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        try {
            return response()->json([
                'unreadMessages' => $this->messagingService->getUnreadCountForInvestor($investorId),
                'unreadAnnouncements' => $this->getUnreadAnnouncementsCount($investorId),
            ]);
        } catch (\Exception $e) {
            \Log::error('Notification count error: ' . $e->getMessage());
            return response()->json([
                'unreadMessages' => 0,
                'unreadAnnouncements' => 0,
            ]);
        }
    }
}
