<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Contracts\LifecycleBoundContextInterface;

/**
 * Class Lifecycle
 * Provides a report on application execution lifecycle that can be archived for monitoring purposes or runtime inspections
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

    private ?LifecycleBoundContextInterface $boundContext = null;

    /**
     * @param LifecycleBoundContextInterface $context
     * @return void
     */
    public function bindContext(LifecycleBoundContextInterface $context): void
    {
        $this->boundContext = $context;
    }

    /**
     * @return void
     */
    public function unbindContext(): void
    {
        $this->boundContext = null;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $data = $this->toArray();
        $data["boundContext"] = null;
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
        $this->boundContext = null;
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
        $this->exceptions[] = Errors::Exception2Array($t);
        $this->boundContext?->exceptionFromLifecycle($t);
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
        $this->boundContext?->entryFromLifecycle($level, $event, $value);
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
        ];
    }
}