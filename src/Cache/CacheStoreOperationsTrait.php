<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\Entity\AbstractEntity;
use Charcoal\Cache\Cache;
use Charcoal\Cache\CachedReferenceKey;
use Charcoal\Cache\Exception\CacheException;

/**
 * Trait CacheStoreOpsTrait
 * @package Charcoal\App\Kernel\Cache
 */
trait CacheStoreOperationsTrait
{
    /**
     * @param string $key
     * @return string
     */
    abstract public function normalizeStorageKey(string $key): string;

    /**
     * @return Cache|null
     */
    abstract public function getCacheStore(): ?Cache;

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @param array|null $referenceKeys
     * @return void
     * @throws CacheException
     */
    public function storeInCache(string $key, mixed $value, ?int $ttl = null, ?array $referenceKeys = null): void
    {
        $primaryKey = $this->normalizeStorageKey($key);
        $this->getCacheStore()?->set($primaryKey, $value, $ttl);
        if ($referenceKeys) {
            foreach ($referenceKeys as $referenceKey) {
                $this->getCacheStore()?->createReferenceKey(
                    $this->normalizeStorageKey($referenceKey), $primaryKey, $ttl
                );
            }
        }
    }

    /**
     * @param string $key
     * @return mixed
     * @throws CacheException
     * @throws \Charcoal\Cache\Exception\CacheDriverOpException
     * @throws \Charcoal\Cache\Exception\CachedEntityException
     */
    public function getFromCache(string $key): mixed
    {
        $instance = $this->getCacheStore()?->get($this->normalizeStorageKey($key));
        if ($instance instanceof CachedReferenceKey) {
            $instance = $instance->resolve($this->getCacheStore());
        }

        if ($instance instanceof AbstractEntity) {
            return $instance;
        }

        return null;
    }

    /**
     * @param string $key
     * @return void
     * @throws \Charcoal\Cache\Exception\CacheDriverOpException
     */
    public function deleteFromCache(string $key): void
    {
        $this->getCacheStore()?->delete($this->normalizeStorageKey($key));
    }
}