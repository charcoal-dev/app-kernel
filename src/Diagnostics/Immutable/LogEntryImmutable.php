<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics\Immutable;

use Charcoal\App\Kernel\Diagnostics\LogEntry;
use Charcoal\App\Kernel\Diagnostics\LogLevel;
use Charcoal\App\Kernel\Support\DtoHelper;

/**
 * Class LogEntrySnapshot
 * @package Charcoal\App\Kernel\Diagnostics\ExecutionSnapshot\Immutable
 */
final readonly class LogEntryImmutable
{
    public LogLevel $level;
    public string $message;
    public int $timestamp;
    public ?array $context;
    public ?array $exception;

    public function __construct(LogEntry $entry)
    {
        $this->level = $entry->level;
        $this->message = $entry->message;
        $this->context = $entry->context ?
            DtoHelper::createFrom($entry->context, 3, true, true, "**RECURSION**") : null;
        $this->exception = $entry->exception ?
            DtoHelper::createFrom($entry->exception, 3, true, true, "**RECURSION**") : null;
        $this->timestamp = $entry->timestamp->getTimestamp();
    }
}