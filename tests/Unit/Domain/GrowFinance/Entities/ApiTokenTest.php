<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\ApiToken;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ApiTokenTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $token = new ApiToken(id: 1, businessId: 5, name: 'Test Token', token: 'abc123', abilities: ['read', 'write']);

        $this->assertSame(1, $token->id);
        $this->assertSame('Test Token', $token->name);
        $this->assertSame(['read', 'write'], $token->abilities);
    }

    #[Test]
    public function has_ability_checks_correctly()
    {
        $token = new ApiToken(id: 1, businessId: 5, name: 'Test', token: 'abc', abilities: ['read', 'write']);

        $this->assertTrue($token->hasAbility('read'));
        $this->assertTrue($token->hasAbility('write'));
        $this->assertFalse($token->hasAbility('delete'));
    }

    #[Test]
    public function is_expired_returns_false_when_no_expiry()
    {
        $token = new ApiToken(id: 1, businessId: 5, name: 'Test', token: 'abc');
        $this->assertFalse($token->isExpired());
    }

    #[Test]
    public function is_expired_returns_true_when_past_expiry()
    {
        $token = new ApiToken(id: 1, businessId: 5, name: 'Test', token: 'abc', expiresAt: new DateTimeImmutable('2020-01-01'));
        $this->assertTrue($token->isExpired());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $token = ApiToken::reconstitute([
            'id' => 1,
            'business_id' => 5,
            'name' => 'Test',
            'token' => 'abc',
            'abilities' => ['read'],
        ]);

        $this->assertSame('Test', $token->name);
        $this->assertTrue($token->hasAbility('read'));
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $token = new ApiToken(id: 1, businessId: 5, name: 'Test', token: 'abc', abilities: ['read']);
        $array = $token->toArray();

        $this->assertSame('Test', $array['name']);
        $this->assertSame(['read'], $array['abilities']);
    }
}
