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

namespace Charcoal\Apps\Kernel\Config;

use Charcoal\Apps\Kernel\Exception\AppConfigException;

/**
 * Class CacheConfig
 * @package Charcoal\Apps\Kernel\Config
 */
class CacheConfig
{
    public readonly bool $use;
    public readonly string $storageDriver;
    public readonly string $hostname;
    public readonly int $port;
    public readonly int $timeOut;

    /**
     * @param mixed $config
     * @throws \Charcoal\Apps\Kernel\Exception\AppConfigException
     */
    public function __construct(mixed $config)
    {
        if (!is_array($config)) {
            throw new AppConfigException('Invalid cache configuration');
        }

        $status = $config["status"] ?? null;
        if (!is_bool($status)) {
            throw new AppConfigException('Invalid cache status');
        }

        $this->use = $status;

        $hostname = $config["hostname"] ?? null;
        if (!is_string($hostname) || !filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new AppConfigException('Invalid cache server IPv4 address');
        }

        $this->hostname = $hostname;

        $this->port = intval($config["port"] ?? 0);
        if ($this->port <= 0x3e8 || $this->port >= 0xffff) {
            throw new AppConfigException('Invalid cache server port');
        }

        $this->timeOut = intval($config["time_out"] ?? 0);
        if ($this->timeOut < 1 || $this->timeOut > 6) {
            throw new AppConfigException('Caching server timeout value is out of range');
        }

        $this->storageDriver = strtolower(strval($config["driver"] ?? ""));
        if ($this->storageDriver !== "redis") {
            throw new AppConfigException('Unsupported caching storage driver');
        }
    }
}
