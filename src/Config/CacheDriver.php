<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

use Charcoal\App\Kernel\Polyfill\NullCache;
use Charcoal\Cache\CacheDriverInterface;
use Charcoal\Cache\Drivers\RedisClient;

/**
 * Class CacheDriver
 * @package Charcoal\App\Kernel\Config
 */
enum CacheDriver: string
{
    case NULL = "null";
    case REDIS = "redis";

    /**
     * @param CacheServerConfig $config
     * @return CacheDriverInterface
     */
    public static function CreateClient(CacheServerConfig $config): CacheDriverInterface
    {
        return match ($config->driver) {
            self::NULL => new NullCache(),
            self::REDIS => new RedisClient($config->hostname, $config->port, $config->timeOut),
        };
    }
}