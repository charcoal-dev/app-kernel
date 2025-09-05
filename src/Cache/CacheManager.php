<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\Config\Snapshot\CacheManagerConfig;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Enums\CacheDriver;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Base\Registry\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Registry\Traits\RegistryKeysLowercaseTrimmed;
use Charcoal\Cache\CacheClient;

/**
 * Class CachePool
 * @package Charcoal\App\Kernel
 * @template-extends AbstractFactoryRegistry<CacheClient>
 */
class CacheManager extends AbstractFactoryRegistry implements AppServiceInterface
{
    use ControlledSerializableTrait;
    use RegistryKeysLowercaseTrimmed;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct(public readonly ?CacheManagerConfig $config)
    {
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
     * Removes all resolved instances to Cache
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->config = $data["config"];
        $this->instances = [];
    }

    /**
     * @return array
     */
    protected function collectSerializableData(): array
    {
        return [
            "config" => $this->config,
        ];
    }

    /**
     * @return array<class-string>
     */
    public static function unserializeDependencies(): array
    {
        return [static::class, CacheManagerConfig::class];
    }
}