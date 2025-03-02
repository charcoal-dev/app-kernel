<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\Contracts\StorageHooks\StorageHooksInvokerTrait;
use Charcoal\App\Kernel\Entity\EntitySource;
use Charcoal\App\Kernel\Module\AbstractModuleComponent;
use Charcoal\App\Kernel\Orm\Db\AbstractOrmTable;
use Charcoal\App\Kernel\Orm\Db\DbAwareTableEnum;
use Charcoal\App\Kernel\Orm\Entity\CacheableEntityInterface;
use Charcoal\App\Kernel\Orm\Exception\EntityNotFoundException;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\App\Kernel\Orm\Repository\AbstractOrmEntity;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\Database\ORM\Exception\OrmException;
use Charcoal\Database\ORM\Exception\OrmModelNotFoundException;
use Charcoal\Database\Queries\LockFlag;

/**
 * Class AbstractOrmRepository
 * @package Charcoal\App\Kernel\Orm
 */
abstract class AbstractOrmRepository extends AbstractModuleComponent
{
    public readonly AbstractOrmTable $table;

    protected int $entityCacheTtl = 86400;
    protected int $entityChecksumIterations = 0x64;

    use StorageHooksInvokerTrait;

    /**
     * @param AbstractOrmModule $module
     * @param DbAwareTableEnum $dbTableEnum
     */
    public function __construct(
        AbstractOrmModule        $module,
        private DbAwareTableEnum $dbTableEnum,
    )
    {
        parent::__construct($module);
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
     * Repository specific serialization data
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = parent::collectSerializableData();
        $data["table"] = null;
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
        parent::onUnserialize($data);
        $this->entityChecksumIterations = $data["entityChecksumIterations"];
        $this->entityCacheTtl = $data["entityCacheTtl"];
        $this->dbTableEnum = $data["dbTableEnum"];
    }

    /**
     * @param AbstractOrmEntity|string|int $entity
     * @return void
     * @throws CacheException
     */
    protected function cacheDeleteEntity(AbstractOrmEntity|string|int $entity): void
    {
        $this->module->memoryCache->deleteFromCache($entity instanceof AbstractOrmEntity ?
            $this->getStorageKeyFor($entity) : $this->getStorageKey($entity));
    }

    /**
     * @param string $whereStmt
     * @param array $queryData
     * @param LockFlag|null $lock
     * @param bool $invokeStorageHooks
     * @return AbstractOrmEntity|array
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    public function getFromDb(
        string    $whereStmt,
        array     $queryData = [],
        ?LockFlag $lock = null,
        bool      $invokeStorageHooks = true
    ): AbstractOrmEntity|array
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
     * @return AbstractOrmEntity|array
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getFromDbColumn(string $column, int|string $value, ?LockFlag $lock = null): AbstractOrmEntity|array
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
                trigger_error(static::class . ' caught CacheException', E_USER_NOTICE);
                $this->module->app->lifecycle->exception(
                    new \RuntimeException(static::class . ' caught CacheException', previous: $e),
                );
            }
        }

        $entity = $this->getFromDb($dbWhereStmt, $dbQueryData, invokeStorageHooks: false);
        $this->module->memoryCache->storeInMemory($entityId, $entity); // Runtime Memory Set

        if ($storeInCache && $entity instanceof $this->table->entityClass) {
            if (!$entity instanceof CacheableEntityInterface) {
                throw new \LogicException(static::class . ' requires CacheableEntityInterface');
            }

            try {
                $cacheTtl = max($cacheTtl, $this->entityCacheTtl);
                $this->module->memoryCache->storeInCache($entityId, $entity->returnCacheableObject(), $cacheTtl > 0 ? $cacheTtl : null);
                $storedInCache = true;
            } catch (CacheException $e) {
                trigger_error(static::class . ' caught CacheException', E_USER_NOTICE);
                $this->module->app->lifecycle->exception(
                    new \RuntimeException(static::class . ' caught CacheException', previous: $e),
                );
            }
        }

        return $this->invokeStorageHooks($entity, EntitySource::DATABASE, $storedInCache ?? false);
    }
}