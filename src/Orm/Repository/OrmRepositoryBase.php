<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Contracts\Domain\ModuleBindableInterface;
use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;
use Charcoal\App\Kernel\Domain\AbstractModule;
use Charcoal\App\Kernel\Orm\Db\OrmTableBase;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Module\OrmModuleBase;
use Charcoal\App\Kernel\Orm\Repository\Traits\EntityFetchTrait;
use Charcoal\App\Kernel\Orm\Repository\Traits\StorageHooksInvokerTrait;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Cache\Exceptions\CacheStoreOpException;
use Charcoal\Contracts\Errors\ExceptionAction;

/**
 * Abstract base class for ORM repository implementations.
 */
abstract class OrmRepositoryBase implements ModuleBindableInterface
{
    public readonly OrmTableBase $table;
    public readonly OrmModuleBase $module;

    protected int $entityCacheTtl = 86400;
    protected int $entityChecksumIterations = 0x64;

    use ControlledSerializableTrait;
    use StorageHooksInvokerTrait;
    use EntityFetchTrait;

    /**
     * @param TableRegistryEnumInterface $dbTableEnum
     * @param ExceptionAction $onCacheException
     */
    public function __construct(
        protected readonly TableRegistryEnumInterface $dbTableEnum,
        public readonly ExceptionAction               $onCacheException = ExceptionAction::Log,
    )
    {
    }

    /**
     * Repository specific serialization data
     * @return array
     */
    public function collectSerializableData(): array
    {
        $data["dbTableEnum"] = $this->dbTableEnum;
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
        $this->dbTableEnum = $data["dbTableEnum"];
        $this->entityChecksumIterations = $data["entityChecksumIterations"];
        $this->entityCacheTtl = $data["entityCacheTtl"];
        $this->onCacheException = $data["onCacheException"];
    }

    public function bootstrap(AbstractModule $module): void
    {
        $this->module = $module;
    }

    /**
     * @api
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
     * @throws CacheStoreOpException
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