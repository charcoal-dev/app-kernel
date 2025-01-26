<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

/**
 * Interface DatabaseEnum
 * @package Charcoal\App\Kernel\Orm\Db
 */
interface DatabaseEnum
{
    public function getDatabaseKey(): string;
}