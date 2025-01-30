<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Module;

use Charcoal\App\Kernel\Container\AppAwareContainer;
use Charcoal\App\Kernel\Orm\CacheStoreEnum;
use Charcoal\Cache\Cache;

/**
 * Class CacheAwareModule
 * @package Charcoal\App\Kernel\Module
 */
abstract class CacheAwareModule extends AppAwareContainer
{
    public readonly RuntimeCache $memoryCache;
    private ?Cache $cache = null;

    public function __construct(protected readonly ?CacheStoreEnum $cacheStoreEnum)
    {
        $this->memoryCache = new RuntimeCache($this);
        parent::__construct();
    }

    /**
     * @return Cache|null
     */
    public function getCacheStore(): ?Cache
    {
        if ($this->cache) {
            return $this->cache;
        }

        if (!$this->cacheStoreEnum) {
            return null;
        }

        return $this->cache = $this->app->cache->get($this->cacheStoreEnum->getServerKey());
    }

    /**
     * Includes only "cacheStoreEnum" in serializable data
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = parent::collectSerializableData();
        $data["cache"] = null;
        $data["memoryCache"] = null;
        $data["cacheStoreEnum"] = $this->cacheStoreEnum;
        return $data;
    }

    /**
     * Starts fresh clean instance of RuntimeCache class
     * @param array $data
     * @return void
     */
    protected function onUnserialize(array $data): void
    {
        parent::onUnserialize($data);
        $this->cache = null;
        /** @noinspection PhpSecondWriteToReadonlyPropertyInspection Property is undefined here */
        $this->memoryCache = new RuntimeCache($this);
        /** @noinspection PhpSecondWriteToReadonlyPropertyInspection Property is undefined here */
        $this->cacheStoreEnum = $data["cacheStoreEnum"];
    }
}