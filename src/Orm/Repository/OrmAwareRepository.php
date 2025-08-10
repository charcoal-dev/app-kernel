<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;
use Charcoal\App\Kernel\Contracts\StorageHooks\StorageHooksInvokerTrait;
use Charcoal\App\Kernel\Orm\Db\AbstractOrmTable;
use Charcoal\App\Kernel\Orm\Entity\AbstractOrmEntity;
use Charcoal\App\Kernel\Orm\Module\OrmAwareModule;
use Charcoal\Base\Enums\ExceptionAction;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\Cipher\Cipher;
use Charcoal\OOP\Traits\ControlledSerializableTrait;

/**
 * Class AbstractOrmRepository
 * @package Charcoal\App\Kernel\Orm\Repository
 */
abstract class OrmAwareRepository
{
    public readonly AbstractOrmTable $table;

    protected int $entityCacheTtl = 86400;
    protected int $entityChecksumIterations = 0x64;
    private ?Cipher $cipher = null;

    use StorageHooksInvokerTrait;
    use ControlledSerializableTrait;
    use EntityFetchTrait;

    /**
     * @param OrmAwareModule $module
     * @param TableRegistryEnumInterface $dbTableEnum
     * @param ExceptionAction $onCacheException
     * @param bool $serializeTable
     */
    public function __construct(
        protected readonly OrmAwareModule             $module,
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
        $data["cipher"] = null;
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
        $this->cipher = null;
        $this->module = $data["module"];
        $this->table = $data["table"];
        $this->dbTableEnum = $data["dbTableEnum"];
        $this->entityChecksumIterations = $data["entityChecksumIterations"];
        $this->entityCacheTtl = $data["entityCacheTtl"];
        $this->onCacheException = $data["onCacheException"];

        if (isset($this->table)) {
            if (spl_object_id($this->table->module) !== spl_object_id($this->module)) {
                throw new \LogicException(static::class . " cannot be unserialized with different module");
            }
        }
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
     * @param AbstractOrmEntity|string|int $entity
     * @return void
     * @throws CacheException
     */
    protected function deleteFromCache(AbstractOrmEntity|string|int $entity): void
    {
        $this->module->deleteFromCache($entity instanceof AbstractOrmEntity ?
            $this->getStorageKeyFor($entity) : $this->getStorageKey($entity));
    }


    /**
     * @param AbstractOrmEntity $entity
     * @return string
     */
    protected function getStorageKeyFor(AbstractOrmEntity $entity): string
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