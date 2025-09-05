<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Events;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Internal\Events\StorageProviderEvents;
use Charcoal\Cache\CacheClient;
use Charcoal\Cache\Events\Connection\ConnectionError;
use Charcoal\Cache\Events\Connection\ConnectionSuccess;
use Charcoal\Events\AbstractEvent;
use Charcoal\Events\Subscriptions\Subscription;

/**
 * Class DataStoreEvents
 * @package Charcoal\App\Kernel\Events
 * @internal
 */
final readonly class CacheEvents extends StorageProviderEvents
{
    protected CacheClient $store;

    /**
     * @param AbstractApp $app
     * @param CacheStoreEnumInterface $store
     */
    public function __construct(AbstractApp $app, CacheStoreEnumInterface $store)
    {
        $this->store = $app->cache->getStore($store);
    }

    /**
     * @param \Closure $closure
     * @return Subscription
     */
    public function onConnect(\Closure $closure): Subscription
    {
        return $this->setListener($this->store->events, ConnectionSuccess::class, $closure);
    }

    /**
     * @param \Closure $closure
     * @return Subscription
     */
    public function onConnectionError(\Closure $closure): Subscription
    {
        return $this->setListener($this->store->events, ConnectionError::class, $closure);
    }

    /**
     * @param AbstractEvent $event
     * @return Subscription
     */
    protected function getSubscription(AbstractEvent $event): Subscription
    {
        return $this->store->events->subscribe();
    }
}