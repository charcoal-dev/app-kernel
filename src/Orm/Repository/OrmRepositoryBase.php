<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;
use Charcoal\App\Kernel\Orm\Db\OrmTableBase;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Module\OrmModuleBase;
use Charcoal\App\Kernel\Orm\Repository\Traits\EntityFetchTrait;
use Charcoal\App\Kernel\Orm\Repository\Traits\StorageHooksInvokerTrait;
use Charcoal\Base\Enums\ExceptionAction;
use Charcoal\Base\Traits\ControlledSerializableTrait;

/**
 * Class OrmRepositoryBase
 * @package Charcoal\App\Kernel\Orm\Repository
 */
abstract class OrmRepositoryBase
{
    public readonly OrmTableBase $table;

    protected int $entityCacheTtl = 86400;
    protected int $entityChecksumIterations = 0x64;

    use StorageHooksInvokerTrait;
    use ControlledSerializableTrait;
    use EntityFetchTrait;

    /**
     * @param OrmModuleBase $module
     * @param TableRegistryEnumInterface $dbTableEnum
     * @param ExceptionAction $onCacheException
     * @param bool $serializeTable
     */
    public function __construct(
        protected readonly OrmModuleBase              $module,
        protected readonly TableRegistryEnumInterface $dbTableEnum,
        public readonly ExceptionAction               $onCacheException = ExceptionAction::Log,
        public readonly bool                          $serializeTable = true,
    )
    {
    }

    /**
     * Repository specific serialization data
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data["table"] = $this->serializeTable ? $this->table : null;
        $data["dbTableEnum"] = $this->dbTableEnum;
        $data["module"] = $this->module;
        $data["entityCacheTtl"] = $this->entityCacheTtl;
        $data["entityChecksumIterations"] = $this->entityChecksumIterations;
        $data["onCacheException"] = $this->onCacheException;
        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->module = $data["module"];
        $this->table = $data["table"];
        $this->dbTableEnum = $data["dbTableEnum"];
        $this->entityChecksumIterations = $data["entityChecksumIterations"];
        $this->entityCacheTtl = $data["entityCacheTtl"];
        $this->onCacheException = $data["onCacheException"];
    }

    /**
     * @return void
     */
    public function resolveDatabaseTable(): void
    {
        if (!isset($this->table)) {
            $this->table = $this->module->app->database->tables->resolve($this->dbTableEnum);
        }
    }

    /**
     * @param OrmEntityBase|string|int $entity
     * @return void
     * @throws \Charcoal\Cache\Exceptions\CacheDriverException
     * @api
     */
    protected function deleteFromCache(OrmEntityBase|string|int $entity): void
    {
        $this->module->deleteFromCache($entity instanceof OrmEntityBase ?
            $this->getStorageKeyFor($entity) : $this->getStorageKey($entity));
    }

    /**
     * @param OrmEntityBase $entity
     * @return string
     */
    protected function getStorageKeyFor(OrmEntityBase $entity): string
    {
        $entityClass = $this->table->entityClass;
        if (!$entity instanceof $entityClass) {
            throw new \BadMethodCallException("Cannot get storage key for " .
                $entity::class . " from " . static::class);
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
    private function getStorageKey(int|string $primaryId): string
    {
        return $this->table->name . ":" . $primaryId;
    }
}