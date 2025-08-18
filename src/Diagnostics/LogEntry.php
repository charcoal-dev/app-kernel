<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

use Charcoal\App\Kernel\Clock\Clock;
use Charcoal\App\Kernel\Diagnostics\Events\LogEntryBroadcast;
use Charcoal\App\Kernel\Enums\LogLevel;

/**
 * Class LogEntry
 * @package Charcoal\App\Kernel\Diagnostics
 */
final readonly class LogEntry implements LogEntryBroadcast
{
    public \DateTimeImmutable $timestamp;

    /**
     * @internal
     */
    public function __construct(
        public LogLevel    $level,
        public string      $message,
        public array       $context = [],
        public ?\Throwable $exception = null,
    )
    {
        $this->timestamp = Clock::now();
    }
}