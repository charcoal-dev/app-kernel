<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

/**
 * Interface TableNameEnum
 * @package Charcoal\App\Kernel\Orm\Db
 */
interface TableNameEnum
{
    public function getTableName(): string;
}