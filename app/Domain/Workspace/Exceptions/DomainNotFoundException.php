<?php

namespace App\Domain\Workspace\Exceptions;

class DomainNotFoundException extends \RuntimeException
{
    public function __construct(string $domain)
    {
        parent::__construct("No registered domain: {$domain}");
    }
}
