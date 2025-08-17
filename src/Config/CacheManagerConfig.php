<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

/**
 * Class CacheConfig
 * @package Charcoal\App\Kernel\Config
 */
final readonly class CacheManagerConfig
{
    /** @var array<string, CacheStoreConfig> */
    public array $providers;

    /**
     * @param array<string, CacheStoreConfig> $configs
     */
    public function __construct(array $configs)
    {
        $this->providers = $configs;
    }
}