<?php

namespace Picqer\Shopware6Plugin\Exception;

use Exception;

class IncompleteConfigurationException extends Exception
{
    /**
     * @var string
     */
    private $subdomain;

    /**
     * @var string
     */
    private $connectionKey;

    public function __construct(
        string $subdomain = null,
        string $connectionKey = null
    ) {
        $this->subdomain = $subdomain;
        $this->connectionKey = $connectionKey;
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    public function getConnectionKey(): string
    {
        return $this->connectionKey;
    }
}