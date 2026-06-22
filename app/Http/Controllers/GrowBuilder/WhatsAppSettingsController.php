<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\WhatsAppCloudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsController extends Controller
{
    private WhatsAppCloudService $whatsapp;

    public function __construct(WhatsAppCloudService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function toggle(GrowBuilderSite $site, Request $request)
    {
        $enabled = $request->boolean('enabled', false);

        $settings = $site->settings ?? [];
        $settings['whatsapp_enabled'] = $enabled;
        $settings['whatsapp_phone_number_id'] = config('services.whatsapp.phone_number_id', '');

        $site->update(['settings' => $settings]);

        // Sync the chatbot enabled state with WhatsApp
        if ($enabled && !($settings['chatbot_enabled'] ?? false)) {
            $settings['chatbot_enabled'] = true;
            $site->update(['settings' => $settings]);
        }

        return response()->json(['success' => true, 'enabled' => $enabled]);
    }

    public function status()
    {
        $configured = $this->whatsapp->isConfigured();
        $profile = $configured ? $this->whatsapp->getBusinessProfile() : null;

        $businessPhone = '';
        $businessName = '';
        if ($profile && isset($profile['data'][0])) {
            $data = $profile['data'][0];
            $businessPhone = $data['websites'][0] ?? '';
            $businessName = $data['description'] ?? '';
        }

        return response()->json([
            'configured' => $configured,
            'phone_number_id' => config('services.whatsapp.phone_number_id'),
            'business_name' => $businessName,
        ]);
    }

    public function testMessage(GrowBuilderSite $site, Request $request)
    {
        $phone = $request->input('phone');
        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'Phone number is required'], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            return response()->json(['success' => false, 'message' => 'Invalid phone number'], 422);
        }

        $result = $this->whatsapp->sendTextMessage($phone, "Hello from {$site->name}! This is a test message from your website dashboard.");

        if ($result) {
            return response()->json(['success' => true, 'message' => 'Test message sent!']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to send test message. Check WhatsApp configuration.'], 500);
    }
}
