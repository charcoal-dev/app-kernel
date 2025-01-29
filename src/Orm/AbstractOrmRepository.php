<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\AppKernel;
use Charcoal\App\Kernel\Container\AppAwareContainer;
use Charcoal\App\Kernel\Entity\ChecksumAwareEntityInterface;
use Charcoal\App\Kernel\Orm\Db\AbstractOrmTable;
use Charcoal\App\Kernel\Orm\Db\DbAwareTableEnum;
use Charcoal\App\Kernel\Orm\Exception\EntityNotFoundException;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\App\Kernel\Orm\Repository\AbstractOrmEntity;
use Charcoal\App\Kernel\Orm\Repository\EntitySource;
use Charcoal\App\Kernel\Orm\Repository\StorageHooksInterface;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\Database\ORM\Exception\OrmException;
use Charcoal\Database\ORM\Exception\OrmModelNotFoundException;
use Charcoal\Database\Queries\LockFlag;

/**
 * Class AbstractOrmRepository
 * @package Charcoal\App\Kernel\Orm
 */
abstract class AbstractOrmRepository extends AppAwareContainer implements ChecksumAwareEntityInterface
{
    public readonly AbstractOrmTable $table;
    public readonly AbstractOrmModule $module;

    protected int $entityCacheTtl = 86400;

    protected function __construct(
        private string           $moduleClass,
        private DbAwareTableEnum $dbTableEnum,
        ?\Closure                $declareChildren
    )
    {
        parent::__construct($declareChildren);
    }

    /**
     * @param AppKernel $app
     * @return void
     */
    public function bootstrap(AppKernel $app): void
    {
        parent::bootstrap($app);
        /** @var AbstractOrmModule $module */
        $module = $this->app->getModule($this->moduleClass);
        $this->module = $module;
        $this->table = $this->app->databases->orm->resolve($this->dbTableEnum);
    }

    abstract protected function createEntityId(): string;

    /**
     * Repository specific serialization data
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = parent::collectSerializableData();
        $data["table"] = null;
        $data["module"] = null;
        $data["entityCacheTtl"] = $this->entityCacheTtl;
        $data["moduleClass"] = $this->moduleClass;
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
        $this->entityCacheTtl = $data["entityCacheTtl"];
        $this->moduleClass = $data["moduleClass"];
        $this->dbTableEnum = $data["dbTableEnum"];
        parent::onUnserialize($data);
    }

    /**
     * @param string $whereStmt
     * @param array $queryData
     * @param LockFlag|null $lock
     * @return AbstractOrmEntity
     * @throws \Charcoal\Database\ORM\Exception\OrmModelMapException
     * @throws \Charcoal\Database\ORM\Exception\OrmModelNotFoundException
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    protected function getFromDbTable(string $whereStmt, array $queryData = [], ?LockFlag $lock = null): AbstractOrmEntity
    {
        /** @var AbstractOrmEntity */
        return $this->table->queryFind($whereStmt, $queryData, limit: 1, lock: $lock)->getNext();
    }

    /**
     * @param string $column
     * @param int|string $value
     * @param LockFlag|null $lock
     * @return AbstractOrmEntity
     * @throws OrmModelNotFoundException
     * @throws \Charcoal\Database\ORM\Exception\OrmModelMapException
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
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

        try {
            $entity = $this->getFromDbTable($dbWhereStmt, $dbQueryData);
            $this->module->entities->storeInMemory($entityId, $entity); // Runtime Memory Set
        } catch (OrmModelNotFoundException) {
            throw new EntityNotFoundException();
        } catch (OrmException $e) {
            throw new EntityOrmException(static::class, $entityId, $e);
        }

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