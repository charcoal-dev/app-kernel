<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Contracts\Orm\Entity\CacheableEntityInterface;
use Charcoal\App\Kernel\Orm\Entity\AbstractOrmEntity;
use Charcoal\App\Kernel\Orm\Exception\EntityNotFoundException;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\Base\Enums\ExceptionAction;
use Charcoal\Base\Enums\FetchOrigin;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\Database\Exception\DatabaseException;
use Charcoal\Database\ORM\Exception\OrmException;
use Charcoal\Database\ORM\Exception\OrmModelNotFoundException;
use Charcoal\Database\Queries\LockFlag;
use Charcoal\Database\Queries\SortFlag;

/**
 * Trait EntityFetchTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityFetchTrait
{
    /**
     * @param string $whereStmt
     * @param array $queryData
     * @param LockFlag|null $lock
     * @param bool $invokeStorageHooks
     * @return AbstractOrmEntity|array
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getFromDb(
        string    $whereStmt,
        array     $queryData = [],
        ?LockFlag $lock = null,
        bool      $invokeStorageHooks = true
    ): AbstractOrmEntity|array
    {
        try {
            $entity = $this->table->queryFind($whereStmt, $queryData, limit: 1, lock: $lock)->getNext();
            return is_object($entity) && $invokeStorageHooks ?
                $this->invokeStorageHooks($entity, FetchOrigin::DATABASE) : $entity;
        } catch (OrmModelNotFoundException) {
            throw new EntityNotFoundException();
        } catch (OrmException $e) {
            throw new EntityOrmException(static::class, $e);
        }
    }

    /**
     * @param string $whereStmt
     * @param array $queryData
     * @param SortFlag|null $sort
     * @param string|null $sortColumn
     * @param int $offset
     * @param int $limit
     * @param LockFlag|null $lock
     * @return array|AbstractOrmEntity[]
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getMultipleFromDb(
        string    $whereStmt,
        array     $queryData = [],
        ?SortFlag $sort = null,
        ?string   $sortColumn = null,
        int       $offset = 0,
        int       $limit = 0,
        ?LockFlag $lock = null,
    ): array
    {
        try {
            return $this->table->queryFind($whereStmt, $queryData, null, $sort, $sortColumn,
                $offset, $limit, $lock)->getAll();
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
     * @param bool $invokeStorageHooks
     * @return AbstractOrmEntity|array
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getFromDbColumn(
        string     $column,
        int|string $value,
        ?LockFlag  $lock = null,
        bool       $invokeStorageHooks = true
    ): AbstractOrmEntity|array
    {
        return $this->getFromDb("`$column`=?", [$value], $lock, $invokeStorageHooks);
    }

    /**
     * @param string $column
     * @param int|string $value
     * @param string $idColumn
     * @return int
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getPrimaryIdFromUnique(string $column, int|string $value, string $idColumn = "id"): int
    {
        try {
            $entityId = (int)($this->table->getDb()->fetch(
                sprintf("SELECT `%s` FROM `%s` WHERE `%s`=? LIMIT 1", $idColumn, $this->table->name, $column),
                [$value]
            )->getNext()[$idColumn] ?? -1);
        } catch (DatabaseException $e) {
            throw new EntityOrmException(static::class, $e);
        }

        if ($entityId > 0) {
            return $entityId;
        }

        throw new EntityNotFoundException();
    }

    /**
     * @param CacheException $e
     * @return void
     * @throws EntityOrmException
     */
    protected function handleCacheException(CacheException $e): void
    {
        if ($this->onCacheException === ExceptionAction::Throw) {
            throw new EntityOrmException(static::class, $e);
        } elseif ($this->onCacheException === ExceptionAction::Log) {
            trigger_error(static::class . ' caught CacheException', E_USER_NOTICE);
            $this->module->app->lifecycle->exception(
                new \RuntimeException(static::class . ' caught CacheException', previous: $e),
            );
        }
    }

    /**
     * @param int|string $primaryId
     * @param bool $useCacheStore
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
        bool       $useCacheStore,
        string     $dbWhereStmt,
        array      $dbQueryData,
        bool       $storeInCache,
        int        $cacheTtl = 0,
    ): AbstractOrmEntity
    {
        $entityId = $this->getStorageKey($primaryId);
        $entity = $this->module->runtimeMemory->get($entityId);
        if ($entity instanceof AbstractOrmEntity) {
            return $this->invokeStorageHooks($entity, FetchOrigin::RUNTIME);
        }

        if ($useCacheStore) {
            try {
                $entity = $this->module->getFromCache($entityId);
                if ($entity instanceof AbstractOrmEntity) {
                    $this->module->runtimeMemory->store($entityId, $entity);
                    return $this->invokeStorageHooks($entity, FetchOrigin::CACHE);
                }
            } catch (CacheException $e) {
                $this->handleCacheException($e);
            }
        }

        $entity = $this->getFromDb($dbWhereStmt, $dbQueryData, invokeStorageHooks: false);
        $this->module->runtimeMemory->store($entityId, $entity); // Runtime Memory Set

        if ($storeInCache && $entity instanceof $this->table->entityClass) {
            if (!$entity instanceof CacheableEntityInterface) {
                throw new \LogicException(static::class . ' requires CacheableEntityInterface');
            }

            try {
                $cacheTtl = max($cacheTtl, $this->entityCacheTtl);
                $this->module->storeInCache($entityId, $entity->getCacheableClone(), $cacheTtl > 0 ? $cacheTtl : null);
                $storedInCache = true;
            } catch (CacheException $e) {
                $this->handleCacheException($e);
            }
        }

        return $this->invokeStorageHooks($entity, FetchOrigin::DATABASE, $storedInCache ?? false);
    }
}