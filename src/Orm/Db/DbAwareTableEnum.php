<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

/**
 * Interface DbAwareTableEnum
 * @package Charcoal\App\Kernel\Orm\Db
 */
interface DbAwareTableEnum
{
    public function getTableName(): string;

    public function getDatabase(): DatabaseEnum;
}