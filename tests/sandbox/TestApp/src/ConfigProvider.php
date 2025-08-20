<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Sandbox\TestApp;

use Charcoal\App\Kernel\Config\Builder\AppConfigBuilder;
use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Config\Snapshot\CacheStoreConfig;
use Charcoal\App\Kernel\Config\Snapshot\DatabaseConfig;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Enums\CacheDriver;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\Database\Enums\DbConnectionStrategy;
use Charcoal\Database\Enums\DbDriver;
use Charcoal\Tests\App\Fixtures\Enums\CacheStore;
use Charcoal\Tests\App\Fixtures\Enums\DbConfig;
use Charcoal\Tests\App\Fixtures\Enums\TimezoneEnum;

final class ConfigProvider
{
    public static function getConfig(AppEnv $env, PathRegistry $paths): AppConfig
    {
        return self::getConfig_1cacheNull_1sqliteDb_UTC($env, $paths);
    }

    public static function getConfig_1cacheNull_1sqliteDb_UTC(AppEnv $env, PathRegistry $paths): AppConfig
    {
        $appConfig = new AppConfigBuilder($env, $paths, TimezoneEnum::UTC);
        $appConfig->cache->set(CacheStore::Secondary,
            new CacheStoreConfig(CacheDriver::NULL, "0.0.0.0", 6379, 6));

        $appConfig->database->set(
            DbConfig::Primary,
            new DatabaseConfig(
                DbDriver::SQLITE,
                "tmp/test-app.db",
                strategy: DbConnectionStrategy::Normal
            )
        );

        $appConfig->security->setSemaphoreDirectory("./tmp/semaphore");
        return $appConfig->build();
    }
}