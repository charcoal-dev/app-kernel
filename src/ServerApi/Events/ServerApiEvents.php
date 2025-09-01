<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Events;

use Charcoal\App\Kernel\Enums\ServerApiEvent;
use Charcoal\App\Kernel\ServerApi\SapiLoaded;
use Charcoal\Events\BehaviorEvent;

/**
 * Represents the server API events within the application.
 * Extends the base BehaviorEvent to provide functionality for handling custom server API events.
 */
final class ServerApiEvents extends BehaviorEvent
{
    public function __construct()
    {
        parent::__construct("app.Events.ServerApi", [
            SapiEventsContext::class,
            SapiLoaded::class
        ]);
    }

    public function subscribe(ServerApiEvent $event, \Closure $callback): void
    {
        $subscription = $this->createSubscription("sapiOnLoad-" . $this->count());
        $subscription->listen($event->getEventContext(), $callback);
    }

    public function dispatch(SapiEventsContext $context): void
    {
        $this->dispatchEvent($context);
    }
}