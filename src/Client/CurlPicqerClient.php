<?php

namespace Picqer\Shopware6Plugin\Client;

use Picqer\Shopware6Plugin\Exception\RequestFailedException;

final class CurlPicqerClient implements PicqerClient
{
    const USER_AGENT = 'Picqer Shopware 6 Plugin (version 1.1.0)';

    public function pushOrder(string $subdomain, string $connectionKey, string $id): void
    {
        $this->post(
            sprintf('https://%s.picqer.com/webshops/shopware6/orderPush/%s', $subdomain, $connectionKey),
            ['id' => $id]
        );
    }

    private function post(string $endpoint, array $body): void
    {
        $session = curl_init();

        curl_setopt($session, CURLOPT_USERAGENT, self::USER_AGENT);

        curl_setopt($session, CURLOPT_URL, $endpoint);
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($session, CURLOPT_TIMEOUT, 2);
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($session, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($session, CURLOPT_FAILONERROR, true);

        curl_exec($session);

        $errorNo = curl_errno($session);
        $errorMessage = curl_error($session);

        curl_close($session);

        if ($errorNo) {
            throw new RequestFailedException($endpoint, $errorMessage);
        }
    }
}