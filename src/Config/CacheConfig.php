<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

/**
 * Class CacheConfig
 * @package Charcoal\App\Kernel\Config
 */
class CacheConfig
{
    private array $servers = [];

    /**
     * @param string $key
     * @param CacheServerConfig $config
     * @return void
     */
    public function set(string $key, CacheServerConfig $config): void
    {
        $this->servers[$key] = $config;
    }

    /**
     * @param string $key
     * @return CacheServerConfig
     */
    public function get(string $key): CacheServerConfig
    {
        if (!isset($this->servers[$key])) {
            throw new \OutOfRangeException(sprintf('No cache server configured matching "%s" key', $key));
        }

        return $this->servers[$key];
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->servers;
    }
}