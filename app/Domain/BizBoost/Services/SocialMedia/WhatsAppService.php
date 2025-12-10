<?php

namespace App\Domain\BizBoost\Services\SocialMedia;

use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService implements SocialMediaServiceInterface
{
    private const API_VERSION = 'v18.0';
    private const BASE_URL = 'https://graph.facebook.com';

    public function __construct(
        private ?BizBoostIntegrationModel $integration = null
    ) {}

    public function getAuthUrl(string $redirectUri, array $scopes = []): string
    {
        // WhatsApp Business uses Facebook OAuth
        $defaultScopes = [
            'whatsapp_business_management',
            'whatsapp_business_messaging',
        ];

        $scopes = array_merge($defaultScopes, $scopes);

        $params = http_build_query([
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);

        return "https://www.facebook.com/" . self::API_VERSION . "/dialog/oauth?{$params}";
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . '/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange code for token: ' . $response->body());
        }

        return $response->json();
    }

    public function getWhatsAppBusinessAccounts(string $accessToken): array
    {
        $response = Http::get(
            self::BASE_URL . '/' . self::API_VERSION . '/me/businesses',
            [
                'fields' => 'client_whatsapp_business_accounts',
                'access_token' => $accessToken,
            ]
        );

        if ($response->failed()) {
            return [];
        }

        return $response->json()['data'] ?? [];
    }

    public function getPhoneNumbers(string $wabaId, string $accessToken): array
    {
        $response = Http::get(
            self::BASE_URL . '/' . self::API_VERSION . "/{$wabaId}/phone_numbers",
            [
                'access_token' => $accessToken,
            ]
        );

        if ($response->failed()) {
            return [];
        }

        return $response->json()['data'] ?? [];
    }

    public function publishPost(BizBoostPostModel $post): array
    {
        // WhatsApp doesn't support traditional "posts" like social media
        // This would be used for broadcast messages to customer lists
        throw new \Exception('WhatsApp does not support post publishing. Use sendMessage() for broadcasts.');
    }

    public function sendMessage(string $to, string $message, ?string $mediaUrl = null, string $mediaType = 'text'): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $phoneNumberId = $this->integration->provider_page_id; // Phone number ID
        $accessToken = $this->integration->access_token;

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
        ];

        if ($mediaUrl) {
            $data['type'] = $mediaType; // image, video, document
            $data[$mediaType] = [
                'link' => $mediaUrl,
                'caption' => $message,
            ];
        } else {
            $data['type'] = 'text';
            $data['text'] = ['body' => $message];
        }

        $response = Http::withToken($accessToken)
            ->post(
                self::BASE_URL . '/' . self::API_VERSION . "/{$phoneNumberId}/messages",
                $data
            );

        if ($response->failed()) {
            Log::error('WhatsApp message failed', [
                'response' => $response->body(),
                'to' => $to,
            ]);
            throw new \Exception('Failed to send message: ' . $response->body());
        }

        return $response->json();
    }

    public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $phoneNumberId = $this->integration->provider_page_id;
        $accessToken = $this->integration->access_token;

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'en'],
            ],
        ];

        if (!empty($parameters)) {
            $data['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => array_map(fn($param) => ['type' => 'text', 'text' => $param], $parameters),
                ],
            ];
        }

        $response = Http::withToken($accessToken)
            ->post(
                self::BASE_URL . '/' . self::API_VERSION . "/{$phoneNumberId}/messages",
                $data
            );

        if ($response->failed()) {
            throw new \Exception('Failed to send template message: ' . $response->body());
        }

        return $response->json();
    }

    public function sendBulkMessages(array $recipients, string $message, ?string $mediaUrl = null): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            try {
                $results[$recipient] = $this->sendMessage($recipient, $message, $mediaUrl);
            } catch (\Exception $e) {
                $results[$recipient] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function deletePost(string $postId): bool
    {
        // WhatsApp messages cannot be deleted once sent
        return false;
    }

    public function getPostAnalytics(string $postId): array
    {
        // WhatsApp doesn't provide post analytics in the traditional sense
        // You can get message delivery status
        return [];
    }

    public function getMessageStatus(string $messageId): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $response = Http::withToken($this->integration->access_token)
            ->get(self::BASE_URL . '/' . self::API_VERSION . "/{$messageId}");

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }

    public function refreshToken(): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        // WhatsApp uses Facebook's token refresh
        $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . '/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'fb_exchange_token' => $this->integration->access_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        return $response->json();
    }

    public function validateToken(): bool
    {
        if (!$this->integration) {
            return false;
        }

        $response = Http::withToken($this->integration->access_token)
            ->get(self::BASE_URL . '/' . self::API_VERSION . '/' . $this->integration->provider_page_id);

        return $response->successful();
    }

    public function registerWebhook(string $callbackUrl, string $verifyToken): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $wabaId = $this->integration->provider_user_id;

        $response = Http::withToken($this->integration->access_token)
            ->post(
                self::BASE_URL . '/' . self::API_VERSION . "/{$wabaId}/subscribed_apps",
                [
                    'callback_url' => $callbackUrl,
                    'verify_token' => $verifyToken,
                ]
            );

        if ($response->failed()) {
            throw new \Exception('Failed to register webhook: ' . $response->body());
        }

        return $response->json();
    }
}
