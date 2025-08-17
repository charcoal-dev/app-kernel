<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Contracts\Lifecycle\LifecycleBoundContextInterface;
use Charcoal\App\Kernel\Support\ErrorHelper;

/**
 * Class Lifecycle
 * Provides a report on the application execution lifecycle that can be archived for monitoring purposes or runtime inspections
 * @package Charcoal\App\Kernel
 */
class Lifecycle
{
    public ?float $startedOn = null;
    public ?float $bootstrappedOn = null;
    public ?string $loadTime = null;
    private array $entries = [];
    private int $count = 0;
    private array $exceptions = [];
    private int $peakMemoryUsage = 0;
    private ?float $cpuTimeUser = null;
    private ?float $cpuTimeSystem = null;
    private ?float $cpuTimeTotal = null;

    private array $boundContext = [];

    /**
     * @param LifecycleBoundContextInterface $context
     * @return int
     */
    public function bindContext(LifecycleBoundContextInterface $context): int
    {
        $this->boundContext[] = $context;
        return array_key_last($this->boundContext);
    }

    /**
     * @param int $index
     * @return void
     */
    public function unbindContext(int $index): void
    {
        unset($this->boundContext[$index]);
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $data = $this->toArray();
        $data["boundContext"] = [];
        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->startedOn = $data["startedOn"];
        $this->bootstrappedOn = $data["bootstrappedOn"];
        $this->loadTime = $data["loadTime"];
        $this->entries = $data["entries"];
        $this->count = $data["count"];
        $this->exceptions = $data["exceptions"];
        $this->peakMemoryUsage = $data["peakMemoryUsage"];
        $this->cpuTimeUser = $data["cpuTimeUser"];
        $this->cpuTimeSystem = $data["cpuTimeSystem"];
        $this->cpuTimeTotal = $data["cpuTimeTotal"];
        $this->boundContext = [];
    }

    /**
     * @return void
     */
    public function purgeAll(): void
    {
        $this->entries = [];
        $this->exceptions = [];
        $this->count = 0;
    }

    /**
     * @param \Throwable $t
     * @return void
     */
    public function exception(\Throwable $t): void
    {
        $this->exceptions[] = ErrorHelper::Exception2Array($t);
        foreach ($this->boundContext as $context) {
            $context->exceptionFromLifecycle($t);
        }
    }

    /**
     * @param string $event
     * @param int|string|bool|null $value
     * @param bool $microTs
     * @return void
     */
    public function error(string $event, int|string|bool|null $value = null, bool $microTs = true): void
    {
        $this->append("error", $event, $value, $microTs);
    }

    /**
     * @param string $event
     * @param int|string|bool|null $value
     * @param bool $microTs
     * @return void
     */
    public function log(string $event, int|string|bool|null $value = null, bool $microTs = true): void
    {
        $this->append("log", $event, $value, $microTs);
    }

    /**
     * @param string $event
     * @param int|string|bool|null $value
     * @param bool $microTs
     * @return void
     */
    public function debug(string $event, int|string|bool|null $value = null, bool $microTs = true): void
    {
        $this->append("debug", $event, $value, $microTs);
    }

    /**
     * @param string $level
     * @param string $event
     * @param int|string|bool|null $value
     * @param bool $microTs
     * @return void
     */
    private function append(
        string               $level,
        string               $event,
        int|string|bool|null $value = null,
        bool                 $microTs = true
    ): void
    {
        $this->entries[] = [
            "level" => $level,
            "event" => $event,
            "value" => $value,
            "microTs" => $microTs ? microtime(true) : null
        ];
        $this->count++;
        foreach ($this->boundContext as $context) {
            $context->entryFromLifecycle($level, $event, $value);
        }
    }

    /**
     * @return void
     */
    public function updateExecutionMetrics(): void
    {
        $this->peakMemoryUsage = memory_get_peak_usage(false);
        $cpuUsage = getrusage(0);
        $this->cpuTimeUser = $cpuUsage["ru_utime.tv_sec"] + ($cpuUsage["ru_utime.tv_usec"] / 1e6);
        $this->cpuTimeSystem = $cpuUsage["ru_stime.tv_sec"] + ($cpuUsage["ru_stime.tv_usec"] / 1e6);
        $this->cpuTimeTotal = $this->cpuTimeUser + $this->cpuTimeSystem;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "count" => $this->count,
            "entries" => $this->entries,
            "exceptions" => $this->exceptions,
            "startedOn" => $this->startedOn,
            "bootstrappedOn" => $this->bootstrappedOn,
            "loadTime" => $this->loadTime,
            "peakMemoryUsage" => $this->peakMemoryUsage,
            "cpuTimeUser" => $this->cpuTimeUser,
            "cpuTimeSystem" => $this->cpuTimeSystem,
            "cpuTimeTotal" => $this->cpuTimeTotal,
        ];
    }
}