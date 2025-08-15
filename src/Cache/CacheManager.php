<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\App\Kernel\Config\CacheDriver;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Base\Abstracts\BaseFactoryRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Cache\CacheClient;

/**
 * Class CachePool
 * @package Charcoal\App\Kernel
 * @template-extends BaseFactoryRegistry<CacheClient>
 */
class CacheManager extends BaseFactoryRegistry
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
     * @param CacheStoreEnumInterface $key
     * @return CacheClient
     */
    public function get(CacheStoreEnumInterface $key): CacheClient
    {
        return $this->getExistingOrCreate($key->getServerKey());
    }

    /**
     * @param string $key
     * @return CacheClient
     */
    protected function create(string $key): CacheClient
    {
        return new CacheClient(
            CacheDriver::CreateClient($this->app->config->cache->get($key)),
            useChecksumsByDefault: false,
            nullIfExpired: true,
            deleteIfExpired: true
        );
    }

    /**
     * Nothing is serialized
     * @return array
     */
    public function __serialize(): array
    {
        // Todo: Enable serialization of CacheClient instances
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