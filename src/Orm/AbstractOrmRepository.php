<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\Contracts\StorageHooksInvokerTrait;
use Charcoal\App\Kernel\Entity\EntitySource;
use Charcoal\App\Kernel\Orm\Db\AbstractOrmTable;
use Charcoal\App\Kernel\Orm\Db\DbAwareTableEnum;
use Charcoal\App\Kernel\Orm\Entity\CacheableEntityInterface;
use Charcoal\App\Kernel\Orm\Exception\EntityNotFoundException;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\App\Kernel\Orm\Repository\AbstractOrmEntity;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\Cipher\Cipher;
use Charcoal\Database\ORM\Exception\OrmException;
use Charcoal\Database\ORM\Exception\OrmModelNotFoundException;
use Charcoal\Database\Queries\LockFlag;
use Charcoal\OOP\Traits\ControlledSerializableTrait;

/**
 * Class AbstractOrmRepository
 * @package Charcoal\App\Kernel\Orm
 */
abstract class AbstractOrmRepository
{
    public readonly AbstractOrmTable $table;

    protected int $entityCacheTtl = 86400;
    protected int $entityChecksumIterations = 0x64;

    use ControlledSerializableTrait;
    use StorageHooksInvokerTrait;

    public function __construct(
        public readonly AbstractOrmModule $module,
        private DbAwareTableEnum          $dbTableEnum,
    )
    {
    }

    /**
     * @param AbstractOrmEntity $entity
     * @return string
     */
    protected function getStorageKeyFor(AbstractOrmEntity $entity): string
    {
        $entityClass = $this->table->entityClass;
        if (!$entity instanceof $entityClass) {
            throw new \LogicException("Cannot suggest storage key for " . $entity::class . " from " . static::class);
        }

        $primaryId = $entity->getPrimaryId();
        if (!$primaryId) {
            throw new \RuntimeException("Cannot get storage key for " . $entity::class . " without primary ID");
        }

        return $this->getStorageKey($primaryId);
    }

    /**
     * @param int|string $primaryId
     * @return string
     */
    protected function getStorageKey(int|string $primaryId): string
    {
        return $this->table->name . ":" . $primaryId;
    }

    /**
     * AbstractOrmModule parent invokes this method when bootstrapped itself
     * @return void
     */
    public function resolveDatabaseTable(): void
    {
        $this->table = $this->module->app->databases->orm->resolve($this->dbTableEnum);
    }

    /**
     * @return Cipher
     */
    protected function getCipher(): Cipher
    {
        return $this->module->getCipher($this);
    }

    /**
     * Repository specific serialization data
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data["table"] = null;
        $data["module"] = $this->module;
        $data["entityCacheTtl"] = $this->entityCacheTtl;
        $data["entityChecksumIterations"] = $this->entityChecksumIterations;
        $data["dbTableEnum"] = $this->dbTableEnum;
        return $data;
    }

    /**
     * Repository specific serialization data
     * @param array $data
     * @return void
     */
    protected function onUnserialize(array $data): void
    {
        $this->entityChecksumIterations = $data["entityChecksumIterations"];
        $this->entityCacheTtl = $data["entityCacheTtl"];
        $this->dbTableEnum = $data["dbTableEnum"];
        /** @noinspection PhpSecondWriteToReadonlyPropertyInspection */
        $this->module = $data["module"];
    }

    /**
     * @param AbstractOrmEntity $entity
     * @return void
     * @throws CacheException
     */
    protected function cacheDeleteEntity(AbstractOrmEntity $entity): void
    {
        $this->module->memoryCache->deleteFromCache($this->getStorageKeyFor($entity));
    }

    /**
     * @param string $whereStmt
     * @param array $queryData
     * @param LockFlag|null $lock
     * @param bool $invokeStorageHooks
     * @return AbstractOrmEntity
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getFromDb(
        string    $whereStmt,
        array     $queryData = [],
        ?LockFlag $lock = null,
        bool      $invokeStorageHooks = true
    ): AbstractOrmEntity
    {
        try {
            /** @var AbstractOrmEntity $entity */
            $entity = $this->table->queryFind($whereStmt, $queryData, limit: 1, lock: $lock)->getNext();
            return $invokeStorageHooks ? $this->invokeStorageHooks($entity, EntitySource::DATABASE) : $entity;
        } catch (OrmModelNotFoundException) {
            throw new EntityNotFoundException();
        } catch (OrmException $e) {
            throw new EntityOrmException(static::class, $e);
        }
    }

    /**
     * @param string $column
     * @param int|string $value
     * @param LockFlag|null $lock
     * @return AbstractOrmEntity
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getFromDbColumn(string $column, int|string $value, ?LockFlag $lock = null): AbstractOrmEntity
    {
        return $this->getFromDb("`$column`=?", [$value], $lock);
    }

    /**
     * @param int|string $primaryId
     * @param bool $checkInCache
     * @param string $dbWhereStmt
     * @param array $dbQueryData
     * @param bool $storeInCache
     * @param int $cacheTtl
     * @return AbstractOrmEntity
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getEntity(
        int|string $primaryId,
        bool       $checkInCache,
        string     $dbWhereStmt,
        array      $dbQueryData,
        bool       $storeInCache,
        int        $cacheTtl = 0,
    ): AbstractOrmEntity
    {
        $entityId = $this->getStorageKey($primaryId);
        $entity = $this->module->memoryCache->getFromMemory($entityId);
        if ($entity instanceof AbstractOrmEntity) {
            return $this->invokeStorageHooks($entity, EntitySource::RUNTIME);
        }

        if ($checkInCache) {
            try {
                $entity = $this->module->memoryCache->getFromCache($entityId);
                if ($entity instanceof AbstractOrmEntity) {
                    $this->module->memoryCache->storeInMemory($entityId, $entity); // Runtime Memory Set
                    return $this->invokeStorageHooks($entity, EntitySource::CACHE);
                }
            } catch (CacheException $e) {
                trigger_error(static::class . ' caught CacheException', E_USER_WARNING);
                $this->module->app->lifecycle->exception(
                    new \RuntimeException(static::class . ' caught CacheException', previous: $e),
                );
            }
        }

        $entity = $this->getFromDb($dbWhereStmt, $dbQueryData, invokeStorageHooks: false);
        $this->module->memoryCache->storeInMemory($entityId, $entity); // Runtime Memory Set

        if ($storeInCache) {
            if (!$entity instanceof CacheableEntityInterface) {
                throw new \LogicException(static::class . ' requires CacheableEntityInterface');
            }

            try {
                $cacheTtl = $cacheTtl > 0 ? $cacheTtl : $this->entityCacheTtl;
                $this->module->memoryCache->storeInCache($entityId, $entity->returnCacheableObject(), $cacheTtl > 0 ? $cacheTtl : null);
                $storedInCache = true;
            } catch (CacheException $e) {
                trigger_error(static::class . ' caught CacheException', E_USER_WARNING);
                $this->module->app->lifecycle->exception(
                    new \RuntimeException(static::class . ' caught CacheException', previous: $e),
                );
            }
        }

        return $this->invokeStorageHooks($entity, EntitySource::DATABASE, $storedInCache ?? false);
    }
}