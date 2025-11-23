<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

use Charcoal\App\Kernel\Diagnostics\Events\BuildStageEvents;
use Charcoal\App\Kernel\Diagnostics\LogEntry;

/**
 * Represents various types of diagnostic events that can occur in the application.
 */
enum DiagnosticsEvent: int
{
    case BuildStage = 0;
    case LogEntry = 1;

    /**
     * @return string
     */
    public function getEventContext(): string
    {
        return match ($this) {
            self::BuildStage => BuildStageEvents::class,
            self::LogEntry => LogEntry::class
        };
    }
}