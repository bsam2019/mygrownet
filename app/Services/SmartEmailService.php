<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmartEmailService
{
    /**
     * Get the best available mailer based on daily limits
     */
    public function getAvailableMailer(): string
    {
        $today = date('Y-m-d');
        
        // Check Brevo usage (300/day limit)
        $brevoCount = Cache::get("email_count_brevo_{$today}", 0);
        if ($brevoCount < 300) {
            Cache::increment("email_count_brevo_{$today}");
            Cache::put("email_count_brevo_{$today}", $brevoCount + 1, now()->endOfDay());
            
            Log::info("Using Brevo mailer", [
                'count' => $brevoCount + 1,
                'limit' => 300,
                'remaining' => 299 - $brevoCount
            ]);
            
            return 'brevo';
        }
        
        // Brevo limit reached, check Gmail usage (500/day limit)
        $gmailCount = Cache::get("email_count_gmail_{$today}", 0);
        if ($gmailCount < 500) {
            Cache::increment("email_count_gmail_{$today}");
            Cache::put("email_count_gmail_{$today}", $gmailCount + 1, now()->endOfDay());
            
            Log::info("Using Gmail mailer (Brevo limit reached)", [
                'count' => $gmailCount + 1,
                'limit' => 500,
                'remaining' => 499 - $gmailCount
            ]);
            
            return 'gmail';
        }
        
        // Both limits reached - log critical warning
        Log::critical('Daily email limit reached for ALL providers', [
            'brevo_used' => $brevoCount,
            'gmail_used' => $gmailCount,
            'total_used' => $brevoCount + $gmailCount,
            'date' => $today
        ]);
        
        // Fallback to Brevo (will likely fail but at least we try)
        return 'brevo';
    }
    
    /**
     * Send email using best available provider
     */
    public function send($mailable, $recipient, $mailer = null)
    {
        try {
            // Use specified mailer or get best available
            $selectedMailer = $mailer ?? $this->getAvailableMailer();
            
            Mail::mailer($selectedMailer)->to($recipient)->send($mailable);
            
            Log::info("Email sent successfully", [
                'mailer' => $selectedMailer,
                'recipient' => $recipient,
                'mailable' => get_class($mailable)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email", [
                'mailer' => $selectedMailer ?? 'unknown',
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            
            // Try alternate provider if primary fails
            if (!$mailer) {
                $alternateMailer = $selectedMailer === 'brevo' ? 'gmail' : 'brevo';
                
                try {
                    Mail::mailer($alternateMailer)->to($recipient)->send($mailable);
                    
                    Log::info("Email sent via fallback mailer", [
                        'primary_mailer' => $selectedMailer,
                        'fallback_mailer' => $alternateMailer,
                        'recipient' => $recipient
                    ]);
                    
                    return true;
                } catch (\Exception $fallbackError) {
                    Log::critical("All email providers failed", [
                        'primary_error' => $e->getMessage(),
                        'fallback_error' => $fallbackError->getMessage(),
                        'recipient' => $recipient
                    ]);
                    
                    return false;
                }
            }
            
            return false;
        }
    }
    
    /**
     * Get current usage statistics
     */
    public function getUsageStats(): array
    {
        $today = date('Y-m-d');
        
        $brevoUsed = Cache::get("email_count_brevo_{$today}", 0);
        $gmailUsed = Cache::get("email_count_gmail_{$today}", 0);
        $totalUsed = $brevoUsed + $gmailUsed;
        
        return [
            'date' => $today,
            'brevo' => [
                'used' => $brevoUsed,
                'limit' => 300,
                'remaining' => max(0, 300 - $brevoUsed),
                'percentage' => round(($brevoUsed / 300) * 100, 1),
            ],
            'gmail' => [
                'used' => $gmailUsed,
                'limit' => 500,
                'remaining' => max(0, 500 - $gmailUsed),
                'percentage' => round(($gmailUsed / 500) * 100, 1),
            ],
            'total' => [
                'used' => $totalUsed,
                'limit' => 800,
                'remaining' => max(0, 800 - $totalUsed),
                'percentage' => round(($totalUsed / 800) * 100, 1),
            ],
        ];
    }
    
    /**
     * Reset daily counters (for testing or manual reset)
     */
    public function resetCounters(): void
    {
        $today = date('Y-m-d');
        
        Cache::forget("email_count_brevo_{$today}");
        Cache::forget("email_count_gmail_{$today}");
        
        Log::info("Email counters reset for {$today}");
    }
    
    /**
     * Check if we can send more emails today
     */
    public function canSendEmail(): bool
    {
        $stats = $this->getUsageStats();
        return $stats['total']['remaining'] > 0;
    }
    
    /**
     * Get recommended mailer for specific email type
     */
    public function getRecommendedMailer(string $emailType): string
    {
        // Critical emails always use Brevo (better deliverability)
        $criticalTypes = [
            'investment_confirmation',
            'payment_receipt',
            'dividend_payment',
            'password_reset',
            'account_verification'
        ];
        
        if (in_array($emailType, $criticalTypes)) {
            $today = date('Y-m-d');
            $brevoCount = Cache::get("email_count_brevo_{$today}", 0);
            
            if ($brevoCount < 300) {
                return 'brevo';
            }
        }
        
        // For non-critical emails, use best available
        return $this->getAvailableMailer();
    }
}
