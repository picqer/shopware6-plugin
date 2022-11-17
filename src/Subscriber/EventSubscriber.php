<?php

namespace Picqer\Shopware6Plugin\Subscriber;

use Picqer\Shopware6Plugin\Client\PicqerClient;
use Picqer\Shopware6Plugin\Exception\InvalidConfigException;
use Picqer\Shopware6Plugin\Exception\RequestFailedException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventSubscriber implements EventSubscriberInterface
{
    private $configService;
    private $client;
    private $logger;

    public function __construct(SystemConfigService $configService, PicqerClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->configService = $configService;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_WRITTEN_EVENT => 'pushOrder',
        ];
    }

    public function pushOrder(EntityWrittenEvent $event): void
    {
        if (!isset($event->getIds()[0])) {
            return;
        }

        $id = $event->getIds()[0];

        try {
            $this->client->pushOrder(
                $this->getConfigurationValue('subdomain'),
                $this->getConfigurationValue('connectionkey'),
                $id
            );
        } catch (InvalidConfigException $e) {
            /*
            * It is possible that the configuration values are not yet set
            * due to the fact that a plugin needs to be installed and activated before you can configure these.
            *
            * Picqer will check the webshop on interval as well.
            * When a call fails there might be a delay in orders showing up in Picqer, but they will not get lost.
            *
            * We choose to silently fail to prevent any disturbances of the webshop.
            */

            $this->logger->info('[Picqer] Plugin configuration is invalid', [
                'message' => $e->getMessage(),
            ]);
        } catch(RequestFailedException $e) {
            $this->logger->info('[Picqer] Could not call webhook', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function getConfigurationValue(string $type): string
    {
        $value = $this->configService->get(sprintf('PicqerExtendedIntegration.config.%s', $type));
        if (!is_string($value)) {
            throw new InvalidConfigException(sprintf('%s not set', ucfirst($type)));
        }

        return $value;
    }
}