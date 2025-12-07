<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomerModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WhatsAppController extends Controller
{
    public function broadcasts(Request $request): Response
    {
        $business = $this->getBusiness($request);

        $broadcasts = DB::table('bizboost_whatsapp_broadcasts')
            ->where('business_id', $business->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $customerCount = $business->customers()->whereNotNull('whatsapp')->count();

        return Inertia::render('BizBoost/WhatsApp/Broadcasts', [
            'broadcasts' => $broadcasts,
            'customerCount' => $customerCount,
            'templates' => $this->getMessageTemplates(),
        ]);
    }

    public function createBroadcast(Request $request): Response
    {
        $business = $this->getBusiness($request);

        $customers = $business->customers()
            ->whereNotNull('whatsapp')
            ->where('is_active', true)
            ->get(['id', 'name', 'whatsapp']);

        $tags = $business->customerTags()->get();

        return Inertia::render('BizBoost/WhatsApp/CreateBroadcast', [
            'customers' => $customers,
            'tags' => $tags,
            'templates' => $this->getMessageTemplates(),
        ]);
    }

    public function storeBroadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:4096',
            'recipient_type' => 'required|in:all,selected,tagged',
            'customer_ids' => 'required_if:recipient_type,selected|array',
            'tag_ids' => 'required_if:recipient_type,tagged|array',
        ]);

        $business = $this->getBusiness($request);

        // Get recipient count
        $recipientCount = $this->getRecipientCount($business, $validated);

        // Store broadcast record
        $broadcastId = DB::table('bizboost_whatsapp_broadcasts')->insertGetId([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'message' => $validated['message'],
            'recipient_type' => $validated['recipient_type'],
            'recipient_filter' => json_encode([
                'customer_ids' => $validated['customer_ids'] ?? [],
                'tag_ids' => $validated['tag_ids'] ?? [],
            ]),
            'recipient_count' => $recipientCount,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('bizboost.whatsapp.broadcasts')
            ->with('success', "Broadcast created with {$recipientCount} recipients. Ready to send!");
    }

    public function exportCustomers(Request $request): StreamedResponse
    {
        $business = $this->getBusiness($request);

        $customers = $business->customers()
            ->whereNotNull('whatsapp')
            ->where('is_active', true)
            ->get(['name', 'whatsapp', 'email']);

        $filename = "whatsapp_contacts_{$business->slug}_" . now()->format('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            
            // Header row
            fputcsv($handle, ['Name', 'WhatsApp Number', 'Email']);
            
            // Data rows
            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->name,
                    $customer->whatsapp,
                    $customer->email ?? '',
                ]);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function templates(Request $request): Response
    {
        return Inertia::render('BizBoost/WhatsApp/Templates', [
            'templates' => $this->getMessageTemplates(),
        ]);
    }

    public function generateMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|string',
            'variables' => 'array',
        ]);

        $template = collect($this->getMessageTemplates())
            ->firstWhere('id', $validated['template_id']);

        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $message = $template['content'];
        
        // Replace variables
        foreach ($validated['variables'] ?? [] as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return response()->json(['message' => $message]);
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function getRecipientCount(BizBoostBusinessModel $business, array $data): int
    {
        $query = $business->customers()
            ->whereNotNull('whatsapp')
            ->where('is_active', true);

        switch ($data['recipient_type']) {
            case 'selected':
                return $query->whereIn('id', $data['customer_ids'] ?? [])->count();
            case 'tagged':
                return $query->whereHas('tags', function ($q) use ($data) {
                    $q->whereIn('bizboost_customer_tags.id', $data['tag_ids'] ?? []);
                })->count();
            default:
                return $query->count();
        }
    }

    private function getMessageTemplates(): array
    {
        return [
            [
                'id' => 'greeting',
                'name' => 'Greeting',
                'category' => 'general',
                'content' => "Hi {customer_name}! 👋\n\nThank you for being a valued customer of {business_name}. We appreciate your support!\n\nVisit us again soon!",
                'variables' => ['customer_name', 'business_name'],
            ],
            [
                'id' => 'new_arrival',
                'name' => 'New Arrival',
                'category' => 'promotion',
                'content' => "🆕 NEW ARRIVAL ALERT!\n\nHi {customer_name},\n\nWe have exciting new products just for you!\n\n✨ {product_name}\n💰 Only K{price}\n\nVisit us today or order via WhatsApp!\n\n{business_name}",
                'variables' => ['customer_name', 'product_name', 'price', 'business_name'],
            ],
            [
                'id' => 'discount',
                'name' => 'Discount Offer',
                'category' => 'promotion',
                'content' => "🔥 SPECIAL OFFER!\n\nHi {customer_name},\n\nGet {discount}% OFF on selected items!\n\n⏰ Valid until {expiry_date}\n\nDon't miss out!\n\n{business_name}",
                'variables' => ['customer_name', 'discount', 'expiry_date', 'business_name'],
            ],
            [
                'id' => 'thank_you',
                'name' => 'Thank You',
                'category' => 'follow_up',
                'content' => "Thank you for your purchase! 🙏\n\nHi {customer_name},\n\nWe hope you love your {product_name}!\n\nIf you have any questions, feel free to reach out.\n\nSee you again soon!\n{business_name}",
                'variables' => ['customer_name', 'product_name', 'business_name'],
            ],
            [
                'id' => 'reminder',
                'name' => 'Visit Reminder',
                'category' => 'follow_up',
                'content' => "Hi {customer_name}! 👋\n\nWe miss you at {business_name}!\n\nIt's been a while since your last visit. Come check out what's new!\n\n📍 {address}\n📞 {phone}",
                'variables' => ['customer_name', 'business_name', 'address', 'phone'],
            ],
            [
                'id' => 'birthday',
                'name' => 'Birthday Wish',
                'category' => 'special',
                'content' => "🎂 HAPPY BIRTHDAY {customer_name}! 🎉\n\nFrom all of us at {business_name}, we wish you a wonderful day!\n\n🎁 As a special gift, enjoy {discount}% OFF on your next purchase!\n\nValid for 7 days. Show this message to redeem.",
                'variables' => ['customer_name', 'business_name', 'discount'],
            ],
        ];
    }
}
