<?php

namespace MyGrowNet\Platform\Sdk\Auth;

class TokenValidator
{
    public function validate(string $token): bool
    {
        return !empty($token);
    }

    public function decode(string $token): ?PlatformToken
    {
        return new PlatformToken(token: $token, type: 'Bearer');
    }
}
