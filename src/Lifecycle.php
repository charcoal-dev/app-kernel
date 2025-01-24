<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

/**
 * Class Lifecycle
 * Provides a report on application execution lifecycle that can be archived for monitoring purposes or runtime inspections
 * @package Charcoal\App\Kernel
 */
class Lifecycle
{
    public float $startedOn;
    public float $bootstrappedOn;
    public string $loadTime;
    private array $entries = [];
    private int $count = 0;
    private array $exceptions = [];

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
        $this->exceptions[] = Errors::Exception2Array($t);
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
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $object = [
            "count" => $this->count,
            "entries" => $this->entries,
            "exceptions" => $this->exceptions
        ];

        if (isset($this->startedOn)) {
            $object["startedOn"] = $this->startedOn;
        }

        if (isset($this->bootstrappedOn)) {
            $object["bootstrappedOn"] = $this->bootstrappedOn;
        }

        if (isset($this->loadTime)) {
            $object["loadTime"] = $this->loadTime;
        }

        return $object;
    }
}