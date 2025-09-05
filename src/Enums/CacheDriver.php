<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

use Charcoal\App\Kernel\Config\Snapshot\CacheStoreConfig;
use Charcoal\App\Kernel\Stubs\NullCache;
use Charcoal\Cache\Adapters\Redis\Ext\RedisClient;
use Charcoal\Contracts\Storage\Cache\CacheAdapterInterface;

/**
 * Class CacheDriver
 * @package Charcoal\App\Kernel\Enums
 */
enum CacheDriver: string
{
    case NULL = "null";
    case REDIS = "redis";

    public static function CreateClient(CacheStoreConfig $config): CacheAdapterInterface
    {
        return match ($config->driver) {
            self::NULL => new NullCache(),
            self::REDIS => new RedisClient($config->hostname, $config->port, $config->timeout),
        };
    }
}