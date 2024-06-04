<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel;

/**
 * Class Lifecycle
 * @package Charcoal\Apps\Kernel
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
