<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use App\Domain\Investor\Services\DocumentManagementService;
use App\Domain\Investor\Services\FinancialReportingService;
use App\Domain\Investor\Services\InvestorMessagingService;
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
        private readonly AnnouncementService $announcementService,
        private readonly InvestorMessagingService $messagingService,
        private readonly \App\Domain\Investor\Services\ShareCertificateService $shareCertificateService,
        private readonly \App\Domain\Investor\Services\DividendManagementService $dividendService,
        private readonly \App\Domain\Investor\Services\InvestorRelationsService $relationsService
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
                    $diff = now()->diffInMinutes($announcement->created_at);
                    
                    if ($diff < 1) {
                        $humanTime = 'Just now';
                    } elseif ($diff < 60) {
                        $humanTime = $diff . ' min' . ($diff > 1 ? 's' : '') . ' ago';
                    } elseif ($diff < 1440) { // Less than 24 hours
                        $hours = floor($diff / 60);
                        $humanTime = $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
                    } else {
                        $humanTime = $announcement->created_at->diffForHumans();
                    }
                    
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'message' => $announcement->message,
                        'type' => $announcement->type,
                        'is_urgent' => $announcement->is_urgent,
                        'created_at' => $announcement->created_at->format('Y-m-d H:i:s'),
                        'created_at_human' => $humanTime,
                    ];
                });
            
            $data['announcements'] = $announcements;
        } catch (\Exception $e) {
            \Log::error('Announcements Error: ' . $e->getMessage());
            $data['announcements'] = [];
        }

        // Get unread messages count
        try {
            $data['unreadMessagesCount'] = $this->messagingService->getUnreadCountForInvestor($investorId);
        } catch (\Exception $e) {
            \Log::error('Unread Messages Count Error: ' . $e->getMessage());
            $data['unreadMessagesCount'] = 0;
        }

        // Phase 1: Get share certificates
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

        // Phase 1: Get dividend summary
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

        // Phase 1: Get latest quarterly report
        try {
            $latestReport = $this->relationsService->getLatestQuarterlyReport();
            $data['latestQuarterlyReport'] = $latestReport ? [
                'id' => $latestReport->id,
                'title' => $latestReport->title,
                'quarter' => $latestReport->quarter,
                'year' => $latestReport->year,
                'published_at' => $latestReport->published_at?->format('Y-m-d'),
            ] : null;
        } catch (\Exception $e) {
            \Log::error('Latest Quarterly Report Error: ' . $e->getMessage());
            $data['latestQuarterlyReport'] = null;
        }

        // Phase 1: Get upcoming meetings
        try {
            $upcomingMeetings = $this->relationsService->getUpcomingMeetings(3);
            $data['upcomingMeetings'] = array_map(fn($meeting) => [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'meeting_date' => $meeting->meeting_date->format('Y-m-d H:i'),
                'type' => $meeting->type,
            ], $upcomingMeetings);
        } catch (\Exception $e) {
            \Log::error('Upcoming Meetings Error: ' . $e->getMessage());
            $data['upcomingMeetings'] = [];
        }

        // Debug: Log the complete data
        \Log::info('Investor Dashboard Complete Data', ['keys' => array_keys($data)]);

        // Merge layout data (for notification bell)
        $data = array_merge($data, $this->getLayoutData($investorId));

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
     * Show investor financial reports
     */
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

        // Get published financial reports
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

    /**
     * Show investor announcements
     */
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

        // Get announcements from the general announcements system (filtered for investors)
        $announcements = AnnouncementModel::active()
            ->where(function ($query) {
                $query->where('target_audience', 'all')
                      ->orWhere('target_audience', 'investors')
                      ->orWhere('target_audience', 'like', '%investor%');
            })
            ->orderByDesc('is_urgent')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($announcement) use ($investorId) {
                // Check if this investor has read this announcement
                $isRead = \DB::table('announcement_reads')
                    ->where('announcement_id', $announcement->id)
                    ->where('user_id', 'investor_' . $investorId)
                    ->exists();

                return [
                    'announcement' => [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'content' => $announcement->message,
                        'summary' => \Str::limit(strip_tags($announcement->message), 150),
                        'type' => $announcement->type ?? 'general',
                        'priority' => $announcement->is_urgent ? 'urgent' : 'normal',
                        'is_pinned' => $announcement->is_urgent ?? false,
                        'published_at' => $announcement->created_at->format('Y-m-d H:i:s'),
                    ],
                    'is_read' => $isRead,
                ];
            });

        // Get unique announcement types for filtering
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

    /**
     * Mark investor announcement as read
     */
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

        // Get messages for this investor
        $messages = $this->messagingService->getMessagesForInvestor($investorId);
        $unreadCount = $this->messagingService->getUnreadCountForInvestor($investorId);

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

    /**
     * Store a new message from investor
     */
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

    /**
     * Mark a message as read
     */
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

    /**
     * Show investor settings
     */
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

        // Get notification preferences
        $preferenceRepository = app(\App\Domain\Investor\Repositories\InvestorNotificationPreferenceRepositoryInterface::class);
        $preferences = $preferenceRepository->findOrCreateForInvestor($investorId);

        return Inertia::render('Investor/Settings', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'preferences' => $preferences->toArray(),
        ]);
    }

    /**
     * Update notification preferences
     */
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

        $preferenceRepository = app(\App\Domain\Investor\Repositories\InvestorNotificationPreferenceRepositoryInterface::class);
        $preferences = $preferenceRepository->findOrCreateForInvestor($investorId);

        $preferences->updatePreferences(
            emailAnnouncements: $validated['email_announcements'] ?? true,
            emailFinancialReports: $validated['email_financial_reports'] ?? true,
            emailDividends: $validated['email_dividends'] ?? true,
            emailMeetings: $validated['email_meetings'] ?? true,
            emailMessages: $validated['email_messages'] ?? true,
            emailUrgentOnly: $validated['email_urgent_only'] ?? false,
            digestFrequency: $validated['digest_frequency'] ?? 'immediate'
        );

        $preferenceRepository->save($preferences);

        return back()->with('success', 'Notification preferences updated');
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

    /**
     * Get unread announcements count for investor
     */
    private function getUnreadAnnouncementsCount(int $investorId): int
    {
        try {
            $activeAnnouncements = AnnouncementModel::active()
                ->where(function ($query) {
                    $query->where('target_audience', 'all')
                          ->orWhere('target_audience', 'investors')
                          ->orWhere('target_audience', 'like', '%investor%');
                })
                ->pluck('id');

            $readAnnouncements = \DB::table('announcement_reads')
                ->where('user_id', 'investor_' . $investorId)
                ->whereIn('announcement_id', $activeAnnouncements)
                ->pluck('announcement_id');

            return $activeAnnouncements->count() - $readAnnouncements->count();
        } catch (\Exception $e) {
            \Log::error('Unread Announcements Count Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get common layout data for all investor pages
     */
    private function getLayoutData(int $investorId): array
    {
        return [
            'unreadMessages' => $this->messagingService->getUnreadCountForInvestor($investorId),
            'unreadAnnouncements' => $this->getUnreadAnnouncementsCount($investorId),
        ];
    }

    /**
     * Get notification counts for polling (JSON endpoint)
     */
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
