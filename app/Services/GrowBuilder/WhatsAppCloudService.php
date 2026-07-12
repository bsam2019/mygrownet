<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudService
{
    private string $token;
    private string $phoneNumberId;
    private string $apiVersion;
    private string $wabaId;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->wabaId = config('services.whatsapp.waba_id', '');
        $this->apiVersion = config('services.whatsapp.api_version', 'v25.0');
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->phoneNumberId);
    }

    public function sendTextMessage(string $to, string $text, ?bool $previewUrl = false): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('WhatsApp not configured');
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => $previewUrl,
                'body' => $text,
            ],
        ]);

        if (!$response->successful()) {
            Log::error('WhatsApp send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to,
            ]);
            return null;
        }

        return $response->json();
    }

    public function sendTemplateMessage(string $to, string $templateName, array $params = [], string $languageCode = 'en'): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('WhatsApp not configured');
            return null;
        }

        $components = [
            'type' => 'body',
            'parameters' => [],
        ];
        foreach ($params as $param) {
            $components['parameters'][] = ['type' => 'text', 'text' => $param];
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => empty($params) ? [] : [$components],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('WhatsApp template send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'template' => $templateName,
            ]);
            return null;
        }

        return $response->json();
    }

    public function sendInteractiveButtons(string $to, string $text, array $buttons): ?array
    {
        if (!$this->isConfigured()) return null;

        $rows = [];
        foreach ($buttons as $i => $btn) {
            $rows[] = [
                'id' => $btn['id'] ?? 'btn_' . $i,
                'title' => $btn['title'],
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $text],
                'action' => ['buttons' => $rows],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('WhatsApp interactive send failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $response->json();
    }

    public function markAsRead(string $messageId): bool
    {
        if (!$this->isConfigured()) return false;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->timeout(10)->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ]);

        return $response->successful();
    }

    public function getBusinessProfile(): ?array
    {
        if (!$this->isConfigured()) return null;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->timeout(10)->get("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/whatsapp_business_profile", [
            'fields' => 'about,address,description,email,websites,profile_picture_url',
        ]);

        if (!$response->successful()) return null;

        return $response->json();
    }

    public function updateBusinessProfile(array $data): bool
    {
        if (!$this->isConfigured()) return false;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->timeout(10)->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/whatsapp_business_profile", $data);

        return $response->successful();
    }

    public static function parseIncomingMessage(array $payload): ?array
    {
        $entry = $payload['entry'][0] ?? null;
        $change = $entry['changes'][0] ?? null;
        $value = $change['value'] ?? null;

        if (!$value) return null;

        $metadata = $value['metadata'] ?? [];
        $contacts = $value['contacts'] ?? [];
        $messages = $value['messages'] ?? [];

        if (empty($messages) || empty($contacts)) return null;

        $message = $messages[0];
        $contact = $contacts[0];

        $from = $message['from'] ?? '';
        $fromName = $contact['profile']['name'] ?? 'Unknown';
        $messageId = $message['id'] ?? '';
        $timestamp = $message['timestamp'] ?? '';

        $text = '';
        $messageType = $message['type'] ?? 'text';

        if ($messageType === 'text') {
            $text = $message['text']['body'] ?? '';
        } elseif ($messageType === 'interactive') {
            $interactive = $message['interactive'] ?? [];
            $text = $interactive['button_reply']['title'] ?? $interactive['list_reply']['title'] ?? '';
        } elseif ($messageType === 'button') {
            $text = $message['button']['text'] ?? '';
        }

        return [
            'from' => $from,
            'from_name' => $fromName,
            'message_id' => $messageId,
            'timestamp' => $timestamp,
            'text' => $text,
            'type' => $messageType,
            'phone_number_id' => $metadata['phone_number_id'] ?? '',
            'raw' => $message,
        ];
    }
}
