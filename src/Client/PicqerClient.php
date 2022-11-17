<?php

namespace Picqer\Shopware6Plugin\Client;

use Picqer\Shopware6Plugin\Exception\RequestFailedException;

interface PicqerClient
{
    /**
     * @throws RequestFailedException
     */
    public function pushOrder(string $subdomain, string $connectionKey, string $id): void;
}