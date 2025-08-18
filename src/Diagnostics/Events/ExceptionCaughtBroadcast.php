<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics\Events;

/**
 * Interface ExceptionCaughtBroadcast
 * @package Charcoal\App\Kernel\Diagnostics\Events
 */
readonly class ExceptionCaughtBroadcast implements DiagnosticsEventsContext
{
    public function __construct(
        public \Throwable $caught,
    )
    {

    }
}