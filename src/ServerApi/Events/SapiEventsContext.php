<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Events;

use Charcoal\Events\Contracts\BehaviourContextEnablerInterface;
use Charcoal\Events\Contracts\EventContextInterface;

/**
 * Represents the context for SAPI events, providing an interface to handle event-related operations
 * and enable specific behaviors within the given context.
 */
interface SapiEventsContext extends EventContextInterface, BehaviourContextEnablerInterface
{
}