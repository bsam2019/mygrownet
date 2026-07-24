<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\OmnichannelRouter;
use App\Domain\BizBoost\Services\BillingLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $logs = DB::table('bizboost_omnichannel_logs')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = DB::table('bizboost_omnichannel_logs')
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN delivery_status = ? THEN 1 ELSE 0 END) as delivered', ['delivered'])
            ->selectRaw('SUM(CASE WHEN delivery_status = ? THEN 1 ELSE 0 END) as failed', ['failed'])
            ->selectRaw('SUM(CASE WHEN channel_type = ? THEN 1 ELSE 0 END) as whatsapp', ['whatsapp'])
            ->selectRaw('SUM(CASE WHEN channel_type = ? THEN 1 ELSE 0 END) as sms', ['sms'])
            ->selectRaw('COALESCE(SUM(net_platform_profit), 0) as total_profit')
            ->first();

        return Inertia::render('BizBoost/Omnichannel/Index', [
            'logs' => $logs,
            'stats' => [
                'total' => (int) ($stats->total ?? 0),
                'delivered' => (int) ($stats->delivered ?? 0),
                'failed' => (int) ($stats->failed ?? 0),
                'whatsapp' => (int) ($stats->whatsapp ?? 0),
                'sms' => (int) ($stats->sms ?? 0),
                'total_profit' => (float) ($stats->total_profit ?? 0),
            ],
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
