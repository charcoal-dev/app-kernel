<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

use Charcoal\App\Kernel\Diagnostics\Events\LogEntryBroadcast;
use Charcoal\App\Kernel\Enums\LogLevel;

/**
 * This class is immutable and is designed to store information such as the log level,
 * message, additional context, associated exception (if any), and the timestamp for
 * when the log event occurred.
 */
final readonly class LogEntry implements LogEntryBroadcast
{
    /**
     * @internal
     */
    public function __construct(
        public \DateTimeImmutable $timestamp,
        public LogLevel           $level,
        public string             $message,
        public array              $context = [],
        public ?\Throwable        $exception = null
    )
    {
    }
}