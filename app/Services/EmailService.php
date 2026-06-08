<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Email Service - Manages multiple email providers to maximize free tiers
 * 
 * Strategy:
 * - Resend: Transactional emails (receipts, notifications, password resets) - 3,000/month
 * - Brevo: Marketing emails (newsletters, campaigns, announcements) - 9,000/month (300/day)
 * - SES: Bulk emails (reports, mass notifications) - 62,000/month
 * 
 * Total FREE capacity: ~74,000 emails/month
 */
class EmailService
{
    /**
     * Send transactional email via Resend (fast delivery, 3,000/month free)
     * Use for: Order confirmations, receipts, password resets, account notifications
     */
    public static function sendTransactional($mailable, $recipient, ?string $subject = null)
    {
        return self::sendWithTracking('resend', 'transactional', $mailable, $recipient, $subject);
    }

    /**
     * Send marketing email via Brevo (300/day free, 9,000/month)
     * Use for: Newsletters, promotional campaigns, announcements
     */
    public static function sendMarketing($mailable, $recipient, ?string $subject = null)
    {
        return self::sendWithTracking('brevo-api', 'marketing', $mailable, $recipient, $subject);
    }

    /**
     * Send bulk email via default mailer
     * Use for: Mass notifications, reports, system-wide announcements
     */
    public static function sendBulk($mailable, $recipients, ?string $subject = null)
    {
        // For bulk, track each recipient separately
        $recipientList = is_array($recipients) ? $recipients : [$recipients];
        
        foreach ($recipientList as $recipient) {
            self::sendWithTracking(config('mail.default'), 'bulk', $mailable, $recipient, $subject);
        }
        
        return true;
    }

    /**
     * Send email with automatic provider selection based on type
     */
    public static function send($mailable, $recipient, string $type = 'transactional', ?string $subject = null)
    {
        return match($type) {
            'marketing' => self::sendMarketing($mailable, $recipient, $subject),
            'bulk' => self::sendBulk($mailable, $recipient, $subject),
            default => self::sendTransactional($mailable, $recipient, $subject),
        };
    }

    /**
     * Internal method to send email with usage tracking
     */
    private static function sendWithTracking(string $provider, string $type, $mailable, $recipient, ?string $subject = null)
    {
        $recipientEmail = is_string($recipient) ? $recipient : $recipient->email ?? 'unknown';
        $status = 'sent';
        $errorMessage = null;

        try {
            Mail::mailer($provider)
                ->to($recipient)
                ->send($mailable);
        } catch (\Exception $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            Log::error("Email sending failed", [
                'provider' => $provider,
                'type' => $type,
                'recipient' => $recipientEmail,
                'error' => $errorMessage,
            ]);
            throw $e; // Re-throw to let caller handle
        } finally {
            // Track usage
            self::trackUsage($provider, $type, $recipientEmail, $subject, $status, $errorMessage);
        }

        return true;
    }

    /**
     * Track email usage in database
     */
    private static function trackUsage(string $provider, string $type, string $recipient, ?string $subject, string $status, ?string $errorMessage)
    {
        try {
            DB::table('email_usage')->insert([
                'provider' => $provider,
                'type' => $type,
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => $status,
                'error_message' => $errorMessage,
                'sent_at' => now(),
                'date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Don't fail email sending if tracking fails
            Log::error("Email usage tracking failed", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get current usage stats
     */
    public static function getUsageStats(string $period = 'month'): array
    {
        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $usage = DB::table('email_usage')
            ->where('date', '>=', $startDate->toDateString())
            ->where('status', 'sent')
            ->select('provider', DB::raw('count(*) as count'))
            ->groupBy('provider')
            ->get()
            ->keyBy('provider');

        return [
            'resend' => [
                'limit' => 3000,
                'used' => $usage->get('resend')->count ?? 0,
                'remaining' => 3000 - ($usage->get('resend')->count ?? 0),
                'period' => 'month',
            ],
            'brevo-api' => [
                'limit' => $period === 'today' ? 300 : 9000,
                'used' => $usage->get('brevo-api')->count ?? 0,
                'remaining' => ($period === 'today' ? 300 : 9000) - ($usage->get('brevo-api')->count ?? 0),
                'period' => $period === 'today' ? 'day' : 'month',
            ],
            'ses' => [
                'limit' => 62000,
                'used' => $usage->get('ses')->count ?? 0,
                'remaining' => 62000 - ($usage->get('ses')->count ?? 0),
                'period' => 'month',
            ],
        ];
    }

    /**
     * Get detailed usage breakdown by type
     */
    public static function getUsageByType(string $period = 'month'): array
    {
        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        return DB::table('email_usage')
            ->where('date', '>=', $startDate->toDateString())
            ->select('type', 'provider', DB::raw('count(*) as count'))
            ->groupBy('type', 'provider')
            ->get()
            ->groupBy('type')
            ->toArray();
    }

    /**
     * Check if we're approaching limits
     */
    public static function checkLimits(): array
    {
        $stats = self::getUsageStats('month');
        $alerts = [];

        foreach ($stats as $provider => $data) {
            $percentUsed = ($data['used'] / $data['limit']) * 100;
            
            if ($percentUsed >= 90) {
                $alerts[] = [
                    'provider' => $provider,
                    'level' => 'critical',
                    'message' => "{$provider} is at {$percentUsed}% capacity ({$data['used']}/{$data['limit']})",
                ];
            } elseif ($percentUsed >= 75) {
                $alerts[] = [
                    'provider' => $provider,
                    'level' => 'warning',
                    'message' => "{$provider} is at {$percentUsed}% capacity ({$data['used']}/{$data['limit']})",
                ];
            }
        }

        return $alerts;
    }
}
