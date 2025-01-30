<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\Orm\Db\AbstractOrmTable;
use Charcoal\App\Kernel\Orm\Db\DbAwareTableEnum;
use Charcoal\App\Kernel\Orm\Entity\StorageHooksInterface;
use Charcoal\App\Kernel\Orm\Exception\EntityNotFoundException;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\App\Kernel\Orm\Repository\AbstractOrmEntity;
use Charcoal\App\Kernel\Orm\Repository\EntitySource;
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

    protected function __construct(
        public readonly AbstractOrmModule $module,
        private DbAwareTableEnum          $dbTableEnum,
    )
    {
    }

    /**
     * Returns a unique ID for entity that is used key for storing in runtime memory and cache
     * @param AbstractOrmEntity|array $entity
     * @return string
     */
    abstract public function getEntityId(AbstractOrmEntity|array $entity): string;

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
        $this->module->entities->deleteFromCache($this->getEntityId($entity));
    }

    /**
     * @param string $whereStmt
     * @param array $queryData
     * @param LockFlag|null $lock
     * @return AbstractOrmEntity
     * @throws EntityNotFoundException
     * @throws EntityOrmException
     */
    protected function getFromDbTable(string $whereStmt, array $queryData = [], ?LockFlag $lock = null): AbstractOrmEntity
    {
        try {
            /** @var AbstractOrmEntity */
            return $this->table->queryFind($whereStmt, $queryData, limit: 1, lock: $lock)->getNext();
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
    protected function getFromDb(string $column, int|string $value, ?LockFlag $lock = null): AbstractOrmEntity
    {
        return $this->getFromDbTable("`$column`=?", [$value], $lock);
    }

    /**
     * @param string $entityId
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
        string $entityId,
        bool   $checkInCache,
        string $dbWhereStmt,
        array  $dbQueryData,
        bool   $storeInCache,
        int    $cacheTtl = 0,
    ): AbstractOrmEntity
    {
        $entity = $this->module->entities->getFromMemory($entityId);
        if ($entity) {
            return $this->returnEntityObject($entity, EntitySource::RUNTIME, false);
        }

        if ($checkInCache) {
            try {
                $entity = $this->module->entities->getFromCache($entityId);
                if ($entity) {
                    $this->module->entities->storeInMemory($entityId, $entity); // Runtime Memory Set
                    return $this->returnEntityObject($entity, EntitySource::CACHE, false);
                }
            } catch (CacheException $e) {
                trigger_error(static::class . ' caught CacheException', E_USER_WARNING);
                $this->module->app->lifecycle->exception(
                    new \RuntimeException(static::class . ' caught CacheException', previous: $e),
                );
            }
        }

        $entity = $this->getFromDbTable($dbWhereStmt, $dbQueryData);
        $this->module->entities->storeInMemory($entityId, $entity); // Runtime Memory Set

        if ($storeInCache) {
            try {
                $cacheTtl = $cacheTtl > 0 ? $cacheTtl : $this->entityCacheTtl;
                $this->module->entities->storeInCache($entityId, $entity, $cacheTtl > 0 ? $cacheTtl : null);
                $storedInCache = true;
            } catch (CacheException $e) {
                trigger_error(static::class . ' caught CacheException', E_USER_WARNING);
                $this->module->app->lifecycle->exception(
                    new \RuntimeException(static::class . ' caught CacheException', previous: $e),
                );
            }
        }

        return $this->returnEntityObject($entity, EntitySource::DATABASE, $storedInCache ?? false);
    }

    /**
     * @param AbstractOrmEntity $entity
     * @param EntitySource $source
     * @param bool $storedInCache
     * @return AbstractOrmEntity
     */
    private function returnEntityObject(
        AbstractOrmEntity $entity,
        EntitySource      $source,
        bool              $storedInCache,
    ): AbstractOrmEntity
    {
        // Invoke StorageHooksInterface
        if ($entity instanceof StorageHooksInterface) {
            $lifecycleEntry = $entity->onRetrieve($source);
            if ($lifecycleEntry) {
                $this->module->app->lifecycle->log($lifecycleEntry, $source->value, true);
            }

            if ($storedInCache) {
                $lifecycleEntry = $entity->onCacheStore();
                if ($lifecycleEntry) {
                    $this->module->app->lifecycle->log($lifecycleEntry, null, true);
                }
            }
        }

        return $entity;
    }
}