<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\OmnichannelRouter;
use App\Domain\BizBoost\Services\BillingLedgerService;
use App\Infrastructure\Persistence\Eloquent\BizBoostOmnichannelLogModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OmnichannelController extends Controller
{
    private OmnichannelRouter $router;
    private BillingLedgerService $ledger;

    public function __construct(OmnichannelRouter $router, BillingLedgerService $ledger)
    {
        $this->router = $router;
        $this->ledger = $ledger;
    }

    public function index()
    {
        $user = auth()->user();
        $logs = BizBoostOmnichannelLogModel::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $all = BizBoostOmnichannelLogModel::where('user_id', $user->id);

        $stats = [
            'total' => (clone $all)->count(),
            'delivered' => (clone $all)->where('delivery_status', 'delivered')->count(),
            'failed' => (clone $all)->where('delivery_status', 'failed')->count(),
            'whatsapp' => (clone $all)->where('channel_type', 'whatsapp')->count(),
            'sms' => (clone $all)->where('channel_type', 'sms')->count(),
            'total_profit' => (float) (clone $all)->sum('net_platform_profit'),
        ];

        return Inertia::render('BizBoost/Omnichannel/Index', [
            'logs' => $logs,
            'stats' => $stats,
            'channels' => [
                ['value' => 'whatsapp', 'label' => 'WhatsApp'],
                ['value' => 'sms', 'label' => 'SMS'],
            ],
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|string',
            'message' => 'required|string|max:4096',
            'channel' => 'nullable|in:whatsapp,sms',
        ]);

        $user = auth()->user();

        $result = $this->router->send(
            userId: $user->id,
            phone: $validated['recipient'],
            message: $validated['message'],
            options: [
                'prefer_channel' => $validated['channel'] ?? 'whatsapp',
                'business_id' => $request->input('business_id'),
            ],
        );

        if ($result['success']) {
            return redirect()->back()->with('success', "Message sent via {$result['channel']}");
        }

        return redirect()->back()->with('error', 'Failed to send message: ' . ($result['error'] ?? 'unknown error'));
    }
}
