<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Contracts\Enums\TimezoneEnumInterface;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;

/**
 * Constructs an application configuration using the provided environment, timezone,
 * cache configuration, and database configuration builders.
 * @api
 */
class AppConfigBuilder implements ConfigBuilderInterface
{
    public function __construct(
        public AppEnv                     $env,
        public TimezoneEnumInterface      $timezone,
        public ?CacheConfigObjectsBuilder $cache,
        public ?DbConfigObjectsBuilder    $database,
    )
    {
    }

    public function build(): AppConfig
    {
        return new AppConfig(
            $this->env,
            $this->timezone,
            $this->cache?->build(),
            $this->database?->build(),
        );
    }
}