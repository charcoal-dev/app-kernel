<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Enums\CacheDriver;
use Charcoal\App\Kernel\Support\NetworkHelper;

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
        $final = [];
        foreach ($configs as $provider => $config) {
            if ($config->driver === CacheDriver::NULL) {
                $final[$provider] = new CacheStoreConfig(CacheDriver::NULL, "0.0.0.0", 0, 0);
                continue;
            }

            if (!NetworkHelper::isValidHostname($config->hostname)) {
                throw new \InvalidArgumentException("Invalid hostname for cache provider: " . $provider);
            }

            if (!NetworkHelper::isValidPort($config->port)) {
                throw new \OutOfBoundsException("Invalid port for cache provider: " . $provider);
            }

            if ($config->timeout < 0 || $config->timeout > 12) {
                throw new \OutOfBoundsException("Invalid timeout for cache provider: " . $provider);
            }

            $final[$provider] = $config;
        }

        $this->providers = $final;
    }
}