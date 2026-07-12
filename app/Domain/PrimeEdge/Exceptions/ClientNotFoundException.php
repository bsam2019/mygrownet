<?php

namespace App\Domain\PrimeEdge\Exceptions;

class ClientNotFoundException extends PrimeEdgeException
{
    public function __construct(string $clientId = '')
    {
        parent::__construct(
            message: "Client not found: {$clientId}",
            errorCode: 'CLIENT_NOT_FOUND',
            context: ['client_id' => $clientId]
        );
    }
}
