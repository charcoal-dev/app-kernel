<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics\ExecutionSnapshot\Immutable;

use Charcoal\App\Kernel\Diagnostics\LogEntry;
use Charcoal\App\Kernel\Support\DtoHelper;

/**
 * Class LogEntrySnapshot
 * @package Charcoal\App\Kernel\Diagnostics\ExecutionSnapshot\Immutable
 */
final readonly class LogEntryDto
{
    public string $level;
    public string $message;
    public float $timestamp;
    public ?array $context;
    public ?array $exception;

    public function __construct(LogEntry $entry)
    {
        $this->level = $entry->level->name;
        $this->message = $entry->message;
        $this->timestamp = (float)$entry->timestamp->format("U.u");

        $this->context = $entry->context ?
            DtoHelper::createFrom($entry->context, 3, true, true, "**RECURSION**") : null;
        $this->exception = $entry->exception ?
            DtoHelper::getExceptionObject($entry->exception) : null;
    }
}