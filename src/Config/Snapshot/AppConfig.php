<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\Enums\TimezoneEnumInterface;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;

/**
 * Represents the application configuration.
 * This class provides a snapshot of the application's configuration at a given point in time.
 * It includes environment settings, timezone configuration, optional cache and database manager configurations,
 * security settings, and server API interface configurations.
 */
readonly class AppConfig implements ConfigSnapshotInterface
{
    public function __construct(
        public AppEnv                 $env,
        public TimezoneEnumInterface  $timezone,
        public ?CacheManagerConfig    $cache,
        public ?DatabaseManagerConfig $database,
        public SecurityConfig         $security,
        public SapiConfigBundle $sapi
    )
    {
    }
}