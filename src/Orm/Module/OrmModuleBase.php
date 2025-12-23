<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Module;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Cache\Traits\CacheStoreOperationsTrait;
use Charcoal\App\Kernel\Cache\Traits\RuntimeCacheOwnerTrait;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreScopeEnumInterface;
use Charcoal\App\Kernel\Domain\AbstractModule;
use Charcoal\App\Kernel\Contracts\Cache\CacheStoreOperationsInterface;
use Charcoal\App\Kernel\Contracts\Cache\RuntimeCacheOwnerInterface;
use Charcoal\App\Kernel\Contracts\Orm\Module\CacheStoreAwareInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\App\Kernel\Orm\Repository\OrmRepositoryBase;
use Charcoal\App\Kernel\Orm\Repository\RepositoryCipherRef;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;
use Charcoal\Semaphore\Exceptions\SemaphoreLockException;

/**
 * Class OrmModuleBase
 * @package Charcoal\App\Kernel\Orm\Module
 */
abstract class OrmModuleBase extends AbstractModule implements
    RuntimeCacheOwnerInterface,
    CacheStoreOperationsInterface
{
    use RuntimeCacheOwnerTrait;
    use CacheStoreOperationsTrait;

    /**
     * @param AbstractApp $app
     */
    public function __construct(AbstractApp $app)
    {
        $this->declareDatabaseTables($app->database->tables);

        if ($this instanceof CacheStoreAwareInterface) {
            $this->initializeCacheStoreAwareContainer();
        }

        parent::__construct();
    }

    abstract protected function declareDatabaseTables(TableRegistry $tables): void;

    abstract public function getCipherFor(OrmRepositoryBase $repo): ?RepositoryCipherRef;

    abstract public function getSemaphore(): SemaphoreScopeEnumInterface|SemaphoreProviderEnumInterface;

    /**
     * @param string $lockId
     * @param float|null $checkInterval
     * @param int $waitTimeout
     * @return SemaphoreLockInterface
     * @throws SemaphoreLockException
     */
    public function getSemaphoreLock(
        string $lockId,
        ?float $checkInterval,
        int $waitTimeout
    ): SemaphoreLockInterface
    {
        return $this->app->security->semaphore->lock(
            $this->getSemaphore(),
            $lockId,
            $checkInterval,
            $waitTimeout
        );
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        if ($this instanceof CacheStoreAwareInterface) {
            $this->initializeCacheStoreAwareContainer();
        }

        parent::__unserialize($data);
    }

    /**
     * Modify parents method to provide support for OrmRepositoryBase
     * @param mixed $value
     * @return bool
     */
    final protected function inspectIncludeChild(mixed $value): bool
    {
        if ($value instanceof OrmRepositoryBase) {
            return true;
        }

        return parent::inspectIncludeChild($value);
    }

    /**
     * Modify parents method to provide support for OrmRepositoryBase
     * @param object $childObject
     * @return void
     */
    final protected function bootstrapChildren(object $childObject): void
    {
        parent::bootstrapChildren($childObject);

        if ($childObject instanceof OrmRepositoryBase) {
            $childObject->resolveDatabaseTable();
        }
    }
}