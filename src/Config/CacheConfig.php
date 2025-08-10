<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;

/**
 * Class CacheConfig
 * @package Charcoal\App\Kernel\Config
 */
class CacheConfig
{
    private array $servers = [];

    /**
     * @param CacheStoreEnumInterface|string $key
     * @param CacheServerConfig $config
     * @return void
     */
    public function set(CacheStoreEnumInterface|string $key, CacheServerConfig $config): void
    {
        $key = $key instanceof CacheStoreEnumInterface ? $key->getServerKey() : $key;
        $this->servers[$key] = $config;
    }

    /**
     * @param CacheStoreEnumInterface|string $key
     * @return CacheServerConfig
     */
    public function get(CacheStoreEnumInterface|string $key): CacheServerConfig
    {
        $key = $key instanceof CacheStoreEnumInterface ? $key->getServerKey() : $key;
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