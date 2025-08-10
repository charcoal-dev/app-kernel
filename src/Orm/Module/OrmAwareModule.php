<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Module;

use Charcoal\App\Kernel\Build\AppBuildPartial;
use Charcoal\App\Kernel\Cache\CacheStoreOperationsTrait;
use Charcoal\App\Kernel\Container\AppAwareContainer;
use Charcoal\App\Kernel\Container\Traits\RuntimeCacheOwnerTrait;
use Charcoal\App\Kernel\Contracts\Cache\CacheStoreOperationsInterface;
use Charcoal\App\Kernel\Contracts\Cache\RuntimeCacheOwnerInterface;
use Charcoal\App\Kernel\Contracts\Orm\Module\CacheStoreAwareInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\App\Kernel\Orm\Repository\OrmAwareRepository;
use Charcoal\Cipher\Cipher;
use Charcoal\Semaphore\FilesystemSemaphore;

/**
 * Class OrmAwareModule
 * @package Charcoal\App\Kernel\Orm
 */
abstract class OrmAwareModule extends AppAwareContainer implements
    RuntimeCacheOwnerInterface,
    CacheStoreOperationsInterface
{
    use RuntimeCacheOwnerTrait;
    use CacheStoreOperationsTrait;

    /**
     * @param AppBuildPartial $app
     * @throws \ReflectionException
     */
    protected function __construct(AppBuildPartial $app)
    {
        $this->declareDatabaseTables($app->database->tables);

        if ($this instanceof CacheStoreAwareInterface) {
            $this->initializeCacheStoreAwareContainer();
        }

        parent::__construct();
    }

    abstract protected function declareDatabaseTables(TableRegistry $tables): void;

    abstract public function getCipherFor(OrmAwareRepository $resolveFor): ?Cipher;

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
        if ($value instanceof OrmAwareRepository) {
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
        if ($this->$childPropertyKey instanceof OrmAwareRepository) {
            $this->$childPropertyKey->resolveDatabaseTable();
            return;
        }

        parent::bootstrapChildren($childPropertyKey);
    }
}