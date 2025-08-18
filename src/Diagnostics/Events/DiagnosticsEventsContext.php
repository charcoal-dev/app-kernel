<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics\Events;

use Charcoal\Events\Contracts\BehaviourContextEnablerInterface;
use Charcoal\Events\Contracts\EventContextInterface;

/**
 * Interface DiagnosticsEventsContext
 * @package Charcoal\App\Kernel\Diagnostics\Events
 */
interface DiagnosticsEventsContext extends EventContextInterface, BehaviourContextEnablerInterface
{
}