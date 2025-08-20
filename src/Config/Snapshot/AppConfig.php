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
 * Class AppConfig
 * @package Charcoal\App\Kernel
 */
readonly class AppConfig implements ConfigSnapshotInterface
{
    public function __construct(
        public AppEnv                 $env,
        public TimezoneEnumInterface  $timezone,
        public ?CacheManagerConfig    $cache,
        public ?DatabaseManagerConfig $database,
        public SecurityConfig         $security,
    )
    {
    }
}