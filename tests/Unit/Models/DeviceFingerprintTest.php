<?php

namespace Tests\Unit\Models;

use App\Models\DeviceFingerprint;
use Tests\TestCase;

class DeviceFingerprintTest extends TestCase
{
    public function test_generates_fingerprint_hash_correctly()
    {
        $deviceInfo = [
            'screen' => '1920x1080',
            'timezone' => 'Africa/Lusaka',
            'language' => 'en-US',
            'platform' => 'Win32',
        ];

        $browserInfo = [
            'name' => 'Chrome',
            'version' => '91.0.4472.124',
        ];

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $hash1 = DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent);
        $hash2 = DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent);

        $this->assertEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1)); // SHA256 produces 64 character hex string
    }

    public function test_generates_different_hashes_for_different_data()
    {
        $deviceInfo1 = ['screen' => '1920x1080'];
        $deviceInfo2 = ['screen' => '1366x768'];
        $browserInfo = ['name' => 'Chrome'];
        $userAgent = 'Mozilla/5.0';

        $hash1 = DeviceFingerprint::generateFingerprint($deviceInfo1, $browserInfo, $userAgent);
        $hash2 = DeviceFingerprint::generateFingerprint($deviceInfo2, $browserInfo, $userAgent);

        $this->assertNotEquals($hash1, $hash2);
    }

    public function test_checks_if_recently_active()
    {
        $fingerprint = new DeviceFingerprint([
            'last_seen_at' => now()->subHours(2),
        ]);

        $this->assertTrue($fingerprint->isRecentlyActive());

        $fingerprint->last_seen_at = now()->subDays(2);
        $this->assertFalse($fingerprint->isRecentlyActive());
    }
}