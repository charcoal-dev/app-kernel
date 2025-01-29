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

    protected function __construct(AppBuildPartial $app, \Closure $declareChildren)
    {
        $this->declareDatabaseTables($app->databases->orm);
        parent::__construct($declareChildren);
        $this->entities = new EntityRuntimeCache($this);
    }

    abstract protected function declareDatabaseTables(DatabaseTableRegistry $tables): void;

    abstract public function getCipher(AbstractOrmRepository $resolveFor): ?Cipher;

    abstract public function getCacheStore(): ?Cache;

    protected function collectSerializableData(): array
    {
        $data = parent::collectSerializableData();
        $data["entities"] = null;
        return $data;
    }

    protected function onUnserialize(array $data): void
    {
        parent::onUnserialize($data);
        /** @noinspection PhpSecondWriteToReadonlyPropertyInspection Property is undefined here */
        $this->entities = new EntityRuntimeCache($this);
    }
}