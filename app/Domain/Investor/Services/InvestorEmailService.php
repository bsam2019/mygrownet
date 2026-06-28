<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorEmailLog;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorEmailLogRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorNotificationPreferenceRepositoryInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class InvestorEmailService
{
    public function __construct(
        private readonly InvestorAccountRepositoryInterface $accountRepository,
        private readonly InvestorNotificationPreferenceRepositoryInterface $preferenceRepository,
        private readonly InvestorEmailLogRepositoryInterface $emailLogRepository
    ) {}

    /**
     * Send announcement email to all eligible investors
     */
    public function sendAnnouncementEmail(
        int $announcementId,
        string $title,
        string $content,
        string $type,
        bool $isUrgent = false
    ): array {
        $preferences = $this->preferenceRepository->findInvestorsForEmailType('announcement', $isUrgent);
        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($preferences as $preference) {
            $investor = $this->accountRepository->findById($preference->getInvestorAccountId());
            
            if (!$investor) {
                $results['skipped']++;
                continue;
            }

            $log = InvestorEmailLog::create(
                investorAccountId: $investor->getId(),
                emailType: 'announcement',
                subject: $title,
                referenceId: $announcementId,
                referenceType: 'announcement'
            );

            try {
                $this->sendEmail(
                    to: $investor->getEmail(),
                    subject: "[MyGrowNet] {$title}",
                    template: 'emails.investor.announcement',
                    data: [
                        'investorName' => $investor->getName(),
                        'title' => $title,
                        'content' => $content,
                        'type' => $type,
                        'isUrgent' => $isUrgent,
                        'announcementId' => $announcementId,
                    ]
                );

                $log->markAsSent();
                $results['sent']++;
            } catch (\Exception $e) {
                $log->markAsFailed($e->getMessage());
                $results['failed']++;
                Log::error("Failed to send announcement email to {$investor->getEmail()}: {$e->getMessage()}");
            }

            $this->emailLogRepository->save($log);
        }

        return $results;
    }

    /**
     * Send financial report email to all eligible investors
     */
    public function sendFinancialReportEmail(
        int $reportId,
        string $title,
        string $reportType,
        string $reportPeriod,
        array $highlights = []
    ): array {
        $preferences = $this->preferenceRepository->findInvestorsForEmailType('financial_report');
        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($preferences as $preference) {
            $investor = $this->accountRepository->findById($preference->getInvestorAccountId());
            
            if (!$investor) {
                $results['skipped']++;
                continue;
            }

            $log = InvestorEmailLog::create(
                investorAccountId: $investor->getId(),
                emailType: 'financial_report',
                subject: $title,
                referenceId: $reportId,
                referenceType: 'financial_report'
            );

            try {
                $this->sendEmail(
                    to: $investor->getEmail(),
                    subject: "[MyGrowNet] {$title}",
                    template: 'emails.investor.financial-report',
                    data: [
                        'investorName' => $investor->getName(),
                        'title' => $title,
                        'reportType' => $reportType,
                        'reportPeriod' => $reportPeriod,
                        'highlights' => $highlights,
                        'reportId' => $reportId,
                    ]
                );

                $log->markAsSent();
                $results['sent']++;
            } catch (\Exception $e) {
                $log->markAsFailed($e->getMessage());
                $results['failed']++;
                Log::error("Failed to send report email to {$investor->getEmail()}: {$e->getMessage()}");
            }

            $this->emailLogRepository->save($log);
        }

        return $results;
    }

    /**
     * Send message notification to investor
     */
    public function sendMessageNotification(
        int $investorAccountId,
        int $messageId,
        string $subject,
        string $preview
    ): bool {
        $preference = $this->preferenceRepository->findByInvestorAccountId($investorAccountId);
        
        if ($preference && !$preference->shouldReceiveEmail('message')) {
            return false;
        }

        $investor = $this->accountRepository->findById($investorAccountId);
        
        if (!$investor) {
            return false;
        }

        $log = InvestorEmailLog::create(
            investorAccountId: $investorAccountId,
            emailType: 'message',
            subject: "New Message: {$subject}",
            referenceId: $messageId,
            referenceType: 'message'
        );

        try {
            $this->sendEmail(
                to: $investor->getEmail(),
                subject: "[MyGrowNet] New Message: {$subject}",
                template: 'emails.investor.message',
                data: [
                    'investorName' => $investor->getName(),
                    'subject' => $subject,
                    'preview' => $preview,
                    'messageId' => $messageId,
                ]
            );

            $log->markAsSent();
            $this->emailLogRepository->save($log);
            return true;
        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());
            $this->emailLogRepository->save($log);
            Log::error("Failed to send message notification to {$investor->getEmail()}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Track email open
     */
    public function trackOpen(int $emailLogId): void
    {
        $log = $this->emailLogRepository->findById($emailLogId);
        
        if ($log) {
            $log->markAsOpened();
            $this->emailLogRepository->save($log);
        }
    }

    /**
     * Track email click
     */
    public function trackClick(int $emailLogId): void
    {
        $log = $this->emailLogRepository->findById($emailLogId);
        
        if ($log) {
            $log->markAsClicked();
            $this->emailLogRepository->save($log);
        }
    }

    /**
     * Get email analytics
     */
    public function getAnalytics(?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array
    {
        $stats = $this->emailLogRepository->getStatistics($from, $to);

        // Add per-type statistics
        $types = ['announcement', 'financial_report', 'dividend', 'meeting', 'message'];
        $byType = [];

        foreach ($types as $type) {
            $byType[$type] = [
                'open_rate' => $this->emailLogRepository->getOpenRateByType($type),
                'click_rate' => $this->emailLogRepository->getClickRateByType($type),
            ];
        }

        $stats['by_type'] = $byType;

        return $stats;
    }

    /**
     * Send email using Laravel Mail
     */
    private function sendEmail(string $to, string $subject, string $template, array $data): void
    {
        // Check if mail is configured
        if (!config('mail.default') || config('mail.default') === 'log') {
            Log::info("Email would be sent to {$to}: {$subject}", $data);
            return;
        }

        Mail::send($template, $data, function ($message) use ($to, $subject) {
            $message->to($to)
                ->subject($subject)
                ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }
}
