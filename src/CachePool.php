<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Config\CacheDriver;
use Charcoal\App\Kernel\Container\AppAwareInterface;
use Charcoal\App\Kernel\Orm\CacheStoreEnum;
use Charcoal\Cache\Cache;
use Charcoal\Cache\CacheDriverInterface;
use Charcoal\OOP\DependencyInjection\AbstractDIResolver;
use Charcoal\OOP\Traits\NoDumpTrait;

/**
 * Class CachePool
 * @package Charcoal\App\Kernel
 */
class CachePool extends AbstractDIResolver implements AppAwareInterface
{
    protected readonly AppBuild $app;

    use NoDumpTrait;

    public function __construct()
    {
        parent::__construct(Cache::class);
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
     * @param CacheStoreEnum|string $key
     * @return Cache
     */
    public function get(CacheStoreEnum|string $key): Cache
    {
        $key = $key instanceof CacheStoreEnum ? $key->getServerKey() : $key;
        return $this->getOrResolve($key);
    }

    /**
     * Resolve instance to Cache and sets up necessary events
     * @param string $key
     * @param array $args
     * @return Cache
     */
    protected function resolve(string $key, array $args): Cache
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
        parent::__unserialize(["instanceOf" => $data["instanceOf"], "instances" => []]);
    }
}