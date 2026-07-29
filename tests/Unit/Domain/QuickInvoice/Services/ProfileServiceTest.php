<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Profile;
use App\Domain\QuickInvoice\Repositories\ProfileRepositoryInterface;
use App\Domain\QuickInvoice\Services\ProfileService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ProfileServiceTest extends TestCase
{
    private ProfileRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $repository;

    private ProfileService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createStub(ProfileRepositoryInterface::class);
        $this->service = new ProfileService($this->repository);
    }

    #[Test]
    public function get_profile_returns_profile(): void
    {
        $profile = Profile::reconstitute(['user_id' => 1, 'name' => 'Biz']);
        $this->repository
            ->method('findByUser')
            ->willReturn($profile);

        $result = $this->service->getProfile(1);
        $this->assertSame('Biz', $result->name);
    }

    #[Test]
    public function get_profile_returns_null_when_not_found(): void
    {
        $this->repository
            ->method('findByUser')
            ->willReturn(null);

        $this->assertNull($this->service->getProfile(99));
    }

    #[Test]
    public function save_profile_creates_new_when_none_exists(): void
    {
        $this->repository
            ->method('findByUser')
            ->willReturn(null);

        $this->repository
            ->method('save')
            ->willReturnCallback(fn(Profile $p) => $p);

        $result = $this->service->saveProfile(1, ['name' => 'New Biz']);
        $this->assertSame('New Biz', $result->name);
        $this->assertSame(1, $result->userId);
    }

    #[Test]
    public function save_profile_merges_with_existing(): void
    {
        $existing = Profile::reconstitute([
            'user_id' => 1,
            'name' => 'Old Name',
            'phone' => '12345',
            'default_tax_rate' => 16,
        ]);

        $this->repository
            ->method('findByUser')
            ->willReturn($existing);

        $this->repository
            ->method('save')
            ->willReturnCallback(fn(Profile $p) => $p);

        $result = $this->service->saveProfile(1, ['name' => 'Updated Name']);
        $this->assertSame('Updated Name', $result->name);
        $this->assertSame('12345', $result->phone);
        $this->assertSame(16.0, $result->defaultTaxRate);
    }

    #[Test]
    public function generate_document_number_returns_null_when_no_profile(): void
    {
        $this->repository
            ->method('findByUser')
            ->willReturn(null);

        $this->assertNull($this->service->generateDocumentNumber(1, 'invoice'));
    }

    #[Test]
    public function generate_document_number_returns_number_and_increments(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'invoice_prefix' => 'INV',
            'invoice_next_number' => 5,
        ]);

        $this->repository
            ->method('findByUser')
            ->willReturn($profile);

        $savedProfiles = [];
        $this->repository
            ->method('save')
            ->willReturnCallback(function (Profile $p) use (&$savedProfiles) {
                $savedProfiles[] = $p;
                return $p;
            });

        $number = $this->service->generateDocumentNumber(1, 'invoice');
        $this->assertSame('INV-0005', $number);

        $this->assertCount(1, $savedProfiles);
        $this->assertSame(6, $savedProfiles[0]->invoiceNextNumber);
    }

    #[Test]
    public function calculate_completion_0_percent(): void
    {
        $profile = Profile::reconstitute(['user_id' => 1]);
        $this->assertSame(0, $this->service->calculateCompletion($profile));
    }

    #[Test]
    public function calculate_completion_100_percent(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'name' => 'Biz',
            'address' => 'Addr',
            'phone' => '123',
            'email' => 'a@b.com',
            'logo' => 'logo.png',
        ]);
        $this->assertSame(100, $this->service->calculateCompletion($profile));
    }

    #[Test]
    public function calculate_completion_partial(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'name' => 'Biz',
            'email' => 'a@b.com',
        ]);
        $this->assertSame(40, $this->service->calculateCompletion($profile));
    }

    #[Test]
    public function profile_to_array_returns_null_for_null(): void
    {
        $this->assertNull($this->service->profileToArray(null));
    }

    #[Test]
    public function profile_to_array_returns_formatted_data(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'name' => 'Biz',
            'email' => 'a@b.com',
            'logo' => 'logo.png',
        ]);
        $result = $this->service->profileToArray($profile);
        $this->assertSame('Biz', $result['name']);
        $this->assertSame('a@b.com', $result['email']);
        $this->assertSame('logo.png', $result['logo']);
        $this->assertIsInt($result['completion_percentage']);
    }
}
