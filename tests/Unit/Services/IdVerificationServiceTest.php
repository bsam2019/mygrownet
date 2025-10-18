<?php

namespace Tests\Unit\Services;

use App\Models\IdVerification;
use App\Models\User;
use App\Services\IdVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private IdVerificationService $idVerificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->idVerificationService = app(IdVerificationService::class);
        Storage::fake('private');
    }

    public function test_submits_verification_successfully()
    {
        $user = User::factory()->create();
        $documentFront = UploadedFile::fake()->image('id_front.jpg');
        $documentBack = UploadedFile::fake()->image('id_back.jpg');
        $selfie = UploadedFile::fake()->image('selfie.jpg');

        $verification = $this->idVerificationService->submitVerification(
            $user,
            IdVerification::DOCUMENT_NATIONAL_ID,
            '123456/78/9',
            $documentFront,
            $documentBack,
            $selfie
        );

        $this->assertInstanceOf(IdVerification::class, $verification);
        $this->assertEquals($user->id, $verification->user_id);
        $this->assertEquals(IdVerification::DOCUMENT_NATIONAL_ID, $verification->document_type);
        $this->assertEquals('123456/78/9', $verification->document_number);
        $this->assertEquals(IdVerification::STATUS_PENDING, $verification->status);

        // Check files were stored
        $this->assertNotNull($verification->document_front_path);
        $this->assertNotNull($verification->document_back_path);
        $this->assertNotNull($verification->selfie_path);

        Storage::disk('private')->assertExists($verification->document_front_path);
        Storage::disk('private')->assertExists($verification->document_back_path);
        Storage::disk('private')->assertExists($verification->selfie_path);

        // Check user verification requirement was updated
        $this->assertFalse($user->fresh()->requires_id_verification);
    }

    public function test_submits_verification_without_optional_files()
    {
        $user = User::factory()->create();
        $documentFront = UploadedFile::fake()->image('id_front.jpg');

        $verification = $this->idVerificationService->submitVerification(
            $user,
            IdVerification::DOCUMENT_PASSPORT,
            'AB123456',
            $documentFront
        );

        $this->assertNotNull($verification->document_front_path);
        $this->assertNull($verification->document_back_path);
        $this->assertNull($verification->selfie_path);
    }

    public function test_approves_verification()
    {
        $user = User::factory()->create(['is_id_verified' => false]);
        $admin = User::factory()->create();
        $verification = IdVerification::factory()->create([
            'user_id' => $user->id,
            'status' => IdVerification::STATUS_PENDING,
        ]);

        $this->idVerificationService->approveVerification($verification, $admin->id);

        $verification->refresh();
        $this->assertEquals(IdVerification::STATUS_APPROVED, $verification->status);
        $this->assertEquals($admin->id, $verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);
        $this->assertNotNull($verification->expires_at);

        // Check user verification status was updated
        $user->refresh();
        $this->assertTrue($user->is_id_verified);
        $this->assertNotNull($user->id_verified_at);
    }

    public function test_rejects_verification()
    {
        $user = User::factory()->create(['requires_id_verification' => false]);
        $admin = User::factory()->create();
        $verification = IdVerification::factory()->create([
            'user_id' => $user->id,
            'status' => IdVerification::STATUS_PENDING,
        ]);

        $reason = 'Document is not clear enough';
        $this->idVerificationService->rejectVerification($verification, $reason, $admin->id);

        $verification->refresh();
        $this->assertEquals(IdVerification::STATUS_REJECTED, $verification->status);
        $this->assertEquals($reason, $verification->rejection_reason);
        $this->assertEquals($admin->id, $verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);

        // Check user verification requirement was reset
        $user->refresh();
        $this->assertTrue($user->requires_id_verification);
    }

    public function test_checks_if_user_requires_verification()
    {
        // New user requiring verification
        $newUser = User::factory()->create(['requires_id_verification' => true]);
        $this->assertTrue($this->idVerificationService->requiresVerification($newUser));

        // User with valid verification
        $verifiedUser = User::factory()->create([
            'requires_id_verification' => false,
            'is_id_verified' => true,
        ]);
        IdVerification::factory()->create([
            'user_id' => $verifiedUser->id,
            'status' => IdVerification::STATUS_APPROVED,
            'expires_at' => now()->addYear(),
        ]);
        $this->assertFalse($this->idVerificationService->requiresVerification($verifiedUser));

        // User with expired verification
        $expiredUser = User::factory()->create([
            'requires_id_verification' => false,
            'is_id_verified' => true,
        ]);
        IdVerification::factory()->create([
            'user_id' => $expiredUser->id,
            'status' => IdVerification::STATUS_APPROVED,
            'expires_at' => now()->subDay(),
        ]);
        $this->assertTrue($this->idVerificationService->requiresVerification($expiredUser));
    }

    public function test_gets_pending_verifications()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        IdVerification::factory()->create([
            'user_id' => $user1->id,
            'status' => IdVerification::STATUS_PENDING,
            'submitted_at' => now()->subDays(2),
        ]);

        IdVerification::factory()->create([
            'user_id' => $user2->id,
            'status' => IdVerification::STATUS_PENDING,
            'submitted_at' => now()->subDay(),
        ]);

        IdVerification::factory()->create([
            'user_id' => $user3->id,
            'status' => IdVerification::STATUS_APPROVED,
        ]);

        $pendingVerifications = $this->idVerificationService->getPendingVerifications();

        $this->assertCount(2, $pendingVerifications);
        // Should be ordered by submitted_at ascending (oldest first)
        $this->assertEquals($user1->id, $pendingVerifications->first()->user_id);
        $this->assertEquals($user2->id, $pendingVerifications->last()->user_id);
    }

    public function test_checks_duplicate_document_numbers()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        IdVerification::factory()->create([
            'user_id' => $user1->id,
            'document_type' => IdVerification::DOCUMENT_NATIONAL_ID,
            'document_number' => '123456/78/9',
            'status' => IdVerification::STATUS_APPROVED,
        ]);

        // Check for duplicate (should return true)
        $isDuplicate = $this->idVerificationService->checkDuplicateDocument(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '123456/78/9'
        );
        $this->assertTrue($isDuplicate);

        // Check for duplicate excluding the same user (should return false)
        $isDuplicate = $this->idVerificationService->checkDuplicateDocument(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '123456/78/9',
            $user1->id
        );
        $this->assertFalse($isDuplicate);

        // Check different document number (should return false)
        $isDuplicate = $this->idVerificationService->checkDuplicateDocument(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '987654/32/1'
        );
        $this->assertFalse($isDuplicate);
    }

    public function test_validates_national_id_format()
    {
        // Valid Zambian NRC format
        $this->assertTrue($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '123456/78/9'
        ));

        // Invalid formats
        $this->assertFalse($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '12345678'
        ));

        $this->assertFalse($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '123456-78-9'
        ));
    }

    public function test_validates_passport_format()
    {
        // Valid passport formats
        $this->assertTrue($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_PASSPORT,
            'AB123456'
        ));

        $this->assertTrue($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_PASSPORT,
            'ZM1234567'
        ));

        // Invalid formats
        $this->assertFalse($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_PASSPORT,
            '123456'
        ));

        $this->assertFalse($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_PASSPORT,
            'A123'
        ));
    }

    public function test_validates_drivers_license_format()
    {
        // Valid lengths
        $this->assertTrue($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_DRIVERS_LICENSE,
            'DL123456'
        ));

        $this->assertTrue($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_DRIVERS_LICENSE,
            '123456789012345'
        ));

        // Invalid lengths
        $this->assertFalse($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_DRIVERS_LICENSE,
            '12345'
        ));

        $this->assertFalse($this->idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_DRIVERS_LICENSE,
            '1234567890123456'
        ));
    }

    public function test_cleans_up_expired_documents()
    {
        Storage::fake('private');

        $user = User::factory()->create();
        
        // Create expired verification with files
        $verification = IdVerification::factory()->create([
            'user_id' => $user->id,
            'status' => IdVerification::STATUS_APPROVED,
            'expires_at' => now()->subMonths(7),
            'document_front_path' => 'id-verifications/front.jpg',
            'document_back_path' => 'id-verifications/back.jpg',
            'selfie_path' => 'id-verifications/selfie.jpg',
        ]);

        // Create the files
        Storage::disk('private')->put($verification->document_front_path, 'fake content');
        Storage::disk('private')->put($verification->document_back_path, 'fake content');
        Storage::disk('private')->put($verification->selfie_path, 'fake content');

        $cleanedCount = $this->idVerificationService->cleanupExpiredDocuments();

        $this->assertEquals(1, $cleanedCount);

        $verification->refresh();
        $this->assertEquals(IdVerification::STATUS_EXPIRED, $verification->status);
        $this->assertNull($verification->document_front_path);
        $this->assertNull($verification->document_back_path);
        $this->assertNull($verification->selfie_path);

        // Check files were deleted
        Storage::disk('private')->assertMissing('id-verifications/front.jpg');
        Storage::disk('private')->assertMissing('id-verifications/back.jpg');
        Storage::disk('private')->assertMissing('id-verifications/selfie.jpg');
    }
}