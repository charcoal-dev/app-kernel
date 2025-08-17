<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Events;

use Charcoal\App\Kernel\Internal\Events\StorageProviderEvents;
use Charcoal\Database\DatabaseClient;
use Charcoal\Database\Events\Connection\ConnectionError;
use Charcoal\Database\Events\Connection\ConnectionSuccess;
use Charcoal\Database\Events\Connection\ConnectionWaiting;
use Charcoal\Events\AbstractEvent;
use Charcoal\Events\Subscriptions\Subscription;

/**
 * Class DataStoreEvents
 * @package Charcoal\App\Kernel\Events
 * @internal
 */
final readonly class DatabaseEvents extends StorageProviderEvents
{
    /**
     * @param DatabaseClient $db
     */
    public function __construct(public DatabaseClient $db)
    {
        parent::__construct($db);
    }

    /**
     * @param \Closure $closure
     * @return Subscription
     */
    public function onConnect(\Closure $closure): Subscription
    {
        return $this->setListener($this->db->events->connectionState, ConnectionSuccess::class, $closure);
    }

    /**
     * @param \Closure $closure
     * @return Subscription
     */
    public function onConnectionError(\Closure $closure): Subscription
    {
        return $this->setListener($this->db->events->connectionState, ConnectionError::class, $closure);
    }

    /**
     * @param \Closure $closure
     * @return Subscription
     */
    public function onLazyConnect(\Closure $closure): Subscription
    {
        return $this->setListener($this->db->events->connectionState, ConnectionWaiting::class, $closure);
    }

    /**
     * @param AbstractEvent $event
     * @return Subscription
     */
    protected function getSubscription(AbstractEvent $event): Subscription
    {
        return $this->db->events->connectionState->subscribe();
    }
}