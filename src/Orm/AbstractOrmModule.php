<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\Build\AppBuildPartial;
use Charcoal\App\Kernel\Container\AppAwareContainer;
use Charcoal\App\Kernel\Orm\Db\DatabaseTableRegistry;
use Charcoal\App\Kernel\Orm\Module\EntityRuntimeCache;
use Charcoal\Cache\Cache;
use Charcoal\Cipher\Cipher;

/**
 * Class AbstractOrmModule
 * @package Charcoal\App\Kernel\Orm
 */
abstract class AbstractOrmModule extends AppAwareContainer
{
    public readonly EntityRuntimeCache $entities;

    /**
     * @param AppBuildPartial $app
     */
    final public function __construct(AppBuildPartial $app)
    {
        $this->declareDatabaseTables($app->databases->orm);
        $this->declareChildren($app);
        $this->entities = new EntityRuntimeCache($this);
        parent::__construct();
    }

    abstract protected function declareChildren(AppBuildPartial $app): void;

    abstract protected function declareDatabaseTables(DatabaseTableRegistry $tables): void;

    abstract public function getCipher(AbstractOrmRepository $resolveFor): ?Cipher;

    abstract public function getCacheStore(): ?Cache;

    /**
     * Automatically include children instances of AbstractOrmRepository
     * @param string $property
     * @param mixed $value
     * @return void
     */
    protected function inspectIncludeChild(string $property, mixed $value): void
    {
        if ($value instanceof AbstractOrmRepository) {
            $this->containerChildrenMap[] = $property;
            return;
        }

        parent::inspectIncludeChild($property, $value);
    }

    /**
     * Automatically bootstrap children instances of AbstractOrmRepository
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
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = parent::collectSerializableData();
        $data["entities"] = null;
        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    protected function onUnserialize(array $data): void
    {
        parent::onUnserialize($data);
        /** @noinspection PhpSecondWriteToReadonlyPropertyInspection Property is undefined here */
        $this->entities = new EntityRuntimeCache($this);
    }
}