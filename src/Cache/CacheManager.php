<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\App\Kernel\Config\CacheDriver;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Base\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Cache\Cache;
use Charcoal\Cache\CacheDriverInterface;

/**
 * Class CachePool
 * @package Charcoal\App\Kernel
 * @template-extends AbstractFactoryRegistry<Cache>
 */
class CacheManager extends AbstractFactoryRegistry
{
    protected readonly AppBuild $app;

    use RegistryKeysLowercaseTrimmed;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct()
    {
    }

    /**
     * Bootstrap this service
     * @param AppBuild $app
     * @return void
     */
    public function bootstrap(AppBuild $app): void
    {
        $this->app = $app;
    }

    /**
     * Resolve or get a resolved Cache instance
     * @param CacheStoreEnumInterface|string $key
     * @return Cache
     */
    public function get(CacheStoreEnumInterface|string $key): Cache
    {
        $key = $key instanceof CacheStoreEnumInterface ? $key->getServerKey() : $key;
        return $this->getExistingOrCreate($key);
    }

    /**
     * Resolve instance to Cache and sets up the necessary events
     * @param string $key
     * @return Cache
     */
    protected function create(string $key): Cache
    {
        $cacheStore = new Cache(
            CacheDriver::CreateClient($this->app->config->cache->get($key)),
            useChecksumsByDefault: false,
            nullIfExpired: true,
            deleteIfExpired: true
        );

        $cacheStore->events->onConnected()->listen(function (CacheDriverInterface $cacheDriver) {
            $this->app->events->onCacheConnection()->trigger([$cacheDriver]);
        });

        return $cacheStore;
    }

    /**
     * Nothing is serialized
     * @return array
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * Removes all resolved instances to Cache
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->instances = [];
    }
}