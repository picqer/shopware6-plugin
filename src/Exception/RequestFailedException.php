<?php

namespace Picqer\Shopware6Plugin\Exception;

use Exception;

class RequestFailedException extends Exception
{
    /**
     * @var string
     */
    private $endpoint;

    public function __construct(
        string $endpoint,
        string $message
    ) {
        $this->endpoint = $endpoint;

        parent::__construct($message);
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }
}