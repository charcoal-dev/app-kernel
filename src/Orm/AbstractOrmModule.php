<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\Build\AppBuildPartial;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Module\AbstractModuleComponent;
use Charcoal\App\Kernel\Module\CacheAwareModule;
use Charcoal\App\Kernel\Orm\Db\DatabaseTableRegistry;
use Charcoal\Cipher\Cipher;
use Charcoal\Semaphore\AbstractSemaphore;

/**
 * Class AbstractOrmModule
 * @package Charcoal\App\Kernel\Orm
 */
abstract class AbstractOrmModule extends CacheAwareModule
{
    /**
     * @param AppBuildPartial $app
     * @param CacheStoreEnumInterface|null $cacheStoreEnum
     */
    protected function __construct(AppBuildPartial $app, ?CacheStoreEnumInterface $cacheStoreEnum)
    {
        $this->declareDatabaseTables($app->databases->orm);
        $this->declareChildren($app);
        parent::__construct($cacheStoreEnum);
    }

    abstract protected function declareChildren(AppBuildPartial $app): void;

    abstract protected function declareDatabaseTables(DatabaseTableRegistry $tables): void;

    abstract public function getCipher(AbstractModuleComponent $resolveFor): ?Cipher;

    /**
     * Modify parents method to provide automatic support for AbstractOrmRepository
     * @param mixed $value
     * @return bool
     */
    protected function inspectIncludeChild(mixed $value): bool
    {
        if ($value instanceof AbstractModuleComponent) {
            return true;
        }

        return parent::inspectIncludeChild($value);
    }

    /**
     * Modify parents method to provide automatic support for AbstractOrmRepository
     * @param string $childPropertyKey
     * @return void
     */
    protected function bootstrapChildren(string $childPropertyKey): void
    {
        if ($this->$childPropertyKey instanceof AbstractOrmRepository) {
            $this->$childPropertyKey->resolveDatabaseTable();
            return;
        }

        parent::bootstrapChildren($childPropertyKey);
    }

    /**
     * Override method below to provide module with AbstractSemaphore channel
     * @return AbstractSemaphore
     */
    public function getSemaphore(): AbstractSemaphore
    {
        throw new \RuntimeException(static::class . ' does not have semaphore channel linked');
    }
}