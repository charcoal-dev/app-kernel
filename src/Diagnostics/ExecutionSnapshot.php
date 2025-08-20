<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

use Charcoal\App\Kernel\Enums\LogLevel;

/**
 * Class ExecutionSnapshot
 * @package Charcoal\App\Kernel\Diagnostics\ExecutionSnapshot\Immutable
 */
final readonly class ExecutionSnapshot
{
    public float $startupTime;

    /** @var array<ExecutionMetrics> */
    public array $metrics;
    /** @var array<LogEntryDto> */
    public array $logs;
    /** @var array<string,int> */
    public array $alerts;

    /**
     * @param int $startupTime
     * @param array<ExecutionMetrics> $metrics
     * @param array<LogEntry> $logs
     * @internal
     */
    public function __construct(int $startupTime, array $metrics, array $logs)
    {
        $this->startupTime = $startupTime / 1e6;
        $this->metrics = $metrics;
        $alerts = [
            LogLevel::Verbose->name => 0,
            LogLevel::Debug->name => 0,
            LogLevel::Info->name => 0,
            LogLevel::Notice->name => 0,
            LogLevel::Warning->name => 0,
            LogLevel::Error->name => 0,
            LogLevel::Critical->name => 0,
        ];

        // Unload metrics
        $logsImmutable = [];
        foreach ($logs as $log) {
            $alerts[$log->level->name]++;
            $logsImmutable[] = new LogEntryDto($log);
        }

        $this->logs = $logsImmutable;
        $this->alerts = $alerts;
    }
}