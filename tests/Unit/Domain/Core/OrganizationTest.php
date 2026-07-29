<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Entities\Organization;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrganizationTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_properties()
    {
        $now = new DateTimeImmutable();
        $updated = $now->modify('+1 hour');

        $org = new Organization(
            id: '1',
            uuid: '550e8400-e29b-41d4-a716-446655440000',
            name: 'Test Org',
            slug: 'test-org',
            type: 'sme',
            status: 'active',
            ownerId: '42',
            country: 'ZM',
            currency: 'ZMW',
            timezone: 'Africa/Lusaka',
            language: 'en',
            settings: ['theme' => 'dark'],
            createdAt: $now,
            updatedAt: $updated,
        );

        $this->assertEquals('1', $org->id);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $org->uuid);
        $this->assertEquals('Test Org', $org->name);
        $this->assertEquals('test-org', $org->slug);
        $this->assertEquals('sme', $org->type);
        $this->assertEquals('active', $org->status);
        $this->assertEquals('42', $org->ownerId);
        $this->assertEquals('ZM', $org->country);
        $this->assertEquals('ZMW', $org->currency);
        $this->assertEquals('Africa/Lusaka', $org->timezone);
        $this->assertEquals('en', $org->language);
        $this->assertEquals(['theme' => 'dark'], $org->settings);
        $this->assertSame($now, $org->createdAt);
        $this->assertSame($updated, $org->updatedAt);
    }

    #[Test]
    public function constructor_accepts_nullable_optional_fields()
    {
        $now = new DateTimeImmutable();

        $org = new Organization(
            id: '1',
            uuid: 'uuid-1',
            name: 'Minimal Org',
            slug: 'minimal',
            type: 'personal',
            status: 'active',
            ownerId: '1',
            country: null,
            currency: null,
            timezone: null,
            language: null,
            settings: [],
            createdAt: $now,
            updatedAt: null,
        );

        $this->assertNull($org->country);
        $this->assertNull($org->currency);
        $this->assertNull($org->timezone);
        $this->assertNull($org->language);
        $this->assertNull($org->updatedAt);
    }
}
