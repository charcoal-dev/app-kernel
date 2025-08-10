<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Module;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Cache\Cache;

/**
 * Trait CacheStoreAwareTrait
 * @package Charcoal\App\Kernel\Orm\Module
 */
trait CacheStoreAwareTrait
{
    private ?Cache $cacheStore = null;
    public readonly ?CacheStoreEnumInterface $cacheStoreEnum;

    abstract public function declareCacheStoreEnum(): ?CacheStoreEnumInterface;

    public function initializeCacheStoreAwareContainer(): true
    {
        $this->cacheStoreEnum = $this->declareCacheStoreEnum();
        return true;
    }

    public function getCacheStore(): ?Cache
    {
        if ($this->cacheStore) {
            return $this->cacheStore;
        }

        if (!$this->cacheStoreEnum) {
            return null;
        }

        return $this->cacheStore = $this->app->cache->get($this->cacheStoreEnum->getServerKey());
    }
}