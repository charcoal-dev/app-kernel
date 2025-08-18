<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Module;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Cache\CacheClient;

/**
 * Trait CacheStoreAwareTrait
 * @package Charcoal\App\Kernel\Orm\Module
 */
trait CacheStoreAwareTrait
{
    private ?CacheClient $cacheStore = null;
    public readonly ?CacheStoreEnumInterface $cacheStoreEnum;

    abstract public function declareCacheStoreEnum(): ?CacheStoreEnumInterface;

    public function initializeCacheStoreAwareContainer(): true
    {
        $this->cacheStoreEnum = $this->declareCacheStoreEnum();
        return true;
    }

    public function getCacheStore(): ?CacheClient
    {
        if ($this->cacheStore) {
            return $this->cacheStore;
        }

        if (!$this->cacheStoreEnum) {
            return null;
        }

        return $this->cacheStore = $this->app->cache->getStore($this->cacheStoreEnum);
    }
}