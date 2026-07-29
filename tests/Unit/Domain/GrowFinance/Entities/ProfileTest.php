<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Profile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $profile = new Profile(id: 1, userId: 42, businessName: 'My Business', accountNumber: 'ACC-001', createdAt: null, updatedAt: null);

        $this->assertSame(1, $profile->id);
        $this->assertSame(42, $profile->userId);
        $this->assertSame('My Business', $profile->businessName);
        $this->assertSame('ACC-001', $profile->accountNumber);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $profile = Profile::reconstitute([
            'id' => 1, 'user_id' => 42, 'business_name' => 'Acme Inc',
            'account_number' => 'ACC-002',
        ]);

        $this->assertSame('Acme Inc', $profile->businessName);
        $this->assertSame('ACC-002', $profile->accountNumber);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $profile = new Profile(id: 1, userId: 42, businessName: 'Biz', accountNumber: 'ACC', createdAt: null, updatedAt: null);
        $array = $profile->toArray();

        $this->assertSame(42, $array['user_id']);
        $this->assertSame('Biz', $array['business_name']);
    }
}
