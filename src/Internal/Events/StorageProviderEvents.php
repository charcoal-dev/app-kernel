<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Events;

use Charcoal\Base\Contracts\Storage\StorageProviderInterface;
use Charcoal\Events\AbstractEvent;
use Charcoal\Events\Exceptions\SubscriptionClosedException;
use Charcoal\Events\Subscriptions\Subscription;

/**
 * Class StorageProviderEvents
 * @package Charcoal\App\Kernel\Internal\Events
 * @internal
 */
readonly abstract class StorageProviderEvents
{
    /**
     * @param StorageProviderInterface $store
     */
    public function __construct(protected StorageProviderInterface $store)
    {
    }

    /**
     * @param \Closure $closure
     * @return Subscription
     */
    abstract public function onConnect(\Closure $closure): Subscription;

    /**
     * @param \Closure $closure
     * @return Subscription
     */
    abstract public function onConnectionError(\Closure $closure): Subscription;

    /**
     * @param AbstractEvent $event
     * @return Subscription
     */
    abstract protected function getSubscription(AbstractEvent $event): Subscription;

    /**
     * @param AbstractEvent $event
     * @param class-string<\Charcoal\Events\Contracts\EventContextInterface> $context
     * @param \Closure $closure
     * @return Subscription
     */
    final protected function setListener(AbstractEvent $event, string $context, \Closure $closure): Subscription
    {
        $subscription = $this->getSubscription($event);

        try {
            $subscription->listen($context, $closure);
        } catch (SubscriptionClosedException $e) {
            throw new \RuntimeException("An unexpected error occurred", previous: $e);
        }

        return $subscription;
    }
}