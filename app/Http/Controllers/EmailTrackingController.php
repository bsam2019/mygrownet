<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailQueueModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailTrackingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailTrackingController extends Controller
{
    /**
     * Track email open
     */
    public function trackOpen(string $token)
    {
        try {
            $queue = EmailQueueModel::where('id', $this->decodeToken($token))
                ->where('status', 'sent')
                ->first();

            if ($queue) {
                EmailTrackingModel::create([
                    'queue_id' => $queue->id,
                    'user_id' => $queue->user_id,
                    'campaign_id' => $queue->campaign_id,
                    'event_type' => 'opened',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Email tracking error: ' . $e->getMessage());
        }

        // Return 1x1 transparent pixel
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Track email click
     */
    public function trackClick(string $token, Request $request)
    {
        $url = $request->query('url');

        try {
            $queue = EmailQueueModel::where('id', $this->decodeToken($token))
                ->where('status', 'sent')
                ->first();

            if ($queue) {
                EmailTrackingModel::create([
                    'queue_id' => $queue->id,
                    'user_id' => $queue->user_id,
                    'campaign_id' => $queue->campaign_id,
                    'event_type' => 'clicked',
                    'event_data' => ['url' => $url],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Email click tracking error: ' . $e->getMessage());
        }

        // Redirect to actual URL
        return redirect($url ?? config('app.url'));
    }

    /**
     * Handle unsubscribe
     */
    public function unsubscribe(string $token)
    {
        try {
            $queue = EmailQueueModel::where('id', $this->decodeToken($token))->first();

            if ($queue) {
                // Mark as unsubscribed
                EmailTrackingModel::create([
                    'queue_id' => $queue->id,
                    'user_id' => $queue->user_id,
                    'campaign_id' => $queue->campaign_id,
                    'event_type' => 'unsubscribed',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Update subscriber status
                if ($queue->campaign_id) {
                    $queue->campaign->subscribers()
                        ->where('user_id', $queue->user_id)
                        ->update([
                            'status' => 'unsubscribed',
                            'unsubscribed_at' => now(),
                        ]);
                }

                return view('emails.unsubscribed', [
                    'message' => 'You have been successfully unsubscribed from this email campaign.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Unsubscribe error: ' . $e->getMessage());
        }

        return view('emails.unsubscribed', [
            'message' => 'Unable to process your unsubscribe request. Please contact support.',
        ]);
    }

    /**
     * Decode tracking token
     */
    private function decodeToken(string $token): int
    {
        return (int) decrypt($token);
    }
}
