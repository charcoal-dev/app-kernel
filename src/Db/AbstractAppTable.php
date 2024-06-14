<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Db;

use Charcoal\Apps\Kernel\Modules\AbstractOrmModule;
use Charcoal\Apps\Kernel\Modules\BaseModule;
use Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject;
use Charcoal\Database\Database;
use Charcoal\Database\ORM\AbstractOrmTable;
use Charcoal\Database\Queries\LockFlag;

/**
 * Class AbstractAppTable
 * @package Charcoal\Apps\Kernel\Db
 */
abstract class AbstractAppTable extends AbstractOrmTable
{
    /**
     * @param \Charcoal\Apps\Kernel\Modules\BaseModule $module
     * @param string $dbInstanceKey
     * @param string $name
     */
    public function __construct(
        public readonly BaseModule $module,
        public readonly string        $dbInstanceKey,
        string                        $name,
    )
    {
        parent::__construct($name);
        if ($this->module instanceof AbstractOrmModule) {
            $this->module->tables->register($this->dbInstanceKey, $this);
        }
    }

    /**
     * @param array $row
     * @return \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject|null
     */
    abstract public function newChildObject(array $row): AbstractAppObject|null;

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["dbInstanceKey"] = $this->dbInstanceKey;
        $data["module"] = $this->module;
        return $data;
    }

    /**
     * @param array $object
     * @return void
     */
    public function __unserialize(array $object): void
    {
        $this->dbInstanceKey = $object["dbInstanceKey"];
        $this->module = $object["module"];
        parent::__unserialize($object);
    }

    /**
     * @param \Charcoal\Database\Database|null $dbArg
     * @return \Charcoal\Apps\Kernel\Db\AppDatabase
     */
    protected function resolveDbInstance(?Database $dbArg = null): Database
    {
        if ($dbArg) {
            return $dbArg;
        }

        if ($this->dbInstance) {
            return $this->dbInstance;
        }

        $this->dbInstance = $this->module->app->kernel->db->getDb($this->dbInstanceKey);
        return $this->dbInstance;
    }

    /**
     * @param string $col
     * @param int|string $value
     * @param \Charcoal\Database\Queries\LockFlag|null $lockFlag
     * @return \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function findByCol(string $col, int|string $value, ?LockFlag $lockFlag = null): AbstractAppObject
    {
        /** @var \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject */
        return $this->queryFind("WHERE `" . $col . "`=?", [$value], limit: 1, lock: $lockFlag)->getNext();
    }
}
