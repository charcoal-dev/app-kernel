<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics\Events;

use Charcoal\Events\BehaviorEvent;
use Charcoal\Events\Dispatch\DispatchReport;
use Charcoal\Events\Subscriptions\Subscription;

/**
 * Represents an event triggered when a log entry is recorded.
 * This class extends the BehaviorEvent and is designed to provide
 * information- or functionality-specific to log entry recording events.
 * @internal
 */
final class DiagnosticsEvents extends BehaviorEvent
{
    /**
     * @return Subscription
     * @api
     */
    public function subscribe(): Subscription
    {
        return $this->createSubscription("diagnostics-" . $this->count());
    }

    /**
     * @param DiagnosticsEventsContext $context
     * @return DispatchReport
     */
    public function dispatch(DiagnosticsEventsContext $context): DispatchReport
    {
        return $this->dispatchEvent($context);
    }
}