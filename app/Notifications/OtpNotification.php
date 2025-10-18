<?php

namespace App\Notifications;

use App\Models\OtpToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected OtpToken $otpToken;

    public function __construct(OtpToken $otpToken)
    {
        $this->otpToken = $otpToken;
    }

    public function via($notifiable): array
    {
        $channels = [];
        
        if ($this->otpToken->type === 'email') {
            $channels[] = 'mail';
        }
        
        if ($this->otpToken->type === 'sms') {
            // Add SMS channel when implemented
            // $channels[] = 'sms';
            Log::info('SMS OTP would be sent', [
                'user_id' => $notifiable->id,
                'phone' => $this->otpToken->identifier,
                'token' => $this->otpToken->token
            ]);
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $purposeText = $this->getPurposeText($this->otpToken->purpose);
        $expiryMinutes = now()->diffInMinutes($this->otpToken->expires_at);

        return (new MailMessage)
            ->subject('VBIF - Your Verification Code')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("You have requested a verification code for {$purposeText}.")
            ->line("Your verification code is: **{$this->otpToken->token}**")
            ->line("This code will expire in {$expiryMinutes} minutes.")
            ->line('If you did not request this code, please ignore this email or contact support if you have concerns.')
            ->line('For security reasons, never share this code with anyone.')
            ->salutation('Best regards, VBIF Security Team');
    }

    public function toSms($notifiable): string
    {
        $purposeText = $this->getPurposeText($this->otpToken->purpose);
        $expiryMinutes = now()->diffInMinutes($this->otpToken->expires_at);

        return "VBIF: Your verification code for {$purposeText} is {$this->otpToken->token}. Valid for {$expiryMinutes} minutes. Never share this code.";
    }

    protected function getPurposeText(string $purpose): string
    {
        return match($purpose) {
            'withdrawal' => 'withdrawal request',
            'sensitive_operation' => 'sensitive account operation',
            'login' => 'account login',
            'tier_upgrade' => 'tier upgrade',
            'profile_update' => 'profile update',
            default => 'account verification'
        };
    }

    public function toArray($notifiable): array
    {
        return [
            'otp_token_id' => $this->otpToken->id,
            'type' => $this->otpToken->type,
            'purpose' => $this->otpToken->purpose,
            'expires_at' => $this->otpToken->expires_at,
            'identifier' => $this->maskIdentifier($this->otpToken->identifier, $this->otpToken->type)
        ];
    }

    protected function maskIdentifier(string $identifier, string $type): string
    {
        if ($type === 'sms') {
            return substr($identifier, 0, 4) . str_repeat('*', 4) . substr($identifier, -4);
        } else {
            $parts = explode('@', $identifier);
            if (count($parts) === 2) {
                $username = $parts[0];
                $domain = $parts[1];
                $maskedUsername = substr($username, 0, 1) . str_repeat('*', max(0, strlen($username) - 1));
                return $maskedUsername . '@' . $domain;
            }
        }
        
        return $identifier;
    }
}