<?php

namespace App\Services\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\EmailLogModel;
use App\Infrastructure\Persistence\Eloquent\CMS\EmailUnsubscribeModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Configure mailer for company
     */
    public function configureMailerForCompany(CompanyModel $company): void
    {
        if ($company->email_provider === 'custom' && $company->smtp_host) {
            // Use company's custom SMTP
            Config::set('mail.mailers.cms_custom', [
                'transport' => 'smtp',
                'host' => $company->smtp_host,
                'port' => $company->smtp_port,
                'encryption' => $company->smtp_encryption,
                'username' => $company->smtp_username,
                'password' => $company->smtp_password ? Crypt::decryptString($company->smtp_password) : null,
                'timeout' => null,
            ]);

            Config::set('mail.from', [
                'address' => $company->email_from_address ?? $company->email,
                'name' => $company->email_from_name ?? $company->name,
            ]);
        } else {
            // Use platform email (default Laravel config)
            Config::set('mail.from', [
                'address' => config('mail.from.address'),
                'name' => $company->name,
            ]);
        }
    }

    /**
     * Send email with tracking
     */
    public function sendEmail(
        CompanyModel $company,
        string $to,
        string $subject,
        string $view,
        array $data,
        string $emailType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $attachmentPath = null
    ): bool {
        // Check if recipient has unsubscribed
        if ($this->isUnsubscribed($company->id, $to, $emailType)) {
            Log::info("Email not sent - recipient unsubscribed", [
                'company_id' => $company->id,
                'email' => $to,
                'type' => $emailType,
            ]);
            return false;
        }

        // Configure mailer
        $this->configureMailerForCompany($company);

        // Create email log
        $log = EmailLogModel::create([
            'company_id' => $company->id,
            'email_type' => $emailType,
            'recipient_email' => $to,
            'recipient_name' => $data['recipient_name'] ?? null,
            'subject' => $subject,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => 'queued',
            'provider' => $company->email_provider,
        ]);

        try {
            $mailer = $company->email_provider === 'custom' ? 'cms_custom' : config('mail.default');
            
            Mail::mailer($mailer)->send($view, $data, function ($message) use ($to, $subject, $company, $attachmentPath) {
                $message->to($to)
                    ->subject($subject)
                    ->replyTo($company->email_reply_to ?? $company->email);

                if ($attachmentPath && file_exists($attachmentPath)) {
                    $message->attach($attachmentPath);
                }
            });

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Log::info("Email sent successfully", [
                'company_id' => $company->id,
                'email' => $to,
                'type' => $emailType,
                'log_id' => $log->id,
            ]);

            return true;
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Email sending failed", [
                'company_id' => $company->id,
                'email' => $to,
                'type' => $emailType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Test SMTP connection
     */
    public function testSmtpConnection(array $config): array
    {
        try {
            // Create a test transport
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $config['host'],
                $config['port'],
                $config['encryption'] !== 'none' ? $config['encryption'] === 'ssl' : false
            );

            if ($config['encryption'] === 'tls') {
                $transport->setStreamOptions([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);
            }

            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);

            // Try to start the transport
            $transport->start();
            $transport->stop();

            return ['success' => true, 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check if email is unsubscribed
     */
    public function isUnsubscribed(int $companyId, string $email, string $emailType): bool
    {
        return EmailUnsubscribeModel::where('company_id', $companyId)
            ->where('email_address', $email)
            ->where(function ($query) use ($emailType) {
                $query->where('unsubscribe_type', 'all')
                    ->orWhere(function ($q) use ($emailType) {
                        if (in_array($emailType, ['reminder', 'overdue'])) {
                            $q->where('unsubscribe_type', 'reminders');
                        }
                    });
            })
            ->exists();
    }

    /**
     * Get email logs for company
     */
    public function getEmailLogs(int $companyId, array $filters = [])
    {
        $query = EmailLogModel::where('company_id', $companyId);

        if (isset($filters['type'])) {
            $query->where('email_type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('recipient_email', 'like', "%{$filters['search']}%")
                    ->orWhere('subject', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate(50);
    }

    /**
     * Get email statistics for company
     */
    public function getEmailStats(int $companyId): array
    {
        $total = EmailLogModel::where('company_id', $companyId)->count();
        $sent = EmailLogModel::where('company_id', $companyId)->where('status', 'sent')->count();
        $failed = EmailLogModel::where('company_id', $companyId)->where('status', 'failed')->count();
        $queued = EmailLogModel::where('company_id', $companyId)->where('status', 'queued')->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'queued' => $queued,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
        ];
    }
}
