<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Module;

use Charcoal\App\Kernel\Orm\Repository\AbstractOrmEntity;
use Charcoal\Cache\Cache;
use Charcoal\Cache\CachedReferenceKey;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\OOP\Traits\NoDumpTrait;
use Charcoal\OOP\Traits\NotCloneableTrait;
use Charcoal\OOP\Traits\NotSerializableTrait;
use Charcoal\OOP\Vectors\ExceptionLog;
use Charcoal\OOP\Vectors\StringVector;

/**
 * Class RuntimeCache
 * @package Charcoal\App\Kernel\Orm\Module
 */
class RuntimeCache
{
    protected array $storage = [];

    use NotSerializableTrait;
    use NoDumpTrait;
    use NotCloneableTrait;

    /**
     * @param CacheAwareModule $module
     */
    public function __construct(protected readonly CacheAwareModule $module)
    {
    }

    /**
     * Normalizes the storage item identifier
     * @param string $key
     * @return string
     */
    protected function normalizeStorageKey(string $key): string
    {
        return strtolower($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function storeInMemory(string $key, mixed $value): void
    {
        $this->storage[$this->normalizeStorageKey($key)] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFromMemory(string $key): mixed
    {
        return $this->storage[$this->normalizeStorageKey($key)] ?? null;
    }

    /**
     * @param string $key
     * @return void
     */
    public function deleteFromMemory(string $key): void
    {
        unset($this->storage[$this->normalizeStorageKey($key)]);
    }

    /**
     * @return Cache|null
     */
    private function getCacheStore(): ?Cache
    {
        return $this->module->getCacheStore();
    }

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

        if ($instance instanceof AbstractOrmEntity) {
            return $instance;
        }

        return null;
    }

    /**
     * @param string|StringVector $key
     * @param ExceptionLog|null $errorBag
     * @return void
     * @throws CacheException
     * @throws \Charcoal\Cache\Exception\CacheDriverOpException
     */
    public function deleteFromCache(string|StringVector $key, ?ExceptionLog $errorBag = null): void
    {
        $key = is_string($key) ? [$key] : $key->getArray();
        if ($key) {
            foreach ($key as $item) {
                $item = $this->normalizeStorageKey($item);

                try {
                    $this->getCacheStore()?->delete($item);
                } catch (CacheException $e) {
                    if (!$errorBag) {
                        throw $e;
                    }

                    $errorBag->append(new \RuntimeException(
                        "Cannot delete \"$item\" from cache server; Check previous",
                        previous: $e
                    ));
                }
            }
        }
    }

    /**
     * Cleans the entire repository
     * @return void
     */
    public function purgeRuntimeMemory(): void
    {
        unset($this->storage);
        $this->storage = [];
    }
}