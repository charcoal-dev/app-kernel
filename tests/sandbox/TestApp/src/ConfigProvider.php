<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Sandbox\TestApp;

use Charcoal\App\Kernel\Config\Builder\CacheConfigObjectsBuilder;
use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Config\Snapshot\CacheStoreConfig;
use Charcoal\App\Kernel\Enums\CacheDriver;
use Charcoal\Tests\App\Fixtures\Enums\CacheStore;
use Charcoal\Tests\App\Fixtures\Enums\TimezoneEnum;

final class ConfigProvider
{
    public static function getConfig(): AppConfig
    {
        return self::getConfig_1cacheNull_1sqliteDb_UTC();
    }

    public static function getConfig_1cacheNull_1sqliteDb_UTC(): AppConfig
    {
        $cacheConfig = new CacheConfigObjectsBuilder();
        $cacheConfig->set(CacheStore::Secondary,
            new CacheStoreConfig(CacheDriver::NULL, "0.0.0.0", 6379, 6));

        return new AppConfig(TimezoneEnum::UTC, $cacheConfig->build(), null);
    }
}