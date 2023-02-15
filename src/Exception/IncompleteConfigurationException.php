<?php

namespace Picqer\Shopware6Plugin\Exception;

use Exception;

class IncompleteConfigurationException extends Exception
{
    /**
     * @var string|null
     */
    private $subdomain;

    /**
     * @var string|null
     */
    private $connectionKey;

    public function __construct(
        string $subdomain = null,
        string $connectionKey = null
    ) {
        $this->subdomain = $subdomain;
        $this->connectionKey = $connectionKey;
    }

    public function getSubdomain()
    {
        return $this->subdomain;
    }

    public function getConnectionKey()
    {
        return $this->connectionKey;
    }
}