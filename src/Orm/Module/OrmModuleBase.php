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
use Charcoal\App\Kernel\Domain\AbstractModule;
use Charcoal\App\Kernel\Contracts\Cache\CacheStoreOperationsInterface;
use Charcoal\App\Kernel\Contracts\Cache\RuntimeCacheOwnerInterface;
use Charcoal\App\Kernel\Contracts\Orm\Module\CacheStoreAwareInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\App\Kernel\Orm\Repository\OrmRepositoryBase;
use Charcoal\Cipher\Cipher;
use Charcoal\Semaphore\Filesystem\FilesystemSemaphore;

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
     * @throws \ReflectionException
     */
    protected function __construct(AbstractApp $app)
    {
        $this->declareDatabaseTables($app->database->tables);

        if ($this instanceof CacheStoreAwareInterface) {
            $this->initializeCacheStoreAwareContainer();
        }

        parent::__construct();
    }

    abstract protected function declareDatabaseTables(TableRegistry $tables): void;

    abstract public function getCipherFor(OrmRepositoryBase $resolveFor): ?Cipher;

    abstract public function getSemaphore(): FilesystemSemaphore;

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
     * Modify parents method to provide support for AbstractOrmRepository
     * @param mixed $value
     * @return bool
     */
    protected function inspectIncludeChild(mixed $value): bool
    {
        if ($value instanceof OrmRepositoryBase) {
            return true;
        }

        return parent::inspectIncludeChild($value);
    }

    /**
     * Modify parents method to provide support for AbstractOrmRepository
     * @param string $childPropertyKey
     * @return void
     */
    protected function bootstrapChildren(string $childPropertyKey): void
    {
        if ($this->$childPropertyKey instanceof OrmRepositoryBase) {
            $this->$childPropertyKey->resolveDatabaseTable();
            return;
        }

        parent::bootstrapChildren($childPropertyKey);
    }
}