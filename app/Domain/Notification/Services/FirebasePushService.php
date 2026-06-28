<?php

declare(strict_types=1);

namespace App\Domain\Notification\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Firebase Cloud Messaging Service
 * Sends push notifications via Firebase HTTP v1 API
 */
class FirebasePushService
{
    private ?string $projectId;
    private ?array $serviceAccount = null;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $credentialsPath = config('services.firebase.credentials', '');
        
        if ($credentialsPath && file_exists($credentialsPath)) {
            $this->serviceAccount = json_decode(file_get_contents($credentialsPath), true);
        }
    }
    
    /**
     * Check if Firebase is configured
     */
    private function isConfigured(): bool
    {
        return !empty($this->projectId);
    }

    /**
     * Send push notification to a specific user
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Firebase not configured, skipping push notification', ['user_id' => $userId]);
            return false;
        }
        
        $tokens = $this->getUserTokens($userId);
        
        if (empty($tokens)) {
            Log::info('No active devices for user', ['user_id' => $userId]);
            return false;
        }

        $success = false;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Send push notification to a specific device token
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            Log::error('Failed to get Firebase access token');
            return false;
        }

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
                'webpush' => [
                    'notification' => [
                        'icon' => '/icons/icon-192x192.png',
                        'badge' => '/icons/badge-72x72.png',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message);

            if ($response->successful()) {
                Log::info('Push notification sent', ['token' => substr($token, 0, 20) . '...']);
                return true;
            }

            $error = $response->json();
            Log::error('FCM send failed', ['error' => $error, 'status' => $response->status()]);

            // Handle invalid token
            if ($response->status() === 404 || 
                ($error['error']['details'][0]['errorCode'] ?? '') === 'UNREGISTERED') {
                $this->removeInvalidToken($token);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('FCM exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): int
    {
        $successCount = 0;
        
        foreach ($userIds as $userId) {
            if ($this->sendToUser($userId, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return false;
        }

        $message = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $message);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM topic send failed', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get user's active device tokens
     */
    private function getUserTokens(int $userId): array
    {
        return DB::table('user_devices')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    /**
     * Remove invalid token from database
     */
    private function removeInvalidToken(string $token): void
    {
        DB::table('user_devices')
            ->where('token', $token)
            ->update(['is_active' => false]);
    }

    /**
     * Get OAuth2 access token for Firebase
     */
    private function getAccessToken(): ?string
    {
        if (!$this->serviceAccount) {
            // Fallback to legacy API key if no service account
            return config('services.firebase.server_key');
        }

        $cacheKey = 'firebase_access_token';
        
        return Cache::remember($cacheKey, 3500, function () {
            return $this->generateAccessToken();
        });
    }

    /**
     * Generate new access token using service account
     */
    private function generateAccessToken(): ?string
    {
        if (!$this->serviceAccount) {
            return null;
        }

        try {
            $now = time();
            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            
            $payload = base64_encode(json_encode([
                'iss' => $this->serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signature = '';
            $privateKey = openssl_pkey_get_private($this->serviceAccount['private_key']);
            openssl_sign("$header.$payload", $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $signature = base64_encode($signature);

            $jwt = "$header.$payload.$signature";

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Failed to get Firebase access token', ['response' => $response->json()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Firebase token generation failed', ['message' => $e->getMessage()]);
            return null;
        }
    }
}