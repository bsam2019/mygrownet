<?php

namespace MyGrowNet\Platform\Sdk\Identity;

class UserIdentity
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly ?string $name = null,
    ) {}

    public static function current(): ?self
    {
        $user = request()->user();
        if (!$user) return null;
        return new self(
            id: (string) $user->id,
            email: $user->email,
            name: $user->name,
        );
    }
}
