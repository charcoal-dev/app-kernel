<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

use Charcoal\App\Kernel\Orm\AbstractOrmModule;
use Charcoal\App\Kernel\Orm\Entity\AbstractOrmEntity;
use Charcoal\Database\Database;

/**
 * Class AbstractOrmTable
 * @package Charcoal\App\Kernel\Orm\Db
 */
abstract class AbstractOrmTable extends \Charcoal\Database\ORM\AbstractOrmTable
{
    public readonly DbAwareTableEnum $enum;

    public function __construct(
        public readonly AbstractOrmModule $module,
        DbAwareTableEnum                  $dbTableEnum,
    )
    {
        $this->enum = $dbTableEnum;
        parent::__construct($this->enum->getTableName());
    }

    abstract public function newChildObject(array $row): AbstractOrmEntity|null;

    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["module"] = $this->module;
        $data["enum"] = $this->enum;
        return $data;
    }

    public function __unserialize(array $object): void
    {
        $this->module = $object["module"];
        $this->enum = $object["enum"];
        parent::__unserialize($object);
    }

    protected function resolveDbInstance(?Database $dbArg = null): Database
    {
        if ($dbArg) {
            return $dbArg;
        }

        if ($this->dbInstance) {
            return $this->dbInstance;
        }

        $this->dbInstance = $this->module->app->databases->getDb($this->enum->getDatabase()->getDatabaseKey());
        return $this->dbInstance;
    }
}