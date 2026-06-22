<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\AISiteChatbotService;
use App\Services\GrowBuilder\WhatsAppCloudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    private WhatsAppCloudService $whatsapp;
    private AISiteChatbotService $chatbot;

    public function __construct(WhatsAppCloudService $whatsapp, AISiteChatbotService $chatbot)
    {
        $this->whatsapp = $whatsapp;
        $this->chatbot = $chatbot;
    }

    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token', 'mygrownet_verify');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('WhatsApp webhook received', ['payload' => $payload]);

        $parsed = WhatsAppCloudService::parseIncomingMessage($payload);

        if (!$parsed || empty($parsed['text'])) {
            return response('OK', 200);
        }

        $this->whatsapp->markAsRead($parsed['message_id']);

        $from = $parsed['from'];
        $text = $parsed['text'];

        $site = $this->resolveSite($parsed);

        if (!$site || !($site->settings['whatsapp_enabled'] ?? false)) {
            $this->whatsapp->sendTextMessage($from, "Thank you for your message! We'll get back to you soon.");
            return response('OK', 200);
        }

        try {
            $answer = $this->chatbot->answer($site, $text);

            if ($answer['has_answer'] ?? false) {
                $this->whatsapp->sendTextMessage($from, $answer['answer']);
            } elseif ($answer['capture_lead'] ?? false) {
                $this->whatsapp->sendTextMessage($from, "I couldn't find an answer to your question. Could you please leave your email so our team can follow up?");
            } else {
                $fallback = $answer['answer'] ?? "Thanks for reaching out about {$site->name}. A team member will get back to you.";
                $this->whatsapp->sendTextMessage($from, $fallback);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp chatbot error', ['error' => $e->getMessage()]);
            $this->whatsapp->sendTextMessage($from, "Thanks for your message! We'll respond shortly.");
        }

        return response('OK', 200);
    }

    private function resolveSite(array $parsed): ?GrowBuilderSite
    {
        $phoneNumberId = $parsed['phone_number_id'] ?? '';

        return GrowBuilderSite::where('settings->whatsapp_phone_number_id', $phoneNumberId)
            ->orWhere('settings->whatsapp_enabled', true)
            ->first();
    }
}
