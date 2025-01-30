<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm;

/**
 * Interface CacheStoreEnum
 * @package Charcoal\App\Kernel\Orm
 */
interface CacheStoreEnum
{
    public function getServerKey(): string;
}