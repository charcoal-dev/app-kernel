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
use Charcoal\App\Kernel\Internal\PathRegistry;

/**
 * Constructs an application configuration using the provided environment, timezone,
 * cache configuration, and database configuration builders.
 */
class AppConfigBuilder implements ConfigBuilderInterface
{
    public readonly CacheConfigObjectsBuilder $cache;
    public readonly DbConfigObjectsBuilder $database;
    public readonly SecurityConfigBuilder $security;
    public readonly SapiConfigBuilder $sapi;

    public function __construct(
        public AppEnv                $env,
        public PathRegistry          $paths,
        public TimezoneEnumInterface $timezone,
    )
    {
        $this->cache = new CacheConfigObjectsBuilder();
        $this->database = new DbConfigObjectsBuilder();
        $this->security = new SecurityConfigBuilder($this->paths->root);
        $this->sapi = new SapiConfigBuilder();
    }

    /**
     * Constructs and returns an AppConfig instance using the provided configurations.
     * @return AppConfig The constructed AppConfig instance.
     */
    public function build(): AppConfig
    {
        return new AppConfig(
            $this->env,
            $this->timezone,
            $this->cache->build(),
            $this->database->build(),
            $this->security->build(),
            $this->sapi->build()
        );
    }
}