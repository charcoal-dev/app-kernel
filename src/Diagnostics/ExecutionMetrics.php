<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

use Charcoal\App\Kernel\Clock\Clock;

/**
 * Class ExecutionMetrics
 * @package Charcoal\App\Kernel\Diagnostics
 */
final readonly class ExecutionMetrics
{
    public int $memoryUsage;
    public int $peakMemoryUsage;
    public float $cpuTimeUser;
    public float $cpuTimeSystem;
    public float $cpuTimeTotal;
    public float $timestamp;

    public function __construct()
    {
        $this->memoryUsage = memory_get_usage(false);
        $this->peakMemoryUsage = memory_get_peak_usage(false);
        $cpuUsage = getrusage(0);
        $this->cpuTimeUser = $cpuUsage["ru_utime.tv_sec"] + ($cpuUsage["ru_utime.tv_usec"] / 1e6);
        $this->cpuTimeSystem = $cpuUsage["ru_stime.tv_sec"] + ($cpuUsage["ru_stime.tv_usec"] / 1e6);
        $this->cpuTimeTotal = $this->cpuTimeUser + $this->cpuTimeSystem;
        $this->timestamp = (float)Clock::now()->format("U.u");
    }
}