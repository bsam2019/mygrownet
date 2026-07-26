<?php

namespace App\Domain\Core\Contracts;

interface ProviderContract
{
    public function capability(): string;
}
