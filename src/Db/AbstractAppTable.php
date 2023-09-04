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

use Charcoal\Apps\Kernel\Modules\Components\AbstractOrmComponent;
use Charcoal\Database\Database;
use Charcoal\Database\ORM\AbstractOrmTable;
use Charcoal\Database\Queries\DbExecutedQuery;

/**
 * Class AbstractAppTable
 * @package Charcoal\Apps\Kernel\Db
 */
abstract class AbstractAppTable extends AbstractOrmTable
{
    /**
     * @param \Charcoal\Apps\Kernel\Modules\Components\AbstractOrmComponent $component
     * @param string $dbInstanceKey
     */
    public function __construct(
        private readonly AbstractOrmComponent $component,
        public readonly string                $dbInstanceKey
    )
    {
        parent::__construct();
        $this->dbInstance = $this->resolveDbInstance();
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["dbInstanceKey"] = $this->dbInstanceKey;
        return $data;
    }

    /**
     * @param array $object
     * @return void
     */
    public function __unserialize(array $object): void
    {
        $this->dbInstanceKey = $object["dbInstanceKey"];
        parent::__unserialize($object);
        $this->dbInstance = $this->resolveDbInstance();
    }

    /**
     * @param \Charcoal\Database\Database|null $dbArg
     * @return \Charcoal\Database\Database
     */
    protected function resolveDbInstance(?Database $dbArg = null): Database
    {
        if ($dbArg) {
            return $dbArg;
        }

        if ($this->dbInstance) {
            return $this->dbInstance;
        }

        $this->dbInstance = $this->component->module->app->kernel->db->getDb($this->dbInstanceKey);
        return $this->dbInstance;
    }

    /**
     * @param object|array $model
     * @param bool $ignoreDuplicate
     * @param \Charcoal\Database\Database|null $db
     * @return \Charcoal\Database\Queries\DbExecutedQuery
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function insert(object|array $model, bool $ignoreDuplicate = false, ?Database $db = null): DbExecutedQuery
    {
        return $this->queryInsert($model, $ignoreDuplicate, $db);
    }

    /**
     * @param string $col
     * @param int|string $value
     * @return \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function findByCol(string $col, int|string $value): \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject
    {
        /** @var \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject */
        return $this->queryFind("WHERE `" . $col . "`=?", [$value], limit: 1)->getNext();
    }
}
