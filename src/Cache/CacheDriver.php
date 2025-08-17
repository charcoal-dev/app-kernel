<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\Config\Snapshot\CacheStoreConfig;
use Charcoal\App\Kernel\Stubs\NullCache;
use Charcoal\Cache\Contracts\CacheDriverInterface;
use Charcoal\Cache\Drivers\RedisClient;

/**
 * Class CacheDriver
 * @package Charcoal\App\Kernel\Cache
 */
enum CacheDriver: string
{
    case NULL = "null";
    case REDIS = "redis";

    public static function CreateClient(CacheStoreConfig $config): CacheDriverInterface
    {
        return match ($config->driver) {
            self::NULL => new NullCache(),
            self::REDIS => new RedisClient($config->hostname, $config->port, $config->timeout),
        };
    }
}