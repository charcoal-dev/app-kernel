<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Config\Snapshot\CacheManagerConfig;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Enums\CacheDriver;
use Charcoal\App\Kernel\Internal\Services\AppServiceConfigAwareInterface;
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
class CacheManager extends BaseFactoryRegistry implements AppServiceConfigAwareInterface
{
    protected readonly ?CacheManagerConfig $config;

    use RegistryKeysLowercaseTrimmed;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct(AbstractApp $app)
    {
        $this->config = $app->config->cache;
    }

    /**
     * @param CacheStoreEnumInterface $key
     * @return CacheClient
     */
    public function getStore(CacheStoreEnumInterface $key): CacheClient
    {
        return $this->getExistingOrCreate($key->getConfigKey());
    }

    /**
     * @param string $key
     * @return CacheClient
     */
    protected function create(string $key): CacheClient
    {
        $config = $this->config?->providers[$key];
        if (!$config) {
            throw new \DomainException("Cache store provider config not found for key: " . $key);
        }

        return new CacheClient(
            CacheDriver::CreateClient($config),
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