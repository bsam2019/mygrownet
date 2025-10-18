<?php

namespace Tests\Unit\Models;

use App\Models\IdVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdVerificationTest extends TestCase
{
    use RefreshDatabase;
    public function test_approves_verification()
    {
        $user = User::factory()->create();
        $verification = IdVerification::factory()->create([
            'user_id' => $user->id,
            'status' => IdVerification::STATUS_PENDING,
        ]);

        $verification->approve(2);

        $this->assertEquals(IdVerification::STATUS_APPROVED, $verification->status);
        $this->assertEquals(2, $verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);
        $this->assertNotNull($verification->expires_at);
    }

    public function test_rejects_verification()
    {
        $verification = IdVerification::factory()->create([
            'status' => IdVerification::STATUS_PENDING,
        ]);

        $reason = 'Document not clear';
        $verification->reject($reason, 2);

        $this->assertEquals(IdVerification::STATUS_REJECTED, $verification->status);
        $this->assertEquals($reason, $verification->rejection_reason);
        $this->assertEquals(2, $verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);
    }

    public function test_checks_if_expired()
    {
        $expiredVerification = new IdVerification([
            'expires_at' => now()->subDay(),
        ]);

        $validVerification = new IdVerification([
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($expiredVerification->isExpired());
        $this->assertFalse($validVerification->isExpired());
    }

    public function test_checks_if_valid()
    {
        $validVerification = new IdVerification([
            'status' => IdVerification::STATUS_APPROVED,
            'expires_at' => now()->addDay(),
        ]);

        $expiredVerification = new IdVerification([
            'status' => IdVerification::STATUS_APPROVED,
            'expires_at' => now()->subDay(),
        ]);

        $rejectedVerification = new IdVerification([
            'status' => IdVerification::STATUS_REJECTED,
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($validVerification->isValid());
        $this->assertFalse($expiredVerification->isValid());
        $this->assertFalse($rejectedVerification->isValid());
    }

    public function test_gets_status_color()
    {
        $statuses = [
            IdVerification::STATUS_PENDING => 'yellow',
            IdVerification::STATUS_APPROVED => 'green',
            IdVerification::STATUS_REJECTED => 'red',
            IdVerification::STATUS_EXPIRED => 'gray',
        ];

        foreach ($statuses as $status => $expectedColor) {
            $verification = new IdVerification(['status' => $status]);
            $this->assertEquals($expectedColor, $verification->getStatusColor());
        }
    }

    public function test_gets_document_types()
    {
        $types = IdVerification::getDocumentTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey(IdVerification::DOCUMENT_NATIONAL_ID, $types);
        $this->assertArrayHasKey(IdVerification::DOCUMENT_PASSPORT, $types);
        $this->assertArrayHasKey(IdVerification::DOCUMENT_DRIVERS_LICENSE, $types);
    }
}