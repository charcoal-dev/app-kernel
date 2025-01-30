<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Config\CacheDriver;
use Charcoal\App\Kernel\Container\AppAwareInterface;
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
    protected readonly AppKernel $app;

    use NoDumpTrait;

    public function __construct()
    {
        parent::__construct(Cache::class);
    }

    public function bootstrap(AppKernel $app): void
    {
        $this->app = $app;
    }

    public function get(string $key): Cache
    {
        return $this->getOrResolve($key);
    }

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

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize(["instanceOf" => $this->instanceOf, "instances" => []]);
    }
}