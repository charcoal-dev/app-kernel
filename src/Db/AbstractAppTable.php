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
use Charcoal\Database\ORM\OrmFetchQuery;
use Charcoal\Database\Queries\DbExecutedQuery;
use Charcoal\Database\Queries\SortFlag;
use Charcoal\OOP\Vectors\StringVector;

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
        protected readonly BaseModule $module,
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
     * @return \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function findByCol(string $col, int|string $value): AbstractAppObject
    {
        /** @var \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject */
        return $this->queryFind("WHERE `" . $col . "`=?", [$value], limit: 1)->getNext();
    }

    /**
     * @param string $whereQuery
     * @param array|null $whereData
     * @param array|null $selectColumns
     * @param \Charcoal\Database\Queries\SortFlag|null $sort
     * @param string|null $sortColumn
     * @param int $offset
     * @param int $limit
     * @param bool $lock
     * @return \Charcoal\Database\ORM\OrmFetchQuery
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function select(
        string    $whereQuery = "1",
        array     $whereData = null,
        ?array    $selectColumns = null,
        ?SortFlag $sort = null,
        ?string   $sortColumn = null,
        int       $offset = 0,
        int       $limit = 0,
        bool      $lock = false
    ): OrmFetchQuery
    {
        return $this->queryFind($whereQuery, $whereData, $selectColumns, $sort, $sortColumn, $offset, $limit, $lock);
    }

    /**
     * @param array $changes
     * @param int|string $primaryValue
     * @param string|null $primaryColumn
     * @return \Charcoal\Database\Queries\DbExecutedQuery
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function update(
        array      $changes,
        int|string $primaryValue,
        ?string    $primaryColumn = null
    ): DbExecutedQuery
    {
        return $this->queryUpdate($changes, $primaryValue, $primaryColumn);
    }

    /**
     * @param object|array $model
     * @param bool $ignoreDuplicate
     * @return \Charcoal\Database\Queries\DbExecutedQuery
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function insert(
        object|array $model,
        bool         $ignoreDuplicate = false
    ): DbExecutedQuery
    {
        return parent::queryInsert($model, $ignoreDuplicate);
    }

    /**
     * @param object|array $model
     * @param \Charcoal\OOP\Vectors\StringVector $updateCols
     * @return \Charcoal\Database\Queries\DbExecutedQuery
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function save(object|array $model, StringVector $updateCols): DbExecutedQuery
    {
        return $this->querySave($model, $updateCols);
    }

    /**
     * @param string $whereQuery
     * @param array $whereData
     * @return \Charcoal\Database\Queries\DbExecutedQuery
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function delete(string $whereQuery = "WHERE ...", array $whereData = []): DbExecutedQuery
    {
        return $this->queryDelete($whereQuery, $whereData);
    }

    /**
     * @param int|string $value
     * @return \Charcoal\Database\Queries\DbExecutedQuery
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    public function deleteWithPrimaryKey(int|string $value): DbExecutedQuery
    {
        return $this->queryDeletePrimaryKey($value);
    }
}
