<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

use Charcoal\App\Kernel\Container\AppAwareComponent;
use Charcoal\App\Kernel\Orm\Db\DatabaseTableRegistry;
use Charcoal\App\Kernel\Orm\Entity\EntityRepository;

/**
 * Class AbstractOrmModule
 * @package Charcoal\App\Kernel\Orm
 */
abstract class AbstractOrmModule extends AppAwareComponent
{
    protected readonly EntityRepository $entities;

    protected function __construct(DatabaseTableRegistry $registry, \Closure $declareChildren)
    {
        $this->declareTables($registry);
        parent::__construct($declareChildren);
        $this->entities = new EntityRepository($this);
    }

    abstract protected function declareTables(DatabaseTableRegistry $tables): void;

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
        $this->entities = new EntityRepository($this);
    }
}