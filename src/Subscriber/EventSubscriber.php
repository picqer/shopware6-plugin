<?php

namespace Picqer\Shopware6Plugin\Subscriber;

use Exception;
use Picqer\Shopware6Plugin\Client\PicqerClient;
use Picqer\Shopware6Plugin\Exception\IncompleteConfigurationException;
use Picqer\Shopware6Plugin\Exception\OrderNotFoundException;
use Picqer\Shopware6Plugin\Exception\RequestFailedException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $configService;

    /**
     * @var PicqerClient
     */
    private $client;

    /**
     * @var EntityRepository
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SystemConfigService $configService,
        PicqerClient $client,
        EntityRepository $orderRepository,
        LoggerInterface $logger
    ) {
        $this->configService = $configService;
        $this->client = $client;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_WRITTEN_EVENT => 'handle',
        ];
    }

    public function handle(EntityWrittenEvent $event): void
    {
        if (! isset($event->getIds()[0])) {
            return;
        }

        $orderId = $event->getIds()[0];

        try {
            $order = $this->orderRepository->search(new Criteria([$orderId]), $event->getContext())->first();
            if (! $order instanceof OrderEntity) {
                throw new OrderNotFoundException(sprintf('Order [%s] not found', $orderId));
            }

            $salesChannelId = $order->getSalesChannelId();
            $disabled = $this->configService->getBool($this->buildConfigKey('disabled'), $salesChannelId);
            if ($disabled) {
                return;
            }

            $subdomain = $this->configService->getString($this->buildConfigKey('subdomain'), $salesChannelId);
            $connectionKey = $this->configService->getString($this->buildConfigKey('connectionkey'), $salesChannelId);
            $debug = $this->configService->getBool($this->buildConfigKey('debug'), $salesChannelId);

            $this->pushOrderToPicqer($subdomain, $connectionKey, $order, $debug, $salesChannelId);
        } catch (Exception $e) {
            $this->logger->error('[Picqer] Caught unexpected exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'orderId' => $orderId,
            ]);
        }
    }

    private function buildConfigKey(string $key): string
    {
        return sprintf('PicqerExtendedIntegration.config.%s', $key);
    }

    private function pushOrderToPicqer(
        string $subdomain,
        string $connectionKey,
        OrderEntity $order,
        bool $debug,
        string $salesChannelId
    ): void {
        try {
            if (empty($subdomain) || empty($connectionKey)) {
                throw new IncompleteConfigurationException($subdomain, $connectionKey);
            }

            $this->client->pushOrder(
                $subdomain,
                $connectionKey,
                $order->getId()
            );
        } catch (IncompleteConfigurationException $e) {
            if (! $debug) {
                return;
            }

            $this->logger->error('[Picqer] Subdomain and/or connection-key not configured', [
                'subdomain' => $e->getSubdomain(),
                'connectionKey' => $e->getConnectionKey(),
                'orderId' => $order->getId(),
                'salesChannelId' => $salesChannelId,
            ]);
        } catch (RequestFailedException $e) {
            if (! $debug) {
                return;
            }

            $this->logger->error('[Picqer] Could not call webhook', [
                'endpoint' => $e->getEndpoint(),
                'message' => $e->getMessage(),
                'orderId' => $order->getId(),
                'salesChannelId' => $salesChannelId,
            ]);
        }
    }
}