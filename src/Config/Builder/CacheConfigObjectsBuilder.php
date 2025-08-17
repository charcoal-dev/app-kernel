<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\CacheManagerConfig;
use Charcoal\App\Kernel\Config\Snapshot\CacheStoreConfig;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;

/**
 * Class CacheConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 * @extends AbstractConfigObjectsCollector<CacheStoreEnumInterface, CacheStoreConfig, CacheManagerConfig>
 */
final class CacheConfigObjectsBuilder extends AbstractConfigObjectsCollector implements ConfigSnapshotInterface
{
    /**
     * @param CacheStoreEnumInterface $key
     * @param CacheStoreConfig $config
     * @return void
     */
    public function set(CacheStoreEnumInterface $key, CacheStoreConfig $config): void
    {
        $this->storeConfig($key, $config);
    }

    /**
     * @return CacheManagerConfig
     */
    public function build(): CacheManagerConfig
    {
        return new CacheManagerConfig($this->getCollection());
    }
}