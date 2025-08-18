<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

/**
 * Class ExecutionSnapshot
 * @package Charcoal\App\Kernel\Diagnostics\ExecutionSnapshot\Immutable
 */
final readonly class ExecutionSnapshot
{
    public float $startupTime;
    public array $logs;

    /**
     * @param int $startupTime
     * @param array<ExecutionMetrics> $metrics
     * @param array<LogEntry> $logs
     * @internal
     */
    public function __construct(int $startupTime, array $metrics, array $logs)
    {
        $this->startupTime = $startupTime / 1e6;


    }
}