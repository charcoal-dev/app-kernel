<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\AppKernel;
use Charcoal\App\Kernel\Container\AppAwareContainer;
use Charcoal\App\Kernel\Orm\Db\AbstractOrmTable;
use Charcoal\App\Kernel\Orm\Db\DbAwareTableEnum;

/**
 * Class AbstractOrmComponent
 * @package Charcoal\App\Kernel\Orm
 */
abstract class AbstractOrmComponent extends AppAwareContainer
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

    protected function getEntity(
        string $identifier,
        bool   $checkInCache,
        string $dbQuery,
        array  $dbQueryData = [],
        bool   $storeInCache = true,
        int    $cacheTtl = 0
    )
    {
        $cacheTtl = $cacheTtl > 0 ? $cacheTtl : $this->entityCacheTtl;
    }

    protected function storeInCache(int $cacheTtl): void
    {

    }

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

    protected function onUnserialize(array $data): void
    {
        $this->entityCacheTtl = $data["entityCacheTtl"];
        $this->moduleClass = $data["moduleClass"];
        $this->dbTableEnum = $data["dbTableEnum"];
        parent::onUnserialize($data);
    }
}