<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostQrCodeModel;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Redirect from QR code short URL to target URL
     */
    public function redirect(string $code)
    {
        $qrCode = BizBoostQrCodeModel::where('short_code', $code)
            ->where('is_active', true)
            ->first();

        if (!$qrCode) {
            abort(404, 'QR code not found');
        }

        // Increment scan count
        $qrCode->increment('scan_count');

        // Log the scan (optional - for analytics)
        // This could be moved to a job for better performance
        \DB::table('bizboost_qr_scans')->insert([
            'qr_code_id' => $qrCode->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
            'scanned_at' => now(),
        ]);

        return redirect($qrCode->target_url);
    }
}
