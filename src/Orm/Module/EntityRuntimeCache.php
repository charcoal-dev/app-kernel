<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Module;

use Charcoal\App\Kernel\Orm\AbstractOrmModule;
use Charcoal\App\Kernel\Orm\Repository\AbstractOrmEntity;
use Charcoal\Cache\Cache;
use Charcoal\Cache\CachedReferenceKey;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;
use Charcoal\OOP\Vectors\ExceptionLog;
use Charcoal\OOP\Vectors\StringVector;

/**
 * Class EntityRuntimeCache
 * Provides methods for storing and managing AbstractOrmEntity objects in runtime memory and to/from assigned cache server
 * @package Charcoal\App\Kernel\Orm\Module
 */
class EntityRuntimeCache extends AbstractInstanceRegistry
{
    /**
     * @param AbstractOrmModule $module
     */
    public function __construct(protected readonly AbstractOrmModule $module)
    {
        parent::__construct(AbstractOrmEntity::class);
    }

    /**
     * Normalizes entity's storage identifier
     * @param string $entityId
     * @return string
     */
    protected function normalizeEntityId(string $entityId): string
    {
        return strtolower($entityId);
    }

    /**
     * Purpose of this and module's getCacheStore() is to possibly define a different cache server to any specific module
     * @return Cache|null
     */
    private function getCacheStore(): ?Cache
    {
        return $this->module->getCacheStore();
    }

    /**
     * Store AbstractOrmEntity object in runtime memory
     * @param string $entityId
     * @param AbstractOrmEntity $object
     * @return void
     */
    public function storeInMemory(string $entityId, AbstractOrmEntity $object): void
    {
        $this->registrySet($this->normalizeEntityId($entityId), $object);
    }

    /**
     * Retrieve stored AbstractOrmEntity object from runtime memory, or NULL
     * @param string $entityId
     * @return AbstractOrmEntity|null
     */
    public function getFromMemory(string $entityId): ?AbstractOrmEntity
    {
        return $this->instances[$this->normalizeEntityId($entityId)] ?? null;
    }

    /**
     * Deletes stored AbstractOrmEntity object from runtime memory
     * @param string $entityId
     * @return void
     */
    public function deleteFromMemory(string $entityId): void
    {
        unset($this->instances[$this->normalizeEntityId($entityId)]);
    }

    /**
     * Stores AbstractOrmEntity object in cache server,
     * Optionally, creates CachedReferenceKeys for multiple keys pointing to same object
     * @param string $entityId
     * @param AbstractOrmEntity $object
     * @param int|null $ttl
     * @param string[]|null $referenceKeys
     * @return void
     * @throws CacheException
     */
    public function storeInCache(string $entityId, AbstractOrmEntity $object, ?int $ttl = null, ?array $referenceKeys = null): void
    {
        $primaryKey = $this->normalizeEntityId($entityId);
        $this->getCacheStore()?->set($primaryKey, clone $object, $ttl);
        if ($referenceKeys) {
            foreach ($referenceKeys as $referenceKey) {
                $this->getCacheStore()?->createReferenceKey($this->normalizeEntityId($referenceKey), $primaryKey, $ttl);
            }
        }
    }

    /**
     * Retrieve stored AbstractOrmEntity object from cache server, or NULL
     * @param string $entityId
     * @return AbstractOrmEntity|null
     * @throws \Charcoal\Cache\Exception\CacheException
     */
    public function getFromCache(string $entityId): ?AbstractOrmEntity
    {
        $instance = $this->getCacheStore()?->get($this->normalizeEntityId($entityId));
        if ($instance instanceof CachedReferenceKey) {
            $instance = $instance->resolve($this->getCacheStore());
        }

        if ($instance instanceof AbstractOrmEntity) {
            return $instance;
        }

        return null;
    }

    /**
     * Deletes entity objects or CachedReferenceKey from cache server
     * If ExceptionLog is provided, any CacheException caught are added to ExceptionLog and NOT thrown
     * @param string|StringVector $entityIds
     * @param ExceptionLog|null $errorBag
     * @return void
     * @throws CacheException
     */
    public function deleteFromCache(string|StringVector $entityIds, ?ExceptionLog $errorBag = null): void
    {
        $entityIds = is_string($entityIds) ? [$entityIds] : $entityIds->getArray();
        if ($entityIds) {
            foreach ($entityIds as $entityId) {
                $entityId = $this->normalizeEntityId($entityId);

                try {
                    $this->getCacheStore()?->delete($entityId);
                } catch (CacheException $e) {
                    if (!$errorBag) {
                        throw $e;
                    }

                    $errorBag->append(new \RuntimeException(
                        "Cannot delete \"$entityId\" from cache server; Check previous",
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
        unset($this->instances);
        $this->instances = [];
    }
}