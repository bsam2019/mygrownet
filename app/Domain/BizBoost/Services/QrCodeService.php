<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\QrCode;
use App\Domain\BizBoost\Repositories\QrCodeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class QrCodeService
{
    public function __construct(
        private QrCodeRepositoryInterface $qrRepo,
    ) {}

    public function findByShortCode(string $code): ?QrCode
    {
        return $this->qrRepo->findByShortCode($code);
    }

    public function incrementScan(int $qrCodeId): void
    {
        DB::table('bizboost_qr_codes')->where('id', $qrCodeId)->increment('scan_count');
    }

    public function recordScan(QrCode $qrCode): void
    {
        DB::table('bizboost_qr_scans')->insert([
            'qr_code_id' => $qrCode->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
            'scanned_at' => now(),
        ]);
    }
}